<?php
namespace Instcar\Server\Controllers;
use Instcar\Server\Models\User as UserModel;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf;
  
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
    $userModel = UserModel::findFirst('name='.$username);
    if(empty($userModel)) {
      $this->flashJson(200, array(), '名称可用');
    } else {
      $this->flashJson(201, array(), '该名称已存在');
    }
  }

  public function registerAction()
  {
    $mobile = $this->request->getPost('mobile');
  }

  public function loginAction()
  {

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

  }

  public function simpleDetailAction()
  {

  }

  public function infoCenterAction()
  {

  }
}