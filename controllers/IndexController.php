<?php
namespace Instcar\Server\Controllers;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        echo "Hello, Instcar !!!";
        exit;
    }

    public function testAction()
    {
        $encryptPassword = $this->crypt->encryptBase64("hello,world111111111", "le pa");
        echo $encryptPassword;

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
