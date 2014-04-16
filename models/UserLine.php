<?php
namespace Instcar\Server\Models;

class UserLine extends \Phalcon\Mvc\Model
{
    public $id;
    public $user_id;
    public $line_id;
    
    public $addtime = '0000-00-00 00:00:00';
    public $modtime;
  
    public function initialize()
    {
        $this->setConnectionService('db');
        $this->belongsTo('user_id', '\Instcar\Server\Models\User', 'id', array('alias' => 'user'));
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
        return "user_line";
    } 
}

