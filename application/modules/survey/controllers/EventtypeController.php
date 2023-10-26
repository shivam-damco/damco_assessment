<?php
/**
 * @author  Harpreet Singh
 * @date    21 Dec, 2015
 * @version 1.0
 * 
 * Controller to handle event operations  
 */

class Survey_EventtypeController extends Damco_Core_CoreController {

    protected $_auth = null;
    protected $_redirector = null;
    protected $_eventTypeModelObject = null;
    protected $_surveyModelObject = null;
    

    /**
     * Method to initialize event controller
     */
    public function init() {
        parent::init();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_auth = Zend_Auth::getInstance();
        $this->_eventTypeModelObject = new Survey_Model_EventTypes();
        $this->_surveyModelObject = new Survey_Model_Survey();
		$this->_surveyEventsModelObject = new Survey_Model_SurveyEvents();
        if(!$this->_auth->getIdentity()){
            $this->_redirect($this->view->serverUrl() );
        }
    }

    /**
     * Method to handle index action operations
     */
    public function indexAction() {
        if ($this->getRequest()->isXmlHttpRequest()) {
			
		   $this->_getByAjax();
        }
        
        $get = $this->getRequest()->getParams();
        
        $this->view->get = $get;
        $this->view->role_name = $this->_user->role_name;
        
        //$eventTypesData = $this->_eventTypeModelObject->getAllEventTypes();
        //$this->view->EventTypesData = $eventTypesData;
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
        
    }

