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
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\InclusionIn as InclusionInValidator;
use Phalcon\Validation\Validator\Url as UrlValidator;

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

        $this->flashJson(200, array('smsid' => intval($ret->smsid), 'phone' => $phone, 'authcode' => $authCode));
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
        $userModel->name = $phone;
        $userModel->headpic = 'http://instcar-avatar-1.oss-cn-qingdao.aliyuncs.com/portrait_'.rand(1, 20);
        $userModel->status = 0;
        if($userModel->save() === false) {
            $errMsgs =  array();
            foreach($userModel->getMessages() as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));
        }

        try {
            $conn = new \XMPPHP_XMPP(
                '115.28.231.132',
                13000,
                'admin',
                'k1990855',
                'xmpphp',
                'ay140222164105110546z',
                false,
                \XMPPHP_Log::LEVEL_INFO);

            $conn->connect();
            $conn->processUntil('session_start');
            $conn->presence();
            $conn->registerNewUser($phone, '123456', '', '', 'ay140222164105110546z');
        } catch (\XMPPHP_Exception $e) {
            // ...
        }
        
        $this->flashJson(200, array(), "恭喜您，注册成功！");
    }

    public function loginAction()
    {
        if($this->user) {
            $this->flashJson(200, array("id" => $this->user->id), "您已经登录");
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
                $this->flashJson(200, array("id" => $userModel->id),  "登录成功");
            } else {
                $this->flashJson(500, array(), "密码错误，请重试");
            }
        }
    }

    public function logoutAction()
    {
        if(!$this->user) {
            $this->flashJson(401);
        }
        try {
            $this->session->destroy();
        } catch (\Exception $e) {
            $this->flashJson(500, array(), "服务端错误: 用户会话清除失败");
        }
        $this->flashJson(200);
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

        $validator = new \Phalcon\Validation();

        if(array_key_exists('name', $_POST)) {
            $validator->add('name', new StringLength(array(
                'min'  => 6,
                'max'  => 16,
                'messageMaximum' => '昵称不能超过16个字',
                'messageMinimum' => '昵称不能少于6个字',
            )));
        }

        if(array_key_exists('email', $_POST)) {
            $validator->add('email', new EmailValidator(array(
                'message' => '邮箱格式不正确',
            )));
        }

        if(array_key_exists('sex', $_POST)) {
            $validator->add('sex', new InclusionInValidator(array(
                'message' => '性别只能为, 女:0,男:1,保密:2',
                'domain'  => array(0, 1, 2),
            )));
        }

        if(array_key_exists('signature', $_POST)){
            $validator->add('signature', new StringLength(array(
                'max' => 48,
                'min' => 1,
                'message' => '用户签名长度最大不能超过48个字',
            )));
        }
        
        if(array_key_exists('show_home_addr', $_POST)) {
            $validator->add('show_home_addr', new InclusionInValidator(array(
                'message' => '是否显示家庭住址只接受0或1',
                'domain'  => array(0, 1),
            )));
        }

        if(array_key_exists('show_comp_addr', $_POST)) {
            $validator->add('show_comp_addr', new InclusionInValidator(array(
                'message' => '是否显示公司住址只接受0或1',
                'domain'  => array(0, 1),
            )));
        }

        if(array_key_exists('headpic', $_POST)) {
            $validator->add('headpic', new UrlValidator(array(
                'message' => '头像地址必须是合法的URL'
            )));
        }
        
        $messages = $validator->validate($_POST);
        if (count($messages)) {
            $errMsgs = array();
            foreach($messages as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));
        }        
        
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

    public function addCarAction()
    {
        if(!$this->user) {
            $this->flashJson(401);
        }
        
        $carId = intval($this->request->getPost('car_id'));
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
        if(count($cars) < 1) {
            $this->flashJson(500, array(), "必须上传1张及以上靓车照");
        }

        $carInfo['license'] = $license;
        $carInfo['cars'] = $cars;

        $userCarModel = new UserCarModel();
        $userCarModel->user_id = $this->user->id;
        $userCarModel->car_id = $carModel->id;
        $userCarModel->info = json_encode($carInfo);
        $userCarModel->status = 1; /* 车辆待审 */

        if($userCarModel->save() == false) {
            $errMsgs =  array();
            foreach($userCarModel->getMessages() as $message) {
                $errMsgs[] = $message->__toString();
            }
            $this->flashJson(500, array(), join("; ", $errMsgs));                        
        }
        $this->flashJson(200, array(), "操作成功");
    }

    public function getCarsAction()
    {
        if(!$this->user) {
            $this->flashJson(401);
        }
        $carId = intval($this->request->getPost('car_id'));
        
        if($carId < 0) {
            $this->flashJson(500, array(), "非法访问：car_id必须大于0");
        }

        $retArr = array();
        
        if($carId == 0) {
            if(!empty($this->user->user_car)){
                foreach ($this->user->user_car as $userCarModel) {
                    $car = array();
                    $car = $userCarModel->toArray();
                    $car['name'] = $userCarModel->car->name;
                    $car['picture'] = $userCarModel->car->picture;
                    $retArr[] = $car;
                }
            }
            $this->flashJson(200, array('total' => count($retArr), 'list' => $retArr));            
        }
        
        if($carId > 0) {
            $where = "user_id = ".$this->user->id." AND car_id = ".$carId;
            $userCarCollection = UserCarModel::find($where);
        
            if(empty($userCarCollection)) {
                $this->flashJson(500, array(), "非法访问：您没有登记该汽车");
            }
            
            foreach ($userCarCollection as $userCarModel) {
                $car = array();
                $car = $userCarModel->toArray();
                $car['name'] = $userCarModel->car->name;
                $car['picture'] = $userCarModel->car->picture;
                $retArr[] = $car;
            }
            
            $this->flashJson(200, array('total' => count($retArr), 'list' => $retArr));
        }
    }

    public function hasCarAction()
    {
        if(!$this->user) {
            $this->flashJson(401);
        }
        $where = "user_id = ".$this->user->id." AND status = 0";
        $userCarCollection = UserCarModel::find($where);
        $count = 0;
        if(!empty($userCarCollection)) {
            $count = $userCarCollection->count();
        }
        $this->flashJson(200, array('total' => $count));
    }
    
    public function getCarStatusAction()
    {
        if(!$this->user) {
            $this->flashJson(401);
        }
        $carId = intval($this->request->getPost('car_id'));
        if($carId <= 0) {
            $this->flashJson(500, array(), "非法访问：car_id必须大于0");
        }
        $where = "user_id = ".$this->user->id." AND car_id = ".$carId;
        $userCarModel = UserCarModel::findFirst($where);
        
        if(empty($userCarModel)) {
            $this->flashJson(500, array(), "非法访问：您没有该汽车");
        }
        $this->flashJson(200, array('status'=> $userCarModel->status));
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
        $userId = $this->user->id;
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