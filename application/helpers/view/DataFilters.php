<?php

/**
 * Helper class to create required layouts
 * 
 * @author Harpreet Singh
 * @date   27 May, 2014
 * @version 1.0
 */
class Damco_View_Helper_DataFilters extends Zend_View_Helper_Abstract {

    private $_user;
    public $translate;

    /**
     * Constructor to initialize Layouts class
     */
    function __construct() {
        $this->translate = Zend_Registry::get('Zend_Translate');

        $this->_user = Zend_Auth::getInstance()->getIdentity();
    }

    /**
     * Method to return data filters for Grid View 
     * @param type $filters
     * @param type $selected
     * @return type
     */
    public function dataFilters($filters = array(), $selected = array()) {
        
        $retString = '<div id="filt-opts" class="clearfix white-bg">';
        $retString .= '<div class="row">';
        $request = $this->view->getRequestInfo();
        //for ESR PDF Dipa 9/25/14 1:30 PM
        $styleFloat = !empty($request->chkme) ? " style='float:left;'" : "" ;
        $buttonHidden = !empty($request->chkme) ? " style=' display: none !important;'" : "" ;
        //EOC for ESR PDF Dipa 9/25/14 1:30 PM
        
        
        //assessment Category start from here
        
         
                if($request->getActionName() == 'assessmentstatus')
                    $retString .= '<div class="col-lg-3">';
                else
                    $retString .= '<div class="col-lg-3">';
                
                $retString .= '<label>' . $this->translate->_('Choose assessment Category:') . '</label>';   
                $retString .= '<select id="assessmentCategoryID" class="selectmenu" name="assessment_category">';
                $assessmentCategoryObj = new assessment_Model_assessmentCategories();


                $result = $assessmentCategoryObj->getAllassessmentCategoriesName();
                
                

                $controller = $request->getControllerName();
                
                $module = $request->getModuleName();
                
                if(!isset($request->rpt_type)||(isset($request->rpt_type)&&$request->rpt_type!='rawdata')){

                    //if(($controller != 'questionanalysis' && $controller != 'avgbyquestion'))
					if(($controller != 'avgbyquestion'))
                    {
                     $retString .= '<option value="0">-- ' . $this->translate->_('All Event Types') .
                        ' --</option>';
                    }
                }
                $i = 0;
                foreach ($result as $value) {
                    $sel = (isset($selected['assessment_category']) && $selected['assessment_category'] == $value['assessment_category_id']) ? ' selected="selected"' : '';
                  /*  if($i==0)
                    {
                        if(isset($selected['assessment_category']) && $selected['assessment_category'] == '1')
                        {
                            $selected['assessment_category'] = $value['assessment_category_id'];
                        }
                    } */
                    $retString .= '<option value="' . $value['assessment_category_id'] . '" '.$sel.'>'. $this->translate->_($value['assessment_category_name']) . '</option>';
                    $i++;
                }
                $retString .= '</select>';
                $retString .= '</div>';
                
        //assessment Category end here
        
               
        
        
        if (in_array('event_type', $filters)) { 
            if($request->getActionName() == 'consolidate')
                $retString .= '<div class="col-lg-3">';
            else if($request->getActionName() == 'assessmentstatus')
                $retString .= '<div class="col-lg-3">';
            else
                $retString .= '<div class="col-lg-3">';
            
            
            $retString .= '<label>' . $this->translate->_('Choose assessment:') . '</label>';   
            $retString .= '<select id="EventTypeID" class="selectmenu" name="event_type">';
            $eventObj = new Survey_Model_EventTypes();
            
            $result = $eventObj->getEventTypesByCategory($selected['assessment_category']);
    
            $controller = $request->getControllerName();
            $module = $request->getModuleName();
            if(!isset($request->rpt_type)||(isset($request->rpt_type)&&$request->rpt_type!='rawdata'))
            {
                if(($controller != 'questionanalysis' && $controller != 'avgbyquestion'))
                {
                 $retString .= '<option value="0">-- ' . $this->translate->_('All Event Types') .
                    ' --</option>';
                }
            }
            
//            
//            var_dump($result[0]);die;
            $i = 0;
            if(!empty($result))
            {
                foreach ($result as $value) {
                    if ((( $this->_user->role_id == '4' 
                             && $controller != 'codered' 
                             /* && $controller == 'avgbyquestion'
                             && $controller != 'unsatisfactory'*/)
                           || $controller == 'performancealert' 
                           || $controller == 'dashboard' 
                           || $controller == 'esr' 
                           || $controller == 'mdr' 
                           || $controller == 'ranking' 
                           ||($this->_user->role_id == '4'
                               && ($controller == 'avgbyquestion' 
                                    || $controller == 'allquestion' ) ) //as per Amit 9/10/14 8:44 PM
                            )
                           && $value['event_type'] == 'Product' 
                           ) {
                        continue;
                    }

                    $sel = (isset($selected['event_type']) && $selected['event_type'] == $value['event_typeid']) ? ' selected="selected"' : '';
                    if($i==0)
                    {
                        if(isset($selected['event_type']) && $selected['event_type'] == '1')
                        {
                            $selected['event_type'] = $value['event_typeid'];
                        }
                    }

                    $i++;



                    $retString .= '<option value="' . $value['event_typeid'] . '" ' . $sel . '>'
                            . $this->translate->_($value['event_type']) . '</option>';
                }
            }
			else if(empty($selected['assessment_category'])) {
				
				$retString .= '<option value="">-- All assessments --</option>';
			}
            else
            {
                $retString .= '<option value="">No Records Found</option>';
            }
            $retString .= '</select>';
            $retString .= '</div>';
        }
        //BOC added by sachin for extra dropdown of assessment
        if($request->getActionName() != 'assessmentstatus')
        {
            if (in_array('assessment', $filters)) { 
                if($request->getActionName() != 'consolidate')
                    $retString .= '<div class="col-lg-3">';
                else
                    $retString .= '<div class="col-lg-6">';
                $retString .= '<label>' . $this->translate->_('Choose assessment Instance:') . '</label>';   
                $retString .= '<select id="assessmentID" class="selectmenu" name="assessment_id">';
                $eventObj = new assessment_Model_assessment();


                $result = $eventObj->getAllByEventType($selected['event_type']);

                $controller = $request->getControllerName();
                $module = $request->getModuleName();
                if(!isset($request->rpt_type)||(isset($request->rpt_type)&&$request->rpt_type!='rawdata')){

                    if(($controller != 'questionanalysis' && $controller != 'avgbyquestion'))
                    {
                     $retString .= '<option value="0">-- ' . $this->translate->_('All Event Types') .
                        ' --</option>';
                    }
                }
                if(!empty($result))
                {
                    foreach ($result as $value) 
                    {
                        $sel = ( isset($selected['assessment']) && $selected['assessment'] == $value['assessment_id'] ) ? ' selected="selected"' : '';
                        $retString .= '<option value="' . $value['assessment_id'] . '" ' . $sel . '>'
                            . $this->translate->_($value['assessment_name']) . '</option>';
                    }
                }
                else 
                {
                    $retString .= '<option value="">No Records Found</option>';
                }
                
                $retString .= '</select>';
                $retString .= '</div>';
            }
        }
        //EOC sachin 1/4/16 12:32 PM

        //BOC added by dipa for Question analysis report 9/30/14 4:20 PM
        if($request->getActionName() != 'consolidate' && $request->getActionName() != 'assessmentstatus')
        {
            if (in_array('question', $filters)) {

                $retString .= '<div class="col-lg-3">';
                $retString .= '<label>' . $this->translate->_('Select Question') . ':</label>';
                $retString .= '<select id="questionid"  class="selectmenu" name="questionid">';
                
                

                $questionObj = new assessment_Model_Questions();
                $objCconfig = new Survey_Model_Config();
                $arrConfigVariables = array('Question_analysis_questionid');
                $config_queid = $objCconfig->getConfigQueIds($arrConfigVariables);    
    //            var_dump(unserialize($config_queid['Question_analysis_questionid']));die;
                $serArray = unserialize($config_queid['Question_analysis_questionid']);
    //            var_dump($selected);die;
                /* switch($selected['event_type'])
                {
                    case "1":                    
                        $allQuestions = $serArray['sales']['questionId'];
                        $where = array("event_typeid" => 1, 
                                "langid" => !empty($this->_user->lang_id) ? $this->_user->lang_id : 1, 
                                'question_type' =>array('Q'));
                        break;
                    case "2": 

                        $allQuestions = $serArray['product']['questionId'];
                        $where = array("event_typeid" => 2, 
                                "langid" => !empty($this->_user->lang_id) ? $this->_user->lang_id : 1, 
                                'question_type' =>array('Q'));
                        break;
                    case "3": 
                        $allQuestions = $serArray['service']['questionId'];
                        $where = array("event_typeid" => 3, 
                                "langid" => !empty($this->_user->lang_id) ? $this->_user->lang_id : 1, 
                                'question_type' =>array('Q'));
                        break; 
                    default:

                        $allQuestions =  array_merge($serArray['sales']['questionId'],
                                                    $serArray['product']['questionId'],
                                                    $serArray['service']['questionId']);
                        $where = array("seq.questionid" => $allQuestions, 
                                        "langid" => !empty($this->_user->lang_id) ? $this->_user->lang_id : 1, 
                                        'question_type' =>array('Q'));
                        break;
                } 1/4/16 12:02 PM sachin */

                $where = array("event_typeid" => $selected['event_type'], 
                                "langid" => !empty($this->_user->lang_id) ? $this->_user->lang_id : 1, 
                                'question_type' =>array('Q'));
                $where["input_type"] = array('checkbox','drop down','radio');
                //var_dump($where);die;
                $result = $questionObj->getQuestionDetails($where,"multi");
                //print_r($result);die;
               /* $retString .= '<option value="">-- ' . $this->translate->_('Select Questions')
                        . ' --</option>'; */       
                if(!empty($result["Qmulti"]))
                {
                    $retString .= '<option value="All" title="All">All</option>';
                    foreach ($result["Qmulti"] as $arrQ) 
                    {  
                        $showQus = strlen($arrQ["question"]) > 100 ?
                                substr($arrQ["question"],0,100).'...':
                                $arrQ["question"];
                        $sel = ( isset($selected['questionid']) && $selected['questionid'] == $arrQ["questionid"] ) ? ' selected="selected"' : '';
                        $retString .= '<option value="' . $arrQ["questionid"] . '" title="'.$arrQ["question_number"].". ".$arrQ["question"].'" ' . $sel . '>'
                                .  $showQus . '</option>';
                    }
                }
                else 
                {
                    $retString .= '<option value="">No Records Found</option>';
                }
                

                $retString .= '</select>';
                $retString .= '</div>';
            }
        }    
        //EOC added by dipa for Question analysis report 9/30/14 4:20 PM
        if (in_array('model', $filters)) {
            $retString .= '<div class="col-lg-3">';
            $retString .= '<label>' . $this->translate->_('Select Model:') . '</label>';
            $retString .= '<select id="model" class="selectmenu" name="model">';

            $eventObj = new Event_Model_Events();
            $result = $eventObj->getModels();
//            print_r($result);die;
            $retString .= '<option value="">-- ' . $this->translate->_('All Models')
                    . ' --</option>';
            foreach ($result as $value) {
                if (!empty($value['model'])) {
                    $sel = ( isset($selected['model']) && $selected['model'] == $value['model'] ) ? ' selected="selected"' : '';
                    $retString .= '<option value="' . $value['model'] . '" ' . $sel . '>'
                            . $value['model'] . '</option>';
                }
            }

            $retString .= '</select>';
            $retString .= '</div>';
        }
        //Created by Amit Kumar on 04-Sep-2014
        //Alert Type Dropdown
        if (in_array('alert_type', $filters)) {
        	$retString .= '<div class="col-lg-3">';
        	$retString .= '<label>' . $this->translate->_('Select Alert Type:') . '</label>';
        	$retString .= '<select id="alert_type" class="selectmenu" name="alert_type">';
        
        	$alertlogObj = new Report_Model_Alertlog();
        	$result = $alertlogObj->getAlertTypes();
        	//print_r($result);die;
        	$retString .= '<option value="">-- ' . $this->translate->_('All Alert Types') 	. ' --</option>';
        	foreach ($result as $value) {
        		//echo $selected['alert_type'] .'=='. $value['email_token'].'<br>';
        		if (!empty($value['email_token'])) {
        			$sel = ( isset($selected['alert_type']) && $selected['alert_type'] == $value['email_token'] ) ? ' selected="selected"' : '';
        			$retString .= '<option value="' . $value['email_token'] . '" ' . $sel . '>'
        					. $this->translate->_( $value['title'] ) . '</option>';
        		}
        	}
        
        	$retString .= '</select>';
        	$retString .= '</div>';
        }
        
        //Created by Amit Kumar on 30-Sep-2014
        //User Roles Dropdown
        if (in_array('user_role', $filters)) {
            $retString .= '</div><div class="row">';
            $retString .= '<div class="col-lg-3">';
            $retString .= '<label>' . $this->translate->_('User Roles:') . '</label>';
            $retString .= '<select id="user_role" class="selectmenu" name="user_role">';
        
            $userActivityObj = new Report_Model_UserActivity();
            $result = $userActivityObj->getUserRoles();
           
            $retString .= '<option value="">-- ' . $this->translate->_('All User Roles') 	. ' --</option>';
            foreach ($result as $value) {
                if (!empty($value['id'])) {
                    $sel = ( isset($selected['user_role']) && $selected['user_role'] == $value['id'] ) ? ' selected="selected"' : '';
                    $retString .= '<option value="' . $value['id'] . '" ' . $sel . '>'
                        . $value['label'] . '</option>';
                }
            }
        
            $retString .= '</select>';
            $retString .= '</div>';
        }
        
        if (in_array('branchlist', $filters)) {
            
            $accHierarchy = new Damco_Core_AccessHierarchy();
            $result = $accHierarchy->get(
                (isset($selected['branchlist']) ? $selected['branchlist'] : ''), (isset($selected['market']) ? $selected['market'] : ''), (isset($selected['sales_region']) ? $selected['sales_region'] : ''), (isset($selected['dealerlist']) ? $selected['dealerlist'] : '')
            );
            //dipa for corporate disable all opt 11/4/14 4:40 PM 
            $disableDealerClass =  $disableDealerAttrib = $disableBranchClass = $disableBranchAttrib = "";
            //print_R($selected);
            if($selected["user_role"] == 0)
            {
                $disableDealerClass =  $disableDealerAttrib = $disableBranchClass = $disableBranchAttrib = "";
            }
            else
            {
                if($selected["user_role"] < 4)
                {
                    if($selected["user_role"] < 2) //for corporate disable all opt
                    {
                       $disableDealerClass = $disableBranchClass = " form-control";
                       $disableDealerAttrib = $disableBranchAttrib = " disabled=true";
                    }
                    else //for branch & ASM disable dealer only
                    {
                        $disableDealerClass =  " form-control";
                        $disableDealerAttrib = " disabled=true";
                    }

                }
            }
            //dipa for corporate disable all opt 11/4/14 4:40 PM
            if ($this->_user->role_id <= '2') {
                $retString .= '<div class="col-lg-3">';
                $retString .= '<label>' . $this->translate->_('Select Branch:')
                . '</label>';
                $retString .= '<select id="branchlist" class="selectmenu '.$disableBranchClass.'" '
                    . 'name="branchlist" '.$disableBranchAttrib.'>';
                $retString .= '<option value="">-- ' . $this->translate->_('All Branches')
                . ' --</option>';
                
                foreach ($result['branches'] as $value) {
                    $sel = ( isset($selected['branchlist']) && $selected['branchlist'] == $value['id'] ) ? ' selected="selected"' : '';
                    $retString .= '<option value="' . $value['id'] . '" ' . $sel . '>'
                        . $value['name'] . '</option>';
                }

                $retString .= '</select>';
                $retString .= '</div>';
            }
            
            if (in_array('dealerlist', $filters)) {
                if ($this->_user->role_id < '4') {
                    $retString .= '<div class="col-lg-3">';
                    $retString .= '<label>' . $this->translate->_('Select Dealer')
                    . '</label>';
                    $retString .= '<select id="dealerlist" class="selectmenu '.$disableDealerClass.'" name="dealerlist"'.$disableDealerAttrib.'>';

                    $retString .= '<option value="">-- ' . $this->translate->_('All Dealers')
                    . ' --</option>';
                    foreach ($result['dealers'] as $value) {
                        $sel = ( isset($selected['dealerlist']) && $selected['dealerlist'] == $value['id'] ) ? ' selected="selected"' : '';
                        $retString .= '<option value="' . $value['id'] . '" ' . $sel . '>'
                            . $value['name'] . '</option>';
                    }
                    $retString .= '</select></div>';
                }
            }
        }
        
        //Nation DropDown     
          if (in_array('nation_type', $filters)&& $this->_user->role_id != '4') {
            
              $retString .= '<div class="col-lg-3">';
              $retString .= '<label>'.$this->translate->_('Select Nation:')
                       .'</label>';
              $retString .= '<select id="nation" class="selectmenu" name="nation">';
              $nationObj = new Event_Model_CompanyStructure();
              if($this->_user->role_id=='2' ||$this->_user->role_id=='3' )
              {
                  $result = $nationObj->getnationlist($this->_user->branch_id);
              }
              else
                  $result = $nationObj->getnationlist();           
//           $retString .= '<option value="">-- '.$this->translate->_('All Nations')
//                    .' --</option>';
             foreach ($result as $value)
             {
                $sel = ( isset($selected['nation'])
                       && $selected['nation'] == $value['nationid'] )
                       ? ' selected="selected"' : '';
                $retString .= '<option value="'.$value['nationid'].'" '.$sel.'>'
                               .$this->translate->_($value['nation_name']).'</option>';
            }
            $retString .= '</select>';
            $retString .= '</div>';
        }
        if (in_array('dealer', $filters)) {
            $retString .= '<div class="col-lg-3">';
            $retString .= '<label>' . $this->translate->_('Select Dealer') . '</label>';
            $retString .= '<select id="DealerID" class="selectmenu" name="dealer">';

            $dealerObj = new Dealer_Model_Dealers();
            $result = $dealerObj->getAll(array('id', 'dealer_name'));

            $retString .= '<option value="0">-- ' . $this->translate->_('All Dealers')
                    . ' --</option>';
            foreach ($result as $value) {
                $sel = ( isset($selected['dealer']) && $selected['dealer'] == $value['id'] ) ? ' selected="selected"' : '';
                $retString .= '<option value="' . $value['id'] . '" ' . $sel . '>'
                        . $value['dealer_name'] . '</option>';
            }


            $retString .= '</select>';
            $retString .= '</div>';
        }

        if (in_array('event_status', $filters) && $this->_user->role_id != '4') {
            $retString .= '<div class="col-lg-3">';
            $retString .= '<label>' . $this->translate->_('Select Event Status:')
                    . '</label>';
            $retString .= '<select id="event_status" class="selectmenu" name="event_status">';

            $eventStatusObj = new Event_Model_EventStatus();

            $result = $eventStatusObj->fetchAll( 'visible = "1"' )->toArray();
            
            $retString .= '<option value="">-- '.$this->translate->_('All Statuses')
                    .' --</option>';

            foreach ($result as $value) {
                $sel = ( isset($selected['event_status']) && $selected['event_status'] == $value['status'] ) ? ' selected="selected"' : '';
                $retString .= '<option value="' . $value['status'] . '" ' . $sel . '>'
                        . $this->translate->_($value['label']) . '</option>';
            }


            $retString .= '</select>';
            $retString .= '</div>';
        }

        if (in_array('search_keyword', $filters)) {
            $retString .= '<div class="col-lg-3">';
            $retString .= '<label>' . $this->translate->_('Search Keyword:')
                    . '</label>';
            $retString .= '<input id="search_keyword" class="form-control"'
                    . ' name="search_keyword" type="text"'
                    . ' value="' . (( isset($selected['search_keyword'])) ? $this->view->escape($selected['search_keyword']) : '') . '" />';
            $retString .= '</div>';
        }

        if (in_array('codered_status', $filters)) {
            $retString .= '<div class="col-lg-3">';
            $retString .= '<label>' . $this->translate->_('Select Status:')
                    . '</label>';
            $retString .= '<select id="codered_status" class="selectmenu" name="codered_status">';

            $result = array(
                'Open' => $this->translate->_('Open'),
                'Closed' => $this->translate->_('Closed'),
                'Reopened' => $this->translate->_('Reopened')
            );
            if (Zend_Controller_Front::getInstance()->getRequest()->getControllerName() == 'codered'
                    && Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'index') {
                $result['allOpen'] = $this->translate->_('All Open');
            }
            $retString .= '<option value="">-- ' . $this->translate->_('All Statuses')
                    . ' --</option>';
            foreach ($result as $k=>$value) {
                $sel = ( isset($selected['codered_status']) && $selected['codered_status'] == $k ) ? ' selected="selected"' : '';
                $retString .= '<option value="' . $k . '" ' . $sel . '>'
                        . $value . '</option>';
            }
            $retString .= '</select></div>';
        }

        if (in_array('show_hierarchy', $filters)) {
            $accHierarchy = new Damco_Core_AccessHierarchy();
            $result = $accHierarchy->get(
                    (isset($selected['branch']) ? $selected['branch'] : ''), (isset($selected['market']) ? $selected['market'] : ''), (isset($selected['sales_region']) ? $selected['sales_region'] : ''), (isset($selected['dealer']) ? $selected['dealer'] : '')
            );
            $retString .= '</div><div class="row">';
            if ($this->_user->role_id <= '2') {
                $retString .= '<div class="col-lg-3">';
                $retString .= '<label>' . $this->translate->_('Select Branch:')
                        . '</label>';
                $retString .= '<select id="branch" class="selectmenu" '
                        . 'name="branch">';
                $retString .= '<option value="">-- ' . $this->translate->_('All Branches')
                        . ' --</option>';

                foreach ($result['branches'] as $value) {
                    $sel = ( isset($selected['branch']) && $selected['branch'] == $value['id'] ) ? ' selected="selected"' : '';
                    $retString .= '<option value="' . $value['id'] . '" ' . $sel . '>'
                            . $value['name'] . '</option>';
                }

                $retString .= '</select>';
                $retString .= '</div>';
            }

            if ($this->_user->role_id <= '2') {
                $retString .= '<div class="col-lg-3">';
                $retString .= '<label>' . $this->translate->_('Select Country:')
                        . '</label>';
                $retString .= '<select id="market" class="selectmenu" '
                        . 'name="market">';
                $retString .= '<option value="">-- ' . $this->translate->_('All Countries')
                        . ' --</option>';

                foreach ($result['markets'] as $value) {
                    $sel = ( isset($selected['market']) && $selected['market'] == $value['id'] ) ? ' selected="selected"' : '';
                    $retString .= '<option value="' . $value['id'] . '" ' . $sel . '>'
                            . $value['name'] . '</option>';
                }

                $retString .= '</select>';
                $retString .= '</div>';
            }

            /* if ( $this->_user->role_id <= '2' ) {
              $retString .= '<div class="col-lg-3">';
              $retString .= '<label>'.$this->translate->
              _('Select Area Sales Manager:').'</label>';
              $retString .= '<select id="asm" class="form-control" '
              .  'name="asm">';
              $retString .= '<option value="">-- All ASMs --</option>';

              foreach ( $result['asm'] as $value ) {
              $sel = ( isset($selected['asm'])
              && $selected['asm'] == $value['id'] )
              ? ' selected="selected"' : '';
              $retString .= '<option value="'.$value['id'].'" '.$sel.'>'
              .$value['name'].'</option>';
              }

              $retString .= '</select>';
              $retString .= '</div>';
              } */

            if ($this->_user->role_id <= '3') {
                $retString .= '<div class="col-lg-3">';
                $retString .= '<label>' . $this->translate->
                                _('Select Area Sales Manager:') . '</label>';
                $retString .= '<select id="sales_region" class="selectmenu" '
                        . 'name="sales_region">';
                $retString .= '<option value="">-- ' . $this->translate->_('All Sales Regions')
                        . ' --</option>';

                foreach ($result['sales_regions'] as $value) {
                    $sel = ( isset($selected['sales_region']) && $selected['sales_region'] == $value['id'] ) ? ' selected="selected"' : '';
                    $retString .= '<option value="' . $value['id'] . '" ' . $sel . '>'
                            . $value['name'] . '</option>';
                }

                $retString .= '</select>';
                $retString .= '</div>';
            }

            if ($this->_user->role_id < '4') {
                $retString .= '<div class="col-lg-3">';
                $retString .= '<label>' . $this->translate->_('Select Dealer')
                        . '</label>';
                $retString .= '<select id="dealer" class="selectmenu" name="dealer">';

                $retString .= '<option value="">-- ' . $this->translate->_('All Dealers')
                        . ' --</option>';
                foreach ($result['dealers'] as $value) {

					if ( empty($value['name']) ) {
						continue;
					}

                    $sel = ( isset($selected['dealer']) && $selected['dealer'] == $value['id'] ) ? ' selected="selected"' : '';
                    $retString .= '<option value="' . $value['id'] . '" ' . $sel . '>'
                            . $value['name'] . '</option>';
                }
                $retString .= '</select></div>';
            }
            $retString .= '</div><div class="row">';
        }

        $displayPeriod = FALSE;
        if (in_array('date_range', $filters)) {
            $retString .= '</div><div class="row"><div class="col-lg-3">';
            $retString .= '<label>' . $this->translate->_('Apply Date Range to:')
                    . '</label>';
            $retString .= '<select id="date_range_field" class="selectmenu" '
                    . 'name="date_range_field">';

            $result = array(
//                'import_date' => 'Import Date',
                'assessment_submission_date' => $this->translate->_('Response Date'),
                'event_date' => $this->translate->_('Event Date'),
            );
            
            if ( isset($selected['date_range']) 
                 && $selected['date_range'] == 'email_send_date' ) {
                $result['email_send_date'] = $this->translate->_('Email Send Date');
            }

            foreach ($result as $key => $value) {
                $sel = ( isset($selected['date_range']) && $selected['date_range'] == $key ) ? ' selected="selected"' : '';
                $retString .= '<option value="' . $key . '" ' . $sel . '>'
                        . $value . '</option>';
            }

            $retString .= '</select>';
            $retString .= '</div>';
            if($request->getControllerName() != 'consolidate')
                $displayPeriod = FALSE;
        }
        
            if ( $request->getActionName() != 'assessmentstatus' ) {
                if (in_array('period', $filters) || $displayPeriod) {
                $retString .= '<div class="col-lg-6 show-report"'.$styleFloat.'><ul class="list-inline">';
                $retString .= '<li><strong>' . $this->translate->_('Select Period:')
                        . '</strong></li>';

                if ( $request->getModuleName() == 'customer'
                     && $request->getControllerName() == 'index') {
                    $retString .= '<li><label>
                                <input type="radio" value="all"' .
                         ((isset($selected['period']['period']) && $selected['period']['period'] == 'all') ? ' checked="checked" ' : '')
                         . ' name="period" id=""> '
                         . $this->translate->_('All')
                         . '</label></li>';
                }

                if ( $request->getModuleName() == 'report'
                     && $request->getControllerName() == 'dashboard'
                     && $request->getActionName() == 'index' ) {
    //                $retString .= '<li><label>
    //                            <input type="radio" value="current_rolling_12_months"' .
    //                     ((isset($selected['period']['period']) && $selected['period']['period'] == 'current_rolling_12_months') ? ' checked="checked" ' : '')
    //                     . ' name="period" id=""> '
    //                     . $this->translate->_('Rolling 12 Months')
    //                     . '</label></li>';
                }

    //            $retString .= '<li><label>
    //                           <input type="radio" value="rolling_12_months" ' .
    //                    ((isset($selected['period']['period']) && $selected['period']['period'] == 'rolling_12_months') ? ' checked="checked" ' : '')
    //                    . ' name="period" id=""> '
    //                    . ( ( $request->getControllerName() == 'dashboard'
    //                          || $request->getControllerName() == 'ranking' 
    //                          || $request->getControllerName() == 'esr' //dipa 9/10/14 8:06 PM
    //                          || $request->getControllerName() == 'questionanalysis' //dipa 10/1/14 10:35 AM
    //                        )
    //                        ? $this->translate->_('Last 12 Months')
    //                        : $this->translate->_('Rolling 12 Months'))
    //                    . '</label></li>';
    //            $retString .= '<li><label>
    //                           <input type="radio" value="by_month"' .
    //                    ((isset($selected['period']['period']) && $selected['period']['period'] == 'by_month') ? ' checked="checked" ' : '')
    //                    . ' name="period" id=""> '
    //                    . $this->translate->_('By Month')
    //                    . '</label></li>';

                if ( $request->getActionName() != 'assessmentstatus' ) {

                    $retString .= '<li><label>
                                <input type="radio" value="by_period"' .
                         ((isset($selected['period']['period']) && $selected['period']['period'] == 'by_period') ? ' checked="checked" ' : '')
                         . ' name="period" id=""> '
                         . $this->translate->_('By Period')
                         . '</label></li>';
                }

                $retString .= '</ul>';

                $months = array('01' => $this->translate->_('Jan'),
                    '02' => $this->translate->_('Feb'),
                    '03' => $this->translate->_('Mar'),
                    '04' => $this->translate->_('Apr'),
                    '05' => $this->translate->_('May'),
                    '06' => $this->translate->_('Jun'),
                    '07' => $this->translate->_('Jul'),
                    '08' => $this->translate->_('Aug'),
                    '09' => $this->translate->_('Sep'),
                    '10' => $this->translate->_('Oct'),
                    '11' => $this->translate->_('Nov'),
                    '12' => $this->translate->_('Dec'));

                $retString .= '<div id="optdiv" class="clearfix"><div id="fromperiod" class="col-lg-6 clearfix"'.$styleFloat.'><ul class="list-inline by_period_selection">';
                $retString .= '<li><strong>' . $this->translate->_('From:')
                        . '</strong></li>';
                $fromDate = !empty($selected['period']['fromDate']) ? date('d/m/Y', strtotime($selected['period']['fromDate'])) : '';
                $toDate = !empty($selected['period']['toDate']) ? date('d/m/Y', strtotime($selected['period']['toDate'])) : '';
                $retString .= '<li><input type="text" style="cursor:pointer" class="form-control" name="fromDate" readonly="true" value="' . $fromDate . '"'
                    . 'id="fromPeriod"></li></ul></div>';
                $retString .= '<div id="toperiod" class="col-lg-6"'.$styleFloat.'><ul class="list-inline by_period_selection"><li style="margin-right:15px;"><strong>' . $this->translate->_('To:')
                        . '</strong></li>';
                $retString .= '<li><input type="text"  style="cursor:pointer" class="form-control" name="toDate" readonly="true" value="' . $toDate
                    . '" id="toPeriod"></li></ul></div></div>';

                $retString .= '<ul class="list-inline by_month_selection">';
                $retString .= '<li><strong>' . $this->translate->_('Month:')
                        . '</strong></li>';
                $retString .= '<li><select name="month" id="month" class="selectmenu">';
                foreach ($months as $key => $value) {
                    $selected['period']['month'] = !empty($selected['period']['month']) ? $selected['period']['month'] : date('m');
                    $sel = ( $selected['period']['month'] == $key ) ? ' selected="selected"' : '';
                    $retString .= '<option value="' . $key . '" ' . $sel . '>'
                            . $value . '</option>';
                }
                $retString .= '</select></li>';
                $retString .= '<li><select name="year" id="year" class="selectmenu">'
                        . '</select></li>';
                $retString .= '</ul></div>';
                $retString .= '<input type="hidden" name="sel_year" value="' .
                        (!empty($selected['period']['year']) ? $selected['period']['year'] : date('Y') ) . '" />';
            }
        }   
        
        if (in_array('month', $filters)) {
            $retString .= '<div class="col-lg-6 show-report"'.$styleFloat.'><ul class="list-inline">';
            $months = array('01' => $this->translate->_('Jan'),
                '02' => $this->translate->_('Feb'),
                '03' => $this->translate->_('Mar'),
                '04' => $this->translate->_('Apr'),
                '05' => $this->translate->_('May'),
                '06' => $this->translate->_('Jun'),
                '07' => $this->translate->_('Jul'),
                '08' => $this->translate->_('Aug'),
                '09' => $this->translate->_('Sep'),
                '10' => $this->translate->_('Oct'),
                '11' => $this->translate->_('Nov'),
                '12' => $this->translate->_('Dec'));

            $retString .= '<ul class="list-inline month_selection">';
            $retString .= '<li><strong>' . $this->translate->_('Month:')
                    . '</strong></li>';
            $retString .= '<li><select name="month" id="month" class="selectmenu">';
            foreach ($months as $key => $value) {
                $selected['month'] = !empty($selected['month']) ? $selected['month'] : date('m');
                $sel = ( $selected['month'] == $key ) ? ' selected="selected"' : '';
                $retString .= '<option value="' . $key . '" ' . $sel . '>'
                        . $value . '</option>';
            }
            $retString .= '</select></li>';
            $retString .= '<li><select name="year" id="year" class="selectmenu">'
                    . '</select></li>';
            $retString .= '</ul></div>';
            $retString .= '<input type="hidden" name="sel_year" value="' .
                    (!empty($selected['year']) ? $selected['year'] : date('Y') ) . '" />';
        }

        if (in_array('search_key', $filters)) {
            $retString .= '<div class="col-sm-3 col-xs-6">';
            $retString .= '<label>' . $this->translate->_('Search Keyword:')
                    . '</label>';
            $retString .= '<input size="10" id="search_key" class="form-control"'
                    . ' name="search_key" type="text"'
                    . ' value="' . (( isset($selected['search_key'])) ? $selected['search_key'] : '') . '" />';
            $retString .= '</div>';
        }
        
        
        if (in_array('report_view', $filters)) {
            $retString .= '</div><div class="row"><div class="col-lg-6"'.$styleFloat.'><ul class="list-inline">';
            $retString .= '<li><strong>' . $this->translate->_('Report View')
            . '</strong></li>';
        
            
               $retString .= '<li><label>
                            <input type="radio" value="user"' .
                                    ((isset($selected['report_view']) && $selected['report_view']== 'user') ? ' checked="checked" ' : '')
                                    . ' name="report_view" id=""> '
                                        . $this->translate->_('User Activity')
                                        . '</label></li>';

        
               $retString .= '<li><label>
                           <input type="radio" value="report" ' .
                                   ((isset($selected['report_view']) && $selected['report_view'] == 'report') ? ' checked="checked" ' : '')
                                   . ' name="report_view" id=""> '
                                       .$this->translate->_('Report Access')
                                           . '</label></li>';

               $retString .= '</ul>';
               $retString .= '</div>';
        }
        
        $buttonName = $this->translate->_('Apply Filters'); // change filter text
        $resourceString = Zend_Controller_Front::getInstance()->getRequest()->getModuleName() . '-' . Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        if ($resourceString == 'performance-target') {
            $buttonName = $this->translate->_('Select');
        }
        //BOC added By dipa 8/13/14 12:35 PM
        $redirectArr = array(
                    'module' => $request->getModuleName(),
                    'controller' => $request->getControllerName(),
                    'action' => $request->getActionName(),
                    'period' => "rolling_12_months"
                        );
        
        
        if ( $request->getModuleName() == 'event'
             && $request->getControllerName() == 'index' )
        {
           $redirectArr["date_range_field"] =  "assessment_submission_date";
        }
        //EOC by Dipa 8/13/14 12:35 PM
        
        //BOC by Anuj  8/27/14 
        if ( $request->getModuleName() == 'report'
             && $request->getControllerName() == 'avgbyquestion' )
        {
           $redirectArr["event_type"] =  "1";
        }
        //EOC by Anuj  8/27/14
        
         //BOC by Anuj  09/04/14 
       if ( $request->getModuleName() == 'event'
             && $request->getControllerName() == 'codered'
             && isset($request->rpt_type) 
             && $request->rpt_type=='reopen')
        {
           $redirectArr["rpt_type"] =  "reopen";
        }

        if ( $request->getModuleName() == 'customer'
                 && $request->getControllerName() == 'index') {
           $redirectArr["period"] =  "all";
        }

        if ( $request->getModuleName() == 'event'
             && $request->getControllerName() == 'index'
             && isset($request->rpt_type)
             && $request->rpt_type=='rawdata') {
           $redirectArr["rpt_type"] =  "rawdata";
           $redirectArr["event_type"] =  "1";
        }
        //EOC by Anuj  09/25/14 
        $redirectArr = array(
                    'module' => $request->getModuleName(),
                    'controller' => $request->getControllerName(),
                    'action' => $request->getActionName(),
                        );
        
        if($request->getActionName() == 'consolidate') {
            $excel_export = $consolidate_export = '';
            if ( $this->view->hasAccess( $this->_user->role_name, 'report', 'questionanalysis', 'exportexcelquestion' ) ) {
                $excel_export = '<span class="button sbtn pull-right"><span><button type="button" 
                        id="df_exporttoexcel" onclick="exportTOExcelConsolidated();" class="btn btn-primary btn-lg">
                        ' . $this->translate->_('Export To Excel') . '</button></span></span>';
            }
            if ( $this->view->hasAccess( $this->_user->role_name, 'report', 'questionanalysis', 'exportexcelconsolidate' ) ) {
                $consolidate_export = '<span class="button sbtn pull-right"><span><button type="button" 
                        id="df_exporttoexcel" onclick="exportTOConsolidateReport();" class="btn btn-primary btn-lg">
                        ' . $this->translate->_('Consolidate Excel') . '</button></span></span>';
            }
            
            $retString .= '<div class="col-sm-6 col-xs-6 btn-search" '.$buttonHidden.'>
                                <span class="button sbtn pull-right"><span>
                                    <button type="button" id="consolidate_submit" class="btn btn-primary btn-lg" onclick="displayInGrid();">
                                    ' . $buttonName . '</button>
                                </span></span>
                                <span class="button sbtn pull-right"><span>
		        <button type="button" id="df_clear_filter" onclick="window.location.href=\'' .
                        $this->view->url($redirectArr, NULL, TRUE) . '\'"
                            class="btn btn-primary btn-lg">
                            ' . $this->translate->_('Clear Filters') . '</button>
		        </span></span>' . $excel_export . $consolidate_export . '</div>';
        }
        else if($request->getActionName() == 'assessmentstatus') {
            $retString .= '<div class="col-sm-4 col-xs-4 btn-search"></div><div class="col-sm-4 col-xs-4 btn-search" '.$buttonHidden.'>
                               
                                <span class="button sbtn pull-right"><span>
                                    <button type="button" id="df_clear_filter" onclick="window.location.href=\'' .
                            $this->view->url($redirectArr, NULL, TRUE) . '\'"
                                        class="btn btn-primary btn-lg">
                                    ' . $this->translate->_('Clear Filters') . '</button>
                                    </span></span>
                                    <span class="button sbtn pull-right"><span>
                                    <button type="button" id="consolidate_submit" class="btn btn-primary btn-lg" onclick="displayInGridassessmentStatus();">
                                    ' . $buttonName . '</button>
                                </span></span> 
                                </span></span>
                        </div>';
        }
        else {
            $excel_export = '';
            if ( $this->view->hasAccess( $this->_user->role_name, 'report', 'questionanalysis', 'exporttoexcel' ) ) {
                $excel_export = '<span class="button sbtn pull-right"><span><button type="button" 
                        id="df_exporttoexcel" onclick="exportTOExcel();" class="btn btn-primary btn-lg">
                        ' . $this->translate->_('Export To Excel') . '</button></span></span>';
            }
            $retString .= '<div class="col-sm-12 col-xs-12 btn-search" '.$buttonHidden.'>
		        <span class="button sbtn pull-right"><span>
		        <button type="submit" id="df_submit" class="btn btn-primary btn-lg">
                        ' . $buttonName . '</button>
		        </span></span>
                        <span class="button sbtn pull-right"><span>
		        <button type="button" id="df_clear_filter" onclick="window.location.href=\'' .
                $this->view->url($redirectArr, NULL, TRUE) . '\'"
                            class="btn btn-primary btn-lg">
                        ' . $this->translate->_('Clear Filters') . '</button>
		        </span></span>' . $excel_export . '</div>';
        } 
        return $retString . '</div></div>';
    }

}