    /**
     * Method to handle add action operations
     */
    public function addAction() {
        
        //$this->_editoptions = new Event_Model_Events();
        $get = $this->getRequest()->getParams();
        
        $form = new Survey_Form_Eventtype();
        
        if ($this->_request->isPost()) {
            $message = '';
            $form->isValid($get);
            if ((!$form->isValid($get))) {
                if (array_key_exists("survey_category", $form->getmessages())) {
                    $message.= "<br/>Please select survey category";
                }
                
                if (array_key_exists("event_type", $form->getmessages())) {
                    $message.= "<br/>Please enter survey";
                }
                /*if (array_key_exists("description", $form->getmessages())) {
                    $message.= "<br/>Please enter description";
                }*/
                
                if (array_key_exists("event_code", $form->getmessages())) {
                    $message.= "<br/>Please enter event code";
                }
                
                if (array_key_exists("dept_select", $form->getmessages())) {
                    $message.= "<br/>Please select conducting department";
                }
                
//                if (array_key_exists("required_time", $form->getmessages())) {
//                    $message.= "<br/>Please select required time";
//                }
                
                $this->view->messages = $message;
            } else {
                
                $isrecordExist = $this->_eventTypeModelObject->checkRecordExist($get['event_type']);
                if($isrecordExist)
                {
                    $message.= "<br/>Survey already exists";
                    $this->view->messages = $message;
                }
                else
                {
                    $eventType = $get['event_type'];
                    $description = $get['description'];       
                    //$eventCode = $get['event_code'];
                    $deptSelect = $get['dept_select'];
                    $deptValue =  $get['dept_value'];
                    $addedDate = Date('Y-m-d h:i:s');
                    $addedBy = $this->_user->id;
                    $modifiedBy = $this->_user->id;
                    $surveyCategory = $get['survey_category'];
                    
                    //print_r($this->_user->id);exit;

                    $arrEventType = array('event_type' => $eventType, 'description' => $description,'department_id'=>$deptSelect,'department'=>$deptValue,'added_date'=>$addedDate,'added_by'=>$addedBy,'modified_by'=>$modifiedBy,'survey_category_id'=>$surveyCategory);

                    $this->_eventTypeModelObject->saveData($arrEventType);

                    $this->_flashMessenger->addMessage(array(
                        'success' => 'Survey has been successfully created'
                    ));
                    $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
                    
                }
                
                
            }
        }
        $this->view->form = $form;
        //$dealerObj = new Dealer_Model_Dealers();
        //$arrDealerlist = $dealerObj->getAll(array('id', 'dealer_name'));
        //$ardealerOption = array("-- Select Dealers --");
        //$accHierarchy = new Damco_Core_AccessHierarchy();
        //$result = $accHierarchy->get();
        //foreach ($result['dealers'] as $value) {
        //    $ardealerOption[$value['id']] = $value['name'];
        //}
        //$form->dealer_id->setMultiOptions($ardealerOption);
    }
    /**
     * Method to return events data for Ajax requests
     */
    private function _getByAjax($returnArray = FALSE) {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $get = $this->getRequest()->getParams();
        
        $aColumns = array('added_date','survey_category_name','event_type','added_date','department','','');
        
    	if(!isset($get['ordercolumn'])){
            if(is_numeric($get['order'][0]['column'])) {
                $field = $aColumns[$get['order'][0]['column']];
                $sortby = ($get['order'][0]['dir']==='asc')? 'ASC' : 'DESC';
                $sOrder = $field. ' ' .$sortby;
            } 
            else {
                $sOrder = '';
            }
        } 
        else {
            $field = $aColumns[$get['ordercolumn']];
            $sortby = ($get['orderdir']==='asc')? 'ASC' : 'DESC';
            $sOrder = $field. ' ' .$sortby;
        }
        
        if(isset($get['search'])) {
            $searchValue = $get['search']['value'];
        }
        else {
            $searchValue = '';
        }
        
        $params = array(
                'start'=>isset($get['start'])?$get['start']:'',
                'length'=>isset($get['length'])?$get['length']:'',
                'orderBy'=>$sOrder,//Added By Amit kumar 16/09/14 3:46 PM for Sorting
                'searchBy' => $searchValue
            );
        $result = $this->_eventTypeModelObject->getEventTypesData($params); 
        $rowCount = $this->_eventTypeModelObject->getCountData($params); 
        //echo "<pre>";print_r($result);exit;
        if(count($result)>0) {
            $data = array(
                'draw' => isset($get['draw'])?$get['draw']:'',
                'recordsTotal' => $rowCount[0]['COUNT'], //$result[1][0]['tot'],
                'recordsFiltered' => $rowCount[0]['COUNT'] //$result[1][0]['tot'],
            );
            $data['data'] = array();
            
            foreach ($result as $value) {
                $added_date = explode(' ',$value['added_date']);
                /*if(strpos($value['event_status_comb'], 'In progress') !== false ||   strpos($value['event_status_comb'], 'Closed') !== false ){
                    $temp = array(
                        $value['event_typeid'],                       
                        $value['survey_category_name'],
						$value['event_type'],
						$added_date['0'],
                        //$value['description'],
                        $value['department'],
                        $value['surveyInstanceCount'],
                        '<a data-toggle="tooltip" data-placement="top" title="Run Survey" href="'.$this->view->serverUrl().'/survey/surveys/add/eventtypeid/'.($value['event_typeid']).'" class="run-survey">Run Survey</a>&nbsp;&nbsp;&nbsp;
						<a data-toggle="tooltip" target="_blank" data-placement="top" title="Preview" href="'.$this->view->serverUrl().'/survey/eventtype/previewsurvey/eventtypeid/'.($value['event_typeid']).'" class="view-icon">Preview Survey</a>&nbsp;&nbsp;&nbsp;',
                        
//                        '<a href="'.$this->view->serverUrl().'/survey/eventtype/edit/id/'.base64_encode($value['event_typeid']).'">Edit</a>&nbsp;&nbsp;&nbsp;
//                        <a href="#" onclick="deleteEventTypes('.$value['event_typeid'].')">Delete</a>&nbsp;&nbsp;&nbsp;
//                        <a href="'.$this->view->serverUrl().'/survey/question/index/eventtypeid/'.($value['event_typeid']).'">Manage Question</a>&nbsp;&nbsp;&nbsp;
//                        <a href="'.$this->view->serverUrl().'/survey/eventtype/managebranching/eventtypeid/'.($value['event_typeid']).'">Manage Branching</a>&nbsp;&nbsp;&nbsp;'
                );
                } else{*/
                $edit_survey = $delete_survey = $manage_questions = $manage_branching = '';
                $run_survey = $preview_survey = '';
                if ( $this->view->hasAccess( $this->_user->role_name, 'survey', 'surveys', 'remindermail' ) ) {
                    $edit_survey = '<a data-toggle="tooltip" data-placement="top" title="Edit Survey" '
                            . 'href="'.$this->view->serverUrl().'/survey/eventtype/edit/id/'
                            . base64_encode($value['event_typeid']).'" class="edit-icon">Edit</a>&nbsp;&nbsp;&nbsp;';
                }
                if ( $this->view->hasAccess( $this->_user->role_name, 'survey', 'surveys', 'delete' ) ) {
                    $delete_survey = '<a data-toggle="tooltip" data-placement="top" title="Delete Survey" '
                            . 'href="#" onclick="deleteEventTypes('.$value['event_typeid'].')" '
                            . 'class="delete-survey">Delete</a>&nbsp;&nbsp;&nbsp;';
                }
                if ( $this->view->hasAccess( $this->_user->role_name, 'survey', 'question', 'index' ) ) {
                    $manage_questions = '<a data-toggle="tooltip" data-placement="top" title="Manage Questions" '
                            . 'href="'.$this->view->serverUrl().'/survey/question/index/eventtypeid/'
                            . ($value['event_typeid']).'" class="manage-questions">Manage Questions</a>&nbsp;&nbsp;&nbsp;';
                }
                if ( $this->view->hasAccess( $this->_user->role_name, 'survey', 'eventtype', 'managebranching' ) ) {
                    $manage_branching = '<a data-toggle="tooltip" data-placement="top" title="Manage Branching" '
                            . 'href="'.$this->view->serverUrl().'/survey/eventtype/managebranching/eventtypeid/'
                            . ($value['event_typeid']).'" class="manage-branching-icon">Manage Branching</a>&nbsp;&nbsp;&nbsp;';
                }
                if ( $this->view->hasAccess( $this->_user->role_name, 'survey', 'surveys', 'add' ) ) {
                    $run_survey = '<a data-toggle="tooltip" data-placement="top" title="Run Survey" '
                            . 'href="'.$this->view->serverUrl().'/survey/surveys/add/eventtypeid/'
                            . ($value['event_typeid']).'" class="run-survey">Run Survey</a>&nbsp;&nbsp;&nbsp;';
                }
                if ( $this->view->hasAccess( $this->_user->role_name, 'survey', 'eventtype', 'previewsurvey' ) ) {
                    $preview_survey = '<a data-toggle="tooltip" target="_blank" data-placement="top" '
                            . 'title="Preview" href="'.$this->view->serverUrl().'/survey/eventtype/previewsurvey/eventtypeid/'
                            . ($value['event_typeid']).'" class="view-icon">Preview Survey</a>&nbsp;&nbsp;&nbsp;';
                }
                if($value['surveyInstanceCount'] == 0) {
                    $temp = array(
                        $value['event_typeid'],                       
                        $value['survey_category_name'],
                        $value['event_type'],
                        $added_date['0'],
                        //$value['description'],
                        $value['department'],
                        $value['surveyInstanceCount'],
                        $edit_survey . $delete_survey . $manage_questions . $manage_branching
                            . $run_survey . $preview_survey
                    );
                }
                else{
                    $temp = array(
                        $value['event_typeid'],                       
                        $value['survey_category_name'],
                        $value['event_type'],
                        $added_date['0'],
                        $value['department'],
                        $value['surveyInstanceCount'],
                        $delete_survey . $manage_branching .  $run_survey . $preview_survey
                    );
		}
                $data['data'][]=$temp;
            }
            //} 
        }
        else
        {
             $data = array(
                'draw' => isset($get['draw'])?$get['draw']:'',
                'recordsTotal' => '0', //$result[1][0]['tot'],
                'recordsFiltered' => '0' //$result[1][0]['tot'],
            );
            $data['data'] = array();
        }
        
        echo json_encode( $data );
        exit;
    }

