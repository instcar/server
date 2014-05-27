<?php
/**
 * Created by PhpStorm.
 * User: guweigang
 * Date: 14-5-27
 * Time: 20:43
 */

namespace Instcar\Server\Models;


class SysConf extends \Phalcon\Mvc\Model
{
    public $id;
    public $name;
    public $value;
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
        return "sysconf";
    }
}
