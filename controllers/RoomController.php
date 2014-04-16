<?php

/**
  *Author : Chen Haichao
  *Time   : 2014/04/08
  *
  *Brief  : api for room
  */


namespace Instcar\Server\Controllers;

use Instcar\Server\Models\Room as RoomModel;
use Instcar\Server\Models\RoomUser as RoomUserModel;

class RoomController extends ControllerBase
{
  public function createAction()
  {
      /// 创建房间
      $user_id = trim($this->request->getPost('user_id'));
      if (empty($user_id))
      {
          $this->flashJson(500, array(), "用户id不能为空");
      }

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

      $status = $this->request->getPost('status');
      if (empty($status))
      {
          $this->flashJson(500, array(), "状态不能为空");
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

      $room = new RoomModel();
      $room->user_id = $user_id;
      $room->line_id = $line_id;
      $room->price = $price;
      $room->status = $status;
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

      /// 创建房间用户对应关系
      $room_user = new RoomUserModel();
      $room_user->room_id = $room->id;
      $room_user->user_id = $user_id;
      $room_user->status = $status;

      if ($room_user->save() == false)
      {
          $errMsgs = array();
          foreach ($room->getMessages() as $message)
          {
              $errMsgs[] = $message->__toString();
          }
          $this->flashJson(500, array(), join("; ", $errMsgs));

      }

      $this->flashJson(200, array('id'=>$room->id), "新增房间成功"); 
  }

  public function closeAction()
  {
      ///删除房间
      $id = trim($this->request->getPost('id'));
      if (empty($id))
      {
          $this->flashJson(500, array(), "room的id不能为空");
      }

      $room = RoomModel::findFirst("id='{$id}'");
      if ($room == false)
      {
          $this->flashJson(500, array(), "room的id不存在");
      }

      if ($room->delete() == false)
      {
          $errMsgs = array();
          foreach ($room->getMessages() as $message)
          {
              $errMsgs[] = $message->__toString();
          }
          $this->flashJson(500, array(), join("; ", $errMsgs));
      }

      ///删除用户房间对应关系
      $room_users = RoomUserModel::find("room_id='{$id}'");
      if ($room_users)
      {
          foreach( $room_user in $room_users)
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

      $this->flashJson(200, array(), "room删除成功");
    
  }

  public function joinAction()
  {
      $room_id = trim($this->request->getPost('room_id'))
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

      $status = $this->request->getPost('status');
      if (empty($status))
      {
          $this->flashJson(500, array(), "状态不能为空");
      }

      $room_user = new RoomUserModel();
      $room_user->room_id = $room_id;
      $room_user->user_id = $user_id;
      $room_user->status = $status;

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

  public function quitAction()
  {

  }

  public function usersAction()
  {

  }

  public function infoById()
  {

  }

  public function confirmAction()
  {

  }

  public function changeStartTimeAction()
  {

  }

  public function changeDescriptionAction()
  {

  }

  public function changeSeatNumAction()
  {

  }
  
}
