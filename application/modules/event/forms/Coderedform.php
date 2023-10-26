<?php

class Event_Form_Coderedform extends Zend_Form {

    private $objRegistry;
    public $objRequest;

    public function init() {
        $this->objRegistry = Zend_Registry::getInstance();
    }

    public function __construct() {

        $EventId = new Zend_Form_Element_Hidden('eventid');

        /* Declaring a form element for Code Red Problem */
        $problem = new Zend_Form_Element_Textarea('problem');
        $problem->setLabel('Comment')
                ->setAttrib('rows', '4')
                ->setAttrib('cols', '61')
                ->setAttrib('class', 'dateDiv selectpicker form-control')
                ->removeDecorator("Label")
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                // ->addValidator('NotEmpty')
                ->removeDecorator("errors")
                ->setRequired(TRUE);


        //Validation logic  
        $objVldEmptyProblem = new Zend_Validate_NotEmpty();
        $objVldEmptyProblem->setMessage("Please Enter Problem ");
        $problem->addValidator($objVldEmptyProblem, TRUE)->setRequired(TRUE);
        
        
        
        /* Declaring a form element for Code Red Solution */
        $solution = new Zend_Form_Element_Textarea('solution');
        $solution->setLabel('Comment')
                ->setAttrib('rows', '4')
                ->setAttrib('cols', '61')
                ->setAttrib('class', 'dateDiv selectpicker form-control')
                ->removeDecorator("Label")
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                // ->addValidator('NotEmpty')
                ->removeDecorator("errors")
                ->setRequired(TRUE);


        //Validation logic  
        $objVldEmptyComment = new Zend_Validate_NotEmpty();
        $objVldEmptyComment->setMessage("Please Enter Solution ");
        $solution->addValidator($objVldEmptyComment, TRUE)->setRequired(TRUE);

        /* Declaring a form element for Code Red Status */
        $coderedstatus = new Zend_Form_Element_Select('status');
        $coderedstatus->setLabel('Status')
                ->removeDecorator("Label")
                ->setAttrib('class', 'dateDiv selectpicker form-control')
                ->setMultiOptions(array('a' => 'dealers'))
                ->setRegisterInArrayValidator(FALSE)
                ->removeDecorator("errors");
        /* //Validation logic  
          $objVldEmptyCoderedstatus = new Zend_Validate_NotEmpty();
          $objVldEmptyCoderedstatus->setMessage("Please Select Status ");
          $coderedstatus->addValidator($objVldEmptyCoderedstatus, TRUE)->setRequired(TRUE); */


        $this->addElements(array($EventId, $problem,$solution, $coderedstatus));
    }

}