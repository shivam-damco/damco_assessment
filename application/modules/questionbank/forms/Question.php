<?php
/*
 * @author sachin 
 * 
 */
class Questionbank_Form_Question extends Zend_Form
{

    //private $language_options = array('' => '');
     
    public function init()
    {
        $this->setAction('')
                ->setMethod('post')
                ->setAttrib('name', 'question');
        $questionNumber = new Zend_Form_Element_Text('question_number');
        $questionNumber->setLabel('Question Label');
        $this->addElement($questionNumber);
        
        $eventType = new Zend_Form_Element_Text('event_type');
        $eventType->setAttrib('hidden','hidden');//->setRequired(true);
        $this->addElement($eventType);
         /**
         * Element for Languages
         */
        
        $coverage_area = new Zend_Form_Element_Select('coverage_area');
        $coverage_area->setLabel('Coverage Area')
                          ->setAttrib('class', 'selectpicker form-control')
                          ->removeDecorator("errors")
                ->setOptions(array('multiple' => FALSE))
                ->setRegisterInArrayValidator(FALSE)
                ->setRequired(true)
                ->addMultiOptions(array(''=>'Please Select','Exception-Handeling'=>'Exception Handeling','Databse'=>'Database','OOPs' => 'OOPs', 'Architecture' => 'Architecture', 'Git' => 'Git', 
                    'Server-Configuration' => 'Server Configuratio'));
        $this->addElement($coverage_area);

        $coverage_level = new Zend_Form_Element_Select('coverage_level');
        $coverage_level->setLabel('Levels')
                          ->setAttrib('class', 'dateDiv selectpicker form-control')
                          ->removeDecorator("errors")
                ->setOptions(array('multiple' => FALSE))
                ->setRegisterInArrayValidator(FALSE)
                ->setRequired(true)
                ->addMultiOptions(array(''=>'Please Select','T0'=>'T0','T1'=>'T1',
                'T2'=>'T2','T3'=>'T3'));
        $this->addElement($coverage_level);


        $input_type = new Zend_Form_Element_Select('input_type');
        $input_type->setLabel('Question Type')
                          ->setAttrib('class', 'dateDiv selectpicker form-control')
                          ->removeDecorator("errors")
                ->setOptions(array('multiple' => FALSE))
                ->setRegisterInArrayValidator(FALSE)
                ->setRequired(true)
                ->addMultiOptions(array(''=>'Please Select','radio'=>'MCQ',
                    'textarea'=>'Case Study','checkbox' => 'MCQ-Multi Selection',
                    'textarea-1' => 'Scenario Based', 
                    'textarea-2' => 'Coding Question', 
                    'label' => 'Label', 'date' => 'Date', ));
        $this->addElement($input_type);
        
        
        $max_res_limit = new Zend_Form_Element_Select('max_res_limit');
        $max_res_limit->setLabel('Please select number of options')
                          ->setAttrib('class', 'dateDiv selectpicker form-control')
                          ->removeDecorator("errors")
                ->setOptions(array('multiple' => FALSE))
                ->setRegisterInArrayValidator(FALSE)
                //->setRequired(true)
                ->addMultiOptions(array('' => 'Please Select','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','11'=>'11',
                    '12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17','18'=>'18','19'=>'19','20'=>'20'));
       
        $this->addElement($max_res_limit);
        
        $question = new Zend_Form_Element_Textarea('question');
        $question->setLabel('Question Text')->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Question Text')->setRequired(true);
        $this->addElement($question);
        
        $event_typeid = new Zend_Form_Element_Textarea('event_typeid');
        $event_typeid->setAttrib('hidden', 'hidden');
        $this->addElement($event_typeid);
        
        $responseA = new Zend_Form_Element_Textarea('response1');
        $responseA->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 1');
        $this->addElement($responseA);
        
       
        $responseB = new Zend_Form_Element_Textarea('response2');
        $responseB->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 2');
        $this->addElement($responseB);
        
        
        $responseC = new Zend_Form_Element_Textarea('response3');
        $responseC->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 3');
        $this->addElement($responseC);
        
        
        $responseD = new Zend_Form_Element_Textarea('response4');
        $responseD->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 4');
        $this->addElement($responseD);
        
        
        $responseE = new Zend_Form_Element_Textarea('response5');
        $responseE->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 5');
        $this->addElement($responseE);
        
        
        $responseF = new Zend_Form_Element_Textarea('response6');
        $responseF->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 6');
        $this->addElement($responseF);
        
        
        $responseG = new Zend_Form_Element_Textarea('response7');
        $responseG->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 7');
        $this->addElement($responseG);
        
        
        $responseH = new Zend_Form_Element_Textarea('response8');
        $responseH->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 8');
        $this->addElement($responseH);
        
        
        $responseI = new Zend_Form_Element_Textarea('response9');
        $responseI->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 9');
        $this->addElement($responseI);
        
        
        $responseJ = new Zend_Form_Element_Textarea('response10');
        $responseJ->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 10');
        $this->addElement($responseJ);
        
        
        $responseK = new Zend_Form_Element_Textarea('response11');
        $responseK->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 11');
        $this->addElement($responseK);
        
        $responseL = new Zend_Form_Element_Textarea('response12');
        $responseL->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 12');
        $this->addElement($responseL);
        
        $responseM = new Zend_Form_Element_Textarea('response13');
        $responseM->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 13');
        $this->addElement($responseM);
        
        $responseN = new Zend_Form_Element_Textarea('response14');
        $responseN->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 14');
        $this->addElement($responseN);
        
        $responseO = new Zend_Form_Element_Textarea('response15');
        $responseO->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 15');
        $this->addElement($responseO);
        
        $responseP = new Zend_Form_Element_Textarea('response16');
        $responseP->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 16');
        $this->addElement($responseP);
        
        $responseQ = new Zend_Form_Element_Textarea('response17');
        $responseQ->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 17');
        $this->addElement($responseQ);
        
        $responseR = new Zend_Form_Element_Textarea('response18');
        $responseR->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 18');
        $this->addElement($responseR);
        
        $responseS = new Zend_Form_Element_Textarea('response19');
        $responseS->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 19');
        $this->addElement($responseS);
        
        $responseT = new Zend_Form_Element_Textarea('response20');
        $responseT->setOptions(array('cols' => '50', 'rows' => '4'))->setLabel('Option 20');
        $this->addElement($responseT);

        for($i = 1; $i <= 20; $i++){
            $optionresponse = new Zend_Form_Element_Radio('optionresponse' . $i);
            $optionresponse->setAttribs(array('class' => 'custom-change-type', 'name' =>'correctanswer', 'data-keyvalue' => 'option'.$i))->setLabel('Correct Option' .$i. ':')
                       ->setRequired(false)
                       ->addMultiOptions(array('score'.$i => ''))
                       ->setSeparator(' ');
            $this->addElement($optionresponse);
        }
		
        $questionScore = new Zend_Form_Element_Text('max_score');
        $questionScore->setLabel('Question Score')->setRequired(false);
        $this->addElement($questionScore);

        $add_record = new Zend_Form_Element_File('add_record');
        $add_record->setAttrib('enctype', 'multipart/form-data');
        $add_record->setLabel('Upload a File:');
        
        $submit = new Zend_Form_Element_Button('submit');
        $submit->setAttrib('id', 'submit')
                ->setAttrib('type', 'submit')
                ->setLabel('Add')
                ->setAttrib('class', 'btn btn-primary')
                ->setOrder(101);
        $this->addElement($submit);
        
    }
}
?>
