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
 *status = 0 等待状态
 *status = 1 运行状态
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

        /// TODO 确认user_id存在性

        $line_id = trim($this->request->getPost('line_id'));
        if (empty($line_id))
        {
          $this->flashJson(500, array(), "路线id不能为空");
        }
        /// TODO 确认lind_id 存在性

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

        $openfire_room_name = "";
        try
        {
            /// 连接openfire并且创建房间
            $conn = new \XMPPHP_XMPP('115.28.231.132', 13000, $user_id, '123456', 'xmpphp', 'ay140222164105110546z', false, $loglevel=\XMPPHP_Log::LEVEL_INFO);
            $conn->connect();
            $conn->processUntil('session_start');
            $conn->presence();
            $conn->createRoom('ay140222164105110546z');
            $openfire_room_name = "{$user_id}@conference.ay140222164105110546z";
        }
        catch(\XMPPHP_Exception $e)
        {
            $this->flashJson(500, array(), $e->getMessage());
        }

        /// 插入room表
        $room = new RoomModel();
        $room->user_id = $user_id;
        $room->line_id = $line_id;
        $room->price = $price;
        /// 默认状态是准备状态
        $room->status = 0;
        $room->description = $description;
        $room->start_time = $start_time;
        $room->max_seat_num = $max_seat_num;
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

        if ($room_user->save() == false)
        {
          $errMsgs = array();
          foreach ($room->getMessages() as $message)
          {
              $errMsgs[] = $message->__toString();
          }
          $this->flashJson(500, array(), join("; ", $errMsgs));

        }

        $this->flashJson(200, array('id'=>$room->id, 'openfire'=>$openfire_room_name), "新增房间成功"); 
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

        ///删除聊天室
        try
        {
            /// 连接openfire并且创建房间
            $conn = new \XMPPHP_XMPP('115.28.231.132', 13000, $user_id, '123456', 'xmpphp', 'ay140222164105110546z', false, $loglevel=\XMPPHP_Log::LEVEL_INFO);
            $conn->connect();
            $conn->processUntil('session_start');
            $conn->presence();
            $conn->destroyRoom('ay140222164105110546z');
        }
        catch(\XMPPHP_Exception $e)
        {
            $this->flashJson(500, array(), $e->getMessage());
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

        $this->flashJson(200, array(), "room删除成功");
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

    /// 乘客加入房间
    public function joinAction()
    {

        $room_id = trim($this->request->getPost('room_id'));
        if (empty($room_id))
        {
          $this->flashJson(500, array(), "room的id不能为空");
        }

        $room = RoomModel::findFirst("id='{$room_id}'");
        if ($room == false)
        {
          $this->flashJson(500, array(), "room id 不存在");
        }

        $user_id = trim($this->request->getPost('user_id'));
        if (empty($user_id))
        {
          $this->flashJson(500, array(), "user id 不能为空");
        }

        $room_user = new RoomUserModel();
        $room_user->room_id = $room_id;
        $room_user->user_id = $user_id;
        $room_user->status = "0";

        if ($room_user->save() == false)
        {
            $errMsgs = array();
            foreach ($room_user->getMessages() as $message)
            {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));
        }

        $this->flashJson(200, array(), "加入房间成功");
    }

    /// 乘客退出房间
    public function quitAction()
    {
        $room_id = trim($this->request->getPost('room_id'));
        if (empty($room_id))
        {
            $this->flashJson(500, array(), "room 的id不能为空");
        }

        $user_id = trim($this->request->getPost('user_id'));
        if (empty($user_id))
        {
            $this->flashJson(500, array(), "user 的id不能为空");

        }

        $room_user = RoomUserModel::findFirst(array("room_id='{$room_id}'", "user_id='{$user_id}'")); 
        if ($room_user == false)
        {
            $this->flashJson(500, array(), "乘客及房间号对应关系不在数据库中");
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

        $this->flashJson(200, array(), "乘客退出房间成功"); 
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
        foreach ($rooms as $room)
        {
            $tmp = $room->toArray();
            $data [] = $tmp;
        }

        $return = array(
            'total' => count($rooms),
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
            $data [] = $room_user->user_id;
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

        $this->flashJson(200, $room->toArray(), "获取房间信息成功");
    }
}
