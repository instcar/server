<?php
namespace Instcar\Server;

class Task
{
    public function registerAutoloaders()
    {
        $loader = new \Phalcon\Loader();
        
        $loader->registerNamespaces(array(
            'Instcar\Server\Tasks'   => __DIR__.'/tasks/',
            'Instcar\Server\Models'  => __DIR__.'/models/',
            'Instcar\Server\Logics'  => __DIR__.'/logics/',
        ))->register();
    }

   /**
    *
    * Register the services here to make them module-specific
    *
    */
    public function registerServices($di)
    {
        // get bootstrap obj
        $bootstrap = $di->get('bootstrap');

        // get config class name
        $confClass = $bootstrap->getConfClass();

        // module config
        $mConfPath = __DIR__.'/confs/'.PHALCON_ENV.'.'.PHALCON_CONF_TYPE;
	if(!is_file($mConfPath)) {
	  throw new \Phalcon\Config\Exception("Module config file not exist, file position: {$mConfPath}");
	}
        if(PHALCON_CONF_TYPE == 'ini') {
            $mConfig = new $confClass($mConfPath);
        } else if(PHALCON_CONF_TYPE == 'php') {
            $mConfig = new $confClass(require_once($mConfPath));
        }

        // global config
        $gConfig = $di->get('config');

        // merge module config and global config, module's will override global's
        $gConfig->merge($mConfig);

        // set config back
        $di->set('config', $gConfig);

        // Registering a dispatcher
        $di->set('dispatcher', function () {
                $dispatcher = new \Phalcon\CLI\Dispatcher();
                $dispatcher->setDefaultNamespace("Instcar\Server\Tasks\\");
                return $dispatcher;
            });
    }

}
