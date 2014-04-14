<?php
namespace Instcar\Server\Models;

class UserDetail extends \Phalcon\Mvc\Model
{
    public $id;
    public $user_id;
    public $id_number;
    public $info = '';
    public $signature = '';
    public $home_addr = '';
    public $show_home_addr = 0;
    public $comp_addr = '';
    public $show_comp_addr = 0;

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
        return "user_detail";
    } 
}

