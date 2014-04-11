<?php

namespace Instcar\Server\Models;

class Point extends \Phalcon\Mvc\Model
{
    public $id;
    public $name;
    public $lat; //纬度
    public $lng; //经度 
    public $geohash;
    public $district;
    public $city;
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
        return "point";
    } 
}