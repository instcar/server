<?Php

/**
  *Author : Chen Haichao
  *Time   : 2014/04/08
  *
  *Brief  : api for room
  */

namespace Instcar\Server\Controllers;

use Instcar\Server\Models\Room as RoomModel;
use Instcar\Server\Models\RoomUser as RoomUserModel;
use Instcar\Server\Models\User as UserModel;
use Instcar\Server\Models\Line as LineModel;

/**
 *房间的接口分为四大类
 *1.创建接口
 *2.修改接口
 *3.销毁接口
 *4.数据显示接口
 *
 *我们定义了房间的状态:
 *status = 0 初始状态
 *status = 1 接收预定状态
 *status = 2 运行状态
 *status = 3 销毁状态
 *
 *注意，司机一共最多能创建6个房间
 *每天仅能创建两个房间
 *
 *
 */

class RoomController extends ControllerBase
{
    /// 司机创建房间
    public function createAction()
    {
        /// 检查参数
        $user_id = trim($this->request->getPost('user_id'));
        if (empty($user_id))
        {
          $this->flashJson(500, array(), "用户id不能为空");
        }

        /// 确认user_id存在性
        $user_info = UserModel::findFirst("id='{$user_id}'");
        if ($user_info == false)
        {
            $this->flashJson(500, array(), "用户id非法，不存在");
        }
        $phone = $user_info->phone;   

        /// TODO 确认lind_id 存在性
        $line_id = trim($this->request->getPost('line_id'));
        if (empty($line_id))
        {
          $this->flashJson(500, array(), "路线id不能为空");
        }

        $price = $this->request->getPost('price');
        if (empty($price))
        {
          $this->flashJson(500, array(), "价格不能为空");
        }

        $description = $this->request->getPost('description');
        if (empty($description))
        {
          $this->flashJson(500, array(), "描述不能为空");
        }

        $start_time = $this->request->getPost('start_time');
        if (empty($start_time))
        {
          $this->flashJson(500, array(), "启动时间不能为空");
        }

        $max_seat_num = $this->request->getPost('max_seat_num');
        if (empty($max_seat_num))
        {
          $this->flashJson(500, array(), "最大座位数不能为空");
        }

        /// 查看司机已经创建的房间数目
        $created_rooms = RoomModel::find("user_id='{$user_id}'");
        $created_rooms_count = count($created_rooms);
//        if ($created_rooms_count > 6)
//        {
//            $this->flashJson(500, array(), "该用户总共创建房间数量已达上限");
//        }

        /// 一天之中不能创建大于两个
        $today = date('Y-m-d 00:00:00'); 
        $created_rooms_today = RoomModel::find("user_id={$user_id} and addtime>'{$today}'");
        $created_rooms_today_count = count($created_rooms_today);
//        if ($created_rooms_today_count >= 2)
//        {
//            $this->flashJson(500, array(), "该用户单日创建房间数量已达上限");
//        }
 
        $room_number = $created_rooms_count + 1;
        
        $conn = new \XMPPHP_ChcXmppClient();
        $ret = true;    
        $ret = $conn->init('115.28.231.132', 13000, $phone,
                        '123456', 'ay140222164105110546z');
        $ret = $conn->createRoom("{$user_id}_{$room_number}");
        if ($ret == false)
        {
            $this->flashJson(500, array(), "openfire 生成房间失败");
        }
        $openfire_room_name = "{$user_id}_{$room_number}@conference.ay140222164105110546z";
       
        /// 插入room表
        $room = new RoomModel();
        $room->openfire = $openfire_room_name;
        $room->user_id = $user_id;
        $room->line_id = $line_id;
        $room->price = $price;
        /// 默认状态是准备状态
        $room->status = 0;
        $room->description = $description;
        $room->start_time = $start_time;
        $room->max_seat_num = $max_seat_num;
        $room->booked_seat_num = 0;
        $room->addtime = $room->modtime = date('Y-m-d H:i:s');

        if ($room->save() == false)
        {
          $errMsgs = array();
          foreach ($room->getMessages() as $message)
          {
              $errMsgs[] = $message->__toString();
          }
          $this->flashJson(500, array(), join("; ", $errMsgs));
        }
        
        /// 创建房间用户对应关系, 至少插入司机和房间的关系
        $room_user = new RoomUserModel();
        $room_user->room_id = $room->id;
        $room_user->user_id = $user_id;
        $room_user->status = "0";
        $room_user->is_owner = "1";

        if ($room_user->save() == false)
        {
          $errMsgs = array();
          foreach ($room->getMessages() as $message)
          {
              $errMsgs[] = $message->__toString();
          }
          $this->flashJson(500, array(), join("; ", $errMsgs));
        }

        $this->flashJson(200, array('id'=>$room->id, 
               'openfire'=>$openfire_room_name), "新增房间成功"); 
    }

