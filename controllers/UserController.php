<?php
namespace Instcar\Server\Controllers;
use Instcar\Server\Models\User as UserModel;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength as StringLength;

class UserController extends ControllerBase
{
    public function checkUserPhoneAction()
    {
        $phone = $this->request->getPost('phone');
    
        $validator = new \Phalcon\Validation();
        $validator->add('phone', new PresenceOf(array(
            'message' => '手机号必须',
        )));
        $validator->add('phone', new RegexValidator(array(
            'pattern' => '/^[1][3578]\d{9}$/',
            'message' => '手机号码格式不正确'
        )));

        $messages = $validator->validate($_POST);
        if (count($messages)) {
            $errMsgs = array();
            foreach($messages as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));
        }
    
        $userModel = UserModel::findFirst('phone='.$phone);
        if(empty($userModel)) {
            $this->flashJson(200, array(), '手机号可用');
        } else {
            $this->flashJson(201, array(), '该手机号已存在');
        }    
    }
  
    public function checkUsernameAction()
    {
        $username = $this->request->getPost('username');

        $validator = new \Phalcon\Validation();
        $validator->add('username', new StringLength(array(
            'max' => 8,
            'min' => 2,
            'messageMaximum' => '名称不能超过 6 个字',
            'messageMinimum' => '名称不能少于 2 个字'
        )));
        
        $messages = $validator->validate($_POST);
        if (count($messages)) {
            $errMsgs = array();
            foreach($messages as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));
        }
        
        $userModel = UserModel::findFirst("name='".$username."'");
        if(empty($userModel)) {
            $this->flashJson(200, array(), '名称可用');
        } else {
            $this->flashJson(201, array(), '该名称已存在');
        }
    }

    public function getAuthCodeAction()
    {
        $validator = new \Phalcon\Validation();
        $validator->add('phone', new PresenceOf(array(
            'message' => '手机号必须',
        )));
        $validator->add('phone', new RegexValidator(array(
            'pattern' => '/^[1][3578]\d{9}$/',
            'message' => '手机号码格式不正确'
        )));
        $messages = $validator->validate($_POST);
        if (count($messages)) {
            $errMsgs = array();
            foreach($messages as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));
        }
        $phone = $this->request->getPost("phone");
        $sms = new \Instcar\Server\Plugins\Sms();
        $authCode = mt_rand(100000, 999999);
        $ret = $sms->send($phone, $authCode);
        if($ret->code != 2) {
            $this->flashJson(500, array(), strval($ret->msg));
        }
        getDI()->get('session')->set('authcode', $authCode);
        $this->flashJson(200, array('smsid' => intval($ret->smsid), 'phone' => $phone));
    }
   
    public function registerAction()
    {
        $phone = $this->request->getPost('phone');
        $postAuthCode = $this->request->getPost('authcode');
        $sessAuthCode = getDI()->get('session')->get('authcode');
        if($postAuthCode !== $sessAuthCode) {
            $this->flashJson(500, array(), "验证码错误");
        }
        $userModel = new UserModel();
        $userModel->phone = $phone;
        $userModel->status = 0;
        $userModel->addtime = $userModel->modtime = date('Y-m-d H:i:s');
        if($userModel->save() === false) {
            $errMsgs =  array();
            foreach($userModel->getMessages() as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));
        }
        $this->flashJson(200, array(), "恭喜您，注册成功！");
    }

    public function loginAction()
    {
        $phone = $this->request->getPost('phone');
        $postAuthCode = $this->request->getPost('authcode');
        $sessAuthCode = getDI()->get('session')->get('authcode');
        if($postAuthCode !== $sessAuthCode) {
            $this->flashJson(500, array(), "验证码错误");
        }
        $userModel = UserModel::findFirst('phone='.$phone);
        if(empty($userModel)) {
            $this->flashJson(500, array(), "用户不存在，请注册");
        } else {
            $this->flashJson(200, array(),  "登录成功");
        }
    }
  
    public function editHeadPicAction()
    {

    }

    public function editPasswordAction()
    {
        
    }

    public function resetPasswordAction()
    {
        
    }

    public function editEmailAction()
    {
        
    }

    public function editUsernameAction()
    {
        
    }

    public function editAgeAction()
    {

    }

    public function editSexAction()
    {

    }

    public function editCompanyAddressAction()
    {

    }

    public function detailAction()
    {
        $userId = intval($this->request->getPost('uid'));
        if($userId <= 0) {
            $this->flashJson(500, array(), '非法请求！');
        }
        $userModel = UserModel::findFirst($userId);
        if(empty($userModel)) {
            $this->flashJson(500, array(), '非法请求！');
        }
        $this->flashJson(200, $userModel->toArray());

    }

    public function simpleDetailAction()
    {

    }

    public function infoCenterAction()
    {

    }
}