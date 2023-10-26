<?php

/**
 * @author  Harpreet Singh
 * @date    21 Dec, 2015
 * @version 1.0
 * 
 * Controller to handle event operations  
 */

class Event_IndexController extends Damco_Core_CoreController {

    protected $_auth = null;
    protected $_redirector = null;
    protected $_eventModelObject = null;

    /**
     * Method to initialize event controller
     */
    public function init() {
        parent::init();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_auth = Zend_Auth::getInstance();
        $this->_eventModelObject = new Event_Model_Events();
    }

    /**
     * Method to handle index action operations
     */
    public function indexAction() {
        
        $get = $this->getRequest()->getParams();
        
        if(!isset($get['surveyid']) && $get['surveyid'] == 0)
        {
            /*$this->_flashMessenger->addMessage(array(
                'error' => 'Survey id not given'
            )); */
            $this->_redirect($this->view->serverUrl() . '/survey/surveys');
            
        }
        
        $isrecordExist = $this->_eventModelObject->checkRecordExist($get['surveyid']);
        if(!$isrecordExist)
        {
            $this->_flashMessenger->addMessage(array(
                        'error' => 'Survey does not exist'
                    ));
            $this->_redirect($this->view->serverUrl() . '/survey/surveys');
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_getByAjax($get);
        }
        $get = $this->getRequest()->getParams();
        $this->view->get = $get;
        $this->view->role_name = $this->_user->role_name;
        
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
    }
    
    public function surveystatusAction() {
        $get = $this->getRequest()->getParams();
        
        if(!isset($get['surveyid']) && $get['surveyid'] == 0) {
            /*$this->_flashMessenger->addMessage(array(
                'error' => 'Survey id not given'
            )); */
            $this->_redirect($this->view->serverUrl() . '/survey/surveys');
        }
        
        $isrecordExist = $this->_eventModelObject->checkRecordExist($get['surveyid']);
        if(!$isrecordExist) {
            $this->_flashMessenger->addMessage(array(
                    'error' => 'Survey Instance does not exist'
                ));
            $this->_redirect($this->view->serverUrl() . '/survey/surveys');
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_getBySurveyAjax($get);
        }
        $get = $this->getRequest()->getParams();
        $this->view->get = $get;
        $this->view->role_name = $this->_user->role_name;
        
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
    }
    
