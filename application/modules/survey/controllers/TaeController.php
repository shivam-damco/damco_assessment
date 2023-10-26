<?php

/**
 * TAE Survey - Task #3619
 *
 * @version     1.0
 * @author      Harpreet Singh
 * @copyright   Triumph
 * @date        12th April, 2019
 *
 */

class Survey_TaeController extends APD_Core_CoreController {
    private $_objDb;
    private $_objConfig;
    private $_objSession;   
    private $_eventid;
    private $_langID;
    private $_survey;
    private $_configValues;
    private $_configObject;
    private $_eventTypeid;
    private $nootherbrandtext;

    /**
     * Initialzing the variables for TAE survery controller
     */
    public function init() {
        parent::init();
        $this->_objConfig = Zend_Registry::get('config');
        $this->_obj = new Survey_Model_Questions();
        $this->_surveyobj = new Event_Model_Events();
        $this->_answerobj = new Survey_Model_Answers();
        $this->_configObject = new Survey_Model_Config();
        $this->_objSession = new Zend_Session_Namespace('Default');
        $this->_helper->layout->setLayout('survey');
        
        $this->_eventid =
        $this->view->eventid = $this->_helper->getEventID(
                $this->_request->getParam('survey'), 'decrypt');
        
        $this->_langID =
        $this->view->langid = $this->_request->getParam('langid', '1');
        
        $this->view->params = $this->_request->getParams();
        $this->view->lang_char_set = 'UTF-8';
        $this->view->lang_code = !empty($this->_objSession->locale['lang_code'])
                                 ? $this->_objSession->locale['lang_code'] : 'en-GB' ;
        $this->view->direction = !empty($this->_objSession->locale['direction'])
                                 ? $this->_objSession->locale['direction'] : 'ltr' ;
       
        $arrConfigVariables = array('survey_make_name', 'survey_submit_button',
            'survey_preferred_language_text', 'survey_ok_button', 'survey_proceed',
            'survey_make_option_nootherbrand', 'survey_error_text', 'age',
            'hide_question_options', 'survey_progress', 'survey_thank_you_text_tae',
            'survey_pre_submit_text', 'survey_notselected_text', 'survey_notexist_orclosed',
            'test_ride_alert_question_id', 'survey_exit_parent_group_id');
        $this->_configValues = $this->_configObject->getConfigQueIds($arrConfigVariables);
        
        $this->view->arrmakes = unserialize($this->_configValues['survey_make_name']);
        $this->view->arrages = explode(',', $this->_configValues['age']);        
        
        if ( !empty($this->_eventid) ) {
            $cond = array( 'event_status' => array( 'Open', 'In progress' ,'Bounce Removed') );
            $this->_survey =
            $this->view->survey = $this->_surveyobj->getSurveyEventdetail($this->_eventid, $cond);
            $this->_eventTypeid = $this->view->survey['event_typeid'];

            if ( !is_array($this->_survey) ) {
                $this->view->closedevent = 'yes';
                $this->_forward('error');
                return;
            }
        }
        else {
            $this->_forward('error');
            return;
        }
        
        if ( $this->view->survey['event_status'] == 'Closed' 
               || $this->view->survey['event_status'] == 'Did not qualify' 
               || $this->view->survey['event_status'] == 'Incomplete' ) {
            $this->_redirect('/survey/tae/thankyou/?langid='.$this->_lang['langid']);
        }
        //get no other brand questionids 
        $this->view->noOtherBrandQuestionIds = explode(",",$this->_configValues['survey_make_option_nootherbrand']);
    }

    /**
     * Method to handle Error operations
     * @return type
     */
    public function errorAction() {
        $introtext = $this->_obj->getStatictext(
                $this->_configValues['survey_notexist_orclosed'], 
                $this->_lang['langid']);
        $this->view->survey_notexist_orclosed = $introtext[0]['question'];
        $this->render('survey_not_exist');
        return;
    }

