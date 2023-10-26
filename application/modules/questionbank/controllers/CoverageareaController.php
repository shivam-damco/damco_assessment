<?php
error_reporting(1);
class Questionbank_CoverageareaController extends Damco_Core_CoreController
{
    protected $_request;
    protected $_modelObj;
    protected $_formObj;


    public function init()
    {
        /* Initialize action controller here */
        parent::init();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_helper->layout->setLayout('layout');
        $this->_auth = Zend_Auth::getInstance();
        $this->_request = $this->getRequest();
        $this->_formObj = new Questionbank_Form_Question();
        if(!$this->_auth->getIdentity()){
            $this->_redirect($this->view->serverUrl() );
        }
    }
    
    // List Coverage Area Data
    public function indexAction(){
    }

    // Add Coverage Area
    public function addAction(){
    
    
    }
    
    // Delete Coverage Area
    public function deleteAction(){
     
    }

    // Edit Coverage Area
    public function editAction(){
     
    }    

}
?>
