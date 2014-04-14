<?php
namespace Instcar\Server\Controllers;
use Instcar\Server\Models\User as UserModel;
use Instcar\Server\Models\UserDetail as UserDetailModel;
use Instcar\Server\Models\Car as CarModel;
use Instcar\Server\Models\UserCar as UserCarModel;

use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Between as BetweenValidator;

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
        getDI()->get('session')->set('authcode', $authCode);
        getDI()->get('session')->set('phone', $phone);
        
        $ret = $sms->send($phone, $authCode);
        if($ret->code != 2) {
            $this->flashJson(500, array(), strval($ret->msg) . " --- auth code: " . $authCode);
        }
        
        getDI()->get('session')->set('smsid', intval($ret->smsid));

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
            'max' => 12,
            'min' => 6,
            'messageMaXimum' => '密码长度不能超过 12 ',
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
        $sessPhone = getDI()->get('session')->get('phone');
        $sessSmsid = getDI()->get('session')->get('smsid');
        if($postAuthCode != $sessAuthCode) {
            $this->flashJson(500, array(), "验证码错误");
        }

        if($phone != $sessPhone) {
            $this->flashJson(500, array(), "手机号码错误");
        }

        $userModel = UserModel::findFirst("phone='{$phone}'");
        if(!empty($userModel)) {
            $this->flashJson(500, array(), "该用户已存在！");
        }
        unset($userModel);
        
        $userModel = new UserModel();
        $userModel->phone = $phone;
        $userModel->password = $this->crypt->encryptBase64($password);
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
        if($this->user) {
            $this->flashJson(200, array(), "您已经登录");
        }
        
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
            'max' => 12,
            'min' => 6,
            'messageMaXimum' => '密码长度不能超过 12 ',
            'messageMinimum' => '密码长度不能小于 6 '
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
        // $encryptPassword = $this->crypt->encryptBase64($password);

        $userModel = UserModel::findFirst("phone='{$phone}'");
        if(empty($userModel)) {
            $this->flashJson(500, array(), "用户不存在，请重试");
        } else {
            if(trim($this->crypt->decryptBase64($userModel->password)) == $password) {
                getDI()->get('session')->set('identity', $userModel->id);
                $this->flashJson(200, array("id" => intval($userModel->id)),  "登录成功");
            } else {
                $this->flashJson(500, array(), "密码错误，请重试");
            }
        }
    }

    public function editAction()
    {
        if(!$this->user) {
            $this->flashJson(401);
        }
        
        $skipAttributes = array(
            'id',
            'phone',
            'user_id',
            'password',
            'status',
            'info',
        );
        
        foreach ($_POST as $key => $val) {
            if(in_array($key, $skipAttributes)) {
                unset($_POST[$key]);
            } else if (array_key_exists($key, $this->user->toArray())) {
                $this->user->{$key} = $val;
            } else {
                if(empty($this->user->user_detail)) {
                    $this->user->user_detail = new UserDetailModel();
                }
                $this->user->user_detail->{$key} = $val;
            }
        }
        
        if($this->user->save() == false) {
            $errMsgs =  array();
            foreach($this->user->getMessages() as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));            
        }
        
        $this->flashJson(200,  array(), "操作成功");
    }

    public function realnameRequestAction()
    {
        if(!$this->user) {
            $this->flashJson(401);
        }
        
        $idCards = (array) $this->request->getPost('id_cards');
        
        if(empty($this->user->user_detail)) {
            $userInfo = array();            
            $this->user->user_detail = new UserDetailModel();
        } else {
            $userInfo = json_decode($this->user->user_detail->info, true);
        }
        
        $userInfo['id_cards'] = $idCards;
        $this->user->user_detail->info = json_encode($userInfo);
        
        if($this->user->save() == false) {
            $errMsgs =  array();
            foreach($this->user->getMessages() as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));            
        }
        $this->flashJson(200,  array(), "操作成功");        
    }

    public function userAddCarAction()
    {
        if(!$this->user) {
            $this->flashJson(401);
        }
        
        $carId = $this->request->getPost('car_id');
        if($carId <=0 ) {
            $this->flashJson(500, array(), "非法请求：汽车ID必须大于0");
        }
        
        $carModel = CarModel::findFirst($carId);
        if(empty($carModel)) {
            $this->flashJson(500, array(), "非法请求：所选汽车不存在");
        }

        $carInfo = array();
        
        $license = (array) $this->request->getPost('license');

        if(empty($license)) {
            $this->flashJson(500, array(), "必须上传行驶证照");
        }
        
        $cars = (array) $this->request->getPost('cars');

        if(count($cars) < 2) {
            $this->flashJson(500, array(), "必须上传2张及以上靓车照");
        }

        $carInfo['license'] = $license;
        $carInfo['cars'] = $cars;

        $userCarModel = new UserCarModel();
        $userCarModel->user_id = $this->user->id;
        $userCarModel->car_id = $carModel->id;
        $userCarModel->info = json_encode($carInfo);
        $userCarModel->status = 1;

        if($userCarModel->save() == false) {
            $errMsgs =  array();
            foreach($userCarModel->getMessages() as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));                        
        }

        $this->flashJson(200, array(), "操作成功");
        
    }

    public function editPasswordAction()
    {
        
    }

    public function resetPasswordAction()
    {
        
    }

    public function editUsernameAction()
    {
        
    }

    public function editCompanyAddressAction()
    {

    }

    public function detailAction()
    {
        if(!$this->user) {
            $this->flashJson(401);
        }
        getDI()->get('logger')->error("session", $_SESSION);
        $validator = new \Phalcon\Validation();
        $validator->add('id', new PresenceOf(array(
            'message' => '用户ID必须',
        )));
        $validator->add('id', new BetweenValidator(array(
            'minimum' => 1,
            'maximum' => 1000000000,
            'message' => '用户ID必须大于0'
        )));

        $messages = $validator->validate($_POST);
        if (count($messages)) {
            $errMsgs = array();
            foreach($messages as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));
        }
        
        $userId = intval($this->request->getPost('id'));

        $userModel = UserModel::findFirst($userId);

        $retArr = array();
        if(empty($userModel)) {
            $this->flashJson(500, array(), '用户不存在！');
        }
        $retArr = $userModel->toArray();
        
        if(!empty($userModel->user_detail)) {
            $retArr['detail'] = $userModel->user_detail->toArray();
        }
        $this->flashJson(200, $retArr);

    }

    public function simpleDetailAction()
    {

    }

    public function infoCenterAction()
    {

    }
}