    /// 司机关闭房间
    public function closeAction()
    {
        /// 获取房间id
        $room_id = trim($this->request->getPost('room_id'));
        if (empty($room_id))
        {
          $this->flashJson(500, array(), "room的id不能为空");
        }

        ///删除用户房间对应关系
        $room_users = RoomUserModel::find("room_id='{$room_id}'");
        if ($room_users)
        {
          foreach( $room_users as $room_user)
          {
              if ($room_user->delete() == false)
              {
                  $errMsgs = array();
                  foreach ($room_user->getMessages() as $message)
                  {
                      $errMsgs[] = $message->__toString();
                  }
                  $this->flashJson(500, array(), join("; ", $errMsgs));
              }
          }
        }
           
        ///获取房主id, 然后销毁房间
        $room = RoomModel::findFirst("id='{$room_id}'");
        if ($room == false)
        {
            $this->flashJson(500, array(), "room的id不存在");
        }
        $user_id = $room->user_id;
        $openfire = $room->openfire;

        $user_info = UserModel::findFirst("id='{$user_id}'");
        if ($user_info == false)
        {
            $this->flashJson(500, array(), "房主id信息查找失败");
        }
        $phone = $user_info->phone;

        /// 删除聊天房间
        $conn = new \XMPPHP_ChcXmppClient();
        $ret = true;    
        $ret = $conn->init('115.28.231.132', 13000, $phone, 
                           '123456', 'ay140222164105110546z');
        $ret = $conn->destroyRoom($openfire);
        if ($ret == false)
        {
            $this->flashJson(500, array(), "openfire 删除房间失败");
        }

        ///删除房间
        if ($room->delete() == false)
        {
          $errMsgs = array();
          foreach ($room->getMessages() as $message)
          {
              $errMsgs[] = $message->__toString();
          }
          $this->flashJson(500, array(), join("; ", $errMsgs));
        }

        $this->flashJson(200, array(), "删除房间成功");
    }

    /// 司机修改房间状态
    /// 等待->开始运行
    public function changeStateAction()
    {
        $room_id = trim($this->request->getPost('room_id'));
        if (empty($room_id))
        {
            $this->flashJson(500, array(), "room 的id不能为空");
        }

        $state = trim($this->request->getPost('state'));
        if (empty($state))
        {
            $this->flashJson(500, array(), "room 的state不能为空");
        }

        /// 查找并修改房间说明 
        $room = RoomModel::findFirst("id='{$room_id}'");
        if ($room == false)
        {
            $this->flashJson(500, array(), "room id不存在");
        }

        $room->state = $state;
        if ($room->save() == false)
        {
          $errMsgs = array();
          foreach ($room->getMessages() as $message)
          {
              $errMsgs[] = $message->__toString();
          }
          $this->flashJson(500, array(), join("; ", $errMsgs));
        }

       $this->flashJson(200, array(), "房间状态修改成功"); 
    }

    /// 司机修改出发时间
    public function changeStartTimeAction()
    {
        $room_id = trim($this->request->getPost('room_id'));
        if (empty($room_id))
        {
            $this->flashJson(500, array(), "room 的id不能为空");
        }

        $start_time = trim($this->request->getPost('start_time'));
        if (empty($start_time))
        {
            $this->flashJson(500, array(), "room 的start_time不能为空");
        }

        /// 查找并修改房间说明 
        $room = RoomModel::findFirst("id='{$room_id}'");
        if ($room == false)
        {
            $this->flashJson(500, array(), "room id不存在");
        }

        $room->start_time = $start_time;
        if ($room->save() == false)
        {
          $errMsgs = array();
          foreach ($room->getMessages() as $message)
          {
              $errMsgs[] = $message->__toString();
          }
          $this->flashJson(500, array(), join("; ", $errMsgs));
        }

       $this->flashJson(200, array(), "房间启动时间修改成功"); 
    }

