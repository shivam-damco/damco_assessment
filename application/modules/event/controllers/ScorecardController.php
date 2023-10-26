<?php

/**
 * @author  Anuj
 * @date    31 May, 2014
 * @version 1.0
 * 
 * Controller to scorecard operations  
 */
class Event_ScorecardController extends Damco_Core_CoreController {

    /**
     * object of database
     *
     * @var object
     */
    private $_objDb;

    /**
     * object for configuration
     *
     * @var object
     */
    private $_objConfig;
    protected $_auth = null;
    protected $_redirector = null;
    // protected $_eventModelObject = null;
    protected $_dbName = null;

    /**
     * Method to initialize scorecard controller
     */
    public function init() {
        parent::init();
        $this->_objConfig = Zend_Registry::get('config');
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_auth = Zend_Auth::getInstance();
        $this->_eventModelScorecard = new Event_Model_Scorecard();
        $this->_eventModelObject = new Event_Model_Events();
        $this->_questionModelobj = new Survey_Model_Questions();
        $this->_langModelobj = new Default_Model_Languages();
        $this->_config = new Survey_Model_Config();
        $get = $this->getRequest()->getParams();
    }

    /**
     * Method to handle index action operations
     */
    public function indexAction() {
        
       // set_time_limit(1120);
        // $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        $get = $this->getRequest()->getParams();
        // print_r($get);die;
        if (isset($get['source'])) {
            $this->_helper->layout->setLayout('drilldown');
        }
        $this->view->get = $get;
        $langid = $this->_request->getParam("langid");
        if(empty($langid))
        {
            $langid = $this->_user->lang_id;
        }
        $this->view->langid = $langid;
        $eventId = $this->_request->getParam("eventid", '');
        $eventId = base64_decode($eventId);
        
        $this->_validateEvent($eventId);
        if ($eventId > 0) {

            $resultevent = $this->_eventModelObject->getEventDetailScoreCard($eventId);
//            if (($resultevent['code_status'] == 'Red' && $get['modulename'] == 'eventlist' && $this->_user->role_id == '4')
//                 ||($resultevent['code_status'] == 'Red' && $get['modulename'] == 'unsatisfactory'))//Condition added for unsatisfactory report ,discussion with Harpreet Sir 29 Sep 2014 1:09PM
//			{
//
//                $this->_redirect($this->view->serverUrl() . '/event/codered/edit?eventid=' . $this->_request->getParam("eventid", ''));
//            }
            
            if ((isset($get['modulename']) && $get['modulename'] == 'performancealert' || $get['modulename'] == 'eventlist') || $resultevent['code_status'] != 'Red') {
                
                //$result = $this->_eventModelScorecard->getSurveyDetails($eventId);
                $result = $resultevent;//$this->_eventModelScorecard->getSurveyDetails($eventId); 8/18/14 3:23 PM Dipa as it was already fetchdata from line: 68
                
                if (!empty($result)) {
                    
//                   $arrMaskdata = $this->_helper->maskdata($eventId,$result,$this->_user->role_id); 
		           //As per Discussion to Harpreet Sir 30 September 2014
//                   if($arrMaskdata[0]['country_name']=='')
//                   {
//                       $arrMaskdata[0]['country_name']=$arrMaskdata[0]['country_code'];
//                   }

//                   $this->view->arrGridData = $result = $arrMaskdata[0];
                   if(!empty($resultevent["langid"])){
                       $arrlang =$this->_langModelobj->getLanguages('', $resultevent["langid"]); //removed type as per Naren sir 8/28/14 10:39 PM                 
                   }
                   else{
                       $arrlang[0]["lang_name"]='English';//As per discussion with Harpreet Sir 10/08/2014 3:37PM
                   }
                   
                   $this->view->langnm = $arrlang[0]["lang_name"];
                   //$this->view->arrEventdetails = $resultevent;
//                   $this->view->modelnum = $modelNum =$arrMaskdata[1];
//                   $this->view->is_anonymous = $is_anonymous = $arrMaskdata[2];
                    $arrConfigVariables = $this->_objConfig["survey"]["skip_keywords"];
                    $config_queid = $this->_config->getConfigQueIds($arrConfigVariables);
                    $this->view->config_queid = $config_queid;
                    //  print_R($config_queid);die;
                    $where = array('eventid' => $eventId);
                    $arrEvent = array('event_typeid', 'langid');
                    $eventtypeid = $this->_eventModelScorecard->getWhere($arrEvent, $where);
                    unset($where);
                    //get those question where user gave their responses
                    
                    // TT #1610
                    
                    
                    // TT #1610 ends
                    
                    // TT #1622
//                    $customerResolution = new Event_Model_CustomerResolution();
//                    $resolutionResult = $customerResolution->GetCustomerResolution($eventId,'ASC','');
//                    $this->view->resolutionResult = $resolutionResult;
                    // TT #1622 ends

                    $arrUserResponsesQuestions = $this->_questionModelobj->getUserResponsebasedQuestions($eventId,$langid);
                    
                    //print_r($arrUserResponsesQuestions);die;
                    //get T & V type questions
                    $where = array("event_typeid" => $eventtypeid[0]['event_typeid'],
                        "langid" => !empty($langid) ? $langid : 1, 'question_type' => array('T', 'V','Q'));
                    //print_R($where);die;
                    $arrLabelquestions = $this->_questionModelobj->getQuestionDetails($where, "all");
                    
                    //print_R($arrLabelquestions);die;
                    $questions = '';
                    $NPSconfig_token = $config_token = '';
                    /* 
                    switch ($eventtypeid[0]['event_typeid']) {
                        case "1": $config_token = "sales_contact_dealer_question_id";
                            $NPSconfig_token = "sales_NPS_question_id";
                            break;
                        case "2": $config_token = "product_contact_dealer_question_id";
                            $NPSconfig_token = "product_NPS_question_id";
                            break;
                        case "3": $config_token = "service_contact_dealer_question_id";
                            $NPSconfig_token = "service_NPS_question_id";
                            break;
                        
                    } 1/5/16 3:41 PM Sachin  */ 
                    $cond['se.eventid'] = ' = "'.$eventId . '"';
                    $employeeDetails = $this->_eventModelObject->getEventsBySurveyName($cond);
                    
                    $this->view->employee = $employeeDetails;
                    if (!empty($arrUserResponsesQuestions)) {// $toLang = 'en';
                        $questions = $this->_helper->GetEventDetails($eventId,$arrUserResponsesQuestions,$arrLabelquestions, $this->_user->role_id);
                        
                    } else {
                        $this->view->errormeg = $this->view->translate("Unauthorized access");
                        $this->redirect(HTTP_PATH);
                    }
                    $this->view->arrResponseData = $questions;
                    $this->view->roleid = $this->_user->role_id;
                } else {
                    $this->view->errormeg = $this->view->translate("Unauthorized access");
                    $this->redirect(HTTP_PATH);
                }
            }
        } else {
            exit;
            $this->view->errormeg = "Event not exist";
        }
    }
    

