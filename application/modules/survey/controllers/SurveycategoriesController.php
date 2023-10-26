<?php

/**
 * @author  Harpreet Singh
 * @date    21 Dec, 2015
 * @version 1.0
 * 
 * Controller to handle event operations  
 */

class Survey_SurveycategoriesController extends Damco_Core_CoreController {

    protected $_auth = null;
    protected $_redirector = null;
    protected $_surveyModelObject = null;
    protected $_surveyEventsModelObject = null;
    protected $_config = null;
    protected $_emailObj = null;
    protected $_emailTemplateObj = null;
	protected $_surveyCategoriesModelObject = null;

    /**
     * Method to initialize event controller
     */
    public function init() {
        parent::init();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_auth = Zend_Auth::getInstance();
        $this->_config = new Survey_Model_Config(); //survey_make_option_nootherbrand
		$this->_surveyCategoriesModelObject = new Survey_Model_SurveyCategories();
         
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
        
        $form = new Survey_Form_Surveycategories();
        
        if ($this->_request->isPost()) {
            $message = '';
            $form->isValid($get);
            
            if ((!$form->isValid($get))) {
                
                if (array_key_exists("survey_category_name", $form->getmessages())) {
                    $message.= "<br/>Please enter survey category name";
                }
                
                $this->view->messages = $message;
            } else {
                
                $isrecordExist = $this->_surveyCategoriesModelObject->checkRecordExist($get['survey_category_name']); 
                if($isrecordExist)
                {
                    $message.= "<br/>Survey Category already exists";
                    $this->view->messages = $message;
                }
                
                else
                {
        
                    $surveyCategoryName = $get['survey_category_name'];            
                    $addedDate = Date('Y-m-d h:i:s');
                    $addedBy = $this->_user->id;
                    $modifiedBy = $this->_user->id;
                    //$employeeDetails = $get['form_ajax_employee'];
                    //print_r($this->_user->id);exit;

                    $arrEventType = array(
                        'survey_category_name' => $surveyCategoryName,
                        'added_date'=>$addedDate,
						'added_by'=>$addedBy,
                        'modified_by'=>$modifiedBy);
                    
                    
                    $surveyCategoryId = $this->_surveyCategoriesModelObject->saveData($arrEventType);
                    
                    $this->_flashMessenger->addMessage(array(
                        'success' => 'Survey Category has been successfully created'
                    ));
                    $this->_redirect($this->view->serverUrl() . '/survey/surveycategories/index');
                    
                }
                
                
            }
        }
        
        $arrConfigVar = array('SURVEY_SELECTION_FILTERS');
        
       
        
        $configSurveySelectionFilters = $this->_config->getConfigQueIds($arrConfigVar);
        $arrSelectionFilterData = unserialize($configSurveySelectionFilters["SURVEY_SELECTION_FILTERS"]);
        
        $this->view->radioData = $arrSelectionFilterData;
        $this->view->form = $form;
    }
    /**
     * Method to return events data for Ajax requests
     */
    private function _getByAjax($returnArray = FALSE) {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $get = $this->getRequest()->getParams();
	$aColumns = array( 's.survey_category_name','');
        
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
                'orderBy'=>$sOrder 
            );
		
		
        $result = $this->_surveyCategoriesModelObject->getSurveyCategoriesData($params); 
        $rowCount = $this->_surveyCategoriesModelObject->getCountData(); 
        
        if(count($result)>0) {
            $data = array(
                'draw' => isset($get['draw'])?$get['draw']:'',
                'recordsTotal' => $rowCount[0]['COUNT'], //$result[1][0]['tot'],
                'recordsFiltered' => $rowCount[0]['COUNT'] //$result[1][0]['tot'],
            );
            $data['data'] = array();
            
            foreach ($result as $value) {
                $edit_survey = $delete_survey = '';
                if ( $this->view->hasAccess( $this->_user->role_name, 'survey', 'surveycategories', 'edit' ) ) {
                    $edit_survey = '<a data-toggle="tooltip" data-placement="top" title="Edit Survey" '
                            . 'href="'.$this->view->serverUrl().'/survey/surveycategories/edit/id/'
                            . base64_encode($value['survey_category_id']).'" class="edit-icon">'
                            . 'Edit</a>&nbsp;&nbsp;&nbsp;';
                }
                if ( $this->view->hasAccess( $this->_user->role_name, 'survey', 'surveycategories', 'delete' ) ) {
                    $delete_survey = '<a data-toggle="tooltip" data-placement="top" title="Delete Survey" '
                            . 'href="#" onclick="deleteSurveyCategory('.$value['survey_category_id'].')"'
                            . 'class="delete-survey">Delete</a>';
                }
                $temp = array(
                        $value['survey_category_name'],
                        $edit_survey . $delete_survey
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
                'error' => 'Survey Category ID not received!!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/surveycategories/index');
        }
        
        $surveyCategoryID = $get['id'];
        
        
        $isValidId = $this->_surveyCategoriesModelObject->checkRecordExistBySurveyCategoryID($surveyCategoryID);
        //$isValidId = $this->_validateEventTypeId($eventTypeID);
        
        if(!$isValidId)
        {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Invalid Survey Category ID!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/surveycategories/index');
        }
        
        if ( !is_numeric($surveyCategoryID) ) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Invalid Survey Category ID!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/surveycategories/index');
        }
        
        
        $this->_surveyCategoriesModelObject->delete($surveyCategoryID);
        
