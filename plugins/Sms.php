<?php
namesapce Instcar\Server\Plugins;

class Sms
{
  protected function getCurlClient($isPost = false)
  {
        $client = new \Buzz\Client\Curl();
        $client->setTimeout(5);
        $client->setVerifyPeer(false);
        $client->setMaxRedirects(0);
        $client->setOption(\CURLOPT_CONNECTTIMEOUT, 3);
        $client->setOption(\CURLOPT_USERAGENT, "instcar-api");
        $client->setOption(\CURLOPT_HTTP_VERSION, \CURL_HTTP_VERSION_1_1);
        $client->setOption(\CURLOPT_POST, $isPost);
        return $client;    
  }

  public function send($phone)
  {
    $request = new \Buzz\Message\Form\FormRequest();
    $request->setHost('http://121.199.16.178');
    $request->setResoure('/webservice/sms.php?method=SendSms');
    $params = array(
		    'account' => 'cf_instcar',
		    'password' => 'se1vbmhk',
		    'mobile' => '18612648090',
		    'content' => 'hello, world',
		    );
    $request->setFields($params);
    $response = new \Buzz\Message\Response();
    $client = $this->getCurlClient(true);
    $client->send($request, $response);
    if($response->isOk()) {
      return true;
    } else {
      return false;
    }
  }
}