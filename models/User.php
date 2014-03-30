<?php

namespace Instcar\Server\Models;

class User extends \Phalcon\Mvc\Model
{
  public $id;
  public $phone;
  public $name;
  public $headpic;
  public $status;
  public $addtime = '0000-00-00 00:00:00';
  public $modtime;
  
  public function initialize()
  {
    $this->setConnectionService('db');
  }

  public function getSource()
  {
    return "user";
  } 
}