    /**
     * Method to handle delete action operations
     */
    public function deleteAction() {
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $get = $this->getRequest()->getParams();
        if ( !isset($get['id']) ) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Event ID not recieved!!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        
        $eventTypeID = $get['id'];
        
        $isValidId = $this->_surveyModelObject->checkRecordExistByEventType($eventTypeID);
        //$isValidId = $this->_validateEventTypeId($eventTypeID);
        
        if($isValidId)
        {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Survey instance is associated with the survey!!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        
        if ( !is_numeric($eventTypeID) ) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Invalid Event ID!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        
        
        $this->_eventTypeModelObject->delete($eventTypeID);
        
        $this->_flashMessenger->addMessage(array(
            'success' => 'Survey has been successfully Deleted'
        ));
        
        $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
    }

 
    /**
     * Method to handle edit action operations
     * @author  Anuj
     * @date    31 May, 2014
     */
    public function editAction() {
        $eventTypeId = $this->_request->getParam("id", '');
        $eventTypeId = base64_decode($eventTypeId);
        
        
        if (!isset($eventTypeId)) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Survey ID not recieved!!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        if (!is_numeric($eventTypeId)) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Invalid Survey ID!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        
        $this->_validateEvent($eventTypeId);
        
        
        $result = $this->_eventTypeModelObject->getEventTypesByID($eventTypeId);
        
        if(count($result) == 0)
        {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Survey ID not valid!!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        
        $this->view->EventTypeData = $result;
        $form = new Survey_Form_Eventtype();
		$form->required_time->setAttrib('disabled','true');
        //echo $result[0]['survey_category_id']; die;
        $form->populate(
            array(
                'eventtypeid' => $result[0]['event_typeid'],
                'event_type' => $result[0]['event_type'],
                'description' => $result[0]['description'],
                'dept_select' => $result[0]['department_id'],
                'dept_value' => $result[0]['department'],
				'survey_category' => $result[0]['survey_category_id'],
				'required_time'=>$result[0]['required_time']
            )
        );
        
        //$form->populate($result);
        
        
        $get = $this->getRequest()->getParams();
        
        if ($this->_request->isPost()) {
            $message = '';
            
            $form->isValid($get);
            
            if ((!$form->isValid($get))) 
            {
                
                if (array_key_exists("event_type", $form->getmessages())) {
                    $message.= "<br/>Please enter survey";
                }
                if (array_key_exists("description", $form->getmessages())) {
                    $message.= "<br/>Please enter description";
                }
                
                if (array_key_exists("event_code", $form->getmessages())) {
                    $message.= "<br/>Please enter event code";
                }
                
                if (array_key_exists("dept_select", $form->getmessages())) {
                    $message.= "<br/>Please select department";
                }
				if (array_key_exists("survey_category", $form->getmessages())) {
                    $message.= "<br/>Please select survey category";
                }
				if (array_key_exists("required_time", $form->getmessages())) {
                    $message.= "<br/>Please enter required time";
                }
                $this->view->messages = $message;
                
            } 
            else 
            {
                $isrecordExist = $this->_eventTypeModelObject->checkRecordExistById($get['event_type'],$get['eventtypeid']);
                
                if($isrecordExist)
                {
                    $message.= "<br/>Survey already exist";
                    $this->view->messages = $message;
                }
                else
                {  
                    $eventtypeId = $get['eventtypeid'];
                    $eventType = $get['event_type'];
                    $description = $get['description'];       
                    //$eventCode = $get['event_code'];
                    $deptSelect = $get['dept_select'];
                    $deptValue =  $get['dept_value'];
                    $modifiedBy = $this->_user->id;
					$surveyCategoryId = $get['survey_category'];
					$requiredTime = $get['required_time'];
                    //print_r($this->_user->id);exit;

                    $arrEventType = array('event_typeid'=>$eventtypeId,'event_type'=> $eventType, 'description' => $description,'department_id'=>$deptSelect,'department'=>$deptValue,'modified_by'=>$modifiedBy,'survey_category_id'=>$surveyCategoryId,'required_time'=>$requiredTime);
                    $this->_eventTypeModelObject->saveData($arrEventType);
                    $this->_flashMessenger->addMessage(array(
                        'success' => 'Survey has been successfully updated'
                    ));
                    $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
                }
            }
        }
        $this->view->form = $form;
    }

    
    
    
   

    /**
     * Method to Authorize access for event ID
     * @param type $eventID
     */
   
    private function _validateEvent( $eventID ) {
          if( $this->_user->role_id!='1') {
            $result = $this->_eventModelObject->getWhere('dealer_id', array(
                'eventid' => $eventID
            ));

            if ( !isset( $result[0] ) ) {
                $this->_flashMessenger->addMessage(array(
                    'error' => $this->view->translate('Invalid survey ID. Please try again')
                ));
                $this->_redirect($this->view->serverUrl() . '/event/index');
            }
            $session = new Zend_Session_Namespace('access_heirarchy');
            if ( !in_array($result[0]['dealer_id'], $session->accessHierarchy['dealers']) ) {
                $this->_flashMessenger->addMessage(array(
                    'error' =>$this->view->translate('Unauthorized access')
                ));
                $this->_redirect($this->view->serverUrl() . '/event/index');
            }
        }
        else
        {
            return true;
        }
    }
    
    /*
     * Method to handle branching 
     * @author sachin
     * @param $eventtypeid
     */
   public function managebranchingAction(){
       $questionModelObj = new Survey_Model_Questions();
        $eventtypeid = $this->getRequest()->getParam("eventtypeid");
        if(!$eventtypeid){
            $this->_flashMessenger->addMessage(array(
                'error' => 'No event type id was given'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        if($this->getRequest()->isPost()){
            $questionModelObj->setEventType($eventtypeid);
            $editNotAllowed = $questionModelObj->showEditLink($eventtypeid);
            if($editNotAllowed){
                $this->_flashMessenger->addMessage(array(
                    'error' => 'Can\'t branch this survey. Survey Instance of this survey already in progess/closed.'
                ));
                $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index/');
            }
            $params = $this->getRequest()->getParams();
            
            if(isset($params['order']) && $params['order']!= '')
            {
                $sort_order = explode(',',$params['order']);
                $questionModelObj->updateBranching($sort_order, $eventtypeid);
                $this->_flashMessenger->addMessage(array(
                    'success' => 'Branching has been successfully created'
                ));
            }
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        } else{
            
            $params = array(
                    'start'=>'',
                    'length'=>'',
                    'orderBy'=>'sort_order',
                    'orderBySecondry'=>'input_type',
                    'eventTypeid' => $eventtypeid
                );
            $result = $questionModelObj->getQuestions($params);
            if(is_array($result) && !empty($result))
                $this->view->result = $result;
            else
            {
                $this->_flashMessenger->addMessage(array(
                'error' => 'No question is associated with this survey.'
                ));
                $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$eventtypeid);
            }
        }
    }

	public function previewsurveyAction(){
		
		$this->_answerpreviewobj = new Survey_Model_AnswersPreview();
		$this->_answerpreviewobj->delete_allanswers();
		$eventtypeid = $this->getRequest()->getParam("eventtypeid");
		$modifiedBy = $this->_user->id;
		$data = $this->_surveyModelObject->getAllByEventType($eventtypeid);
		$surveyid =	$data[0]['survey_id'];
		$currentdate =  date("Y-m-d");
		if(!isset($surveyid) && empty($surveyid)){
			$fields = array('event_typeid'=>$eventtypeid, 'survey_name'=>'Preview Survey', 'start_date'=>$currentdate, 'end_date'=>$currentdate, 'added_on'=>$currentdate , 'added_by'=>$modifiedBy, 'modified_by'=>$modifiedBy ,'required_time'=>'10', 'flag'=>'0');
			$surveyid = $this->_surveyModelObject->saveData($fields);
			
		}
		

		$checkdata = $this->_surveyEventsModelObject->checkDataforpreview($surveyid);
		if(empty($checkdata)){
			$surveyCode = bin2hex(openssl_random_pseudo_bytes('32'));
			$arrSurveyEvents = array('employee_id'=>'000000','employee_name'=> 'preview','employee_department'=>'','survey_id'=>$surveyid,'event_typeid'=>$eventtypeid,'event_date'=>$currentdate,'event_status'=>'Preview','email'=>'','survey_code'=>$surveyCode,'modified_by'=>$modifiedBy);
			$insertedid = $this->_surveyEventsModelObject->saveData($arrSurveyEvents);
			$getData = $this->_surveyEventsModelObject->getSurveyeventsDatabyId($insertedid);
			$surveycode = $getData[0]['survey_code'];
		}
		else{
			$surveycode = $checkdata[0]['survey_code'];
		}
		$this->_redirect($this->view->serverUrl() . '/survey/index/start/?survey='.$surveycode.'&preview=true');
	}
}

