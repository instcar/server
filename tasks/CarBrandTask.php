<?php
namespace Instcar\Server\Tasks;

class CarBrandTask extends \Phalcon\CLI\Task
{
    public function mainAction()
    {
        $ak = 'Jj9FK935EUtlLplH';
        $sk = 'CVqwEj4JsgnEZUzn8ttLuNRIrvWwKZ';
        $ep = 'http://oss-cn-qingdao.aliyuncs.com';
        // $bucket = 'car-brand';
        $bucket = 'instcar-avatar-1';
        $aliyun = new \Aliyun($ak, $sk, $ep);

        // $dir = "/Users/guweigang/Downloads/car-brands/*.jpg";
        $dir = "/Users/guweigang/Downloads/default-avatar/*.jpg";        
        
        foreach (glob($dir) as $filename) {
            // echo "$filename size " . filesize($filename) . "\t" . basename($filename) . "\n";
            $key = basename($filename);
            try {
                $result = $aliyun->putResourceObject($bucket, $key, fopen($filename, 'r'), filesize($filename));
            } catch (\Exception $e) {
                continue ;
            }
            if(!is_object($result)) {
                echo "Sorry, error to upload " . $filename . PHP_EOL;
                exit(1);
            }            
        }
    }
}

