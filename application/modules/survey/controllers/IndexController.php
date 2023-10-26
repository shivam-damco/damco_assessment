<?php

/**
 * Online Survey: Index Controller
 *
 * @package     Truimp: Online Survey
 * @version     1.0
 * @author      Dipanwita Kundu
 * @copyright   Truimp
 *
 */
class Survey_IndexController extends Damco_Core_CoreController {

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

    /**
     * specify the test mode 1 for testing and 0 for production/live
     *
     * @var boolean
     */
    //private $_testMode = 0;

    /**
     * stores the default session namespace object
     *
     * @var object
     */
    private $_objSession;   

    /**
     * Added By: Manooj Kumar Dhar <manoojd@damcogroup.com>
     * 
     * this variable will be used to display the personal information only once or under certain specific situation
     * this variable will be used for mobile apppliacation only and not for the desktop, website.
     * @mobile
     */
    //private $_personalInfo;

    /**
     * Added By: Manooj Kumar Dhar <manoojd@damcogroup.com>
     * 
     * this vairable will be used to switch the view template to display the section without question part when no question in survey will be left.
     * this variable will be used for mobile apppliacation only and not for the desktop, website.
     * @mobile
     */
    //private $_noquestionLeft;

    /**
     * stores the current survey event id
     *
     * @var int
     */
    private $_eventid;
    private $eventtable;
    //private $_currentSurveyReportInfo;

    /**
     * Set flag if dealer name is changed in survey edit
     */
    //private $_survey_dealer_changed = "no";

    /**
     * Set flag if staff name is changed in survey edit
     */
    //private $_survey_staff_changed = "no";
    private $dependantQuestions = array(228);
    /**
     * initialzing the varaibles for online survery controller
     *
     */
    public function init() {
        parent::init();
        $this->_objConfig = Zend_Registry::get('config');
        $this->_obj = new Survey_Model_Questions();
        $this->_surveyobj = new Event_Model_Events();
        $this->_answerobj = new Survey_Model_Answers();
		$this->_answerpreviewobj = new Survey_Model_AnswersPreview();
        //$this->_dealerobj = new Dealer_Model_Dealers();
        $this->_eventobj = new Event_Model_Events();
        $this->_objSession = new Zend_Session_Namespace('Default');
        $this->_objsurveyconfig = new Survey_Model_Config();
        $mytestmode = $this->_request->getParam('q',"");
        if ($this->_objConfig["survey"]["test_mode"] == "1" && !empty($mytestmode)) {
            $encryptedval = $this->_helper->getEventID(trim($mytestmode),"encrypt");
            $this->_request->setParam('survey', $encryptedval);
            //$this->_testMode = 1;
        }
        
        $reqInfo = $this->view->getRequestInfo();// print_r($a->getModuleName());die;
       // echo ->getControllerName ."|".$this->view->getRequestInfo()->getControllerName;
        if($reqInfo->getModuleName() == "survey"  && ($reqInfo->getActionName() != "thankyou" && $reqInfo->getActionName() != "toc" )  )
        {
            $myevtid = $this->_request->getParam('survey');
            $this->view->eventid = $this->_eventid =  $this->_helper->getEventID($myevtid,"decrypt");
            if (empty($this->_objSession->eventtable)) {  // || $this->_objSession->act != "edit"
                $this->_objSession->eventtable = "survey_event_answers";
                $this->_objSession->statusClosed = '';
                $this->_objSession->act = '';
            } 
        }
        
        $this->_helper->layout->setLayout('survey');
        
        //print_r($this->_request->getParams());
        $userlangid = $this->view->langid = $this->_request->getParam('langid',"1");
        if(!empty($userlangid))
        {
            $this->setUserLanguage($userlangid);
        }
        
        if(!empty($this->_objSession->locale["langid"]))
        {
            $this->view->lang = $this->_lang = $this->_objSession->locale;
            $lang_char_set = $this->_objSession->locale["lang_character_set"];                 
        }        
        else
        {
                $this->view->lang = $this->_lang;
                $lang_char_set = "UTF-8";
        }
       
       $this->view->lang_char_set = $this->lang_char_set = !empty($lang_char_set) ? $lang_char_set : "UTF-8" ;
       $this->view->lang_code = ! empty($this->_objSession->locale['lang_code'])
                                ? $this->_objSession->locale['lang_code'] : 'en-GB' ;
       $this->view->direction = ! empty($this->_objSession->locale['direction'])
                                ? $this->_objSession->locale['direction'] : 'ltr' ;
       //get all configurable variables name
       
        
         $arrConfigVariables=array('survey_submit_button','survey_preferred_language_text','survey_ok_button',
            'survey_error_text');
        $config_queid = $this->_objsurveyconfig->getConfigQueIds($arrConfigVariables);  

        //submit button name        
        $buttonName = $this->_obj->getStatictext($config_queid['survey_submit_button'],$this->_lang['langid']); 
        //var_dump($buttonName);die;
        $this->view->submitbuttontext = $buttonName[0]["question"]; 
        // select preferred language text
        $preferredLanguageLabel = $this->_obj->getStatictext($config_queid['survey_preferred_language_text'],$this->_lang['langid']);        
        $this->view->survey_select_prefer_language_text = $preferredLanguageLabel[0]["question"];
        
        //survey error text
        $preferredLanguageLabel = $this->_obj->getStatictext($config_queid['survey_error_text'],$this->_lang['langid']);        
        $this->view->survey_error_text = $preferredLanguageLabel[0]["question"];
        //
        $this->view->params = $this->_request->getParams();
        $this->view->session = $this->_objSession;
		 $this->view->preview = $preview = $this->_request->getParam('preview');
        //echo$this->_eventid;die;
        
        
        if ($this->_eventid) 
        {	
			if($preview!='true'){
				$cond = array("event_status" => array('Open', 'In progress' ,'Bounce Removed'), 'event_date >= '.Date('Y-m-d') => ''); //added by dipa 9/19/14 6:11 PM
			}
			else{
				$cond = '';
			}
            $this->survey = $this->view->survey = $this->_eventobj->getEventdetail($this->_eventid, $cond,$preview);
           
            if (!is_array($this->view->survey)) {
                $this->view->closedevent = "yes";
                $this->_forward('error');
                return;
            }
        }
        
        // Check if user clicks the back button of browser once submitted the survey		
        if (($this->view->survey['event_status'] == 'Closed' || $this->view->survey['event_status'] == 'Did not qualify' || $this->view->survey['event_status'] == 'Incomplete') &&				$this->_request->getParam('submitSurvey') && $this->_objSession->act != "edit") {
            $this->_redirect('/survey/index/thankyou/?langid='.$this->_lang['langid']);
        }
        
       
    }

    Public function errorAction() {
        
        /* $this->_objconfig = new Survey_Model_Config();       
        $config_token="survey_notexist_orclosed";       
        $qid = $this->_objconfig->findRow("config_val",array("config_var"=>$config_token));
        //$this->lang["langid"] = $this->view->langid = $sellangid =  $this->_request->getParam('langid',"1");*/
        $introtext = 'Introduction Text';// $this->_obj->getStatictext($qid["config_val"],$this->view->langid);       
        //$this->view->survey_notexist_orclosed = $introtext[0]["question"];
        
		$this->view->survey_notexist_orclosed = 'This survey is either already closed or does not exists.';
        $this->render('survey_not_exist');
        return; 
    }

    /**
     * default action of the controllers
     *
     */
    public function indexAction() {
        header("Content-type: text/html; charset='".$this->lang_char_set."'");
        
		$this->_surveysobj = new Survey_Model_Survey();					
		$this->eventobj = new Survey_Model_SurveyEvents();
		
        if ($this->_objSession->act == "edit" && $_SESSION["arrEDitSurvey"][$this->view->eventid] == "yes") {
            $cond = array("event_status" => array('Closed'), 'event_date < now()' => '',);
            $this->view->survey_exist = $this->_eventobj->getEventdetail($this->_eventid, $cond);

            if ($this->view->survey_exist["event_status"] == "Closed") {
                if (is_array($this->view->survey_exist)) {
                    $this->view->closedevent = "yes";
                    $this->render('survey_not_exist');
                }
            }
        }
        //to show intro text from language table
        /* switch($this->view->survey['event_typeid'])
        {
            case "1": $config_token="sales_survey_introtext";break;
            case "2": $config_token="survey_product_introtext";break;
            case "3": $config_token="survey_service_introtext";break;
            default:$config_token="sales_survey_introtext";break;
        } */
        $config_token="sales_survey_introtext";
        ////////////////////////////////
        $arrConfigVariables=array($config_token,'survey_start_button');
        $config_queid = $this->_objsurveyconfig->getConfigQueIds($arrConfigVariables);    
		
		
		$eventdata = $this->eventobj->getSurveyeventsDatabyId($this->_eventid);

		$surveys_data = $this->_surveysobj->getSurveyByID($eventdata[0]['survey_id']);		
		
		$this->view->introtext = $surveys_data[0]['landing_page_content'];
       /* if(!empty($config_token))
        {
            $introtext = $this->_obj->getStatictext($config_queid[$config_token],$this->_lang['langid']);         
            $this->view->introtext = $introtext[0]["question"]; 
        }*/
       
        $startbutton = $this->_obj->getStatictext($config_queid['survey_start_button'],$this->_lang['langid']);       
        $this->view->startbutton = $startbutton[0]["question"];       
        
          // var_dump($this->_eventid);die;
        if (empty($this->_eventid)) {
            $this->_redirect('/survey/index/error/?langid='.$this->_lang['langid']);
        } 
        
    }

