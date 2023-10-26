<?php

class Survey_Form_Surveycategories extends Zend_Form {

    private $objRegistry;
    public $objRequest;
    public $_config = null;
    

    public function init() {
        $this->objRegistry = Zend_Registry::getInstance();
    }

    public function __construct() {
        
        $this->setName('add_survey_category');
        $surveycategoryId = new Zend_Form_Element_Hidden('survey_category_id');
        
        
        $survey_category_name = new Zend_Form_Element_Text('survey_category_name');
        $survey_category_name->setLabel('Survey Category Name')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter survey category name'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'))
                ->setAttribs(array('placeholder' => 'Please Add Survey Category Name'));
          
        $submit = new Zend_Form_Element_Button('submitbtn');
        $submit->setAttrib('id', 'submit')
                ->setAttrib('type', 'submit')
                ->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary')
                ->setOrder(101);
        
        $reset = new Zend_Form_Element_Button('resetbtn');
        $reset->setAttrib('id', 'submit')
                ->setAttrib('type', 'reset')
                ->setLabel('Cancel')
                ->setAttrib('class', 'btn btn-primary')
                ->setOrder(101);
        
        $this->addElements(array($submit,$survey_category_name,$reset, $surveycategoryId));
    }
    
    public function isValid($data) 
    {
        
        $valid = parent::isValid($data);
        return $valid;
    }
    
    
}
