<?php
namespace Instcar\Server\Tasks;

class SampleTask extends \Phalcon\CLI\Task
{
    public function mainAction()
    {
        echo <<<EOT
+---------------------------------------------------+
|              Congratulation !                     |
|                                                   |
| You are here: Instcar\Server\Tasks\SampleTask;   |
|                                                   |
+---------------------------------------------------+
EOT;
        echo PHP_EOL;
    }
}
