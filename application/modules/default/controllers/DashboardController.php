<?php

class Default_DashboardController extends Damco_Core_CoreController
{

    protected $_auth = null;
    protected $_redirector = null;

    public function init()
    {
        /* Initialize action controller here */
                parent::init();
		$this->_redirector = $this->_helper->getHelper('Redirector');
		$this->_helper->layout->setLayout('layout');
                $this->_helper->layout()->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
		$this->_auth = Zend_Auth::getInstance();
    }

    public function indexAction()
    {    
       
    }

    public function logoutAction()
    {
        $this->_auth->clearIdentity();
        Zend_Session::destroy();
        $configObj = new Survey_Model_Config();
        $url = $configObj->fetchRow('config_var = "TOL_URL"')->toArray();
        $this->redirect($url['config_val']);
        exit;
    }

    /**
     * Action to test ACL
     **/
    public function corpAction()
    {
        // action body
        
       
		echo"Welcome corporate user"; 
                
    }
	
	/**
     * Action to test ACL
     **/
    public function branchAction()
    {
        // action body
		echo "Welcome branch user";
    }
    
    
}




