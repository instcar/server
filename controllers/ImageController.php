<?php
namespace Instcar\Server\Controllers;
use Instcar\Server\Models\Image as ImageModel;
use Instcar\Server\Models\User  as UserModel;

class ImageController extends ControllerBase
{
    static $imgBucket = array(
        1 => 'instcar-car-pic-1',
        2 => 'instcar-user-pic-1',
    );
    
    public function uploadAction()
    {
        $type   = intval($this->request->getPost('type'));
        $userId = intval($this->request->getPost('user_id'));

        $userModel = UserModel::findFirst($userId);
        if(empty($userModel)) {
            $this->flashJson(500, array(), "用户不存在");
        }
        
        $ak = 'Jj9FK935EUtlLplH';
        $sk = 'CVqwEj4JsgnEZUzn8ttLuNRIrvWwKZ';
        $ep = 'http://oss-cn-qingdao.aliyuncs.com';

        if(!isset(self::$imgBucket[$type])) {
            $this->flashJson(500, array(), "非法请求, 未指定图片类别");
        }
        
        $bucket = self::$imgBucket[$type];
        $imgPrefix = 'http://' . $bucket . '.' . parse_url($ep, \PHP_URL_HOST).'/';
        
        $aliyun = new \Aliyun($ak, $sk, $ep);
        
        if ($this->request->hasFiles() == true) {
            // Print the real file names and sizes
            $retArr = array();
            
            foreach ($this->request->getUploadedFiles() as $file) {
                // filename
                $key = \BullSoft\Utility::createGuid($bucket);
                
                try {
                    $result = $aliyun->putResourceObject($bucket, $key, fopen($file->getTempName(), 'r'), $file->getSize());
                } catch (\Exception $e) {
                    $retArr[$file->getKey()] = false;
                    continue ;
                }
                
                if(!is_object($result)) {
                    $retArr[$file->getKey()] = false;
                    continue ;
                }
                
                $imgModel = new ImageModel();
                $imgModel->user_id = $userId;
                $imgModel->name = $key;
                $imgModel->extname = $file->getRealType();
                $imgModel->url_prefix = $imgPrefix;
                
                if($imgModel->save() == false) {
                    $errMsgs =  array();
                    foreach($userModel->getMessages() as $message) {
                        $errMsgs[] = $message->__toString();
                    }
                    $this->flashJson(500, array(), join("; ", $errMsgs));                    
                }
                
                $retArr[$file->getKey()] = $imgPrefix.$key;
            }
            $this->flashJson(200, $retArr);
        }
        $this->flashJson(500, array(), "非法请求， 没有上传图片");
    }
    
    public function upload($files, $type)
    {
        
    }

    
    public function testAction( )
    {
        $ak = 'Jj9FK935EUtlLplH';
        $sk = 'CVqwEj4JsgnEZUzn8ttLuNRIrvWwKZ';
        $ep = 'http://oss-cn-qingdao.aliyuncs.com';
        $bucket = 'instcar-car-pic-1';
        
        $key = 'mysql.png';
        
        $aliyun = new \Aliyun($ak, $sk, $ep);

        // var_dump($aliyun->listObjects($bucket));
        // exit;

        
        // $filePath = '/Users/guweigang/tmp/mysql.png';
        // var_dump($aliyun->putResourceObject($bucket, $key, fopen($filePath, 'r'), filesize($filePath)));

        var_dump($aliyun->getObject($bucket, $key));
    }
}