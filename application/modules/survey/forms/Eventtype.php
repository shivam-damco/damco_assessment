<?php

class Survey_Form_Eventtype extends Zend_Form {

    private $objRegistry;
    public $objRequest;
	public $_surveyCategoriesModelObject = null;

    public function init() {
        $this->objRegistry = Zend_Registry::getInstance();
    }

    public function __construct() {

        $this->setName('add_event');
        $eventTypeId = new Zend_Form_Element_Hidden('eventtypeid');
		
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
                 ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => 'Please select survey category'))
                ->setAttribs(array('class' => 'form-control'))
                ->addFilter('StringTrim')
                ->setAttribs(array('placeholder' => 'Please select'))
                ->setMultiOptions($surveyCategoryArr);
				
		$timer = array();
		$timer['']='Please Select';
		$timer[1]= "1 Minute";
		$timer[2]= "2 Minutes";
		for($count=5;$count<=30;$count=$count+5){
			$timer[$count] = $count." "."Minutes";
		}
		$required_time = new Zend_Form_Element_Select('required_time');
        $required_time->setLabel('Required Time')
                 ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter Required Time'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'))
                ->setAttribs(array('placeholder' => 'Please Add Required Time'))
				->setMultiOptions($timer);

        /* Declaring a form element for Dealer Name */
        $event_type = new Zend_Form_Element_Text('event_type');
        $event_type->setLabel('Survey Title')
                 ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter survey'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'))
                ->setAttribs(array('placeholder' => 'Please Add Survey'));
        
        
        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Description')
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter description'))
                ->setAttrib('COLS', '40')
                ->setAttrib('ROWS', '4')
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'))
                ->setAttribs(array('placeholder' => 'Please Add Survey Description'));
        
        /*$event_code = new Zend_Form_Element_Text('event_code');
        $event_code->setLabel('Event Code')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => 'Please enter event code'))
                ->setAttribs(array('autocomplete' => 'off','class' => 'form-control'))
                ->setAttribs(array('placeholder' => 'Please Add Event Code')); */
        
        $deptArr = array(''=>'Please Select');
		
		$url = 'http://172.29.8.74:7070/api/Service/GetMasterData/';
		$data = array(
                    "key"=> "department",
                    "DivisionIdsList"=> array("0"),
                    "DepartmentIdsList"=> array("0"),
                    "DesignationIdsList"=> array("0"),
                    "RoleIdsList" => array("0"),
                    "LocationIdsList"=> array("0"),
                    "ProjectsIdsList"=> array("0"));
							
		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data)))
        );
        
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        //execute post
        $response = curl_exec($ch);
		
		$response = json_decode($response);
        
		
        //$response = json_decode(file_get_contents('http://172.29.8.74:7070/api/Service/GetMasterData?key=department'));
        
        if(is_array($response) && !empty($response))
        {
            foreach($response as $departmentData)
            {
                $deptArr[$departmentData->Key] = $departmentData->Value;
            }
        }
		
        
        $deptSelect = new Zend_Form_Element_Select('dept_select');
        $deptSelect->setLabel('Conducting Department')
                 ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => 'Please select conducting department name'))
                ->setAttribs(array('class' => 'form-control','onchange'=>'setDapartmentValue()'))
                ->addFilter('StringTrim')
                ->setAttribs(array('placeholder' => 'Please select'))
                ->setMultiOptions($deptArr);
        
        $deptValue = new Zend_Form_Element_Hidden('dept_value');
        
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
        
        $this->addElements(array($submit,$eventTypeId, $event_type, $description, $deptSelect, $deptValue,$required_time, $reset,$survey_category));
    }
    
    public function isValid($data) 
    {
        
        $valid = parent::isValid($data);
        return true;
    }
}
