<?php
namespace Instcar\Server\Models;

class Brand extends \Phalcon\Mvc\Model
{
    public $id;
    public $name;
    public $iconname;
    public $letter;
  
    public function initialize()
    {
        $this->setConnectionService('db');
    }

    
    public function getSource()
    {
        return "brand";
    } 
}
