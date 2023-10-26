<?php
/*if( PHP_SAPI != 'cli'
	&& $_SERVER["HTTP_X_FORWARDED_PROTO"] == "http" ){
	header("location:https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	die;
}*/
error_reporting(1);

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

define("DOCUMENT_ROOT", $_SERVER['DOCUMENT_ROOT']);

//define("HTTP_ROOT", $_SERVER['HTTP_HOST']);
if (isset($_SERVER['HTTP_HOST']) === TRUE) 
{
  define("HTTP_ROOT", $_SERVER['HTTP_HOST']);
}

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

//Dipa
$application = new Zend_Application(
    APPLICATION_ENV, 
     array(
        'config' => array(
            APPLICATION_PATH . '/configs/application.ini',
            APPLICATION_PATH . '/configs/local.ini',
        )
    )
);
if(PHP_SAPI == 'cli')
{   
    try {
        $opts = new Zend_Console_Getopt(
            array(
                'help|h' => 'Displays usage information.',
                'action|a=s' => 'Action to perform in format of module.controller.action',
                'verbose|v' => 'Verbose messages will be dumped to the default output.',
                'development|d' => 'Enables development mode.',
            )
        );
        $opts->parse();
    } catch (Zend_Console_Getopt_Exception $e) {
        exit($e->getMessage() ."\n\n". $e->getUsageMessage());
    }

    if(isset($opts->h)) {
        echo $opts->getUsageMessage();
        exit;
    }
    if(isset($opts->a)) {
        $reqRoute = array_reverse(explode('.',$opts->a));

        @list($action,$controller,$module) = $reqRoute;

        $request = new Zend_Controller_Request_Simple($action,$controller,$module);
        $front = Zend_Controller_Front::getInstance();

        $front->setRequest($request);
        $front->setRouter(new Damco_Controller_Router_Cli());
        

        $front->setResponse(new Zend_Controller_Response_Cli());

        $front->throwExceptions(true);
        $front->addModuleDirectory(APPLICATION_PATH . '/modules/');
        $application = new Zend_Application(
            APPLICATION_ENV, 
             array(
                'config' => array(
                    APPLICATION_PATH . '/configs/application.ini',
                    APPLICATION_PATH . '/configs/local.ini',
                )
            )
        );
        $application->bootstrap();
        $front->dispatch();       
    }
    
}
else
{
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    } else {
        $protocol = 'http';
    }
    define("HTTP_PATH", $protocol.'://'.$_SERVER['HTTP_HOST']);
    $application->bootstrap()
        ->run();
}
die;