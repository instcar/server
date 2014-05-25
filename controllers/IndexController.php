<?php
namespace Instcar\Server\Controllers;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        echo "Hello, Instcar !!!";
        exit;
    }

    public function test1Action()
    {
        $conn = new \XMPPHP_XMPP('115.28.231.132', 13000, 'test13', 'test13', 'xmpphp', 'ay140222164105110546z', true, $loglevel=\XMPPHP_Log::LEVEL_VERBOSE);
        try {
            $conn->connect();
            $conn->processUntil('session_start');
            $conn->presence();
            //$conn->createRoom('ay140222164105110546z');
            $conn->destroyRoom('ay140222164105110546z');
            //$conn->message('test3@AY140222164105110546Z', 'This is a test message!');
            //$conn->registerNewUser("test13", "test13", $server="ay140222164105110546z");
        } catch(\XMPPHP_Exception $e) {
            die($e->getMessage());
        }        
    }
    
    public function testAction()
    {
        $encryptPassword = $this->crypt->encryptBase64("hello,world111111111", "le pa");
        echo $encryptPassword;
	var_dump($this->crypt->decryptBase64($encryptPassword, "le pa"));

        $client = new \GuzzleHttp\Client();
        $response = $client->get('http://guzzlephp.org');
        $res = $client->get('https://api.github.com/user', ['auth' =>  ['user', 'pass']]);
        echo $res->getStatusCode();
        // 200
        echo $res->getHeader('content-type');
        // 'application/json; charset=utf8'
        echo $res->getBody();
        // {"type":"User"...'
        var_export($res->json());
        exit;
    }

    public function test2Action()
    {
        $sql = "city IN ('北京','天津') ORDER BY id ASC LIMIT 0, 8";
        $collection = \Instcar\Server\Models\Point::find($sql);
        var_dump($collection->toArray());
        exit;
    }

    public function smsAction()
    {
        $phone = trim($this->request->getPost("phone"));
        if(empty($phone)) {
            $this->flashJson(500, array(), "手机号不能为空");
        }
        $sms = new \Instcar\Server\Plugins\Sms();
        $ret = $sms->send(18612648090);
        var_dump($ret);
        if($ret->code != 2) {
            $this->flashJson(500, array(), strval($ret->msg));
        }
        $this->flashJson(200, array('smsid' => intval($ret->smsid)));
    }
}
