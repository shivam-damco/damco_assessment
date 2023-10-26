<?php

class Survey_Form_Surveys extends Zend_Form {

    private $objRegistry;
    public $objRequest;
    public $_eventTypesModelObject = null;
    public $_config = null;
	public $_surveyCategoriesModelObject = null;
	public $_surveyEmailModelObject = null;
	public $_contentModelObject = null;
	public $_surveycontentModelObject = null;
    

    public function init() {
        $this->objRegistry = Zend_Registry::getInstance();
    }

    public function __construct() {
        
        $this->setName('add_survey');
        $surveyId = new Zend_Form_Element_Hidden('survey_id');
		$survey_test_email = new Zend_Form_Element_Hidden('test_email_status');
		
        
        $this->_eventTypesModelObject = new Survey_Model_EventTypes();
        $eventTYpeData = $this->_eventTypesModelObject->getAllEventTypesName();
		
		$this->_surveycontentModelObject = new Survey_Model_Survey();
		$surveycontent = $this->_surveycontentModelObject->getSurveyContents(Zend_Controller_Front::getInstance()->getRequest()->getParam( 'eventtypeid',0 ));
				
		if(count($surveycontent) == 0) {
			
			$this->_surveyEmailModelObject = new Survey_Model_AlertEmailTemplates();
			$surveyEmail = $this->_surveyEmailModelObject->getDamcoInternalSurveyEmailTemplate();		
			$this->_contentModelObject = new Survey_Model_CmsContents();
			$landingContent = $this->_contentModelObject->getContentTemplate(1);
			$thanksContent = $this->_contentModelObject->getContentTemplate(2);		
			$email_subj = $surveyEmail[0]['subject'];
			$email_cont = $surveyEmail[0]['content'];
			$landing_cont = $landingContent[0]['content'];
			$thank_cont = $thanksContent[0]['content'];		
			
		}		
		else {
			
			$email_subj = $surveycontent[0]['invite_subject'];
			$email_cont = $surveycontent[0]['invite_content'];
			$landing_cont = $surveycontent[0]['landing_page_content'];
			$thank_cont = $surveycontent[0]['thanks_message'];
			
		}
		
		$eventTypeArr = array();
        $eventTypeArr[''] = 'Please Select';
        foreach($eventTYpeData as $typeData)
        {
            $eventTypeArr[$typeData['event_typeid']] = $typeData['event_type'];
        }
		
        //$deptArr = array(''=>'Please Select','1'=>'IT','2'=>'Admin','3'=>'Account','4'=>'HR');
        
        $event_type = new Zend_Form_Element_Select('event_type');
        $event_type->setLabel('Survey Title')
                // ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => 'Please select survey'))
                ->setAttribs(array('class' => 'form-control'))
                ->addFilter('StringTrim')
                ->setAttribs(array('placeholder' => 'Please select survey'))
                ->setAttrib('disabled','disabled')
                ->setMultiOptions($eventTypeArr);
				
		//Survey Category Select Option		
		$this->_surveyCategoriesModelObject = new Survey_Model_SurveyCategories();
        $surveyCategoryData = $this->_surveyCategoriesModelObject->getAllSurveyCategoriesName();
		
		$surveyCategoryArr = array();
        $surveyCategoryArr[''] = 'Please Select';
        foreach($surveyCategoryData as $categoryData)
        {
            $surveyCategoryArr[$categoryData['survey_category_id']] = $categoryData['survey_category_name'];
        }
		
		$survey_category = new Zend_Form_Element_Select('survey_category');
        $survey_category->setLabel('Survey Category')
                ->addValidator('NotEmpty', true, array('messages' => 'Please select survey category'))
                ->setAttribs(array('class' => 'form-control'))
                ->addFilter('StringTrim')
                ->setAttribs(array('placeholder' => 'Please select'))
				->setAttrib('disabled','disabled')
                ->setMultiOptions($surveyCategoryArr);
        
        $survey_name = new Zend_Form_Element_Text('survey_name');
        $survey_name->setLabel('Survey Instance Name')
                ->setRequired(true) 
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter survey instance name'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'))
                ->setAttribs(array('placeholder' => 'Please Add Survey Instance Name'));
               
			
        
        $start_date = new Zend_Form_Element_Text('start_date');
        $start_date->setLabel('Start Date')
                 ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter start date'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'))
                ->setAttribs(array('placeholder' => 'Please Add Start Date'));
        
        $end_date = new Zend_Form_Element_Text('end_date');
        $end_date->setLabel('End Date')
                 ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter end date'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'))
                ->setAttribs(array('placeholder' => 'Please Add End Date'));
        
		$survey_invite_subject = new Zend_Form_Element_Text('survey_invite_subject');
        $survey_invite_subject->setLabel('Survey Invitation Subject')
                ->setRequired(true) 
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter survey instance name'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'))
                ->setAttribs(array('placeholder' => 'Please Add Survey Instance Name'))
				->setValue($email_subj);
				
		$survey_invite_text = new Zend_Form_Element_Textarea('survey_invite_text');
        $survey_invite_text->setLabel('Survey Invitation Email')
                ->setRequired(true) 
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter Survey Invitation Email'))                
                ->setAttribs(array('placeholder' => 'Please Add Survey Invitation Email'))
				->setValue($email_cont);
				
		$landing_page_message = new Zend_Form_Element_Textarea('landing_page_message');
        $landing_page_message->setLabel('Landing Page Message')
                ->setRequired(true) 
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter landing page message'))              
                ->setAttribs(array('placeholder' => 'Please Add landing page message'))
				->setValue($landing_cont);

		$survey_thanks_message = new Zend_Form_Element_Textarea('survey_thanks_message');
        $survey_thanks_message->setLabel('Survey Thanks Message')
                ->setRequired(true) 
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter Survey Thanks Message'))               
                ->setAttribs(array('placeholder' => 'Please Add Survey Thanks Message'))
				->setValue($thank_cont);
		
		
        $timer = array();
	
    $timer['']='Please Select';
	$timer[1]= "1 Minute";
	$timer[2]= "2 Minutes";
	for($count=5;$count<=30;$count=$count+5)
        {
            $timer[$count] = $count." "."Minutes";
	}
        
        $required_time = new Zend_Form_Element_Select('required_time');
        $required_time->setLabel('Required Time')
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter Required Time'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'))
                ->setAttribs(array('placeholder' => 'Please Add Required Time'))
                ->setMultiOptions($timer);

		$email_subject = new Zend_Form_Element_Text('email_subject');
        $email_subject->setLabel('Email Subject')
                //->setRequired(true) 
               // ->addValidator('NotEmpty', true, array('messages' => 'Please enter email subject'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'))
                ->setAttribs(array('placeholder' => 'Please Add Email Subject'));
		
		
		$add_record = new Zend_Form_Element_File('add_record');
		$add_record->setAttrib('enctype', 'multipart/form-data');
		$add_record->setLabel('Upload a File:');

        $add_external_aspirants = new Zend_Form_Element_File('add_external_aspirants');
        $add_external_aspirants->setAttrib('enctype', 'multipart/form-data');
        $add_external_aspirants->setLabel('External Recipients File:');
			 //  ->setDestination('/var/www/upload');		
        
        $submit = new Zend_Form_Element_Button('submitbtn');
        $submit->setAttrib('id', 'submit')
                ->setAttrib('type', 'submit')
                ->setLabel('Send Survey')
                ->setAttrib('class', 'btn btn-primary')
                ->setOrder(101);
				
		$send_test_email = new Zend_Form_Element_Button('emailbtn');
        $send_test_email->setAttrib('id', 'send_test_email')
                ->setAttrib('type', 'submit')
                ->setLabel('Send Test Email')
                ->setAttrib('class', 'btn btn-primary')
                ->setOrder(102);		
        
        $reset = new Zend_Form_Element_Button('resetbtn');
        $reset->setAttrib('id', 'submit')
                ->setAttrib('type', 'reset')
                ->setLabel('Cancel')
                ->setAttrib('class', 'btn btn-primary')
                ->setOrder(101);
        
       $update = new Zend_Form_Element_Button('updatebtn');
       $update->setAttrib('id', 'update')
                ->setAttrib('type', 'submit')
                ->setLabel('Update Survey')
                ->setAttrib('class', 'btn btn-primary')
                ->setOrder(101);
        
        
        
        

        
        /*foreach($arrSelectionFilterData as $selectionData)
        {
            
        }
        exit; */
        
        $this->addElements(array($submit,$event_type, $survey_name, $start_date, $end_date, $survey_invite_text, $survey_invite_subject, $landing_page_message, $survey_thanks_message, 
		 $send_test_email, $survey_test_email, $reset, $surveyId,$survey_category,$required_time,$email_subject,$add_record, $add_external_aspirants, $update));
    }
    
    public function isValid($data) 
    {
        //$valid = parent::isValid($data);
        $valid = true;
        return $valid;
    }
    
    
}
