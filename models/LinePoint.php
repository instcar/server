<?php

namespace Instcar\Server\Models;

class LinePoint extends \Phalcon\Mvc\Model
{
    public $id;
    public $line_id;
    public $point_id; 
    public $pre_point_id;
    public $post_point_id;
    public $distance;
    public $starttime = '0000-00-00 00:00:00';
    public $price;
    public $addtime = '0000-00-00 00:00:00';
    public $modtime;
  
    public function initialize()
    {
        $this->setConnectionService('db');
    }

    public function getSource()
    {
        return "line_point";
    } 
}