    /**
     * Method to handle the TAE survey
     */
    public function indexAction() {
        $this->_setStaticQuestions();
        $questionGroupID = $this->_request->getParam('group_id', '1');
        $questionDetails = $this->_obj->getQuestionDetails(array( 
            'event_typeid' => $this->_eventTypeid, 'langid' => 1, 'groupid' => $questionGroupID));
//        echo '<pre>'; print_r($questionDetails);die;
        if ( !empty($questionDetails[0]['questionid'])) {
            $this->view->answer = $this->_answerobj->getAnswers('survey_event_answers',
                    $this->_eventid, $questionDetails[0]['questionid']);
        }
           
        $arrgotoevent_bothlang = $this->_obj->getQuestionDetails( array( 
            'event_typeid' => $this->_eventTypeid, 'display_page' => $questionDetails[0]['display_page'], 
            'langid' => 1, 'question_type' => array('T', 'L')), 'lbl' );           
        $this->view->labels =  $arrgotoevent_bothlang['Qlbl'];
          
        $allGroupQuestionDetails = $this->_obj->getQuestionDetails( array( 
            'event_typeid' => $this->_eventTypeid, 'langid' => 1, 'groupid' => $questionDetails[0]['groupid'], 
            'question_type' => array('Q', 'V') ), 'multi' );
//        echo '<pre>'; print_r($allGroupQuestionDetails);die;
        $this->view->allGroupQuestion_dtls = $allGroupQuestionDetails['Qmulti'];
        $this->view->allGroupEngQuestion_dtls = $allGroupQuestionDetails['QEngmulti'];
        
        if ( empty($questionGroupID) ) {
            $qid[] = $questionDetails[0]['questionid'];
        }
        else {               
           foreach($this->view->allGroupQuestion_dtls as $arr) {
               $qid[] = $arr['questionid'];
           }
        }
        
        $arrallGroupQuestionanswer = $this->_answerobj->getAnswers(
                'survey_event_answers', $this->_eventid, $qid);
        
        if ( !empty($arrallGroupQuestionanswer) ) {   
            foreach($arrallGroupQuestionanswer as $arr) {
                  $this->view->answer[$arr['questionid']] = $arr;
            }
        }
        
        $this->view->anscnt = 1;
        if ( !empty($questionDetails[0]['questionid']) ) {                
            $arrQanswd = $this->_answerobj->getCountAnswers('survey_event_answers', 
                    $this->_eventid, $questionDetails[0]['questionid']);
            $this->view->anscnt = $arrQanswd['cnt'];
        }
            
        //get total no of questions are there in this event type
        $totalQues = $this->_obj->getAllQuestionEventTypewise($this->_eventTypeid);
        $this->view->multiplier = $totalQues['num'];
        
        $systemErrorMessage = $this->_obj->getQuestionDetails(array(
            'seq.questionid' => '264',
            'langid' => !empty($this->_lang['langid']) ? $this->_lang['langid'] : 1
        ));
        $this->view->systemErrorMessage = $systemErrorMessage[0]['question'];
        
        
    }

    /**
     * Mehtod to handle Ajax based requests
     */
    public function processresponseAction() {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_forward('error'); exit;
        }
        
        $this->_setStaticQuestions();
        
        $this->_helper->layout->disableLayout();
        $question_dtls = $this->_obj->getQuestionDetails( array(
            'event_typeid' => $this->view->survey['event_typeid'], 
            'langid' => !empty($this->_lang['langid']) ? $this->_lang['langid'] : 1, 
            'seq.questionid' => $this->_request->getParam('questionid')));
        $question = $this->view->question = $question_dtls[0];
//      print_r($question_dtls);die;
        $questionEng = $this->view->questionEng = $question_dtls[1];
        if(!empty($question['questionid']))
        {
            $this->view->answer = $this->_answerobj->getAnswers('survey_event_answers', 
                    $this->_eventid, $question['questionid']);
        }
        
        $this->view->qIdForPrevBrandName = $question['questionid'];

