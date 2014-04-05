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
    }

    public function getSource()
    {
        return "line";
    } 
}
