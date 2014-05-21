<?php
/**
 * Created by PhpStorm.
 * User: guweigang
 * Date: 14-5-21
 * Time: 14:28
 */

namespace Instcar\Server\Models;


class UserPush extends \Phalcon\Mvc\Model
{
    public $id;
    public $user_id;
    public $channel_id;
    public $appuid;
    public $platform;
    public $addtime;
    public $modtime;

    public function initialize()
    {
        $this->setConnectionService('db');
        $this->belongsTo("user_id", "\Instcar\Server\Models\User", "id", array("alias" => "user"));
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
        return "user_push";
    }
} 