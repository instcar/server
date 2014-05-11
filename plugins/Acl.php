<?php
namespace Instcar\Server\Plugins;

use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;

class Acl extends Plugin
{
    protected $di;
    protected $eventManager;

    protected $module     = '';
    protected $controller = '';
    protected $action     = '';

    public function __construct($di, $evtManager)
    {
        $this->di = $di;
        $this->eventManager = $evtManager;
    }

    public function beforeDispatch(\Phalcon\Events\Event $event, \Phalcon\Mvc\Dispatcher $dispatcher)
    {
        $this->module     = $dispatcher->getModuleName();
        $this->controller = $dispatcher->getControllerName();
        $this->action     = $dispatcher->getActionName();

        $dbUser = null;
        $userId = $this->session->get('identity');
        if(!$userId) {
        } else {
            $dbUser = \Instcar\Server\Models\User::findFirst(intval($userId));
            if(!empty($dbUser)) {
                $this->di->set('user', $dbUser);
            }
        }
        return true;
    }

    public function isAllowed()
    {
        $list = $this->di->get('user')->acl;
        
        if(count($list) == 0) {
            return false;
        }
        
        foreach ($list as $item) {

            $isOK = true;
            
            if($item->module != '*' && $this->module != $item->module) {
                $isOK = false;
            }
            if($item->controller != '*' && $this->controller != $item->controller) {
                $isOK = false;
            }
            if($item->action != '*' && $this->action != $item->action) {
                $isOK = false;
            }

            if($isOK == true) return $isOK;
        }
        return $isOK;
    }
}

/* Acl.php ends here */