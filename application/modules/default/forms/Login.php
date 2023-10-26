<?php

class Default_Form_Login extends Zend_Form
{

    public function init()
    {
        /*-- For username --*/
		$username = $this->createElement('text', 'username');
		$username->addFilter('StringTrim');
		$username->setAttribs(array('maxlength'=>'100','class'=>"form-control", 'required'=>'true', 'placeholder' => 'Username'));		
		$username->setRequired(TRUE);
	    $username->addErrorMessage("Please enter username.");
		$username->setDecorators(array('ViewHelper','Errors'));
		$username->removeDecorator('Errors');
		$this->addElement($username); 

		/*-- For password --*/
		$pass = $this->createElement('password', 'password');
		$pass->addFilter('StringTrim');	
		$pass->setAttribs(array('maxlength'=>'50','class'=>"form-control", 'required'=>'true', 'placeholder' => 'password'));		
		$pass->setRequired(TRUE);
	    $pass->addErrorMessage("Please enter password.");
		$pass->setDecorators(array('ViewHelper','Errors'));
		$pass->removeDecorator('Errors');
		$this->addElement($pass); 
		
		/*--Form submission login button --*/
		$login = $this->createElement('submit', 'Login');
		$login->setAttribs(array('class'=>'btn btn-primary'));
		$login->setDecorators(array('ViewHelper','Errors'));
		$login->addFilter('StringTrim');		   
		$this->addElement($login); 
    }


}

