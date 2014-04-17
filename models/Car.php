<?php
namespace Instcar\Server\Models;

class Car extends \Phalcon\Mvc\Model
{
    public $id;
    public $name;
    public $picture;
    public $parent_brand;
    public $brand;
    public $series;
    public $addtime = '0000-00-00 00:00:00';
    public $modtime;
  
    public function initialize()
    {
        $this->setConnectionService('db');
        $this->hasMany('id', '\Instcar\Server\Models\UserCar', 'car_id', array('alias' => 'user_car'));
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
        return "car";
    } 
}
