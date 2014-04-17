<?php
namespace Instcar\Server\Models;

class User extends \Phalcon\Mvc\Model
{
    public $id;
    public $phone;
    public $name = '';
    public $sex = 2;
    public $age = 0;
    public $password;
    public $email = '';
    public $headpic = '';
    public $status = 0;
    public $addtime = '0000-00-00 00:00:00';
    public $modtime;
  
    public function initialize()
    {
        $this->setConnectionService('db');
        $this->hasOne('id', '\Instcar\Server\Models\UserDetail', 'user_id', array('alias' => 'user_detail'));
        $this->hasMany('id', '\Instcar\Server\Models\UserCar', 'user_id', array('alias' => 'user_car'));
        $this->hasManyToMany('id', '\Instcar\Server\Models\UserCar', 'user_id', 'car_id', '\Instcar\Server\Models\Car', 'id', array('alias' => 'car'));
    }

    public function beforeValidationOnCreate()
    {
        $this->addtime = date("Y-m-d H:i:s");
        $this->modtime = date("Y-m-d H:i:s");
    }

    public function beforeUpdate()
    {
        $this->modtime = date("Y-m-d H:i:s");
    }
    
    public function getSource()
    {
        return "user";
    } 
}
