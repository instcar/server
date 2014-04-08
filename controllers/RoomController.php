<?php

/**
  *Author : Chen Haichao
  *Time   : 2014/04/08
  *
  *Brief  : api for room
  */


namespace Instcar\Server\Controllers;

use Instcar\Server\Models\Room as RoomModel

class RoomController extends ControllerBase
{
  public function createAction()
  {
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

      $room = new RoomModel();
      $room->user_id = $user_id;
      $room->line_id = $line_id;
      $room->price = $price;
      $room->status = $status;
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

      $this->flashJson(200, array(), "新增房间成功"); 
  }

  public function closeAction()
  {

  }

  public function joinAction()
  {

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
