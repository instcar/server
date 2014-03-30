<?php
namespace Instcar\Server\Controllers;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
      echo "Hello, Instcar !!!";
      exit;
    }

    public function smsAction()
    {
      $sms = new \Instcar\Server\Plugins\Sms();
      $sms->send(18612900050);
      echo "Success";
      exit;
    }
}