    private function _getBySurveyAjax($returnArray = FALSE, $type = '') {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $get = $this->getRequest()->getParams();
        
        $aColumns = array('employee_id','employee_name','email','survey_name','event_status','survey_date');
        
    	if(!isset($get['ordercolumn'])) {
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
        
        if($type == 'export') {
            $sortby = ($get['order_by']==='asc')? 'ASC' : 'DESC';
            $sOrder = ($aColumns[$get['columnIndex']]). ' ' .$sortby;
        }
        
        $params = array(
                'start'=>isset($get['start'])?$get['start']:'',
                'length'=>isset($get['length'])?$get['length']:'',
                'orderBy'=>$sOrder //Added By Amit kumar 16/09/14 3:46 PM for Sorting
            );
        $where = array();
        $where['se.survey_id'] = (isset($get['surveyid'])) ? ' = "'.$get['surveyid'].'"' : '';
        $where['se.event_status'] = (isset($get['event_status'])) ? ' = "'.$get['event_status'].'"' : '';
        //$where['ea.response_options'] = (isset($get['qresp'])) ? ' = "'.$get['qresp'].'"' : '';
        $where['ea.questionid'] = (isset($get['questionid'])) ? ' = "'.$get['questionid'].'"' : '';
        if(isset($get['month']) && isset($get['month']) && isset($get['month']) && isset($get['month'])){
           // $where['survey_date'] = ' BETWEEN "'. $get['year'].'-'. $get['month']. '-' .$get['startDay'].
             //           ' 12:00:00 " AND "' . $get['year'].'-'. $get['month']. '-' .$get['endDay'] . ' 23:59:59"';
        }
        
        $result = $this->_eventModelObject->getEventsStatusBySurveyName($params,$where);
        $rowCountTemp = $this->_eventModelObject->getCountEventsStatusBySurveyName($where);
        $rowCount = $rowCountTemp[0];
        if(count($result)>0) {
            $data = array(
                'draw' => isset($get['draw'])?$get['draw']:'',
                'recordsTotal' => $rowCount['COUNT'], //$result[1][0]['tot'],
                'recordsFiltered' => $rowCount['COUNT'] //$result[1][0]['tot'],
            );
            $data['data'] = array();
            
            foreach ($result as $value) {
                $employee_name = $value['employee_name'];
                if ( $value['event_status'] == 'Closed' && $this->view->hasAccess( $this->_user->role_name, 
                        'event', 'scorecard', 'index' ) ) {
                    $employee_name = '<a href="'.$this->view->serverUrl().'/event/scorecard/index?eventid='.base64_encode($value['event_id']).'&modulename=eventlist">'.$value['employee_name'].'</a>';
                }
		
                $survey_date = explode(' ',$value['survey_date']);
                $temp = array(
			$value['employee_id'],
                        $employee_name,
			$value['email'],
                        $value['survey_name'],
                        $value['event_status'],
                        $value['survey_date']
                       // '<a href="'.$this->view->serverUrl().'/survey/eventtype/edit/id/'.base64_encode($value['event_typeid']).'">Edit</a>&nbsp;&nbsp;&nbsp;
                       // <a href="#" onclick="deleteEventTypes('.$value['event_typeid'].')">Delete</a>&nbsp;&nbsp;&nbsp;
                      //  <a href="'.$this->view->serverUrl().'/survey/question/index/eventtypeid/'.($value['event_typeid']).'">Manage Question</a>&nbsp;&nbsp;&nbsp;
                      //  <a href="'.$this->view->serverUrl().'/survey/eventtype/managebranching/eventtypeid/'.($value['event_typeid']).'">Manage Branching</a>&nbsp;&nbsp;&nbsp;'
                );
                $data['data'][]=$temp;
            } 
        }
        else {
            $data = array(
                'draw' => isset($get['draw'])?$get['draw']:'',
                'recordsTotal' => '0', //$result[1][0]['tot'],
                'recordsFiltered' => '0' //$result[1][0]['tot'],
            );
            $data['data'] = array();
        }
        
        if($type == 'export') {
            return $result;
        }
        echo json_encode( $data );
        exit;
    }

    /**
     * Method to return events data for Ajax requests
     */
    private function _getByAjax($returnArray = FALSE, $type = '') {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $get = $this->getRequest()->getParams();
        $aColumns = array('employee_id','employee_name','email','survey_name','event_status','event_response');
        
    	if(!isset($get['ordercolumn'])) {
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
        
        
        $params = array(
                'start'=>isset($get['start'])?$get['start']:'',
                'length'=>isset($get['length'])?$get['length']:'',
                'orderBy'=>$sOrder //Added By Amit kumar 16/09/14 3:46 PM for Sorting
            );
        $where = array();
        $where['se.survey_id'] = (isset($get['surveyid'])) ? ' = "'.$get['surveyid'].'"' : '';
        $where['se.event_status'] = (isset($get['event_status'])) ? ' = "'.$get['event_status'].'"' : '';
        $where['ea.response_options'] = (isset($get['response'])) ? '"'.$get['response'].'"' : '';
        if(!empty($get['startDate']) && !empty($get['endDate'])) {
                $where['survey_date'] = ' BETWEEN "'. $get['startDate'].
                ' 00:00:00 " AND "' . $get['endDate'] . ' 23:59:59"';
        }
        $where['ea.questionid'] = (isset($get['questionid'])) ? ' = "'.$get['questionid'].'"' : '';
		                        
        $result = $this->_eventModelObject->getEventsBySurveyName($where, $params);
        $rowCountTemp = $this->_eventModelObject->getCountEventsBySurveyName($where);
        $rowCount = $rowCountTemp[0];
        if(count($result)>0) {
            $data = array(
                'draw' => isset($get['draw'])?$get['draw']:'',
                'recordsTotal' => $rowCount['COUNT'], //$result[1][0]['tot'],
                'recordsFiltered' => $rowCount['COUNT'] //$result[1][0]['tot'],
            );
            $data['data'] = array();
            
            foreach ($result as $value) {
                if($value['event_status'] == 'Closed') {
                    $employee_name = '<a href="'.$this->view->serverUrl().'/event/scorecard/index?eventid='.base64_encode($value['event_id']).'&modulename=eventlist">'.$value['employee_name'].'</a>';
                } 
                else {
                    $employee_name = $value['employee_name'];
                }
                $temp = array(    
			$value['employee_id'],
                        $employee_name,
			$value['email'],
                        $value['survey_name'],
                        $value['event_status'],
                        $value['event_date']
                       // '<a href="'.$this->view->serverUrl().'/survey/eventtype/edit/id/'.base64_encode($value['event_typeid']).'">Edit</a>&nbsp;&nbsp;&nbsp;
                       // <a href="#" onclick="deleteEventTypes('.$value['event_typeid'].')">Delete</a>&nbsp;&nbsp;&nbsp;
                      //  <a href="'.$this->view->serverUrl().'/survey/question/index/eventtypeid/'.($value['event_typeid']).'">Manage Question</a>&nbsp;&nbsp;&nbsp;
                      //  <a href="'.$this->view->serverUrl().'/survey/eventtype/managebranching/eventtypeid/'.($value['event_typeid']).'">Manage Branching</a>&nbsp;&nbsp;&nbsp;'
                );
                $data['data'][]=$temp;
            } 
        }
        else {
            $data = array(
                'draw' => isset($get['draw'])?$get['draw']:'',
                'recordsTotal' => '0', //$result[1][0]['tot'],
                'recordsFiltered' => '0' //$result[1][0]['tot'],
            );
            $data['data'] = array();
        }
        if($type == 'export') {
            return $result;
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
        if ( !isset($get['eventid']) ) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Event ID not recieved!!'
            ));
            $this->_redirect($this->view->serverUrl() . '/event/index');
        }
        
        $eventID = base64_decode($get['eventid']);
        if ( !is_numeric($eventID) ) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Invalid Event ID!'
            ));
            $this->_redirect($this->view->serverUrl() . '/event/index');
        }
        
        $this->_validateEvent($eventID);
        
        $this->_eventModelObject->update(array(
            'event_status' => 'Deleted'
        ), 'eventid = "'.$eventID.'"');
        
        $this->_flashMessenger->addMessage(array(
            'success' => 'Survey has been successfully marked as Deleted'
        ));
        $this->_redirect($this->view->serverUrl() . '/event/index');
    }

 
    /**
     * Method to handle edit action operations
     * @author  Anuj
     * @date    31 May, 2014
     */
    public function editAction() {

        $this->_editoptions = new Event_Model_Events();
        $eventId = $this->_request->getParam("eventid", '');
        $eventId = base64_decode($eventId);
        if (!isset($eventId)) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Event ID not recieved!!'
            ));
            $this->_redirect($this->view->serverUrl() . '/event/index');
        }
        if (!is_numeric($eventId)) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Invalid Event ID!'
            ));
            $this->_redirect($this->view->serverUrl() . '/event/index');
        }
        $this->_validateEvent($eventId);
        $get = $this->getRequest()->getParams();
        $result = $this->_eventModelObject->getevtdtls($eventId);
        $this->view->EventData = $result;
        $form = new Event_Form_ClosedEvents();
        $form->populate($result);
        if ($this->_request->isPost()) {
            $message = '';
            if ((!$form->isValid($get))) {
                /*if (array_key_exists("dealer_id", $form->getmessages())) {
                    $message.= "<br/>Please Select dealer";
                } */
                if (array_key_exists("email_address", $form->getmessages())) {
                    $message.= "<br/>Please Enter Valid Email Address";
                }
                $this->view->messages = $message;
            } else {
                //$dealerid = $get['dealer_id'];
                $emailid = $get['email_address'];
                //$arrEvent = array('dealer_id' => $dealerid, 'email_address' => $emailid);
                $this->_editoptions->dbupdate($arrEvent, ' eventid = "' . $eventId . '"');
                $this->_flashMessenger->addMessage(array(
                    'success' => 'Survey has been successfully updated'
                ));
                $this->_redirect($this->view->serverUrl() . '/event/index');
            }
        }
        $this->view->form = $form;
        /*$dealerObj = new Dealer_Model_Dealers();
        $arrDealerlist = $dealerObj->getAll(array('id', 'dealer_name'));
        $ardealerOption = array("-- Select Dealers --");
        $accHierarchy = new Damco_Core_AccessHierarchy();
        $result = $accHierarchy->get();
        foreach ($result['dealers'] as $value) {
            $ardealerOption[$value['id']] = $value['name'];
        }
        $form->dealer_id->setMultiOptions($ardealerOption); */
    }

    /**
     * Method to export the data into csv 
     * @author  Anuj
     * @date    7 June, 2014
     */
    
      public function exportdataAction() {
        ini_set('memory_limit', '-1');
        $get = $this->getRequest()->getParams();
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
              
        $worksheet_name = $exp_name = 'List-Events';       
        $file_location = $exp_name . date("Y-m-d") . ".xlsx";    
        $arrHeaderkeys = array(
            $this->view->translate('Employee Id'),
            $this->view->translate('Name'),
            $this->view->translate('Email'),
            $this->view->translate('Survey'),
            $this->view->translate('Status'),
            $this->view->translate('Response Date'));
        
        if(isset($get['rpt_type']) && $get['rpt_type']=='iptrack')
        {
           $arrHeaderkeys[] =  $this->view->translate('IP Address');
        }
        foreach($arrHeaderkeys as $k)
        {           
           $header[0][] = $k;
        }
        $arrDataContent = $this->_getBySurveyAjax(TRUE,'export');
        foreach ($arrDataContent as $arrContent) {
            $survey_name = $arrContent['survey_name'];
            $line=array();
            $line[] = $arrContent['employee_id']; 
            $line[] = $arrContent['employee_name']; 
            $line[] = $arrContent['email']; 
            $line[] = $arrContent['survey_name']; 
            $line[] = $arrContent['event_status'];
            $line[] = $arrContent['survey_date'];
            $header[] = $line; 
            unset($line);        
        }
        $headerOne[] = array('0'=>'Survey:','1'=>$survey_name);
        $this->_helper->CreateExcelfile($file_location,$header,$headerOne,$worksheet_name);      
        
    } 

    /**
     * Method to Authorize access for event ID
     * @param type $eventID
     */
   
    private function _validateEvent( $eventID ) {
          if( $this->_user->role_id!='1') {
            /*$result = $this->_eventModelObject->getWhere('dealer_id', array(
                'eventid' => $eventID
            )); */

            if ( !isset( $result[0] ) ) {
                $this->_flashMessenger->addMessage(array(
                    'error' => $this->view->translate('Invalid event ID. Please try again')
                ));
                $this->_redirect($this->view->serverUrl() . '/event/index');
            }
            $session = new Zend_Session_Namespace('access_heirarchy');
            /*if ( !in_array($result[0]['dealer_id'], $session->accessHierarchy['dealers']) ) {
                $this->_flashMessenger->addMessage(array(
                    'error' =>$this->view->translate('Unauthorized access')
                ));
                $this->_redirect($this->view->serverUrl() . '/event/index');
            } */
        }
        else
        {
            return true;
        }
    }
   

    /* Method Used For Resend Mail */

    public function resendinviteAction($emailToken = 'Sales Survey Invite') {
        $get = $this->getRequest()->getParams();
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $eventId = base64_decode($get['eventid']);
        $data = $this->_eventModelObject->getEventCustomer($eventId);
        $eventdata=$this->_eventModelObject->getEventDetail($eventId);
        if($eventdata['event_status']=='Open' ||$eventdata['event_status']=='In progress'||$eventdata['event_status']=='Expired')
        {
            //$this->_db->delete('survey_event_answers', 'eventid = ' . $eventId);
            $this->_answerObject->deleteAnswers($eventId);
        }
        $toEmail = $data['email_address'];
        
        if ( empty( $toEmail ) ) {
            $this->_flashMessenger->addMessage(array(
                'error' => $this->view->translate('Customer does not have an email address!')
            ));
            $this->_redirect($this->view->serverUrl() . '/event/index/index/period/rolling_12_months/date_range_field/survey_submission_date');
            exit;
        }
        
        $type = $data['event_typeid'];
        switch ($type) {
            case "1":
                $typename = "Sales";
                $emailToken = "sales_survey_invite";
                $subject="Sales Survey Invite";
                break;
            case "2":
                $typename = "Product";
                $emailToken = "product_survey_invite";
                $subject="Product Survey Invite";
                break;
            case "3":
                $typename = "Service";
                $emailToken = "service_survey_invite";
                $subject = "Service Survey Invite";
                break;
        }
        $macros = array();
        $browserCode = md5(microtime().$get['eventid']);
        $macros['{BASE_URL}'] = HTTP_PATH . '/';
        $macros['{TITLE}'] = ucwords(strtolower($data['title']));
        $macros['{CUS_TITLE}'] = ucwords(strtolower($data['title']));
        $macros['{LASTNAME}'] = (!empty($data['surname'])) ? ucwords(strtolower($data['surname'])) :ucwords(strtolower($data['first_name']));
        $macros['{MODEL}'] = $data['vehicle_code_desc'];
        //$macros['{DEALER}'] =$data['dealer_name'];
        $macros['{SURVEY_LINK}'] = HTTP_PATH.'/survey/index/?survey='
                .$this->_helper->getEventID($eventId).'&langid='.$data['langid'];
        $macros['{BROWSER_LINK}'] = HTTP_PATH.'/survey/email/?emailcode='
                .$browserCode;
        $macros['{OPTOUT_LINK}'] = HTTP_PATH . '/survey/email/optout/?token='
                . md5($eventId);
        $macros['{YEAR}'] = date('Y');

        $emailArr = $this->_parserObject->parse($emailToken, $data['langid'], $macros);
      //  $this->_EmailSave->_savescheduledEmailData($eventId, $toEmail, '', $emailArr, $typename, $emailToken,$browserCode);
        //SendEmail($toEmail = '', $fromEmail = '', $ccEmail = '', $bccEmail = '', $content = '', $subject = '', $sendEmail = 'Y')
        $ObjMail = new Damco_Email();
        $result=$ObjMail->SendEmail($toEmail, '', '','', $emailArr["content"],$emailArr["subject"],"Y");
        if ($result == 'sent') {
            $alertLogDataArr = array(
                'token' => $emailToken,
                'event_type' => $typename,
                'object_type' => $objectType,
                'object_id' => $eventId,
                'email_subject' => $emailArr["subject"],
                'to_addess' => $toEmail,
                'cc_address' => '',
                'bcc_address' => '',
                'alert_content' => $emailArr["content"],
                'is_mail_sent' => 'Y',
                'browser_code' => $browserCode
            );
            $this->ObjAlertLog->insert($alertLogDataArr);
            $arrEvent = array('event_status' =>"Open",
                'email_send_date'=>new Zend_Db_Expr('now()'), 'invite_sent' => '1');
            $this->_eventModelObject->dbupdate($arrEvent, 'eventid="'.$eventId.'"');
            $this->_flashMessenger->addMessage(array(
                'success' => $this->view->translate('Email has been sent successfully')
            ));
        }
        $this->_redirect($this->view->serverUrl() . '/event/index/index/period/rolling_12_months/date_range_field/survey_submission_date');
        $this->view->messages = $message;
    }
}