<?php

class Default_Form_Addlanguage extends Zend_Form
{

    private $language_options = array('' => '');
     
    public function init()
    {
        $this->setAction('')
                ->setMethod('post')
                ->setAttrib('name', 'login');
        $questionId = new Zend_Form_Element_Text('questionid');
        $questionId->setLabel('Question Id');
        $this->addElement($questionId);
        
         /**
         * Element for Languages
         */
        
        $languages =
                new Zend_Form_Element_Select('langid');
        $languages->setLabel('Please select preferred language')
                          ->setAttrib('class', 'dateDiv selectpicker form-control')
                          ->removeDecorator("errors")
                ->setOptions(array('multiple' => FALSE))
                ->setRegisterInArrayValidator(FALSE)
                ->setRequired(true)
                ->addMultiOptions($this->language_options);
       
        $this->addElement($languages);
        
        $question = new Zend_Form_Element_Textarea('question');
        $question->setLabel('Question Text')->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Question Text');
        $this->addElement($question);
        
        $responseA = new Zend_Form_Element_Textarea('response1');
        $responseA->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Response 1');
        $this->addElement($responseA);
        
       
        $responseB = new Zend_Form_Element_Textarea('response2');
        $responseB->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Response 2');
        $this->addElement($responseB);
        
        
        $responseC = new Zend_Form_Element_Textarea('response3');
        $responseC->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Response 3');
        $this->addElement($responseC);
        
        
        $responseD = new Zend_Form_Element_Textarea('response4');
        $responseD->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Response 4');
        $this->addElement($responseD);
        
        
        $responseE = new Zend_Form_Element_Textarea('response5');
        $responseE->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Response 5');
        $this->addElement($responseE);
        
        
        $responseF = new Zend_Form_Element_Textarea('response6');
        $responseF->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Response 6');
        $this->addElement($responseF);
        
        
        $responseG = new Zend_Form_Element_Textarea('response7');
        $responseG->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Response 7');
        $this->addElement($responseG);
        
        
        $responseH = new Zend_Form_Element_Textarea('response8');
        $responseH->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Response 8');
        $this->addElement($responseH);
        
        
        $responseI = new Zend_Form_Element_Textarea('response9');
        $responseI->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Response 9');
        $this->addElement($responseI);
        
        
        $responseJ = new Zend_Form_Element_Textarea('response10');
        $responseJ->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Response 10');
        $this->addElement($responseJ);
        
        
        $responseK = new Zend_Form_Element_Textarea('response11');
        $responseK->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Response 11');
        $this->addElement($responseK);
        
        $gradeLabelText = new Zend_Form_Element_Textarea('grade_label_text');
        $gradeLabelText->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Grade Label Text');
        $this->addElement($gradeLabelText);
        
         $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Submit',
        ));
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
    public function setLanguage_options($dbOptionRows,
                                                 $pushData = false,
                                                 $append = false)
    {
        $elementId = 'langid';
        $elementOptions = strtolower(substr(__FUNCTION__, 3));

        if (empty($dbOptionRows)) {
            $dbOptionRows = array();
            $append = true;
        }

        $append ? NULL
                : ($this->$elementOptions = array());
        
        foreach ($dbOptionRows as $row) {
            $displayFormat = $row['lang_name'];
            $options = &$this->$elementOptions;
            $options[$row['langid']] = $displayFormat;
        }

        if ($pushData) {
            $selectElement = $this->getElement($elementId);
            $append ? $selectElement->addMultiOptions($this->$elementOptions)
                    : $selectElement->setMultiOptions($this->$elementOptions);
        }
        
        return $this;
    }
}

