<?php

class Default_UserController extends Damco_Core_CoreController
{

    protected $_auth = null;
    protected $_redirector = null;

    public function init()
    {
        /* Initialize action controller here */
		
        parent::init();
		$this->_redirector = $this->_helper->getHelper('Redirector');
		$this->_auth = Zend_Auth::getInstance();
        $this->_userModelObject = new Survey_Model_EventTypes();
	
    }

    public function indexAction()
    {    
       
    }

    

    /**
     * Action to change Password
     **/
  
   
   public function changepasswordAction() {
        //$this->_editoptions = new Event_Model_Events();
        $get = $this->getRequest()->getParams();
               
        $form = new Default_Form_ChangePassword();        
        if ($this->_request->isPost()) {
            $message = '';
            $form->isValid($get);
            if ((!$form->isValid($get))) {
                if (array_key_exists("old_passowrd", $form->getmessages())) {
                    $message.= "<br/>Please enter password";
                }
                
                if (array_key_exists("new_passowrd", $form->getmessages())) {
                    $message.= "<br/>Please enter new password";
                }
                /*if (array_key_exists("description", $form->getmessages())) {
                    $message.= "<br/>Please enter description";
                }*/
                
                if (array_key_exists("confirm_password", $form->getmessages())) {
                    $message.= "<br/>Please enter confirm password";
                }
                
                if($get['new_password']!= $get['confirm_password']){
                    $message.= "<br/>New password and confirm password does not match";
                }
                
                             
         
                $this->view->messages = $message;
            } else {
		        if($get['new_password']!=$get['confirm_password']){
                    $message.= "<br/>New password and confirm password does not match";
                    $this->view->messages = $message;
                }
		
			   //echo $get['old_password'];exit;
               $encryptedPassword =md5($get['old_password']);
                $isOldPasswordMatch = $this->_userModelObject->checkOldPassword($this->_user->id,$encryptedPassword);
                if(!$isOldPasswordMatch)
                {
                    $message.= "<br/>Please enter correct password";
                    $this->view->messages = $message;
                }
                else
                {
                    $oldPassword = $get['old_password'];
                    $newPassword = md5($get['new_password']);       
                    $modified = $this->_user->id;
                    $modifiedBy = $this->_user->id;
                    $userId = $this->_user->id;
                    //print_r($this->_user->id);exit;

                    $userDetailsArr = array('password' => $newPassword, 'modified' => $modified, 'modified_by' => $modifiedBy, 'id' => $userId);

                    $this->_userModelObject->updatePassword($userDetailsArr);

                    $this->_flashMessenger->addMessage(array(
                        'success' => 'Password Changed Successfully'
                    ));
                    $this->_redirect($this->view->serverUrl() . '/assessment/assessments');
                    
                }
                
                
            }
        }
	
        $this->view->form = $form;
    }
    
    
}











