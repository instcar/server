<?php
namespace Instcar\Server\Models;

class OnlineCar extends \Phalcon\Mvc\Model
{
    public $id;
    public $user_id;
    public $line_id;
    public $lat;
    public $lng;
    public $geohash;
    public $addtime = '0000-00-00 00:00:00';
    public $modtime;
  
    public function initialize()
    {
        $this->setConnectionService('db');
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
        return "online_car";
    } 
}
