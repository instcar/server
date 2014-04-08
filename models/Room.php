<?php

/*
 *Author : Chen Haichao
 *Time   : 2014/04/08
 *Brief  : operations for room 
 **/

namespace Instcar\Server\Models;

class Room extends \Phalcon\Mvc\Model
{
    public $id;
    public $user_id;
    public $line_id;
    public $price;
    public $status;
    public $addtime;
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
        return "room";
    } 
}
