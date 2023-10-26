<?php
       
class Default_Form_ChangePassword extends Zend_Form {

    private $objRegistry;
    public $objRequest;

    public function init() {
	
        $this->objRegistry = Zend_Registry::getInstance();
    }

    public function __construct() {
		
        $oldPassword = new Zend_Form_Element_Password('old_password');
        $oldPassword->setLabel('Password')
                 ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter password'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'));
                //->setAttribs(array('placeholder' => 'Please Add assessment'));
        
        $newPassword = new Zend_Form_Element_Password('new_password');
        $newPassword->setLabel('New Password')
                 ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter new password'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'));
                //->setAttribs(array('placeholder' => 'Please Add assessment'));
        
        $confirmPassword = new Zend_Form_Element_Password('confirm_password');
        $confirmPassword->setLabel('Confirm Password')
                 ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter confirm password'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'));
                //->setAttribs(array('placeholder' => 'Please Add assessment'));

        
        $submit = new Zend_Form_Element_Button('submitbtn');
        $submit->setAttrib('id', 'submit')
                ->setAttrib('type', 'submit')
                ->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary')
                ->setOrder(101);
        
     	//echo "Hello111";exit ;
        $this->addElements(array($submit,$oldPassword, $newPassword, $confirmPassword));
	//echo "dddddd";exit;
    }
    
    public function isValid($data) 
    {
        
        $valid = parent::isValid($data);
        return true;
    }
}