    /// 司机修改房间说明
    public function changeRoomDescAction()
    {
        $room_id = trim($this->request->getPost('room_id'));
        if (empty($room_id))
        {
            $this->flashJson(500, array(), "room 的id不能为空");
        }

        $description = trim($this->request->getPost('description'));
        if (empty($description))
        {
            $this->flashJson(500, array(), "room 的description不能为空");
        }

        /// 查找并修改房间说明 
        $room = RoomModel::findFirst("id='{$room_id}'");
        if ($room == false)
        {
            $this->flashJson(500, array(), "room id不存在");
        }

        $room->description = $description;
        if ($room->save() == false)
        {
          $errMsgs = array();
          foreach ($room->getMessages() as $message)
          {
              $errMsgs[] = $message->__toString();
          }
          $this->flashJson(500, array(), join("; ", $errMsgs));
        }

       $this->flashJson(200, array(), "房间描述修改成功"); 
    }

    /// 司机修改最大座位数
    public function changeMaxSeatNumAction()
    {
        $room_id = trim($this->request->getPost('room_id'));
        if (empty($room_id))
        {
            $this->flashJson(500, array(), "room 的id不能为空");
        }

        $max_seat_num = trim($this->request->getPost('max_seat_num'));
        if (empty($max_seat_num))
        {
            $this->flashJson(500, array(), "room 的max_seat_num不能为空");
        }

        /// 查找并修改房间说明 
        $room = RoomModel::findFirst("id='{$room_id}'");
        if ($room == false)
        {
            $this->flashJson(500, array(), "room id不存在");
        }

        $room->max_seat_num = $max_seat_num;
        if ($room->save() == false)
        {
          $errMsgs = array();
          foreach ($room->getMessages() as $message)
          {
              $errMsgs[] = $message->__toString();
          }
          $this->flashJson(500, array(), join("; ", $errMsgs));
        }

       $this->flashJson(200, array(), "房间最大座位数修改成功"); 
    }

    /// 乘客预定位置
    public function bookAction()
    {
        $room_id = trim($this->request->getPost('room_id'));
        if (empty($room_id))
        {
            $this->flashJson(500, array(), "room的id不能为空");
        }

        /// 确认房间存在
        $room = RoomModel::findFirst("id='{$room_id}'");
        if ($room == false)
        {
            $this->flashJson(500, array(), "room id 不存在");
        }
        $openfire_room_id = $room->openfire;

        $user_id = trim($this->request->getPost('user_id'));
        if (empty($user_id))
        {
            $this->flashJson(500, array(), "user id 不能为空");
        }

        /// 确认用户存在
        $user_info = UserModel::findFirst("id='{$user_id}'");
        if ($user_info == false)
        {
            $this->flashJson(500, array(), "用户id非法，不存在");
        }

        /// 确认用户只加入一次
        $room_user = RoomUserModel::findFirst("room_id='{$room_id}' AND user_id='{$user_id}'");
        if ($room_user != false)
        {
            $this->flashJson(500, array(), "用户已加入该房间，不能重复加入");
        } 

        $room_user = new RoomUserModel();
        $room_user->room_id = $room_id;
        $room_user->user_id = $user_id;
        $room_user->status = "0";
        $room_user->is_owner = "0";

        if ($room_user->save() == false)
        {
            $errMsgs = array();
            foreach ($room_user->getMessages() as $message)
            {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));
        }

        /// 占用房间座位+1
        $room->booked_seat_num = $room->booked_seat_num + 1;
        if ($room->save() == false)
        {
            $this->flashJson(500, array(), "房间用户数+1失败");
        }

