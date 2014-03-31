<?php

// 执行命令：
// cd /path/to/instcar/
// php skeleton/tools/console.php server:geo-hash main

namespace Instcar\Server\Tasks;
use BullSoft\Geo as Geo;
use Instcar\Server\Models\Point as PointModel;

class GeoHashTask extends \Phalcon\CLI\Task
{
    public function mainAction()
    {
        $points = PointModel::find();
        foreach ($points as $point) {
            $hash = Geo\Hash::encode($point->lng, $point->lat);
            $point->geohash = $hash;
            if($point->save() === false) {
                foreach ($point->getMessages() as $message) {
                    echo $point->id . "\t" . $message . PHP_EOL;
                }
            } else {
                echo "Successfully update ... " . PHP_EOL;
            }
        }
    }
}
