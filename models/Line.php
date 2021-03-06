<?php
namespace Instcar\Server\Models;

class Line extends \Phalcon\Mvc\Model
{
    public $id;
    public $name;
    public $description;
    public $price;
    public $addtime = '0000-00-00 00:00:00';
    public $modtime;
  
    public function initialize()
    {
        $this->setConnectionService('db');
        $this->hasManyToMany('id', '\Instcar\Server\Models\LinePoint', 'line_id', 'point_id', '\Instcar\Server\Models\Point', 'id', array('alias' => 'point'));

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
        return "line";
    } 
}
