<?php

class Settings_Form_Settings extends Zend_Form {

//    private $objRegistry;
//    public $objRequest;
//
//    public function init() {
//        $this->objRegistry = Zend_Registry::getInstance();
//    }
//
//    public function __construct($roles = array(), $getRoleId) {
//        // $this->setName('Edit_event');
//        /* Declaring a form element for Dealer Name */
//        $Roles = new Zend_Form_Element_Select('role_id', array('onchange' => 'changeRole();'));
//        $Roles->setLabel('Role_id')
//                ->removeDecorator("Label")
//                ->setAttrib('class', 'dateDiv selectpicker form-control')
//                ->setMultiOptions($roles)
//                ->removeDecorator("errors")
//                ->setOptions(array('multiple' => FALSE))
//                ->setRegisterInArrayValidator(FALSE)
//                ->setRequired(TRUE)
//                ->setValue($getRoleId);
//
//        $this->addElements(array($Roles));
//    }

 /**
     * default value for Role dropdown.
     */
    private $roles_options = array('' => '');

    /**
     * (non-PHPdoc)
     * 
     * @return null
     * @see Zend_Form::init()
     */
    public function init()
    {
        /**
         * Element for Roles 
         */
        
        $roles =
                new Zend_Form_Element_Select('role_id', array('onchange' => 'changeRole();'));
        $roles->setLabel('Role_id')
                ->removeDecorator("Label")
                          ->setAttrib('class', 'dateDiv selectpicker selectmenu')
                          ->removeDecorator("errors")
                ->setDisableTranslator(true)
                ->setOptions(array('multiple' => FALSE))
                ->setRegisterInArrayValidator(FALSE)
                ->setRequired(true)
                ->addMultiOptions($this->roles_options);
       
        $this->addElement($roles);
    }

    /**
     * Setter for Roles dropdown.
     * 
     * @author Maninder Bali
     * @param array $dbOptionRows Roles
     * @param boolean $pushData Push the options into element.
     * @param boolean $append Append options or overwrite
     * @return Settings_Form_Settings
     */
    public function setRoles_options($dbOptionRows,
                                                 $pushData = false,
                                                 $append = false)
    {
        $elementId = 'role_id';
        $elementOptions = strtolower(substr(__FUNCTION__, 3));

        if (empty($dbOptionRows)) {
            $dbOptionRows = array();
            $append = true;
        }

        $append ? NULL
                : ($this->$elementOptions = array());
        
        foreach ($dbOptionRows as $row) {
            $displayFormat = $row['label'];
            $options = &$this->$elementOptions;
            $options[$row['id']] = $displayFormat;
        }

        if ($pushData) {
            $selectElement = $this->getElement($elementId);
            $append ? $selectElement->addMultiOptions($this->$elementOptions)
                    : $selectElement->setMultiOptions($this->$elementOptions);
        }
        
        return $this;
    }
}

