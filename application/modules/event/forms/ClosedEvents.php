<?php

class Event_Form_ClosedEvents extends Zend_Form {

    private $objRegistry;
    public $objRequest;

    public function init() {
        $this->objRegistry = Zend_Registry::getInstance();
    }

    public function __construct() {


        $this->setName('Edit_event');
        $EventId = new Zend_Form_Element_Hidden('eventid');

        /* Declaring a form element for Dealer Name */
        $dealerid = new Zend_Form_Element_Select('dealer_id');
        $dealerid->setLabel('Dealer Name')
                ->removeDecorator("Label")
                ->setAttrib('class', 'dateDiv selectpicker form-control')
                ->setMultiOptions(array('a' => 'dealers'))
                ->removeDecorator("errors")
                ->setOptions(array('multiple' => FALSE))
                ->setRegisterInArrayValidator(FALSE)
                ->setRequired(TRUE);
        //Validation logic  
        $objVldEmptyDealerId = new Zend_Validate_GreaterThan(0, true);
        $objVldEmptyDealerId->setMessage("Please Select Dealer ");
        $dealerid->addValidator($objVldEmptyDealerId, TRUE)->setRequired(TRUE);

        /* Declaring a form element for Email */
        $SupplierEmail = new Zend_Form_Element_Text('email_address');
        $SupplierEmail->setLabel('Email')
                ->setAttrib('maxlength', '100')
                ->setAttrib('class', 'form-control')
                ->addValidator('EmailAddress', TRUE) // added true here
                ->setAttrib('size', '40')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->removeDecorator("Label")
                ->removeDecorator("errors")
                ->setRequired(TRUE);
        //Validation logic  
        $objVldEmptyEmail = new Zend_Validate_NotEmpty();
        $objVldEmptyEmail->setMessage("Please enter Email Id");
        $SupplierEmail->addValidator($objVldEmptyEmail, TRUE)->setRequired(TRUE);

        $this->addElements(array($EventId, $dealerid, $SupplierEmail));
    }

}