    /**
     * action to start the online survey
     *
     */
    Public function startAction() {
		$this->view->preview = $preview = $this->_request->getParam('preview');
		$userInfo = Zend_Auth::getInstance()->getStorage()->read();
		if(!isset($userInfo->id) && $preview == 'true'){
			$this->_redirect($this->view->serverUrl());
		}

        header("Content-type: text/html; charset='".$this->lang_char_set."'");              
        //for proceed
        
        
        $arrConfigVariables=array('survey_proceed','survey_progress','survey_pre_submit_text',"survey_notselected_text");
        $config_queid = $this->_objsurveyconfig->getConfigQueIds($arrConfigVariables);              
       
        //proceed
        $proceedbutton = $this->_obj->getStatictext($config_queid['survey_proceed'],$this->_lang['langid']);        
        $this->view->proceedbutton = $proceedbutton[0]["question"];
        
        $progressbar = $this->_obj->getStatictext($config_queid['survey_progress'],$this->_lang['langid']); 
        $this->view->progressbar = $progressbar[0]["question"];
        
        $surveyPreSubmitText = $this->_obj->getStatictext($config_queid['survey_pre_submit_text'],$this->_lang['langid']); 
        $this->view->survey_pre_submit_text = $surveyPreSubmitText[0]["question"];
        
        
        $surveyNotSelectedText = $this->_obj->getStatictext($config_queid['survey_notselected_text'],$this->_lang['langid']); 
        $this->view->survey_notselected_text = $surveyNotSelectedText[0]["question"];
        
		
        
       //for submit buuton 
        
        if (!is_array($this->view->survey)) {
            $this->render('survey_not_exist');
        } else {
            
            $grp_pgid = (isset($this->view->params["grp"]) ? $this->view->params["grp"] : 0);
            
            /* if(!empty($grp_pgid))
            {
                $where = array("event_typeid" => $this->view->survey['event_typeid'], "langid" => !empty($this->_lang['langid']) ? $this->_lang['langid'] : 1, "groupid" => $grp_pgid);
            }
            else {
                $where = array("event_typeid" => $this->view->survey['event_typeid'], "langid" => !empty($this->_lang['langid']) ? $this->_lang['langid'] : 1, "first_question" => "Yes");
                
            } */
            $where = array("event_typeid" => $this->view->survey['event_typeid'], 
                 "langid" => !empty($this->_lang['langid']) ? $this->_lang['langid'] : 1, "first_question" => "Yes");
           //print_r($where);die;
            
			
            $question_dtls = $this->_obj->getQuestionDetails($where);
			     
			if($question_dtls[0]==''){
				$where = array("event_typeid" => $this->view->survey['event_typeid'], 
                 "langid" => !empty($this->_lang['langid']) ? $this->_lang['langid'] : 1);
				 $question_dtls = $this->_obj->getQuestionDetails($where);
				 if(empty($question_dtls[0])){
					 $error_message = 'Please  add questionnaire to this survey';
				 }
				 else{
					  $error_message = 'Please manage branching';
				 }
				 $this->_flashMessenger->addMessage(array(
                'error' => $error_message
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype');
			}
           
             //echo ">>";print_R($question_dtls);//die;
            //$this->view->question = $question_dtls[0];
            //$this->view->questionEng = $question_dtls[1];
            
           // $this->processSurveyQuestion($question_dtls, $this->view->survey,$question_dtls);
           // $this->processSurveyQuestion($question_dtls, $this->view->survey, $question_dtls);      
            $preview = $this->_request->getParam('preview');
            if(!empty($question_dtls[0]['questionid']))
            {
                if($preview == 'true') {
                    $this->view->answer = $this->_answerobj->getAnswers('survey_event_answers_preview', $this->_eventid, $question_dtls[0]['questionid']);
                }
                else {
                    $this->view->answer = $this->_answerobj->getAnswers('survey_event_answers', $this->_eventid, $question_dtls[0]['questionid']);
                }
            }
           
            //echo "<pre>";print_r($this->view->answer);exit;
             //get labels
             $where=array();
             $where = array("event_typeid" => $this->view->survey['event_typeid'], 'sort_order' => $question_dtls[0]['sort_order'],
                 "langid" => $this->_lang['langid'], 'question_type' => 'T');//                  
            
            $arrgotoevent_bothlang = $this->_obj->getQuestionDetails($where,"lbl");    
			
            $this->view->labels =  $arrgotoevent_bothlang["Qlbl"];
            
            
           // print_R($this->view->labels);
            //show group-wise ques & ans
           $where = array("event_typeid" => $this->view->survey['event_typeid'], 
                "langid" => !empty($this->_lang['langid']) ? $this->_lang['langid'] : 1, 
               // "groupid" => $question_dtls[0]['groupid'], 
                "first_question" => "Yes",
                'question_type' =>array('Q','V','T'));
           
           $allGroupQuestion_dtls = $this->_obj->getQuestionDetails($where,"multi");
            
            
          
            $arrallGroupQuestion_dtls = $allGroupQuestion_dtls["Qmulti"];
            $arrallEngGroupQuestion_dtls = $allGroupQuestion_dtls["QEngmulti"];
            
            //echo "<pre>";print_r($allGroupQuestion_dtls);//die;
            $arrallGroupQuestion_dtls = $this->_obj->getAnswerLoggedCount($arrallGroupQuestion_dtls,$this->survey['survey_id']);
            $this->view->allGroupQuestion_dtls =  $this->processSurveyQuestion($arrallGroupQuestion_dtls, $this->view->survey,$arrallGroupQuestion_dtls);
            
             
                   
            $this->view->allGroupEngQuestion_dtls = $this->processSurveyQuestion($arrallEngGroupQuestion_dtls, $this->view->survey,$arrallEngGroupQuestion_dtls); //$allGroupQuestion_dtls["QEngmulti"];
            
    //      echo "<pre>";  print_r($this->view->allGroupEngQuestion_dtls );die('aa');
            //echo "<pre><hr>ss";print_r($this->view->allGroupEngQuestion_dtls);
            /* if(empty($grp_pgid))
            {
                $qid[] = $question_dtls[0]['questionid'];
            }
            else 
            {               
               foreach($arrallGroupQuestion_dtls as $arr)
               {
                   $qid[] = $arr["questionid"];
               }
            }*/
            
            foreach($arrallGroupQuestion_dtls as $arr)
            {
                   $qid[] = $arr["questionid"];
            }
               
            //echo "Line no: 251 =>"; print_r($qid);
            if($preview == 'true') {
                $arrallGroupQuestionanswer = $this->_answerobj->getAnswers('survey_event_answers_preview', $this->_eventid, $qid);
            }
            else {
                $arrallGroupQuestionanswer = $this->_answerobj->getAnswers('survey_event_answers', $this->_eventid, $qid);
            }
            
            if(!empty($arrallGroupQuestionanswer))
            {   
                foreach($arrallGroupQuestionanswer as $arr)
                {
                      $this->view->answer[$arr["questionid"]]=$arr;
                }
            }/* */
            //for progress bar
            $this->view->anscnt = 1;
            if(!empty($question_dtls[0]['questionid']))
            {
                if($preview == 'true') {
                    $arrQanswd = $this->_answerobj->getCountAnswers('survey_event_answers_preview', $this->_eventid, $question_dtls[0]['questionid']);
                }
                else {
                    $arrQanswd = $this->_answerobj->getCountAnswers('survey_event_answers', $this->_eventid, $question_dtls[0]['questionid']);
                }
            }
            
            $this->view->anscnt = $arrQanswd['cnt'];
            
            //get total no of questions are there in this event type
           $totalQues = $this->_obj->getAllQuestionEventTypewise($this->view->survey["event_typeid"]);
            //print_R($totalQues);
            $this->view->multiplier = $totalQues["num"];// !empty($totalQues["num"]) ? (100/$totalQues["num"]) : 0; 7/1/14 10:45 PM
            
           
            
        }
        
    }
   

    Public function processresponseAction() {
        header("Content-Type: text/html; charset=".$this->lang_char_set);
        //to show intro text from language table
        $config_token="";
		$preview = $this->_request->getParam('preview');
        switch($this->view->survey['event_typeid'])
        {
            case "1": $config_token="survey_sales_questionid_for_marketname"; break;            
            case "3": $config_token="survey_service_questionid_for_marketname"; break;
            default : $config_token=""; break;
        }
        //$config_token="Dipa";
        //for proceed button       
        
        $arrConfigVariables=array('survey_proceed','survey_show_question_onecolumn',$config_token,'survey_show_question_twocolumn','survey_question_tag');
        $config_queid = $this->_objsurveyconfig->getConfigQueIds($arrConfigVariables);              
        
        //proceed
        $proceedbutton = $this->_obj->getStatictext($config_queid['survey_proceed'],$this->_lang['langid']);        
        $this->view->proceedbutton = $proceedbutton[0]["question"];
        
        //for question show in a single column
        $qIdForOneColumn = $this->_obj->getStatictext($config_queid['survey_show_question_onecolumn'],$this->_lang['langid']);
        $arrqIdForOneColumn = explode(",",$config_queid['survey_show_question_onecolumn']);
        $this->view->arrqIdForOneColumn =$arrqIdForOneColumn ;
        //for question show in a two column
        
        //$qIdForTwoColumn = $this->_obj->getStatictext($config_queid['survey_show_question_twocolumn'],$this->_lang['langid']);die;
        $arrqIdForTwoColumn = explode(",",$config_queid['survey_show_question_twocolumn']);
        $this->view->arrqIdForTwoColumn =$arrqIdForTwoColumn ;
        
        //survey question tag
        $arrqSurveyQuestionTag = unserialize($config_queid['survey_question_tag']);
        $this->view->arrqSurveyQuestionTag =$arrqSurveyQuestionTag ;
       
        $this->_helper->layout->disableLayout();
        $where = array("event_typeid" => $this->view->survey['event_typeid'],
            "langid" => !empty($this->_lang['langid']) ? $this->_lang['langid'] : 1, 
            "seq.questionid" => $this->_request->getParam('questionid'));
        
        $question_dtls = $this->_obj->getQuestionDetails($where);
        $question = $this->view->question = $question_dtls[0];
   //   echo "<pre>";print_r($question_dtls);die;
        $questionEng = $this->view->questionEng = $question_dtls[1];  
        if ($questionEng['max_user'])
         {   
            
            $response_cnt = $this->_obj->getAnswerCountByIndex($questionEng,$this->survey['survey_id'],$this->_request->getParam('response')); 
            if($response_cnt>=$questionEng['max_user']){
              print 'Error-Access';
              die;
            }  
         
         }
        if(!empty($question['questionid'])) {
            if($preview == 'true') {
                $this->view->answer = $this->_answerobj->getAnswers('survey_event_answers_preview', $this->_eventid, $question['questionid']);
            }
            else {
                $this->view->answer = $this->_answerobj->getAnswers('survey_event_answers', $this->_eventid, $question['questionid']);
            }
        }        
        //7/21/14 1:57 PM
        $ansEngtag = $prefix = "";
        foreach($arrqSurveyQuestionTag as $arr)
        {
            if(stripos($question["question"],$arr) !== false)//
            {
                switch($arr)
                {
                    case "<Dealer>": 
                        $ansEngtag .= !empty($ansEngtag) ? ",<".trim($this->survey["dealer_name"]).">" : "<".trim($this->survey["dealer_name"]).">" ;
                        $prefix = $question["question_number"]."::::";
                        break;
                    
                }
                
            }
        }
//=-=-=-=-=-=-=-==-=-=-=-=-=-=-=-=-==-=-=-=-=-=-=-=-=-==-=-		

        $response = $this->_request->getParam('response');
        $questionText = $this->_request->getParam('questiontext',"");
        $arrQuestionAnswer = "";
        $arrQuestionAnswer = array(
            'eventid' => $this->_eventid,
            'questionid' => $question['questionid'],
            'response_options' => $response,
            'answer1' => '',
            'answer2' => '',
            'answer3' => '',
            'answer4' => '',
            'answer5' => '',
            'answer6' => '',
            'answer7' => '',
            'answer8' => '',
            'answer9' => '',
            'answer10' => '',
            'answer11' => '',
            'answer12' => '',
            'answer13' => '',
            'answer14' => '',
            'answer15' => '',
            'answer16' => '',
            'answer17' => '',
            'answer18' => '',
            'answer19' => '',
            'answer20' => '',
            /* 'answer_eng1' => $ansEngtag,
            */
            'score' => '',
           // 'code_status' => 'amber',
            'questiontext' => !empty($prefix) ? $prefix.$question["question"] : $questionText,
        );
        //add in log file
       // $this->logfile($arrQuestionAnswer,'',$this->_eventid,"Rawdata");
        switch ($question['input_type']) {
            case 'radio':
                if ($response > 20) {
                    $response = 20;
                }
                $arrQuestionAnswer['answer'. ($response)] = $question["response".$response]; //7/17/14 9:31 AM
                // $arrQuestionAnswer['score'] = ($question["max_score"]>0) ? ($response-1) : 0;
                $arrQuestionAnswer['score'] = ($question["max_score"] > 0) ? $question["score".$response] : 0;
               
                break;

            case 'drop down':
               //echo "Line 439"; print_R($question);
                $response = trim($response);                
                $arrQuestionAnswer['response_options'] = '1';
                $arrQuestionAnswer['answer1'] = $response;
                               
                break;           
            case 'checkbox':

                $response = stripslashes($response);
                
                // 6/26/14 2:03 PM  
                if($question["response1"] == "Make")
                {
                    $arrQuestionAnswer['response_options'] = $response;
                    $userResp = '';
                    $arrresponse = array();
                    $arrresponse = explode(",", $response);                   
                    if (count($arrresponse)) {

                        foreach ($arrresponse as $val) {                        
                           $userResp .= !empty($userResp) ? ",". $this->arrmakename[$val] : $this->arrmakename[$val];

                        }
                       $arrQuestionAnswer['answer1'] = $userResp; 
                    }
                }
                else
                {
                    $arrresponse = explode(",", $response);
                    $arrQuestionAnswer['response_options'] = $response; //implode(',', range(1, count($response)));
                    
                    if ( is_array($arrresponse) && count($arrresponse) > 0 ) {
                        foreach ($arrresponse as $i => $val) {
                           // $arrQuestionAnswer['answer' . $val] = $val;
                            $arrQuestionAnswer['answer' . $val] = $question["response".$val];
                            
                            $scoreValue = 0;
                            if($question["max_score"] > 0){
                                 $scoreValue += $question["score".$val];
                                 // $arrQuestionAnswer['score'] = $scoreValue;
                                 $arrQuestionAnswer['score'] = ($question["max_score"] > 0) ? $scoreValue : 0;
                            }
                            
                           // $arrQuestionAnswer['answer_eng' . $val] = $question["response".$val];;
                        }
                    }
                }

                break;            
            case 'textarea':
            case 'date':
            case 'text':
                 $arrQuestionAnswer['response_options'] = '1';
                 /*$response = str_replace($this->arrSearchKeys, $this->arrSearchValues, $response); */   
                 $arrQuestionAnswer['answer1'] = $response;                 
                 $response = empty($response) ? " " :$response;
            default:
                break;
        }
        
       // print_R($arrQuestionAnswer);die;
        //print_R($arrQuestionAnswer);die;
        if(!empty($question['questionid']))
        {
            $answer = $this->_answerobj->getAnswers('survey_event_answers', $this->_eventid, $question['questionid']);
        }
        
        $this->view->questionLeft = $this->_obj->getCountNextQuestion($this->view->survey['event_typeid'], $question['ID']);
        
        //add in log file
        //$this->logfile($arrQuestionAnswer,'',$this->_eventid,"Populated Data"); 12/29/15 1:02 PM
        if ($response != '') {
            
            unset($where);
            if (empty($answer['answerid'])) {
				if($preview == 'true'){
					$answer['answerid'] = $this->_answerpreviewobj->dbinsert($arrQuestionAnswer);
	               
				}
				else{
					 $answer['answerid'] = $this->_answerobj->dbinsert($arrQuestionAnswer);
				}
				
            } else {
                $where[] = " answerid = '" . $answer['answerid'] . "'";
                unset($arrQuestionAnswer["questiontext"]);
				if($preview == 'true'){
					$this->_answerpreviewobj->dbupdate($arrQuestionAnswer, $where);
	               
				}
				else{
					 $this->_answerobj->dbupdate($arrQuestionAnswer, $where);
				}
  
            }
            unset($where);
            
          
            if ($this->_objSession->act != "edit" && $preview !='true') {
                $arrEvent = array(
                    'event_status' => 'In progress',
                );
                //$where["eventid"] = $this->_eventid;
                $this->_surveyobj->dbupdate($arrEvent, ' eventid = "' . $this->_eventid . '"');
            }
            // Check for dropdown field model name - 11/02/2011
           
            $shownxtquestion = $this->_request->getParam('shownxtquestion');
            
                $action = $this->getResponseActionToPerform($questionEng);
                $this->view->curaction = $action['action'];//6/7/14 7:08 PM
             //echo $shownxtquestion; print_R($action);die;
                if($shownxtquestion == 'y')
                {
                    unset($where);
                    
                    switch ($action['action']) {

                        case 'delete_and_show_next' :
                            $delquestions = $this->_objDb->select()
                                    ->from(array('seq' => 'survey_event_questions'), 'group_concat(seq.questionid) as ids')
                                    ->where('seq.ID in (' . $action['delete_ids'] . ')')
                                    ->query()
                                    ->fetch();

                            $this->_objDb->delete('survey_event_answers', 'questionid IN (' . $delquestions['ids'] . ') AND eventid = ' . $this->_eventid);

                            echo "
                                <script type='text/javascript'>
                                        window.location.href=window.location.href;
                                </script>";
                            die;
                            break;
                        case 'show_next' :
                            $this->view->lastQid = $question['questionid'];
                            $where = array("event_typeid" => $this->view->survey['event_typeid'], "ID" => $action['ID'], 
                                "langid" => $this->_lang['langid']);
                            $arrgotoevent_bothlang = $this->_obj->getQuestionDetails($where);
                            
                            $arrGotoEvent = $this->view->question = $this->view->questionLeft = $arrgotoevent_bothlang[0];
                           
                            if ($question["input_type"] == "drop down") {
                                $this->view->arroptions = $this->_obj->getalloptions($question["questionid"], $this->_lang['langid']);
                            } 
                           
                            if(!empty($arrGotoEvent['questionid']))
                            {
                                $arrQanswd = $this->_answerobj->getCountAnswers('survey_event_answers', $this->_eventid, $arrGotoEvent['questionid']);
                            }
                            //check for last questions responses for next question options - KD
                            $this->view->setOptions = false;
                           
                           // print_r($arrGotoEvent);die;
                            $arrallGroupQuestion_dtls[0] = $arrallEngGroupQuestion_dtls[0] = $arrGotoEvent;
                            $qid = $arrGotoEvent["questionid"];
                          //echo "Line no: 251 =>"; print_r($arrallGroupQuestion_dtls);die;
                            if($preview == 'true') {
                                $arrallGroupQuestionanswer = $this->_answerobj->getAnswers('survey_event_answers_preview', $this->_eventid, $qid);
                            }
                            else {
                                $arrallGroupQuestionanswer = $this->_answerobj->getAnswers('survey_event_answers', $this->_eventid, $qid);
                            }
                           //print_R($arrallGroupQuestionanswer);
                            if(!empty($arrallGroupQuestionanswer))
                            {   
                                $this->view->answer[$arrallGroupQuestionanswer["questionid"]]=$arrallGroupQuestionanswer;
                                /* foreach($arrallGroupQuestionanswer as $arr)
                                {
                                      $this->view->answer[$arr["questionid"]]=$arr;
                                } */
                            }
                            $arrallGroupQuestion_dtls = $this->_obj->getAnswerLoggedCount($arrallGroupQuestion_dtls,$this->survey['survey_id']);
                            $this->view->allGroupQuestion_dtls =  $this->processSurveyQuestion($arrallGroupQuestion_dtls, $this->view->survey,$arrallGroupQuestion_dtls);
                            
                            $this->view->allGroupEngQuestion_dtls = $this->processSurveyQuestion($arrallEngGroupQuestion_dtls, $this->view->survey,$arrallEngGroupQuestion_dtls);
                            
                            //Dipa 7/2/14 11:28 AM
                           
                            break;

                        case 'did not qualify, optout':
                            case 'open' :                                       

                         break;  
			
                         case 'close' : 
                         case 'did not qualify':    
                           echo $action['action'];;
                                
                            $this->view->questionLeft['num'] = 0;
                            $this->view->noquestionLeft = true;
                            $this->_noquestionLeft = true;
                            die;
                            // =-=-=-=-=-=-=-=-=-=-==-=-=-=-=-=-=-=-=-=-=
                            break;              

                        default:
                            echo "
                                                        <script type='text/javascript'>
                                                                deactivateSurveySubmission();
                                                        </script>
                                                ";
                            break;
                    }
                    
                    
                   if(!empty($qid) && is_array($qid) && count($qid) > 0)
                   {
                    $arrQanswd = $this->_answerobj->getCountAnswers('survey_event_answers', $this->_eventid, $qid[count($qid)-1]);
                    $this->view->anscnt = $arrQanswd['cnt'];
                   }

            }
            elseif($shownxtquestion != 'shownothing')
            {
                
                $where = array("event_typeid" => $this->view->survey['event_typeid'], "ID" => $action['ID'], "langid" => $this->_lang['langid']);
                $arrgotoevent_bothlang = $this->_obj->getQuestionDetails($where);

                $arrGotoEvent = $this->view->question = $this->view->questionLeft = $arrgotoevent_bothlang[0];
                $this->view->allGroupQuestion_dtls =  array($arrgotoevent_bothlang[0]);
                $this->view->allGroupEngQuestion_dtls = array($arrgotoevent_bothlang[1]);       
                
                ////get all answers/////
                if(!empty($this->view->question['questionid']))
                {
                    if($preview == 'true') {
                        $answers[$this->view->question['questionid']] = $this->_answerobj->getAnswers('survey_event_answers_preview', $this->_eventid, $this->view->question['questionid']);
                    }
                    else {
                        $answers[$this->view->question['questionid']] = $this->_answerobj->getAnswers('survey_event_answers', $this->_eventid, $this->view->question['questionid']);
                    }
                }
                $this->view->answer = $answers;
                //print_R($this->view->answer);
                ///////////
                if(!empty($arrGotoEvent['questionid']))
                {
                    $arrQanswd = $this->_answerobj->getCountAnswers('survey_event_answers', $this->_eventid, $arrGotoEvent['questionid']);
                }
                $this->view->anscnt = $arrQanswd['cnt'];

            }
            
            if($shownxtquestion != 'shownothing')
            {
                //for label
                $where = array("event_typeid" => $this->view->survey['event_typeid'], 'sort_order' => $arrGotoEvent['sort_order'],
                                "langid" => $this->_lang['langid'], 'question_type' => 'T');
                
               /* $where = array("event_typeid" => $this->view->survey['event_typeid'], 
                    'display_page' => $this->view->question['display_page'], "langid" => $this->_lang['langid'],
                    'question_type' => array('T'));*/
               //print_r($where);//die;                 
                $arrgotoevent_bothlang = $this->_obj->getQuestionDetails($where,"multi");
     //print_r($arrgotoevent_bothlang);die;
                $this->view->labels =  $arrgotoevent_bothlang["Qmulti"];               
            }
            
            //add in log file 11/4/14 3:07 PM
           // $this->logfile(array("shownxtquestion"=>$shownxtquestion),'',$this->_eventid,"shownextparam");
        } else {
            
            // added By Manooj Kumar Dhar
            if ($this->_request->getActionName() <> '') {
                $where = array("event_typeid" => $this->view->survey['event_typeid'], "seq.questionid" => $this->_request->getParam('questionid'), "langid" => $this->_lang['langid']);

                $arrgotoevent_bothlang = $this->_obj->getQuestionDetails($where);          
                $quesForView = $this->processSurveyQuestion(array($arrgotoevent_bothlang[0]), $this->view->survey, $arrgotoevent_bothlang[0]);
                $this->view->question = $quesForView;
                $this->view->questionEng = $this->processSurveyQuestion(array($arrgotoevent_bothlang[1]), $this->view->survey, $arrgotoevent_bothlang[1]);
                if(!empty($arrgotoevent_bothlang[0]['questionid']))
                {
                    $arrQanswd = $this->_answerobj->getCountAnswers('survey_event_answers', $this->_eventid, $arrgotoevent_bothlang[0]['questionid']);
                }
                $this->view->anscnt = $this->view->cnt = $arrQanswd['cnt'];
                
               
            } else {
                $where = array("event_typeid" => $this->view->survey['event_typeid'], "seq.questionid" => $this->_request->getParam('questionid'), "langid" => $this->_lang['langid']);
                $arrgotoevent_bothlang = $this->_obj->getQuestionDetails($where);          
                $quesForView = $this->processSurveyQuestion(array($arrgotoevent_bothlang[0]), $this->view->survey, $arrgotoevent_bothlang[0]);
                $this->view->question = $quesForView;
                $this->view->questionEng = $this->processSurveyQuestion(array($arrgotoevent_bothlang[1]), $this->view->survey, $arrgotoevent_bothlang[1]);
                if(!empty($arrgotoevent_bothlang[0]['questionid']))
                {
                    $arrQanswd = $this->_answerobj->getCountAnswers('survey_event_answers', $this->_eventid, $arrgotoevent_bothlang[0]['questionid']);
                }
                $this->view->anscnt = $this->view->cnt = $arrQanswd['cnt'];               
            }
            // upto here added By Manooj Kumar Dhar
           // print_R($this->view->question);die;
            if($preview == 'true') {
                $this->view->answer = $this->_answerobj->getAnswers('survey_event_answers_preview', $this->_eventid, $arrgotoevent_bothlang[0]['questionid']);
            }
            else {
                $this->view->answer = $this->_answerobj->getAnswers('survey_event_answers', $this->_eventid, $arrgotoevent_bothlang[0]['questionid']);
            }
//print_R($this->view->labels);
          
            $where = array("event_typeid" => $this->view->survey['event_typeid'], 'display_page' => $arrgotoevent_bothlang[0]['display_page'], "langid" => $this->_lang['langid'], 'question_type' => 'T');
    //                  
            $arrgotoevent_bothlang = $this->_obj->getQuestionDetails($where);

            $this->view->labels = $arrgotoevent_bothlang[0];
            //$this->view->questionEng = $arrgotoevent_bothlang[1];
          
        }
		//print_r( $this->view->question);
    }

    Public function getResponseActionToPerform(& $question) {
        $response = $this->_request->getParam('response');

        $action = $this->checkResponseActionToPerform($question, $question['action_response_a'], $question['action_goto_a']);
        if (empty($action)) {
            $action = $this->checkResponseActionToPerform($question, $question['action_response_b'], $question['action_goto_b']);
        }
        if (empty($action)) {
            $action = $this->checkResponseActionToPerform($question, $question['action_response_c'], $question['action_goto_c']);
        }
      
        if (preg_match("/^ID\s[0-9]+$/", $action)) {
            return array(
                'action' => 'show_next',
                'ID' => preg_replace("/^ID\s/", "", $action),
            );
        } elseif (preg_match("/^Delete [0-9]+(,[0-9]+)* ID\s[0-9]+$/", $action)) {
            return array(
                'action' => 'delete_and_show_next',
                'ID' => preg_replace("/^Delete\s[0-9]+(,[0-9]+)*\sID\s/", "", $action),
                'delete_ids' => preg_replace("/\sID\s[0-9]+/", "", preg_replace("/^Delete\s/", "", $action)),
            );
        } elseif ($action == 'Submit Survey (did not qualify)') {
            return array(
                'action' => 'did not qualify',
            );
        } elseif ($action == 'Submit Survey (event is closed)') {
            return array(
                'action' => 'close',
            );
        }
    }

    Public function checkResponseActionToPerform(& $question, $actionResponse, $actionGoTo) {
       //echo $actionResponse;
        $response = $this->_request->getParam('response');
        //print_R($actionResponse);die;
        $action = '';
        if (in_array($actionResponse, array('ALL')) && $response != '') {
            $action = $actionGoTo;
        } elseif (in_array($actionResponse, array('N/A')) && $response == 'N/A') {
            $action = $actionGoTo;
        }
         //For new changes 7/4/14 6:06 PM If ID 8030 response in (3,4) goto ID 8440 else,
        elseif (preg_match("/If ID [0-9]{1,} response in*/", $actionResponse)) {
            $action = $actionGoTo;

            $ID = preg_replace(array("/If ID /", "/ response in (.)+/"), "", $actionResponse);
            preg_match_all('/\(([0-9,]+)\)+/i', $actionResponse, $responseVal);            
            //print_r($responseVal);die;
            $ans = $this->_answerobj->getQuestionIDBasedAnswers($this->_eventid,$ID);
            $arrResponseVal  = explode(",",$responseVal[1][0]);
//print_r($ans);die;
            if (!empty($arrResponseVal) && isset($ans['response_options']) && in_array($ans['response_options'],$arrResponseVal)) {
                $action = trim(preg_replace(array("/^.+goto /", "/else,$/"), "", $actionResponse));
            }
            //echo $action;die;
        }
        elseif (preg_match("/If ID [0-9]{1,}\s=*/", $actionResponse)/* && $response == 3*/) { //If ID 8030 = Yes, goto ID 8080 else,
            $action = $actionGoTo;

            $ID = preg_replace(array("/If ID /", "/ = (.)+/"), "", $actionResponse);
            //print_r($ID);die;
            $ans = $this->_answerobj->getQuestionIDBasedAnswers($this->_eventid,$ID);
//print_r($ans);die;
            if (preg_match("/If ID " . $ID . " = " . $ans['response' . $ans['response_options']] . ",*/", $actionResponse)) {
                $action = trim(preg_replace(array("/^.+goto /", "/else,$/"), "", $actionResponse));
            }
            
        }        
        elseif ($question['input_type'] == 'radio') {
            if ($actionResponse == $question['response' . $response]) {
                $action = $actionGoTo;
            }
            //10/01/2014 TT: 329
            if (empty($action) && in_array($actionResponse, array('Any'))) {
                $action = $actionGoTo;
            }
        }        
        elseif (($question['input_type'] == 'textarea' || $question['input_type'] == 'date' )  && in_array($actionResponse, array('ALL')) ) {
            $action = $actionGoTo;            
        }
        elseif ($question['input_type'] == 'text') {
            $action = $actionGoTo;

            if (!is_array($response)) {

                $response = stripslashes($response);
                //eval('$response = ' . $response);
                $arrresponse = explode(",", $response); //6/6/14 1:58 PM
            }

            if (in_array($actionResponse, array('Any'))) {
                $action = '';
                foreach ($arrresponse as $resp) {
                    if (trim($resp) != '') {
                        $action = $actionGoTo;
                        break;
                    }
                }
            } else {
                $arrRequiredResponses = explode(',', $actionResponse);

                foreach ($arrRequiredResponses as $resp) {
                    for ($i = 1; $i <= 20; $i++) {
                        if ($question['response' . $i] == $resp) {
                            if ($response[$i] == '') {
                                $action = '';
                                break;
                            }
                        }
                    }
                    if ($action == '') {
                        break;
                    }
                }
            }
        } elseif ($question['input_type'] == 'drop down') {
           /* if ($response != '') {
                $action = $actionGoTo;
            } else {
                $action = "";
            }*/
            $action = $actionGoTo;

            
            $response = stripslashes($response);

            //eval('$response = ' . $response);
            $arresponse = explode(",", $response);
            if (in_array($actionResponse, array('Any'))) {
               
                $action = '';
                //print_r($arresponse);
                foreach ($arresponse as $resp) {
                    if (trim($resp) != '') {
                        $action = $actionGoTo;
                        break;
                    }
                }
            } else {//print_R($arresponse);die;
                foreach ($arresponse as $resp) {
                    for ($i = 1; $i <= 20; $i++) {
                        //// 03/01/2014
                        /*if (stripos($question['response' . $i], "Other (free text)") !== false) {
                            $question['response' . $i] = "Other";
                        }*/
                        if($resp == "Other" && $actionResponse == "Other" )
                        {
                            $action = $actionGoTo;
                            break;
                        }
                        elseif(str_replace("~","'",$resp) == $this->nootherbrandtext &&  $actionResponse == "Nootherbrand" )
                        {
                            $action = $actionGoTo;
                            break;
                        }
                        else
                        {

                                if ($question['response' . $i] == $actionResponse /* && $resp == $i */ ) {

                                                $action = $actionGoTo;
                                                break;

                                }
                                else
                                {   //echo "sdfd";
                                         $action = '';
                                }
                        }
                    }                    
                }
//                if ($action == '') {
//                        break;
//                    }
                //echo "<br>|||dd".$action; die;
            }
        } elseif ($question['input_type'] == 'calendar') {
            if ($response != '') {
                $action = $actionGoTo;
            } else {
                $action = "";
            }
        } elseif ($question['input_type'] == 'drop down text') {
            $action = $actionGoTo;

            $response = stripslashes($response);
            
            //eval('$response = ' . $response);
            $arrresponse = explode(",", $response); //6/6/14 1:58 PM

            if (in_array($actionResponse, array('Any'))) {
                $action = '';
                foreach ($arrresponse as $resp) {
                    if (trim($resp) != '') {
                        $action = $actionGoTo;
                        break;
                    }
                }
            } else {
                $arrRequiredResponses = explode(',', $actionResponse);

                foreach ($arrRequiredResponses as $resp) {
                    for ($i = 1; $i <= 2; $i++) {
                        if ($question['response' . $i] == $resp) {
                            if ($response[$i] == '') {
                                $action = '';
                                break;
                            }
                        }
                    }
                    if ($action == '') {
                        break;
                    }
                }
            }
        } elseif ($question['input_type'] == 'checkbox') {
            $action = $actionGoTo;

            
            $response = stripslashes($response);
            
            //eval('$response = ' . $response);
            $arresponse = explode(",", $response);
            if (in_array($actionResponse, array('Any'))) {
               
                $action = '';
                //print_r($arresponse);
                foreach ($arresponse as $resp) {
                    if (trim($resp) != '') {
                        $action = $actionGoTo;
                        break;
                    }
                }
            } else {
                foreach ($arresponse as $resp) {
                    if($question["response1"] == "Make")
                    {
                        if($resp == "Other" && $actionResponse == "Other" )
                        {
                            $action = $actionGoTo;
                            break;
                        }
                    }
                    else
                    {
                        for ($i = 1; $i <= 20; $i++) {
                            //// 03/01/2014
    //                        if (stripos($question['response' . $i], "Other (free text)") !== false) {
    //                            $question['response' . $i] = "Other";
    //                        }

                            if ($question['response' . $i] == $actionResponse && $resp == $i ) {

                                    $action = $actionGoTo;
                                    break;

                            }
                            else
                            {   //echo "sdfd";
                                 $action = '';
                            }
                        }
                    }
                }
//                if ($action == '') {
//                        break;
//                    }
                //echo "<br>|||dd".$action; die;
            }
        }
        //echo $action;
        return $action;
    }

    Public function processSurveyQuestion($arrquestion, $survey, $lastQuestion = array()) {
       // print_R($arrquestion);die;
        $retarr = array();
        if(is_array($arrquestion))
        {
            foreach($arrquestion as $question)
            {
                $dealercolumns = array("dealer_name");
                
                
                     
//                $dlr = $this->_dealerobj->getWhere($dealercolumns, array("id" => $survey["dealer_id"]));

//                $dealer_name = $dlr[0]['dealer_name'];
//                $question['question'] = preg_replace("/<Dealer>|<Dealership1>/i", $dealer_name, $question['question']);
//                $question['question'] = preg_replace("/<model>/i", trim($survey['vehicle_code_desc']), $question['question']);
                if (!empty($this->view->params['response'])) {
                    $resp = $this->view->params['response'];
                    if (preg_match("/array\(/", $resp)) {
                        
                        $resp = stripslashes($resp);
                        eval('$resp = ' . $resp);

                        $question['question'] = preg_replace("/<Name1>/", $resp[1], $question['question']);
                    }
                }
               
                $retarr[]=$question;
            }
        }
        else
        {
            $retarr[]=$arrquestion;
        }       
        return $retarr;
    }    

    Public function submitAction() {

        if (!$this->_eventid) {
            $this->render('survey_not_exist');
            return;
        }

        
        if ($this->_objSession->act != "edit") {
            $arrUpdate = array(
                'browser_agent' => $_SERVER['HTTP_USER_AGENT'],
            );
            $this->_surveyobj->dbupdate($arrUpdate, ' eventid = "' . $this->_eventid . '"');           
        }
        $questionid = $this->_obj->getAllQuestionIDByEventType($this->view->survey['event_typeid']);
        
		foreach($questionid as $keys => $question_id){
			
				$resultQuestionSet = $this->_obj->getQuestionDetailsByQuestionID($question_id['questionid']);
        //BOC To Check if option is choosen by maximum user
        $resultQuestionSet[0]['questionid'] = $question_id;
        if($resultQuestionSet[0]['max_user']){
            $ans = $this->_answerobj->getAnswers('survey_event_answers', $this->_eventid, $question_id['questionid']);
            if($ans['response_options']){      
              $response_cnt = $this->_obj->getAnswerCountByIndex($resultQuestionSet[0],$this->survey['survey_id'],$ans['response_options']);    
              if($response_cnt>=$resultQuestionSet[0]['max_user']){
                   $this->_surveyobj->dbupdate(array('event_status'=>'In Progress'), ' eventid = "' . $this->_eventid . '"');
                   $this->_redirect('/survey/index/start/?survey='.$this->_request->getParam('survey'));
                   exit;
              } 
            }
        }
        //EOC  checking maximum user
        
       
        $arr_que[] =  $question_id['questionid'];
		}   
       // $qids = implode(',', array_keys($this->view->params['question']));//print_R($qids);die;
	   $qids = implode(',', array_values($arr_que));
	 //  echo $qids;
        $lastchar = substr($qids, -1, 1);
        if ($lastchar == ",") {
            $qids = substr_replace($qids, '', -1);
        }
//echo $qids;die;
        if ($qids != '') {
            $arrqids = explode(",",$qids);
            /* $where = array();
            $where[] = $this->getAdapter()->quoteInto('eventid = ?', $this->_eventid);
            $where[] = $this->getAdapter()->quoteInto('questionid not in (?)', $qids);*///"eventid=?",$this->_eventid,"questionid"=>$qids);
            //print_R($delwhere);die;
            $num_rows_affected = $this->_answerobj->deleteExtraAnswers($this->_eventid, $qids);
          //  $num_rows_affected = $this->_answerobj->delete("survey_event_answers",$where);
        }    /**/ 

//echo "sdfd". $this->view->params['task'];die;
        switch ($this->view->params['task']) {
              

            case 'close' :
              
              //$is_contact = $this->_obj->isSendToDealerEmail($this->_eventid,$this->view->survey["event_typeid"],"is_contact");
             // $is_low_score = $this->_obj->isSendToDealerEmail($this->_eventid,$this->view->survey["event_typeid"],"low_score");
               
                //$getTotal_score = $this->_obj->getTotalScore($this->_eventid);
               
                $arrEvent = array(
                    'event_status' => 'Closed',
                    'ip_address' => $this->_helper->GetUserIP(), //6/10/14 6:33 PM
                   // 'max_score' => !empty($getTotal_score['max_score']) ? $getTotal_score['max_score'] : 0 ,//- $maxscore,
                   // 'actual_score' => $getTotal_score['actual_score'],
                  //  'satisfaction_percent' => !empty($getTotal_score['max_score']) ? round($getTotal_score['actual_score'] * 100 / $getTotal_score['max_score'], 2) : 0 ,
                    'langid' =>$this->_lang["langid"]
                );
               //print_r($arrEvent);
                if ($this->_objSession->act != "edit") {
                    $arrEvent["survey_date"] = new Zend_Db_Expr('now()') ;
                    //$where["eventid"] = $this->_eventid;
                    
                }
                $this->_surveyobj->dbupdate($arrEvent, ' eventid = "' . $this->_eventid . '"');
                //chk if scoring question is attempted by the user 
               
               /* if($this->view->survey["event_typeid"] ==2)
                {
                    $userScore = !empty($arrEvent['max_score']) ? round($arrEvent['actual_score']*100/$arrEvent['max_score'],2) : 0 ;
                    $this->updateCodeStatus($userScore,$this->view->survey["event_typeid"],$is_contact);//
                }
                else
                {
                    $isattempt= $this->_answerobj->isScoringQuestionAttempted($this->view->survey["event_typeid"],$this->_eventid);
                    if($isattempt == 'Y' )
                    {
                        $this->updateCodeStatus(round($arrEvent['actual_score']*100/$arrEvent['max_score'],2),$this->view->survey["event_typeid"],$is_contact);//
                    }
                    //if scoring question not attempted set actual_score is null Dipa 8/20/14 11:21 AM
                    else
                    {
                        $arrEvent = array(
                        'actual_score' => new Zend_Db_Expr('NULL'),
                         'max_score'=>'0',
                         'satisfaction_percent'=>'0'   
                         );                   
                        $this->_surveyobj->dbupdate($arrEvent, ' eventid = "' . $this->_eventid . '"');

                    }
                }
                
                $this->processPostSubmissionUpdates($this->_eventid);
               
                //To update view_cust_info field in survey_event table
                
                $this->_obj->updateViewCustInfoField($this->_eventid,$this->view->survey["event_typeid"]);
                
                
                // whether send_alert_to_dealer value as per response - 09/12/2010 by Pragya Dave
                
                $emailObj = new Damco_Email();
                //to check whether low score Or customercontact dealer is selected
                // Customer alert should not be generated if responded as anonymous.  7/29/14 3:40 PM
                
                $where = array('eventid' => $this->_eventid);               
                $anonymous = $this->_eventobj->getWhere("is_anonymous", $where);
                if($anonymous[0]['is_anonymous'] == '0')
                {                    
                    $sendToDealerEmail = $emailObj->sendCustomerAlert($this->_eventid,array("low_score"=>$is_low_score,'is_contact'=>$is_contact)); 
                }
                elseif ($anonymous[0]['is_anonymous'] == '1') {
                    $arrEvent = array(
                    'code_status' => ''
                     );
                    $this->_surveyobj->dbupdate($arrEvent, ' eventid = "' . $this->_eventid . '"');
                }
                
                //$this->logfile($is_contact,$is_low_score,$this->_eventid);
                    //
                 //send survey completation mail
                 //$emailObj = new Damco_Email();
                //Changes as per client's req 7/31/14 10:45 AM
                if($is_contact == "N" && $is_low_score == "N" && $this->view->survey["event_typeid"] !=2)
                {
                    $emailObj->sendCompletedSurveyAlert($this->_eventid);    
                }
                 //to chk performance
                 $emailObj->sendPerformanceAlert($this->_eventid); // 12/30/15 4:43 PM  */
                                  
                break;

           case 'did not qualify' :
                $arrEvent['event_status'] = 'Did not qualify';            
                
                if ($this->_objSession->act != "edit") {
                    $arrEvent['survey_date'] = new Zend_Db_Expr('now()');
                  
                }
                $this->_surveyobj->update($arrEvent, "eventid = " . $this->_eventid);
                //$this->processPostSubmissionUpdates("response_options");
                
                break;          

            case 'open' :
                $arrEvent = array(
                    'event_status' => 'Open',
                    'survey_date' => new Zend_Db_Expr('now()'),
                );
                $this->_objDb->update('survey_events', $arrEvent, "eventid = " . $this->_eventid);
                break;          

            default:

                if ($this->view->survey['complain_registered'] == 'Yes') {
                    $arrEvent = array(
                        'event_status' => 'Incomplete',
                        'survey_date' => new Zend_Db_Expr('now()'),
                    );

                    $this->_objDb->update('survey_events', $arrEvent, "eventid = " . $this->_eventid);
                }
                break;
        }      
      
        // Clear session	
        $this->_objSession->act = "";
        $this->_objSession->statusClosed = "";
        $_SESSION["arrEDitSurvey"][$this->view->eventid] = "";
        $this->_objSession->eventtable = '';

        $this->_redirect('/survey/index/thankyou/?langid='.$this->_lang['langid'].'&survey_id='.$this->survey['survey_id']);
        die;
    }

    Public function processPostSubmissionUpdates($type = '') { 
        $questions = $this->_obj->getEventdataUpdatableQuestion($this->_eventid);
        $arrEvents = array();
        //print_R($questions);
        foreach ($questions as $ques) {
            $fields = explode(';', $ques['event_fields_tobe_updated']);
            foreach ($fields as $fld) {
                $arrFld = explode(':', $fld);
                
                    if ($ques['answer' . $arrFld[0]] != '') {
                        switch ($arrFld[1]) {                           

                            case 'dealer_id':
                                $dealercolumns = array('id',"dealer_name","dealershipid");
                                //echo "here1:".$arrFld[0].$ques['answer' . $arrFld[0]]."<br>";
                                $dlrdtls = explode("::",$ques['answer' . $arrFld[0]]); //strored as "47::Hot Motorbike"
                                $dlr =  $this->_dealerobj->getWhere($dealercolumns, array("id" => $dlrdtls[0]));                                   
                              
                               //get customer related with this event
                               $relatedCustomerDtls = $this->_eventobj->getEventCustomer($this->_eventid);
                               //update customer tbl:
                               $arrCustomerDealerdtls = array(
                                    'dealer_id' => $dlrdtls[0],
                                    'dealer_name' => $dlr[0]['dealer_name'],
                                    'dealer_code'=> $dlr[0]['dealershipid']
                                );
                              // print_R($arrCustomerDealerdtls);die;
                               $this->_customerobj = new Customer_Model_Customers();
                               $this->_customerobj->dbupdate($arrCustomerDealerdtls, ' id = "' . $relatedCustomerDtls["id"] . '"');
                               
                               $arrEventDealerid = array(
                                    'dealer_id' => $dlrdtls[0],
                                    //'dealer_name' => $dlr['dealer_name'],
                                );
                              $this->_surveyobj->dbupdate($arrEventDealerid, ' eventid = "' . $this->_eventid . '"');
                              // $this->logfile($arrEventDealerid,$arrCustomerDealerdtls,$this->_eventid);
                                /*  echo "Here2";print_r($dlr);
                               echo "Here3";print_r($relatedCustomerDtls);
                               echo "Here4";print_r($arrCustomerDealerdtls);die;*/
                             /**/
                               // End code
                               //die;
                                break;

                            default:
                               //
                                break;
                        }
                    }
              
            }
        }        
        
    }

    function logAlert($alert_type, $event_type, $email_subject, $alert_content, $attachments = '', $to_addess, $cc_address = '', $bcc_address = '', $importid = '') {
        $alert = array(
            'alert_type' => $alert_type,
            'event_type' => $event_type,
            'object_type' => 'event',
            'object_id' => $this->_eventid,
            'email_subject' => $email_subject,
            'to_addess' => $to_addess,
            'cc_address' => $cc_address,
            'bcc_address' => $bcc_address,
            'alert_content' => $alert_content,
            'attachments' => $attachments,
        );
        $this->_objDb->insert('alerts_log', $alert);
    }

    Public function changelanguageAction() {
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $langid =  $this->_request->getParam('langid',"1");
        $this->setUserLanguage($langid);
        
        echo "done";
         
    }
    
    private function setUserLanguage($langid=1)
    {
        $langObj = new Default_Model_Languages();
       
        $language =  $langObj->getLanguages('survey',$langid);
     //  print_r($language);die;
        if (is_array($language)) {
            $this->_objSession->locale['language'] = $language[0]['lang_name'];
            $this->_objSession->locale['lang_code'] = $language[0]['lang_code'];
            $this->_objSession->locale['langid'] = $language[0]['langid'];
            $this->_objSession->locale['direction'] = $language[0]['direction'];
            $this->_objSession->locale['lang_character_set'] = $language[0]['lang_character_set'];
        } 
    }

    Public function thankyouAction() {
        $this->_objconfig = new Survey_Model_Config();
		$config_token="survey_thank_you_text";
		
		$this->_surveysobj = new Survey_Model_Survey();		
		$surveys_data = $this->_surveysobj->getSurveyByID($this->_request->getParam('survey_id'));		
        
        $qid = $this->_objconfig->findRow("config_val",array("config_var"=>$config_token));
        $introtext = $this->_obj->getStatictext($qid["config_val"],$this->_lang['langid']);
       // print_R($introtext);die;
       //$this->view->thankstext = $introtext[0]["question"];
		$this->view->thankstext = $surveys_data[0]['thanks_message'];
    }    

    /**
     * Update event code status
     */
    public function updateCodeStatus($act_score,$event_typeid, $is_contact='N') {
        $arrCodeStat = "";
        if ($this->view->survey['event_typeid'] == 1) {
            $green = $this->_objConfig["survey"]["sales"]["minScoreGreen"];//$this->_objConfig->CodeStatusRange->OESminScore->Green;
            $amber = $this->_objConfig["survey"]["sales"]["minScoreAmber"];//$this->_objConfig->CodeStatusRange->OESminScore->Amber;
        } else {
            $green = $this->_objConfig["survey"]["service"]["minScoreGreen"];//$this->_objConfig->CodeStatusRange->PESPOVminScore->Green;
            $amber = $this->_objConfig["survey"]["service"]["minScoreAmber"];//$this->_objConfig->CodeStatusRange->PESPOVminScore->Amber;
        }
       //echo $green."|".$amber;die;
        if($is_contact == "Y")
        {
            $arrCodeStat = array(
                    'code_status' => 'Red',
                    'code_red_status' => 'Open',
                );
        }
        else
        {
            if($event_typeid != 2)
            {
                if ($act_score >= $green) {
                    $arrCodeStat = array(
                        'code_status' => 'Green',
                    );
                } elseif ($act_score >= $amber) {
                    $arrCodeStat = array(
                        'code_status' => 'Amber',
                    );
                } else {
                    $arrCodeStat = array(
                        'code_status' => 'Red',
                        'code_red_status' => 'Open',
                    );
                }
            }
        }
        if(!empty($arrCodeStat))
        {
            $this->_surveyobj->dbupdate($arrCodeStat, ' eventid = "' . $this->_eventid . '"');
        }
         
        //$this->_objDb->update('survey_events', $arrCodeStat, "eventid = " . $this->_eventid);
    }
       
    public function checkgrouplastquestionAction()
    { 
		
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $where = array("event_typeid" => $this->view->survey['event_typeid'], "seq.questionid" => $this->_request->getParam('questionid'));
         //    print_r($where);die;
        
        $question_dtls = $this->_obj->getQuestionDetails($where);
        
        
        //print_r($question_dtls);die;
        $response = $this->_request->getParam('response');
        // if this question has child return parent question's ID && id
        $parent_grp_dtls = array();
        
        if($question_dtls[0]["parent_id"] > 0)
        {
            $where = array("event_typeid" => $this->view->survey['event_typeid'], "seq.questionid" => $question_dtls[0]["parent_id"]);
            $parent_grp_dtls = $this->_obj->getQuestionDetails($where);
        }
        else
        {
            $parent_grp_dtls[0]["ID"] = $question_dtls[0]["ID"];
            $parent_grp_dtls[0]["questionid"] = $question_dtls[0]["questionid"];
        }
        
       
       //get nxt questions dtls
       $nxtqdtls = $this->getnextQuestionDeatils($this->view->survey['event_typeid'],'1', $question_dtls[0]["questionid"],$response);
       
       //get next grpid
       
       $questionNextGroupId = $this->_obj->getNextGroupId( $this->view->survey['event_typeid'], $question_dtls[0]["questionid"],
               $question_dtls[0]["groupid"]);
        //print_r($questionNextGroupId);die;       
         //get no of total answer received :
       
       $myQid = $this->_request->getParam('questionid');
	   $preview = $this->_request->getParam('preview');
       
        if(!empty($myQid))
        {
			if($preview=='true'){
				$arrQanswd = $this->_answerobj->getCountAnswers('survey_event_answers_preview', $this->_eventid, $myQid);  
			}
			else{
				$arrQanswd = $this->_answerobj->getCountAnswers('survey_event_answers', $this->_eventid, $myQid);  
			}
                
        }
		
		if($arrQanswd['cnt']==0){
			$arrQanswd['cnt']++;
			$_SESSION['temp_que'] = $parent_grp_dtls[0]["questionid"];
		}
	
		if(($arrQanswd['cnt']>1) || ($_SESSION['temp_que']!= $parent_grp_dtls[0]["questionid"])){
			$arrQanswd['cnt']++;
		}
		
        $ret = array("group_last_ques"=>$question_dtls[0]["group_last_ques"],
            "is_participate_in_branching"=>$question_dtls[0]["is_participate_in_branching"],
            //"next_group_id"=>$questionNextGroupId["groupid"],"is_grpshow"=>$nxtqdtls, 
            //"parent_grp_id"=>array("ID" =>$parent_grp_dtls[0]["ID"], 
                "qid"=>$parent_grp_dtls[0]["questionid"],
            "anscnt"=>$arrQanswd['cnt']);
        
        $this->view->anscnt = $arrQanswd['cnt'];
        
        echo json_encode($ret);
    }
    

    public function getnextQuestionDeatils($event_typeid,$langid,$questionid,$response)
    {
        
        $where = array("event_typeid" => $event_typeid, "langid" => $langid, "seq.questionid" => $questionid);
        
        //
        $question_dtls = $this->_obj->getQuestionDetails($where);
        
        $question =  $question_dtls[0];       
        $questionEng = $question_dtls[1];
        //print_R($questionEng);die;
        $action = $this->getResponseActionToPerform($questionEng);
        
       //print_R($action);die;
        unset($where);
        $arrGotoEvent = array();
        if(!empty($action))
        {
            switch ($action['action']) {

                case 'show_next' :               
                    $where = array("event_typeid" => $event_typeid, "ID" => $action['ID'], "langid" => $langid);
                    //print_R($where);die;
                    $arrgotoevent_bothlang = $this->_obj->getQuestionDetails($where);
    //print_R($arrgotoevent_bothlang);die;
                    $arrGotoEvent =  $arrgotoevent_bothlang[0];   

                    break;
                case "did not qualify":
                            case "close":
                        return "showgrpnxt";
                    break;
            }
        }
        
        //echo $arrGotoEvent["groupid"] .">". $question["groupid"];
        
        if(!empty($arrGotoEvent) && ($arrGotoEvent["groupid"] > $question["groupid"])) //next ques grp > current grp, den show all data at a time
        {
            return "showgrpnxt";
        }
        elseif(!empty($arrGotoEvent) && ($arrGotoEvent["groupid"] == $question["groupid"])) //next ques grp == current grp, den do nothing
        {
            return "shownothing";
        }
        elseif(empty($arrGotoEvent)) //next ques grp == current grp, 7/1/14 5:12 PM den do nothing
        {
            return "shownothing";
        }
        
        else
        {
            return "shownxt"; //next ques grp < current grp, den show individual ques at a time
        }
        
    }
    /**
     * @this function is added by Manooj Dhar
     */
    

    private function printdata($data) {
        echo "<pre>";
        print_r($data);
    }
    
    private function logfile($is_contact,$is_low_score='',$eventid,$showmsg='')
    {
        $str="";
        /* $stream = @fopen('log/logfile'.date("W_Y").'.txt', 'a', false);
            $writer = new Zend_Log_Writer_Stream($stream);
            $logger = new Zend_Log($writer);
//            $is_contact= (is_array($is_contact)) ? implode("|",$is_contact) : $is_contact;
//            $is_low_score= (is_array($is_low_score) && !empty($is_low_score)) ? implode("~",$is_low_score) : $is_low_score;
            if(is_array($is_contact) && !empty($is_contact) )
            {
                foreach($is_contact as $k=>$v)
                {
                    if(!empty($v))
                    {
                        $str .=$k."=>".$v."\n\r";
                    }
                }
            }
            if(is_array($is_low_score) && !empty($is_low_score) )
            {
                foreach($is_low_score as $k=>$v)
                {
                    if(!empty($v))
                    {
                        $str .=$k."=>".$v."\n\r";
                    }
                }
            }
            $logger->info('Informational message =>'.$showmsg.':'.$str." | Eventid:".$eventid."\n"); */
    }
    
    public function redirectAction( ) {
        if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
        if ( !empty( $details )
             && is_object($details) ) {
            $objLocationDetails = new Survey_Model_LocationDetails( );
            $objLocationDetails->insert(array(
                'ip_address' => $ip,
                'hostname' => isset($details->hostname) ? $details->hostname : '',
                'city' => isset($details->city) ? $details->city : '',
                'region' => isset($details->region) ? $details->region : '',
                'country' => isset($details->country) ? $details->country : '',
                'location' => isset($details->loc) ? $details->loc : '',
                'organisation' => isset($details->org) ? $details->org : '',
                'data' => serialize($details)
            ));
        }
        
        $langObj = new Default_Model_Languages( );
        $locale = new Zend_Locale();
        $result = $langObj->fetchRow('UPPER(lang_code) LIKE "%'
                .strtoupper($locale->getLanguage()).'%"');
        if (is_object($result) ) {
            $result = $result->toArray();
            $langID = $result['langid'];
            $this->view->lang_char_set = !empty( $result['lang_character_set'] )
                        ?$result['lang_character_set']:'UTF-8';
        }
        else {
            $langID = '1';
            $this->view->lang_char_set = 'UTF-8';
        }
        
        $pageObj = new Default_Model_Pages( );
        $result = $pageObj->getPageContent('maintenance_page', $langID);
        $this->view->content = $result[0]['content'];
    }  
    
    public function tocAction()
    {
       $this->_helper->layout->setLayout('toc');
        $pageObj = new Default_Model_Pages( );
        $result = $pageObj->getPageContent('terms_of_services', 1);
        $this->view->content = $result[0]['content'];
    }
    /* pdf footer */
    public function pdffooterAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->view->customText = $this->getRequest()->getParam('customText','');
    }
    
}
?>
