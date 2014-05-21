<?php
/**
 * Created by PhpStorm.
 * User: guweigang
 * Date: 14-5-20
 * Time: 22:36
 */

namespace Instcar\Server\Models;


class Position extends \Phalcon\Mvc\Model
{
    public $id;
    public $user_id;
    public $lat;
    public $lng;
    public $geoash;
    public $is_last;
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
        return "position";
    }
} 