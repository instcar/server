<?php
namespace Instcar\Server\Controllers;

class UserController extends ControllerBase
{
  public function checkUserPhoneAction()
  {
    $phone = $this->request->getPost('phone');
  }
  
  public function checkUsernameAction()
  {

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