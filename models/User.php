<?php
namespace Instcar\Server\Models;

class User extends \Phalcon\Mvc\Model
{
    public $id;
    public $phone;
    public $name;
    public $password;
    public $email = '@';
    public $headpic;
    public $status;
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
        return "user";
    } 
}