        $response = $this->_request->getParam('response');
        $questionText = $this->_request->getParam('questiontext',"");
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
            'answer_eng1' => '',
            'answer_eng2' => '',
            'answer_eng3' => '',
            'answer_eng4' => '',
            'answer_eng5' => '',
            'answer_eng6' => '',
            'answer_eng7' => '',
            'answer_eng8' => '',
            'answer_eng9' => '',
            'answer_eng10' => '',
            'answer_eng11' => '',
            'score' => '',
            'code_status' => 'amber',
            'questiontext' => $questionText,
        );
        
        switch ( $question['input_type'] ) {
            case 'radio':
                if ( $response > 11 ) {
                    $response = 11;
                }
                $arrQuestionAnswer['answer'. ($response)] = $question['response' . $response];
                $arrQuestionAnswer['score'] = ($question['max_score'] > 0) ? ($response-1) : 0;
                break;
                
            case 'drop down':
                $response = trim($response);
                if ( trim($question['response1']) == 'Nationname' ) {
                    $arroptions = $this->view->getMarketBasedDealers($this->_eventid,
                            $this->qIdForMarketName);
                    foreach ( $arroptions as $arrd ) {
                        $myres = str_ireplace("~", "'", $response);
                        if ( $arrd['dealer_name'] == $myres ) {
                            $response = $arrd['id'] . '::' . $myres;
                        }
                        else {
                            $myres =  str_ireplace('~', '&', $response);
                            if ( $arrd['dealer_name'] == $myres ) {
                                $response = $arrd['id'] . '::' . $myres;
                            }
                        }
                    }
                    $arrQuestionAnswer['response_options'] = '1';
                    $arrQuestionAnswer['answer1'] = $response;
                } 
                elseif ( trim($question['response1']) == 'NationsName' ) {
                    $sl = !empty($this->_lang['langid']) ? $this->_lang['langid'] : 1;
                    $arroptions = $this->view->getNations($sl);
                    foreach ($arroptions as $arrd) {
                        $dbStructName = trim($arrd['nation_name']);
                        $myres = str_ireplace("~", "'", $response);
                        if ($dbStructName == $myres) {
                            $response = $arrd['nationid'] . '::' . $myres;
                        } else {
                            $myres =  str_ireplace('~', '&', $response);
                            if ($dbStructName == $myres) {
                                $response = $arrd['nationid'] . '::' . $myres;
                            }
                        }
                    }
                    $arrQuestionAnswer['response_options'] = '1';
                    $arrQuestionAnswer['answer1'] = $response;
                }
                elseif ( trim($question['response1']) == 'Marketname' ) {
                    $sl = !empty($this->_lang['langid']) ? $this->_lang['langid'] : 1;
                    $arroptions = $this->view->getMarkets(3, $sl);
                    foreach ($arroptions as $arrd) {                        
                        $dbStructName = trim($arrd['country_name']);
                        if($dbStructName == trim($response)) {
                            $response = $arrd['structid'] . '::' . $response;
                        }                        
                    }                   
                    $arrQuestionAnswer['response_options'] = '1';
                    $arrQuestionAnswer['answer1'] = $response;
                }
                elseif ( trim($question['response1']) == 'Make' ) {
                    if ( $this->nootherbrandtext == $response ) {
                        $response = $this->arrmakename[1];
                    }                                    
                    $arrQuestionAnswer['response_options'] = '1';
                    $arrQuestionAnswer['answer1'] = str_replace('- Select -', '', trim($response));
                }
                elseif ( trim($question['response1']) == 'Age' ) {
                    $arrQuestionAnswer['response_options'] = '1';                    
                    $arrQuestionAnswer['answer1'] = str_replace('- Select -', '', trim($response));
                }
		elseif ( trim($question['response1']) == 'Model' ) {
                    $response = str_replace('~', '/', $response);
                    $arrQuestionAnswer['response_options'] = '1';
                    $arrQuestionAnswer['answer1'] = $response;
                }
                else {
                    $arrQuestionAnswer['response_options'] = '1';
                    $arrQuestionAnswer['answer1'] = $response;
                }                
                break;
                
            case 'checkbox':
                if (get_magic_quotes_gpc()) {
                    $response = stripslashes($response);
                }
                $arrresponse = explode(',', $response);
                $arrQuestionAnswer['response_options'] = $response;
                if ( count($response) ) {
                    foreach ($arrresponse as $i => $val) {
                        $arrQuestionAnswer['answer' . $val] = $question['response' . $val];
                        $arrQuestionAnswer['answer_eng' . $val] = $question['response' . $val];
                    }
                }
                break;
                
            case 'textarea':
            case 'calendar':
            case 'text':
                 $arrQuestionAnswer['response_options'] = '1';
                 $arrQuestionAnswer['answer1'] = $response;                 
                 $response = empty($response) ? ' ' : $response;
            default:
                break;
        }
        
        if ( !empty($question['questionid']) ) {
            $answer = $this->_answerobj->getAnswers( 'survey_event_answers', 
                    $this->_eventid, $question['questionid'] );
        }
        
        if ( $response != '' ) {
            if ( empty($answer['answerid']) ) {
                $answer['answerid'] = $this->_answerobj->dbinsert( $arrQuestionAnswer );
            } 
            else {
                $where[] = " answerid = '" . $answer['answerid'] . "'";
                unset($arrQuestionAnswer["questiontext"]);
                $this->_answerobj->dbupdate($arrQuestionAnswer, $where);
                
            }
            
            if ( $this->view->survey['event_status'] != 'In progress' ) {
                $this->_surveyobj->dbupdate( array(
                        'event_status' => 'In progress',
                    ), ' eventid = "' . $this->_eventid . '"');
            }
           
            $shownxtquestion = $this->_request->getParam('shownxtquestion');
            $action = $this->getResponseActionToPerform($questionEng);
//            var_dump($action);die;
            $this->view->curaction = $action['action'];
                
            if ( $shownxtquestion == 'y' ) {
                switch ( $action['action'] ) {
                    case 'delete_and_show_next' :
                        $delquestions = $this->_objDb->select()
                            ->from(array('seq' => 'survey_event_questions'), 
                                    'group_concat(seq.questionid) as ids')
                            ->where('seq.ID in (' . $action['delete_ids'] . ')')
                            ->query()
                            ->fetch();
                        $this->_objDb->delete('survey_event_answers', 
                                'questionid IN (' . $delquestions['ids'] . ') 
                                AND eventid = ' . $this->_eventid);
                        break;
                    case 'show_next' :
                        $this->view->lastQid = $question['questionid'];
                        $where = array('event_typeid' => $this->view->survey['event_typeid'], 
                            'ID' => $action['ID'], 'langid' => $this->_lang['langid']);
                        $arrgotoevent_bothlang = $this->_obj->getQuestionDetails($where);

                        $arrGotoEvent = 
                        $this->view->question = $arrgotoevent_bothlang[0];

                        if ( $question['input_type'] == 'drop down' ) {
                            $this->view->arroptions = $this->_obj->getalloptions(
                                    $question['questionid'], $this->_lang['langid']);
                        } 

                        if ( !empty($arrGotoEvent['questionid']) ) {
                            $arrQanswd = $this->_answerobj->getCountAnswers(
                                'survey_event_answers', $this->_eventid, 
                                $arrGotoEvent['questionid']);
                        }

                        $grp_pgid = $arrGotoEvent["groupid"];
                        if ( !empty($grp_pgid) ) {                              
                            $where = array('event_typeid' => $this->view->survey['event_typeid'], 
                                'langid' => !empty($this->_lang['langid']) ? $this->_lang['langid'] : 1, 
                                'groupid' => $grp_pgid, 'question_type' => array('V', 'Q'));
                        }                       
                        else {
                            $where = array('event_typeid' => $this->view->survey['event_typeid'], 
                                'langid' => !empty($this->_lang['langid']) ? $this->_lang['langid'] : 1, 
                                'question_type' => array('V', 'Q'));
                        }                            

                        $allGroupQuestion_dtls = $this->_obj->getQuestionDetails(
                                $where, 'multi');
                        $arrallGroupQuestion_dtls = 
                        $this->view->allGroupQuestion_dtls = $allGroupQuestion_dtls['Qmulti'];
                        $this->view->allGroupEngQuestion_dtls = $allGroupQuestion_dtls['QEngmulti'];

                        $qid = array();
                        if ( empty($grp_pgid) ) {
                            $qid[] = $question_dtls[0]['questionid'];
                        }
                        else {               
                           foreach($arrallGroupQuestion_dtls as $arr) {
                               $qid[] = $arr['questionid'];
                           }
                        }

                        $arrallGroupQuestionanswer = $this->_answerobj->getAnswers(
                                'survey_event_answers', $this->_eventid, $qid);
                        if ( !empty($arrallGroupQuestionanswer) ) {   
                            foreach($arrallGroupQuestionanswer as $arr) {
                                $this->view->answer[$arr['questionid']] = $arr;
                            }
                        }
                        break;

                    case 'did not qualify, optout':
                    case 'close, optout':
                    case 'open' :
                    case 'open, convert to email contact':                   
                        break;  
                    case 'close' : 
                    case 'did not qualify':
                        $this->view->noquestionLeft = true;
                        $this->_noquestionLeft = true;
                        echo json_encode(array(
                            'error_code' => '0',
                            'error_msg' => 'Success',
                            'status' => $action['action'],
                            'question_id' => $question['questionid'],
                            'ID' => $question['ID'],
                            'question_string' => $this->view->render('tae/process-response.phtml'),
                        )); die;
                        break;              
                    default:
                        break;
                }
            }
            
            if ( !empty($action['action']) ) {
                $where = array('event_typeid' => $this->view->survey['event_typeid'], 
                    'display_page' => $this->view->question['display_page'], 
                    'langid' => $this->_lang['langid'], 'question_type' => array('T', 'L'));
                $arrgotoevent_bothlang = $this->_obj->getQuestionDetails( $where, 'multi' );
                $this->view->labels =  $arrgotoevent_bothlang['Qmulti'];
            }
            
            $arrQanswd = $this->_answerobj->getCountAnswers('survey_event_answers', 
                $this->_eventid, '');
            $this->view->anscnt = $arrQanswd['cnt'];
        }
        
        if ( $question['ID'] == '9010'
             && $this->_request->getParam('response') == '2' ) {
            $questionsLeft['num'] = 1;
        }
        else {
            $questionsLeft = $this->_obj->getRemainingQuestions( $this->_eventid,
                $this->view->survey['event_typeid'], $question['ID'] );
        }
        
        if( $response == 'No other brand' ) {
            $exit_parent_groups = unserialize($this->_configValues['survey_exit_parent_group_id']);
            foreach ( $exit_parent_groups as $question_ids => $parent_id) {
                if ( in_array ( $question['questionid'], explode ( ",",$question_ids) ) ) {
                    $question['exit_group_parent_id'] = $parent_id;
                }
            }
        }
        
        if ( !empty($question['parent_id']) && $question['exit_group_parent_id'] == 0 ) {
            $result = $this->_obj->getQuestionDetails(array(
                'display_page' => $question['parent_id'],
                'event_typeid' => $this->view->survey['event_typeid'],
            ));
            $question['ID'] = $result[0]['ID'];
            $question['questionid'] = $result[0]['questionid'];
        }
        elseif ( $question['exit_group_parent_id'] > 1 ) {
            $result = $this->_obj->getQuestionDetails(array(
                'display_page' => $question['exit_group_parent_id'],
                'event_typeid' => $this->view->survey['event_typeid'],
            ));
            $question['ID'] = $result[0]['ID'];
            $question['questionid'] = $result[0]['questionid'];
        }        
        
        echo json_encode(array(
            'error_code' => '0',
            'error_msg' => 'Success',
            'status' => ($question['group_last_ques'] == 1) ? 'next' : 'save',
            'answer_count' => $this->view->anscnt,
            'questions_count' => $this->view->anscnt + $questionsLeft['num'],
            'question_id' => $question['questionid'],
            'ID' => $question['ID'],
            'question_string' => $this->view->render('tae/process-response.phtml'),
        )); die;
    }

    /**
     * Method to return next action to be performed
     * @param type $question
     * @return type
     */
    private function getResponseActionToPerform( $question ) {
        $action = $this->checkResponseActionToPerform( $question, 
                $question['action_response_a'], $question['action_goto_a'] );
        
        if ( empty($action) ) {
            $action = $this->checkResponseActionToPerform($question, 
                    $question['action_response_b'], $question['action_goto_b']);
        }
        
        if ( empty($action) ) {
            $action = $this->checkResponseActionToPerform($question, 
                    $question['action_response_c'], $question['action_goto_c']);
        }
      
        if ( preg_match("/^ID\s[0-9]+$/", $action) ) {
            return array(
                'action' => 'show_next',
                'ID' => preg_replace("/^ID\s/", "", $action),
            );
        } 
        elseif ( preg_match("/^Delete [0-9]+(,[0-9]+)* ID\s[0-9]+$/", $action) ) {
            return array(
                'action' => 'delete_and_show_next',
                'ID' => preg_replace("/^Delete\s[0-9]+(,[0-9]+)*\sID\s/", "", $action),
                'delete_ids' => preg_replace("/\sID\s[0-9]+/", "", preg_replace("/^Delete\s/", "", $action)),
            );
        } 
        elseif ( $action == 'Submit Survey (did not qualify)' ) {
            return array(
                'action' => 'did not qualify',
            );
        } 
        elseif ( $action == 'Submit Survey (event is closed)' ) {
            return array(
                'action' => 'close',
            );
        }
    }

    /**
     * @param type $question
     * @param type $actionResponse
     * @param type $actionGoTo
     * @return string
     */
    private function checkResponseActionToPerform( $question, $actionResponse, $actionGoTo ) {
        $response = $this->_request->getParam('response');
        $action = '';
        if ( in_array( $actionResponse, array('ALL') ) && $response != '' ) {
            $action = $actionGoTo;
        } 
        elseif ( in_array( $actionResponse, array('N/A') ) && $response == 'N/A' ) {
            $action = $actionGoTo;
        }
        //If ID 10090 = Yes AND ID 10010 = No goto ID 10480 else,
        elseif( preg_match("/If ID [0-9]{1,}\s=[a-z A-Z 0-9]{1,} AND ID [0-9]{1,}\s={1,}/", $actionResponse) ) {
            $action = $actionGoTo;
            $actionResponseNew = preg_replace(array("/, goto ID (.)+/"), "", $actionResponse);
            $str = explode(" AND ", $actionResponseNew);
            $question_ids = array();
            $i = 0;
            foreach ( $str as $value ) {
                $ID = preg_replace(array("/If ID /","/ID /", "/ = (.)+/"), "", $value);
                $question_ids[$i] = $ID;
                $i++;
            }
            foreach ($question_ids as $key=>$ID) {
                $ans[$key] = $this->_answerobj->getQuestionIDBasedAnswers($this->_eventid, $ID);
            }
            if ( $ans[0]['response' . $ans[0]['response_options']] == 'Make' || 
                    $ans[0]['response' . $ans[0]['response_options']] == 'Model' ) {
                $match = 'answer';
            }
            else {
                $match = 'response';
            }
            $actionResponseNew = "If ID " . $question_ids[0] . " = " . $ans[0][$match . $ans[0]['response_options']] ;
            for($j=1;$j<$i;$j++) {
                if ( $ans[$j]['response' . $ans[$j]['response_options']] == 'Make' || 
                    $ans[$j]['response' . $ans[$j]['response_options']] == 'Model' ) {
                    $match = 'answer';
                }
                else {
                    $match = 'response';
                }
                $actionResponseNew .= " AND ID " . $question_ids[$j] . " = " . $ans[$j][$match . $ans[$j]['response_options']] ;
            }
            if (preg_match("/" . $actionResponseNew . ",*/", $actionResponse)) {
                $action = trim(preg_replace(array("/^.+goto /", "/else,$/"), "", $actionResponse));
            }
        }
        //If ID 8030 response in (3,4) goto ID 8440 else,
        elseif ( preg_match("/If ID [0-9]{1,} response in*/", $actionResponse) ) {
            $action = $actionGoTo;
            $ID = preg_replace(array("/If ID /", "/ response in (.)+/"), "", $actionResponse);
            preg_match_all('/\(([0-9,]+)\)+/i', $actionResponse, $responseVal);            
            $ans = $this->_answerobj->getQuestionIDBasedAnswers($this->_eventid, $ID);
            $arrResponseVal  = explode(",", $responseVal[1][0]);
            if ( !empty($arrResponseVal) 
                 && isset($ans['response_options']) 
                 && in_array($ans['response_options'],$arrResponseVal) ) {
                $action = trim(preg_replace(array("/^.+goto /", "/else,$/"), "", $actionResponse));
            }
        }
        //If ID 8030 = Yes, goto ID 8080 else,
        elseif ( preg_match("/If ID [0-9]{1,}\s=*/", $actionResponse) ) {
            $action = $actionGoTo;
            $ID = preg_replace(array("/If ID /", "/ = (.)+/"), "", $actionResponse);
            $ans = $this->_answerobj->getQuestionIDBasedAnswers($this->_eventid, $ID);
            if ( $ans['response' . $ans['response_options']] == 'Make' || 
                    $ans['response' . $ans['response_options']] == 'Model' ) {
                $match = 'answer';
            }
            else {
                $match = 'response';
            }
            if (preg_match("/If ID " . $ID . " = " . $ans[ $match . $ans['response_options']] . ", goto*/", $actionResponse)) {
                $action = trim(preg_replace(array("/^.+goto /", "/else,$/"), "", $actionResponse));
            }
        }        
        //checkbox_with_comments
//        elseif ( $actionResponse == 'checkbox_with_comments' ) {
//            $responseOptionsMapping = array();
//            $actions = explode(',', $actionGoTo);
//            foreach ( $actions as $value ) {
//                $temp = explode(':', $value);
//                $responseOptionsMapping[$temp[0]] = $temp[1];
//            }
//            $action = $responseOptionsMapping[$response];
//        }        
        elseif ( $question['input_type'] == 'radio' ) {
//            echo 'in' . $actionResponse . ' ' . $question['response' . $response];
            if ($actionResponse == $question['response' . $response]) {
                $action = $actionGoTo;
            }
            if ( empty($action) && in_array($actionResponse, array('Any')) ) {
                $action = $actionGoTo;
            }
        }        
        elseif ( $question['input_type'] == 'textarea' 
                 && in_array( $actionResponse, array('ALL') ) 
                 && empty($question["alert1"]) ) {
            $action = $actionGoTo;            
        }
        elseif ($question['input_type'] == 'text') {
            $action = $actionGoTo;

            if (!is_array($response)) {

                if (get_magic_quotes_gpc()) {
                    $response = stripslashes($response);
                }
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
                    for ($i = 1; $i <= 11; $i++) {
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
        } 
        elseif ($question['input_type'] == 'drop down') {
            $action = $actionGoTo;
            if ( get_magic_quotes_gpc( ) ) {
                $response = stripslashes($response);
            }
            
            $arresponse = explode(',', $response);
            if ( in_array( $actionResponse, array('Any')) ) {
                $action = '';
                foreach ( $arresponse as $resp ) {
                    if ( trim($resp) != '' ) {
                        $action = $actionGoTo;
                        break;
                    }
                }
            } 
            else {
                foreach ( $arresponse as $resp ) {
                    for ($i = 1; $i <= 11; $i++) {
                        if ( $resp == 'Other' 
                             && $actionResponse == 'Other' ) {
                            $action = $actionGoTo;
                            break;
                        }
                        elseif( str_replace("~","'",$resp) == $this->nootherbrandtext 
                                && $actionResponse == 'Nootherbrand' ) {
                            $action = $actionGoTo;
                            break;
                        }
                        else {
                            if ( $question['response' . $i] == $actionResponse ) {
                                $action = $actionGoTo;
                                break;
                            }
                            else {
                                $action = '';
                            }
                        }
                    }         
                }
            }
        }
        elseif ($question['input_type'] == 'calendar') {
            if ($response != '') {
                $action = $actionGoTo;
            } else {
                $action = "";
            }
        } elseif ($question['input_type'] == 'drop down text') {
            $action = $actionGoTo;

            if (get_magic_quotes_gpc()) {
                $response = stripslashes($response);
            }
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

            if (get_magic_quotes_gpc()) {
                $response = stripslashes($response);
            }
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
                        for ($i = 1; $i <= 11; $i++) {
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

    /**
     * Method to handle Submit events
     * @return type
     */
    public function submitAction() {
        if ( !$this->_eventid ) {
            $this->render('survey_not_exist');
            return;
        }
        
        $questionIDs = implode(',', array_keys($this->view->params['question']));

        if ( $questionIDs != '' ) {
            $this->_answerobj->deleteExtraAnswers( $this->_eventid, $questionIDs );
        }

        $arrEvent = array(
            'survey_date' => new Zend_Db_Expr('now()'),
            'ip_address' => $this->_helper->GetUserIP(),
            'browser_agent' => $_SERVER['HTTP_USER_AGENT'],
            'langid' => $this->_lang['langid']
        );
        
        switch ($this->view->params['task']) {
            case 'close' :
                $arrEvent['event_status'] = 'Closed';
                break;

           case 'did not qualify' :
                $arrEvent['event_status'] = 'Did not qualify';            
                break;          

            case 'open' :
                $arrEvent['event_status'] = 'Open';
                break;          

            default:
                break;
        }
        
        $this->_surveyobj->update($arrEvent, 'eventid = ' . $this->_eventid);
        
        if ( $arrEvent['event_status'] == 'Closed' ) {
            $test_alert_question_id = $this->_configValues['test_ride_alert_question_id'];
            $ans = $this->_answerobj->getAnswers('survey_event_answers',
                    $this->_eventid, $test_alert_question_id);
            if( is_array( $ans ) && !empty( $ans ) ) {
                if ( $ans['answer1'] == 'Yes' ) {
                    $emailObj = new APD_Email();
                    $emailObj->TestRideAlert($this->_eventid, 3, 'tre_ride_alert'); 
                }
            }
        }
        // Clear session	
        $this->_objSession->act = 
        $this->_objSession->statusClosed =
        $this->_objSession->eventtable = '';
        
        $this->_redirect('/survey/tae/thankyou/?langid='.$this->_lang['langid']);
        die;
    }

    /**
     * Method to handle Thanks page operations
     */
    public function thankyouAction() {
        $introtext = $this->_obj->getStatictext(
                $this->_configValues['survey_thank_you_text_tae'], 
                $this->_lang['langid']);
        $this->view->thankstext = $introtext[0]['question'];
    }    

    /**
     * Method to display thanks message for Opt-out
     */
    public function thankyouoptoutAction() {
        $introtext = $this->_obj->getStatictext(
                $this->_configValues['survey_optout_text'], 
                $this->_lang['langid']);
        $this->view->thankstext = $introtext[0]['question'];
    }
	
    /**
     * Method to set the static questions values
     */
    private function _setStaticQuestions() {
        $result = $this->_obj->getStaticQuestionstext(
                "'" . implode("','", array($this->_configValues['survey_proceed'], 
                    $this->_configValues['survey_progress'],
                    $this->_configValues['survey_pre_submit_text'],
                    $this->_configValues['survey_notselected_text'],
                    $this->_configValues['survey_ok_button'],
                    $this->_configValues['survey_error_text'],
                    $this->_configValues['survey_make_option_nootherbrand'],
                    $this->_configValues['survey_submit_button'])) . "'", 
                $this->_lang['langid'] );        
        foreach ( $result as $value ) {
            switch ( $value['questionid'] ) {
                case $this->_configValues['survey_proceed']:
                    $this->view->proceedbutton = $value['question'];
                    break;
                case $this->_configValues['survey_progress']:
                    $this->view->progressbar = $value['question'];
                    break;
                case $this->_configValues['survey_pre_submit_text']:
                    $this->view->survey_pre_submit_text = $value['question'];
                    break;
                case $this->_configValues['survey_notselected_text']:
                    $this->view->survey_notselected_text = $value['question'];
                    break;
                case $this->_configValues['survey_make_option_nootherbrand']:
                    $this->view->nootherbrandtext = $value['question'];
                    break;
                case $this->_configValues['survey_ok_button']:
                    $this->view->OKbutton = $value['question'];
                    break;
                case $this->_configValues['survey_error_text']:
                    $this->view->survey_error_text = $value['question'];
                    break;
                default:
                    break;
            }
        }
    }
}