<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initConfig() {
        Zend_Registry::set('config', $this->getOptions());
    }

    /**
     *
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _initDb()
    {
        // Define application environment
        $appDbConfig = $this->_options['resources']['db'];
        $adapter = Zend_Db::factory($appDbConfig['adapter'], $appDbConfig['params']);
        Zend_Db_Table_Abstract::setDefaultAdapter($adapter);
        Zend_Registry::set('TRIUMPHDB', self::setDbProfiler($adapter, '[' . $appDbConfig['params']['dbname'] . '] : '));
        return $adapter;
    }
    
    /**
     * This function defines resources to be loaded with zend autoloader and it also sets different auth storage for different modules viz., default and admin
     * @author Neeraj Garg
     * */
    protected function _initAppAutoload() {

        $autoLoader = Zend_Loader_Autoloader::getInstance();

        $resourceLoader = new Zend_Loader_Autoloader_Resource(array('basePath' => APPLICATION_PATH, 'namespace' => 'Application', 'resourceTypes' => array('form' => array('path' => 'forms/', 'namespace' => 'Form_'), 'model' => array('path' => 'models/', 'namespace' => 'Model_'))));
        $autoLoader->registerNamespace('Damco_Core_');
        $autoLoader->registerNamespace('Damco_tcpdf_');
        $autoLoader->registerNamespace('Damco_Excelwriter_');
        Zend_Session::start();
        // // Return it so that it can be stored by the bootstrap

        // if (get_magic_quotes_gpc()) {

        //     function stripMagicQuotes(&$value) {
        //         $value = (is_array($value)) ? array_map('stripMagicQuotes', $value) : stripslashes($value);
        //         return $value;
        //     }

        //     stripMagicQuotes($_GET);
        //     stripMagicQuotes($_POST);
        //     stripMagicQuotes($_COOKIE);
        // }


        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setControllerDirectory(array(
            "default" => APPLICATION_PATH . "/controllers"
        ));

        $router = new Zend_Controller_Router_Rewrite();
        $request = new Zend_Controller_Request_Http();
        $router->route($request);

        $this->auth = Zend_Auth::getInstance();
        $frontController->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());



        $frontController->setDefaultControllerName('index');
        $frontController->setDefaultAction('index');
        $frontController->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array(
            'module' => 'default',
            'controller' => 'error',
            'action' => 'error'
                )
                )
        );

       /* to implement   
        *  $translate = new Zend_Translate( array(
            'adapter' => 'gettext',
            'content' => APPLICATION_PATH . '/languages/en.mo',
            'locale' => 'en'
        ) );
        Zend_Registry::set('Zend_Translate', $translate); */

        return $autoLoader;
    }

    protected function _initPlugins() {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Damco_Controller_Plugin_ACL());
        $front->registerPlugin(new Damco_Controller_Plugin_Locale());
    }
    
    /**
     * Set Db profiler.
     * 
     * @param string $msg message
     * @param Zend_Db_Adapter_Abstract $dbAdapter database adapter object
     * @throws Zend_Exception
     * @author Maninder Bali
     * @return Zend_Db_Adapter_Abstract
     */
    protected function setDbProfiler(Zend_Db_Adapter_Abstract $dbAdapter = null, $msg = 'DB Queries : ')
    {
        if (null == $dbAdapter) {
            $dbAdapter = $this->bootstrapDb()
                ->getResource('db');
        }
        switch(strtolower(APPLICATION_ENV)) {
            case 'production':
                $profiler = new Zend_Db_Profiler();
                $profiler->setEnabled(true);
                $dbAdapter->setProfiler($profiler);
                // Not considered yet.
                break;
            
            case 'staging':
                // Not considered yet.
                break;
            
            case 'testing':
                // Not considered yet.
                break;
            
            case 'development':
                $profiler = new Zend_Db_Profiler_Firebug($msg . ucfirst(strtolower(APPLICATION_ENV)));
                $profiler->setEnabled(true);
                $dbAdapter->setProfiler($profiler);
                break;
            default:
                throw new Zend_Exception('Unknown <b>Application Environment</b> to create db profiler in bootstrap.', Zend_Log::WARN);
        }
        
        return $dbAdapter;
    }

    /**
     *
     * @return Zend_Log
     * @author Maninder Bali
     * @throws Zend_Exception
     */
    protected function _initLog()
    {
        $logger = new Zend_Log();
        switch(strtolower(APPLICATION_ENV)) {
            case 'production':
            case 'staging':
            case 'testing':
                $writer = new Zend_Log_Writer_Null;
                $logger = new Zend_Log($writer);
                break;

            case 'development':
                // Firebug is available in development environment only.
                $firebug = new Zend_Log_Writer_Firebug();
                $logger->addWriter($firebug);

                break;
            
            default:
                throw new Zend_Exception('Unknown <b>Application Environment</b> to create log writer in bootstrap.', Zend_Log::WARN);
        }
        Zend_Registry::set("logger", $logger);
        return $logger;
    }    
    
}