        $this->flashJson(200, array(), "预定成功");
    }

    /// 乘客退出预定位置
    public function unbookAction()
    {
        $room_id = trim($this->request->getPost('room_id'));
        if (empty($room_id))
        {
            $this->flashJson(500, array(), "room 的id不能为空");
        }
        
        /// 确认房间存在
        $room = RoomModel::findFirst("id='{$room_id}'");
        if ($room == false)
        {
            $this->flashJson(500, array(), "room id 不存在");
        }
        $openfire_room_id = $room->openfire;

        $user_id = trim($this->request->getPost('user_id'));
        if (empty($user_id))
        {
            $this->flashJson(500, array(), "user 的id不能为空");
        }
        /// 确认用户存在
        $user_info = UserModel::findFirst("id='{$user_id}'");
        if ($user_info == false)
        {
            $this->flashJson(500, array(), "user id非法，不存在");
        }

        $room_user = RoomUserModel::findFirst("room_id='{$room_id}' AND user_id='{$user_id}'");
        if ($room_user == false)
        {
            $this->flashJson(500, array(), "乘客及房间号对应关系不存在");
        }

        if ($room_user->delete() == false)
        {
            $errMsgs = array();
            foreach ($room_user->getMessages() as $message)
            {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));
        }

        /// 占用房间座位-1
        if ($room->booked_seat_num > 0)
        {
            $room->booked_seat_num = $room->booked_seat_num - 1;
            if ($room->save() == false)
            {
                $this->flashJson(500, array(), "房间用户数-1失败");
            }
        }

        $this->flashJson(200, array(), "取消预定成功"); 
    }

    /// 查询某条线路的房间列表
    public function getLineRoomsAction()
    {
        $line_id = trim($this->request->getPost('line_id'));
        if (empty($line_id))
        {
            $this->flashJson(500, array(), "路线的id不能为空");
        }

        $rooms = RoomModel::find("line_id='{$line_id}'");

        $data = array();
        $count = 0;
        foreach ($rooms as $room)
        {
            $room_owner_id = $room->user_id;
            $room_owner = UserModel::findFirst("id='{$room_owner_id}'");
            if ($room_owner == false)
            {
                continue;
            }
            $room_owner_arr = $room_owner->toArray();
            $room_arr = $room->toArray();
            $tmp = array();
            $tmp ["user"] = $room_owner_arr;
            $tmp ["room"] = $room_arr;
            $data [] = $tmp;
            $count++;
        }

        $return = array(
            'total' => $count,
            'list' => $data);

        $this->flashJson(200, $return, '获取路线的房间成功');
    }

    /// 查询某房间的用户列表
    public function getRoomUsersAction()
    {
        $room_id = trim($this->request->getPost('room_id'));
        if (empty($room_id))
        {
            $this->flashJson(500, array(), "房间的id不能为空");
        }
        
        $room = RoomModel::findFirst("id='{$room_id}'");
        if ($room == false)
        {
            $this->flashJson(500, array(), "房间的id不存在");
        }

        $room_users = RoomUserModel::find("room_id='{$room_id}'"); 
        
        $data = array();
        foreach ($room_users as $room_user)
        {
            $user_id = $room_user->user_id;
            $user = UserModel::findFirst("id='{$user_id}'");
            $data [] = $user->toArray();  
        }

        $return = array (
            'total' => count($room_users),
            'list' => $data);

        $this->flashJson(200, $return , "获取房间的用户列表成功");
    }

    /// 查询某单个房间的信息
    public function getRoomInfoAction()
    {
        $room_id = trim($this->request->getPost('room_id'));
        if (empty($room_id))
        {
            $this->flashJson(500, array(), "房间的id不能为空");
        }

        $room = RoomModel::findFirst("id='{$room_id}'");
        if ($room == false)
        {
            $this->flashJson(500, array(), "房间的id不存在");
        }

        $data = array();

        $user_id = $room->user_id;
        $user_info = UserModel::findFirst("id='{$user_id}'");
        if ($user_info == false)
        {
            $this->flashJson(500, array(), "房主不存在");
        }
        $data ["room"] = $room->toArray();
        $data ["user"] = $user_info->toArray();
        
        $this->flashJson(200, $data, "获取房间信息成功");
    }

    /// 查询某个用户的出行计划
    public function getUserRoomsAction()
    {
        $user_id = trim($this->request->getPost('user_id'));
        if (empty($user_id))
        {
            $this->flashJson(500, array(), "用户id不能为空");
        }

        $room_users = RoomUserModel::find("user_id='{$user_id}'");
        if ($room_users == false)
        {
            $this->flashJson(500, array(), "查询该用户的行程失败");
        }

        $data = array();
        $count = 0;
        foreach ($room_users as $room_user)
        {
            $room_id = $room_user->room_id;
            $room_info = RoomModel::findFirst("id='{$room_id}'");
            if ($room_info == false)
            {
                continue;
            }

            $tmp_arr = array();
            $tmp_arr ["room"] = $room_info->toArray();
            $tmp_arr ["relation"] = $room_user->toArray();
            $data [] = $tmp_arr;
            $count++; 
        }
        
        $return = array(
            'total' => $count,
            'list' => $data);

        $this->flashJson(200, $return, '获取用户行程成功');
    } 
}
