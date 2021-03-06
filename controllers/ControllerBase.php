<?php 
namespace Instcar\Server\Controllers;

class ControllerBase extends \Phalcon\Mvc\Controller
{
    protected $config;
    protected $user;
    protected $breadcrumb = array();
    
    protected function initialize()
    {
        $this->config = $this->getDI()->get('config');

        if($this->di->has('user')) {
            $this->user = $this->di->get('user');
        } else {
            $this->user = null;
        }

        $this->breadcrumb = array(
            'module'     => $this->dispatcher->getModuleName(),
            'controller' => $this->dispatcher->getControllerName(),
            'action'     => $this->dispatcher->getActionName(),
        );
    }

    protected function forward($uri)
    {
    	$uriParts = explode('/', $uri);
    	return $this->dispatcher->forward(
    		array(
                'module'     => $uriParts[0],
    			'controller' => $uriParts[1], 
    			'action'     => $uriParts[2]
    		)
    	);
    }

    protected function flashJson($status, $data = array(), $msg="")
    {
        $this->response->setContentType('application/json');
        $this->response->sendHeaders();
        if(empty($data)) {
            echo json_encode(array(
                'status' => intval($status),
                'data'   => $data,
                'msg'    => $msg,
            ), JSON_FORCE_OBJECT );
        } else {
            echo json_encode(array(
                'status' => intval($status),
                'data'   => $data,
                'msg'    => $msg,
            ));
        }
        
        exit ;
    }
}
