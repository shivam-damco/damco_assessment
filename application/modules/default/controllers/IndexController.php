<?php

class Default_IndexController extends Zend_Controller_Action {
    protected $_auth;
    protected $_user;
    protected $_redirector = null;
    private $_configObject;

    public function init() 
    {
        $this->_configObject = new Survey_Model_Config();
        $this->_redirector = $this->_helper->getHelper('Redirector');

        $this->_helper->layout->setLayout('login');
        $this->_auth = Zend_Auth::getInstance();
        $this->_user = $this->_auth->getIdentity();

        $get = $this->getRequest()->getParams();
        if ( $this->_auth->hasIdentity() && $this->getRequest()->getActionName() != 'logout' ) 
        {
            $this->_redirector->gotoSimple('index', 'index', 'event');
        }
    }

    public function indexAction() {
        // create login form object
        $loginForm = new Default_Form_Login();

        $userModel = new Default_Model_UsersMapper();
        
        if ($this->getRequest()->isPost()) {

            if ($loginForm->isValid($_POST)) {
                $username = $this->getRequest()->getPost('username');
                $password = $this->getRequest()->getPost('password');
                $dataArr = array(
                    'username' => $username,
                    'password' => $password
                );
                $result = $userModel->auth($dataArr);
                if ( $result ) {
                    //redirect to member dashboard					
                    //$this->_redirector->gotoSimple('index', 'index', 'event');
                    $this->_redirector->gotoSimple('index', 'assessments', 'assessment');
                } else {
                    //redirect to login page with flash messenger error message
                    $this->_helper->flashMessenger->addMessage('Either Username or Password is wrong!');
                    $this->_redirector->gotoSimple('index', 'index', 'default');
                }
            }
        }

        $this->view->form = $loginForm;
        $this->view->flashMessages = $this->_helper->flashMessenger->getMessages();
    }
    
    public function logoutAction() {
    	$this->objUserActivity = new Report_Model_UserActivity();
    	
    	$this->objUserActivity->update(array(
                'logged_out_time' => date('y-m-d G:i:s'),
            ), ' session_id = "'.session_id().'"');
    	
        $this->_auth->clearIdentity();
        Zend_Session::destroy();
        $this->_redirector->gotoSimple('index', 'index', 'default');
        exit;
 }
	
}

