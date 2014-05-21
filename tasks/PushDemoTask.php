<?php
namespace Instcar\Server\Tasks;

use Instcar\Server\Plugins\Push as PushPlugin;


class PushDemoTask extends \Phalcon\CLI\Task
{
    public function mainAction()
    {
        $push = new PushPlugin();
        $push->getClient()->pushMessageAndroid("708924809444636429");
        $push->getClient()->queryBindList("708924809444636429");
    }
}