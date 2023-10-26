<?php

/**
 * @author  Harpreet Singh
 * @date    29 May, 2014
 * @version 1.0
 * 
 * Controller to handle common ajax operations  
 */
class Survey_AjaxController extends Damco_Core_CoreController {

    protected $_eventModelObject = null;
    protected $_surveyEventQuestionObj = null;
    protected $_eventTypesObj = null;

    /**
     * Method to initialize event controller 
     */
    public function init() {
        parent::init();
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->hirearchy_session = new Zend_Session_Namespace('hierarchy_dealer');
        $this->_surveyEventQuestionObj = new Survey_Model_Questions();
        $this->_eventTypesObj = new Survey_Model_EventTypes();
    }

    /**
     * Method to populate years via AJAX for data filters
     */
    public function fillyearsAction() {
        $get = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest()
            && isset($get['date_range_field'])) {
            
            switch ( $get['date_range_field'] ) {
                case 'import_date':
                    $column = 'added_date';
                    break;
                case 'event_date':
                    $column = 'event_date';
                    break;
                case 'date_of_sale':
                    $column = 'date_of_sale';
                    break;
                default:
                    $column = 'survey_date';
                    break;
            }
            
            if ( $column == 'date_of_sale' ) {
                $cusModelObject = new Customer_Model_Customers();
                $result = $cusModelObject->getDateRange($column);
                $from = date('Y', strtotime($result['min_date']));
                $to = date('Y', strtotime($result['max_date']));
            }
            else {
                $this->_eventModelObject = new Event_Model_Events();
                $result = $this->_eventModelObject->getDateRange($column);
                $from = date('Y', strtotime($result['min_date']));
                $to = date('Y', strtotime($result['max_date']));
            }
            
            $retString = '';
            for ($i=$from; $i<=$to; $i++) {
                $sel = ( isset($get['selected_year'])
                         && $get['selected_year'] == $i )
                       ? ' selected="selected"' : '';
                $retString .= '<option value="'.$i.'" '.$sel.'>'
                               .$i.'</option>';
            }

            echo json_encode(array(
                'error_code' => '0',
                'error_msg'  => 'Success',
                'years_dd'   => $retString,
                'range'   => array('from' => $from,
                                    'to' => $to),
            ));
            exit;
        }
        die('Invalid Request');
    }

    /**
     * Method to populate Markets, AMSs and Dealers via AJAX for data filters
     */
    public function fillmarketsAction() {
        $get = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest()
            && isset($get['branch'])) {
            
            $accHierarchy = new Damco_Core_AccessHierarchy();
            $result = $accHierarchy->get($get['branch']);
            
            $markets = '<option value="">-- ' . $this->view->translate('All Countries')
                        . ' --</option>';
            $salesRegions = '<option value="">-- ' . $this->view
                        ->translate('All Sales Regions') . ' --</option>';
            $dealers = '<option value="">-- ' . $this->view
                        ->translate('All Dealers') . ' --</option>';
            
            if ( $this->_user->role_id <= '2' ) {
                foreach ( $result['markets'] as $value ) {
                    $markets .= '<option value="'.$value['id'].'">'
                        .$value['name'].'</option>';
                }
            }
            if ( $this->_user->role_id <= '3' ) {
                foreach ( $result['sales_regions'] as $value ) {
                    $salesRegions .= '<option value="'.$value['id'].'">'
                        .$value['name'].'</option>';
                }
            }
            $dealerArray = array();
            foreach ( $result['dealers'] as $value ) {

				if ( empty( $value['name'] ) ) {
					continue;
				}

                $dealerArray[]=$value['id'];
                $dealers .= '<option value="'.$value['id'].'">'
                    .$value['name'].'</option>';
            }
/**
 * @todo Remove Core Session
 */
            $_SESSION['hierarchy_dealer'] = $dealerArray;
            echo json_encode(array(
                'error_code' => '0',
                'error_msg'  => 'Success',
                'markets_dd' => $markets,
                'sales_region_dd' => $salesRegions,
                'dealers_dd' => $dealers,
            ));
            exit;
        }
        die('Invalid Request');
    }

    /**
     * Method to populate ASMs and Dealers via AJAX for data filters
     */
    public function fillasmsAction() {
        $get = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest()
            && isset($get['market']) && isset($get['branch'])) {
            
            $accHierarchy = new Damco_Core_AccessHierarchy();
            $result = $accHierarchy->get($get['branch'], $get['market']);
            
            $asm = '<option value="">-- All ASMs --</option>';
            $dealers = '<option value="">-- ' . $this->view->translate('All Dealers')
                    . ' --</option>';
            
            if ( $this->_user->role_id <= '2' ) {
                foreach ( $result['asm'] as $value ) {
                    $asm .= '<option value="'.$value['id'].'">'
                        .$value['name'].'</option>';
                }
            }
             $dealerArray = array();

            
            foreach ( $result['dealers'] as $value ) {

				if ( empty( $value['name'] ) ) {
					continue;
				}

                $dealerArray[] = $value['id'];
                $dealers .= '<option value="'.$value['id'].'">'
                    .$value['name'].'</option>';
            }

            /**
                * @todo Remove Core Session
            */
            $_SESSION['hierarchy_dealer'] = $dealerArray;
            echo json_encode(array(
                'error_code' => '0',
                'error_msg'  => 'Success',
                'asm_dd' => $asm,
                'dealers_dd' => $dealers,
            ));
            exit;
        }
        die('Invalid Request');
    }

    /**
     * Method to populate Sales Regions and Dealers via AJAX for data filters
     */
    public function fillsalesregionsAction() {
        $get = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest()
            && isset($get['market']) && isset($get['branch'])) {
            $accHierarchy = new Damco_Core_AccessHierarchy();
            $result = $accHierarchy->get($get['branch'], $get['market']);
            
            $salesRegions = '<option value="">-- ' . $this->view
                    ->translate('All Sales Regions') . ' --</option>';
            $dealers = '<option value="">-- ' . $this->view->translate('All Dealers')
                    . ' --</option>';
            
            if ( $this->_user->role_id <= '3' ) {
                foreach ( $result['sales_regions'] as $value ) {
                    $salesRegions .= '<option value="'.$value['id'].'">'
                        .$value['name'].'</option>';
                }
            }
            
             $dealerArray = array();

             foreach ( $result['dealers'] as $value ) {

				if ( empty( $value['name'] ) ) {
					continue;
				}

                 $dealerArray[] = $value['id'];
                $dealers .= '<option value="'.$value['id'].'">'
                    .$value['name'].'</option>';
            }

            /**
                * @todo Remove Core Session
            */
            $_SESSION['hierarchy_dealer'] = $dealerArray;
            
           

            echo json_encode(array(
                'error_code' => '0',
                'error_msg'  => 'Success',
                'sales_regions_dd' => $salesRegions,
                'dealers_dd' => $dealers,
            ));
            exit;
        }
        die('Invalid Request');
    }

    /**
     * Method to populate Dealers via AJAX for data filters
     */
    public function filldealersAction() {
        $get = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest()
            && isset($get['market'])
            && isset($get['branch'])
            && isset($get['sales_region'])) {
            
            $accHierarchy = new Damco_Core_AccessHierarchy();
            $result = $accHierarchy->get($get['branch'], $get['market'],
                    $get['sales_region']);
            
            $dealers = '<option value="">-- ' . $this->view->translate('All Dealers')
                    . ' --</option>';
            
            
            $dealerArray = array();

             foreach ( $result['dealers'] as $value ) {

				if ( empty( $value['name'] ) ) {
					continue;
				}

                 $dealerArray[] = $value['id'];
                $dealers .= '<option value="'.$value['id'].'">'
                    .$value['name'].'</option>';
            }

            /**
                * @todo Remove Core Session
            */
            $_SESSION['hierarchy_dealer'] = $dealerArray;
            
            
           

            echo json_encode(array(
                'error_code' => '0',
                'error_msg'  => 'Success',
                'dealers_dd' => $dealers,
            ));
            exit;
        }
        die('Invalid Request');
    }
    
    public function getlanguageAction() {
		echo '{
	"sEmptyTable":     "No data available in table",
	"sInfo":           "Showing _START_ to _END_ of _TOTAL_ entries",
	"sInfoEmpty":      "Showing 0 to 0 of 0 entries",
	"sInfoFiltered":   "(filtered from _MAX_ total entries)",
	"sInfoPostFix":    "",
	"sInfoThousands":  ",",
	"sLengthMenu":     "Show _MENU_ entries",
	"sLoadingRecords": "Loading...",
	"sProcessing":     "Processing...",
	"sSearch":         "Search:",
	"sZeroRecords":    "No matching records found",
	"oPaginate": {
		"sFirst":    "First",
		"sLast":     "Last",
		"sNext":     "Next",
		"sPrevious": "Previous"
	},
	"oAria": {
		"sSortAscending":  ": activate to sort column ascending",
		"sSortDescending": ": activate to sort column descending"
	}
}';
		die;

        $get = $this->getRequest()->getParams();
        if ( isset($get['langid']) ) {
            switch ( $get['langid'] ) {
                case '17':
                    $url = 'http://cdn.datatables.net/plug-ins/be7019ee387/i18n/Swedish.json';
                    break;
                case '16':
                    $url = 'http://cdn.datatables.net/plug-ins/be7019ee387/i18n/Spanish.json';
                    break;
                case '14':
                    $url = 'http://cdn.datatables.net/plug-ins/725b2a2115b/i18n/Portuguese-Brasil.json';
                    break;
                case '12':
                    $url = 'http://cdn.datatables.net/plug-ins/be7019ee387/i18n/Japanese.json';
                    break;
                case '11':
                    $url = 'http://cdn.datatables.net/plug-ins/be7019ee387/i18n/Italian.json';
                    break;
                case '7':
                    $url = 'http://cdn.datatables.net/plug-ins/be7019ee387/i18n/German.json';
                    break;
                case '6':
                    $url = 'http://cdn.datatables.net/plug-ins/be7019ee387/i18n/French.json';
                    break;
                case '3':
                    $url = 'http://cdn.datatables.net/plug-ins/be7019ee387/i18n/Dutch.json';
                    break;
                default :
                    $url = 'http://cdn.datatables.net/plug-ins/be7019ee387/i18n/English.json';
                    break;
            }
        }
        else {
            $url = 'http://cdn.datatables.net/plug-ins/be7019ee387/i18n/English.json';
        }
        
        echo @file_get_contents( $url );
        die;
    }
    
    /**
     * Method to populate Markets, AMSs and Dealers via AJAX for data filters
     */
    public function fillquestionsAction() {
        $get = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest()
            && isset($get['event_typeid'])) {
            
            $questionObj = new Survey_Model_Questions();
            $objCconfig = new Survey_Model_Config();
            $arrConfigVariables = array('Question_analysis_questionid');
            $config_queid = $objCconfig->getConfigQueIds($arrConfigVariables);        
            $serArray = unserialize($config_queid['Question_analysis_questionid']);
           
            $allQuestions =  array_merge($serArray['sales']['questionId'],
                                                $serArray['product']['questionId'],
                                                $serArray['service']['questionId']);
                    $where = array( "event_typeid" => $get['event_typeid'], 
                                    "langid" => !empty($this->_user->lang_id) ? $this->_user->lang_id : 1, 
                                    'question_type' =>array('Q'));
            $where["input_type"] = array('checkbox','drop down','radio');
            $result = $questionObj->getQuestionDetails($where,"multi");
            if ( !empty($result["Qmulti"]) ) {
                $questions = '';
                $questions .= '<option value="All" title="All" >All</option>';
                foreach ($result["Qmulti"] as $arrQ) {  
                    $showQus = strlen($arrQ["question"]) > 100 ?
                            substr($arrQ["question"],0,100).'...':
                            $arrQ["question"];
                    
                    $questions .= '<option value="' . $arrQ["questionid"] . '" title="'.$arrQ["question_number"].". ".$arrQ["question"].'" >'
                            .  $showQus . '</option>';
                }
               //echo $questions; //die;
            }
            else
            {
                $questions = '';
                $questions .= '<option value="" title="" >No Records Found</option>';
            }
            echo json_encode(array(
                'error_code' => '0',
                'error_msg'  => 'Success',
                'question_dd' => $questions                
            ));           
            exit;
        }
        die('Invalid Request');
    }
    
    public function fillsurveyAction() {
        $get = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest()
            && isset($get['event_typeid'])) {
            $surveyObj = new Survey_Model_Survey();
            $where = array( "event_typeid" => $get['event_typeid']);
            $temp_result = $surveyObj->checkRecordExistByEventType($get['event_typeid']);
            
            if(!$temp_result){
                //die('No survey exist for this event type');
            }
            $result = $surveyObj->getRecordsByEventType($get['event_typeid']);
            
//           $questions = '<option value="">-- ' .  $this->view->translate('Select Questions')
//                    . ' --</option>'; /**/
//            var_dump($result);die;
            //$result = $surveyObj->
            if ( !empty($result) ) {
                $surveys = '';
                foreach ($result as $arrSurvey) {  
                    $showSur = strlen($arrSurvey["survey_name"]) > 100 ?
                            substr($arrSurvey["survey_name"],0,100).'...':
                            $arrSurvey["survey_name"];
                    
                    $surveys .= '<option value="' . $arrSurvey["survey_id"] . '">'
                            .  $showSur . '</option>';
                }
               //echo $questions; //die;
            } 
            else
            {
                $surveys = '';
                $surveys .= '<option value="">No Records Found</option>';
            }
            echo json_encode(array(
                'error_code' => '0',
                'error_msg'  => 'Success',
                'survey_dd' => $surveys                
            ));           
            exit;
        }
        die('Invalid Request');
    }
    
    public function displayingridAction()
    {
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        echo 'here';exit;
        
        echo 'here';exit;
    }
    
    public function getquestiondetailsbyeventtypeAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $get = $this->getRequest()->getParams();
        
        $questionData = $this->_surveyEventQuestionObj->getAllQuestionDataByEventType($get['event-type-id']);
        
        
        if(!empty($questionData))
            echo json_encode($questionData);   
        else
            echo json_encode('No Records Found');
        
        exit;
        
        
    }
	public function getsurveynamesbycategoryAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $get = $this->getRequest()->getParams();
        
        $surveyData = $this->_eventTypesObj->getAllEventTypesBySurveyCategoryId($get);
        
        
        if(!empty($surveyData))
            echo json_encode($surveyData);
        else
            echo json_encode('No Records Found');
        
        exit;
        
        
    }
    
    public function filleventtypeAction() {
        $get = $this->getRequest()->getParams();
        
        if ($this->getRequest()->isXmlHttpRequest()
            && isset($get['category_id'])) {
            $surveyObj = new Survey_Model_EventTypes();
            //$where = array( "event_typeid" => $get['event_typeid']);
            $temp_result = $surveyObj->getEventTypesByCategory($get['category_id']);
            
            
            if(!$temp_result){
                //die('No survey exist for this event type');
            }
            $result = $surveyObj->getEventTypesByCategory($get['category_id']);
            
//           $questions = '<option value="">-- ' .  $this->view->translate('Select Questions')
//                    . ' --</option>'; /**/
//            var_dump($result);die;
            //$result = $surveyObj->
            if ( !empty($result) ) {
                $surveys = '';
                foreach ($result as $arrSurvey) {  
                    $showSur = strlen($arrSurvey["event_type"]) > 100 ?
                            substr($arrSurvey["event_type"],0,100).'...':
                            $arrSurvey["event_type"];
                    
                    $surveys .= '<option value="' . $arrSurvey["event_typeid"] . '">'
                            .  $showSur . '</option>';
                }
               //echo $questions; //die;
            } 
            else
            {
                $surveys = '';
                $surveys .= '<option value="">No Records Found</option>';
            }
            echo json_encode(array(
                'error_code' => '0',
                'error_msg'  => 'Success',
                'survey_dd' => $surveys                
            ));           
            exit;
        }
        die('Invalid Request');
    }
    
    
    
    
}