        $this->_flashMessenger->addMessage(array(
            'success' => 'Survey Category has been successfully Deleted'
        ));
        
        $this->_redirect($this->view->serverUrl() . '/survey/surveycategories/index');
    }

 
    /**
     * Method to handle edit action operations
     * @author  Anuj
     * @date    31 May, 2014
     */
    public function editAction() {
        
        $surveyCategoryId = $this->_request->getParam("id", '');
        
        $surveyCategoryId = base64_decode($surveyCategoryId);
       
        if (!isset($surveyCategoryId)) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Survey Category ID not received!!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/surveycategories/index');
        }
        
        if (!is_numeric($surveyCategoryId)) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Invalid Survey Category ID!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/surveycategories/index');
        }
        
        $result = $this->_surveyCategoriesModelObject->getSurveyCategoryByID($surveyCategoryId);
        
        if(count($result) == 0)
        {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Invalid Survey Category ID!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/surveycategories/index');
        }
        
        
        $this->view->SurveyData = $result;
        $form = new Survey_Form_Surveycategories();
        
        $form->populate(
            array(
                'survey_category_id' => $result[0]['survey_category_id'],
                'survey_category_name' => $result[0]['survey_category_name']
            )
        );
        
        //$form->populate($result);
        
        
        $get = $this->getRequest()->getParams();
        // $surveyCategoryId = $this->_request->getParam("id", '');
        if ($this->_request->isPost()) {
            $message = '';
            
            $form->isValid($get);
            
            
            if ((!$form->isValid($get))) 
            {
              
                if (array_key_exists("survey_category_name", $form->getmessages())) {
                    $message.= "<br/>Please enter survey category name";
                }
				
                $this->view->messages = $message;
                
            } 
            else 
            { 
                $isrecordExist = $this->_surveyCategoriesModelObject->checkRecordExistById($surveyCategoryId,$get['survey_category_name']);
                if($isrecordExist)
                {
                    $message.= "<br/>Survey Category already exists with the same name.";
                    $this->view->messages = $message;
                }
				else{
                    //$surveyCategoryId = $this->_request->getParam("id", '');
                    $surveyCategoryName = $get['survey_category_name'];      
                    $modifiedBy = $this->_user->id;
                    

                    $arrSurvey = array('survey_category_id'=>$surveyCategoryId,'survey_category_name' => $surveyCategoryName,'modified_by'=>$modifiedBy);
                   
                    $this->_surveyCategoriesModelObject->saveData($arrSurvey);
                    $this->_flashMessenger->addMessage(array(
                        'success' => 'Survey Category has been successfully updated'
                    ));
                   $this->_redirect($this->view->serverUrl() . '/survey/surveycategories/index');
                
            }
		}
      }
        $this->view->form = $form;
    }
    
    /*
     * Method to view a survey Category
     */

     /**
     * Method to handle edit action operations
     * @author  Anuj
     * @date    31 May, 2014
     */
    public function viewAction() {
        
        $surveyCategoryId = $this->_request->getParam("id", '');
        
        $surveyCategoryId = base64_decode($surveyCategoryId);
        
        if (!isset($surveyCategoryId)) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Survey Category ID not received!!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/surveycategories/index');
        }
        
        if (!is_numeric($surveyCategoryId)) {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Invalid Survey Category ID!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/surveycategories/index');
        }
        
        $result = $this->_surveyCategoriesModelObject->getSurveyCategoryByID($surveyCategoryId);
        
        if(count($result) == 0)
        {
            $this->_flashMessenger->addMessage(array(
                'error' => 'Invalid Survey Category ID!'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/surveycategories/index');
        }
        
        
        $this->view->SurveyData = $result;
        $form = new Survey_Form_Surveycategories();
        $form->survey_category_name->setAttrib('disabled','disabled');
        $form->populate(
            array(
                'survey_category_id' => $result[0]['survey_category_id'],
                'survey_category_name' => $result[0]['survey_category_name']
            )
        );
        $this->view->form = $form;
    }    
}