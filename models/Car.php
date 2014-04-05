<?php
namespace Instcar\Server\Models;

class Car extends \Phalcon\Mvc\Model
{
    public $id;
    public $name;
    public $picture;
    public $brand;
    public $series;
    public $addtime = '0000-00-00 00:00:00';
    public $modtime;
  
    public function initialize()
    {
        $this->setConnectionService('db');
    }

    public function getSource()
    {
        return "car";
    } 
}
