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

        $phone = trim($this->request->getPost('phone'));

        $userModel = UserModel::findFirst('phone='.$phone);
        if(empty($userModel)) {
            $this->flashJson(200, array(), '手机号可用');
        } else {
            $this->flashJson(500, array(), '该手机号已存在');
        }    
    }
  
    public function checkUsernameAction()
    {
        $username = $this->request->getPost('username');

        $validator = new \Phalcon\Validation();
        $validator->add('username', new PresenceOf(array(
            'username' => '用户名必须',
        )));
        $validator->add('username', new StringLength(array(
            'max' => 8,
            'min' => 2,
            'messageMaximum' => '名称长度不能超过 8 ',
            'messageMinimum' => '名称长度不能小于 2 '
        )));
        
        $messages = $validator->validate($_POST);
        if (count($messages)) {
            $errMsgs = array();
            foreach($messages as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));
        }
        
        $userModel = UserModel::findFirst("name='{$username}'");
        if(empty($userModel)) {
            $this->flashJson(200, array(), '名称可用');
        } else {
            $this->flashJson(500, array(), '该名称已存在');
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
        
        $phone = trim($this->request->getPost("phone"));

        $sms = new \Instcar\Server\Plugins\Sms();
        $authCode = mt_rand(100000, 999999);
        $ret = $sms->send($phone, $authCode);
        if($ret->code != 2) {
            $this->flashJson(500, array(), strval($ret->msg));
        }
        $this->flashJson(200, array('smsid' => intval($ret->smsid), 'phone' => $phone));
    }
   
    public function registerAction()
    {
        $validator = new \Phalcon\Validation();
        $validator->add('phone', new PresenceOf(array(
            'message' => '手机号必须',
        )));
        $validator->add('phone', new RegexValidator(array(
            'pattern' => '/^[1][3578]\d{9}$/',
            'message' => '手机号码格式不正确'
        )));

        $validator->add('password', new PresenceOf(array(
            'message' => '密码必须',
        )));
        $validator->add('password', new StringLength(array(
            'max' => 32,
            'min' => 6,
            'messageMaXimum' => '密码长度不能超过 32 ',
            'messageMinimum' => '密码长度不能小于 6 '
        )));

        $validator->add('authcode', new PresenceOf(array(
            'message' => '验证码必须',
        )));
        $validator->add('authcode', new StringLength(array(
            'max' => 6,
            'min' => 6,
            'messageMaXimum' => '验证码长度必须为 6 ',
            'messageMinimum' => '验证码长度必须为 6 '
        )));
        
        $messages = $validator->validate($_POST);
        
        if (count($messages)) {
            $errMsgs = array();
            foreach($messages as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));
        }
        
        $phone = trim($this->request->getPost('phone'));
        $password = trim($this->request->getPost('password'));
        $postAuthCode = trim($this->request->getPost('authcode'));        

        $sessAuthCode = getDI()->get('session')->get('authcode');
        if($postAuthCode != $sessAuthCode) {
            $this->flashJson(500, array(), "验证码错误");
        }

        $userModel = UserModel::findFirst("phone='{$phone}'");
        if(!empty($userModel)) {
            $this->flashJson(500, array(), "该用户已存在！");
        }
        unset($userModel);
        
        $userModel = new UserModel();
        $userModel->phone = $phone;
        $userModel->password = md5($password);
        $userModel->status = 0;
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
        $phone = trim($this->request->getPost('phone'));
        $password = trim($this->request->getPost('password'));
        $encryptPassword = md5($password);
        
        $userModel = UserModel::findFirst("phone='{$phone}' AND password='{$encryptPassword}'");
        if(empty($userModel)) {
            $this->flashJson(500, array(), "用户不存在或密码错误，请重试");
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