    /**
     * Method to Authorize access for event ID
     * @param type $eventID
     */
    private function _validateEvent($eventID) {
          if( $this->_user->role_id!='1') {
            $result = $this->_eventModelObject->getWhere('dealer_id', array(
                'eventid' => $eventID
            ));
            
            if (!isset($result[0])) {
                $this->_flashMessenger->addMessage(array(
                    'error' => $this->view->translate('Invalid Event ID. Please try again')
                ));
                $this->_redirect($this->view->serverUrl() . '/event/index');
            }
            $session = new Zend_Session_Namespace('access_heirarchy');
            if (!in_array($result[0]['dealer_id'], $session->accessHierarchy['dealers'])) {
                $this->_flashMessenger->addMessage(array(
                    'error' => $this->view->translate('Unauthorized access')
                ));
                $this->_redirect($this->view->serverUrl() . '/event/index');
            }
         }
        else
        {
            return true;
        }
    }

    private function replaceTagFromQuestion($question, $replacedByFromOwndata, $replacedByFromAnswerdata, $tagname, $is_anonymous = "No") {
        if ($is_anonymous == "Yes") {
            $question = preg_replace("/<" . $tagname . ">/i", $replacedByFromOwndata, $question);
        } else {
            preg_match_all('/<([a-z]+)>+/i', $question, $arrcust_dnm);
            if (isset($arrcust_dnm) && !empty($arrcust_dnm)) {
                foreach ($arrcust_dnm[0] as $cust_dnm) {
                    if ($cust_dnm == "<" . $tagname . ">") {
                        $arrExactdata = explode("::::", $replacedByFromAnswerdata);
                        $question = $arrExactdata[1];
                    } else {
                        //$question = preg_replace("/<Dealer>/i", $result['dealer_name'], $userResQues['question']);
                        $question = preg_replace("/<" . $tagname . ">/i", $replacedByFromOwndata, $question);
                    }
                }
            }
        }
        //echo $replacedByFromAnswerdata;die;
        return $question;
    }

}
