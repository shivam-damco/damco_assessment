<?php
class Survey_QuestionController extends Damco_Core_CoreController
{
    protected $_request;
    protected $_modelObj;
    protected $_formObj;


    public function init()
    {
        /* Initialize action controller here */
        parent::init();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_helper->layout->setLayout('layout');
        $this->_auth = Zend_Auth::getInstance();
        $this->_request = $this->getRequest();
        $this->_modelObj = new Survey_Model_Questions();
        $this->_formObj = new Survey_Form_Question();
        $this->_eventTypeObj = new Survey_Model_EventTypes();
		$this->_surveyCategoryObj = new Survey_Model_SurveyCategories();
        
        if(!$this->_auth->getIdentity()){
            $this->_redirect($this->view->serverUrl() );
        }
    }
    public function indexAction(){
        $eventTypeId = $this->_request->getParam('eventtypeid');
        
        if(!$eventTypeId){
            $this->_flashMessenger->addMessage(array(
                        'error' => 'No event type id was given'
                    ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        $eventName = $this->_modelObj->getEventTypeName($eventTypeId);
        
        if($eventName){
            $this->view->eventName = $eventName['event_type'];
        } else{
            $this->_flashMessenger->addMessage(array(
                        'error' => 'Event type id does not exist'
                    ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        $showeditlink = $this->_modelObj->showEditLink($eventTypeId);
        $this->view->surveyInprogress = ($showeditlink) ? '0' : '1';
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_getByAjax($eventTypeId);
        }
        
        $get = $this->getRequest()->getParams();
        $this->view->eventTypeId = $eventTypeId;
        $this->view->get = $get;
        $this->view->role_name = $this->_user->role_name;
        
        //$eventTypesData = $this->_eventTypeModelObject->getAllEventTypes();
        //$this->view->EventTypesData = $eventTypesData;
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
    }
    /**
     * Method to return events data for Ajax requests
     */
    private function _getByAjax($eventTypeId = 0, $returnArray = FALSE) {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $get = $this->getRequest()->getParams();
        
        
        
        $aColumns = array( 'sl.question','s.input_type');
        
    	if(!isset($get['ordercolumn'])){
	        if(is_numeric($get['order'][0]['column'])) {
	        	$field = $aColumns[$get['order'][0]['column']];
	        	$sortby = ($get['order'][0]['dir']==='asc')? 'ASC' : 'DESC';
	        	$sOrder = $field. ' ' .$sortby;
	        } else {
	        	$sOrder = '';
	        }
        } else {
        	$field = $aColumns[$get['ordercolumn']];
        	$sortby = ($get['orderdir']==='asc')? 'ASC' : 'DESC';
        	$sOrder = $field. ' ' .$sortby;
        }
        
        
        $params = array(
                'start'=>isset($get['start'])?$get['start']:'',
                'length'=>isset($get['length'])?$get['length']:'',
                'orderBy'=>$sOrder, //Added By Amit kumar 16/09/14 3:46 PM for Sorting
                'eventTypeid' => $eventTypeId
            );
        $result = $this->_modelObj->getQuestions($params); 
        $rowCount = $this->_modelObj->getCountData($eventTypeId); 
        $showeditlink = $this->_modelObj->showEditLink($eventTypeId);
        $bjh = $eventTypeId;
        
        /* if($showeditlink){
            $linkstr = '<a href="'.$this->view->serverUrl().'/survey/question/view/id/dummy_id/eventtypeid/"'+ $bjh +'>View</a>&nbsp;&nbsp;&nbsp;';
        } else{
            $linkstr = '<a href="'.$this->view->serverUrl().'/survey/question/edit/id/dummy_id">Edit</a>&nbsp;&nbsp;&nbsp;<a href="#" onclick="deleteQuestion(dummy_id)">Delete</a>&nbsp;&nbsp;&nbsp;';
        } */
        $req_action_name = ($showeditlink) ? 'view' : 'edit' ;
        if(count($result)>0)
        {
            $data = array(
                'draw' => isset($get['draw'])?$get['draw']:'',
                'recordsTotal' => $rowCount[0]['COUNT'], //$result[1][0]['tot'],
                'recordsFiltered' => $rowCount[0]['COUNT'] //$result[1][0]['tot'],
            );
            $data['data'] = array();
            
            foreach ($result as $value)
            {
                $link = '<a data-toggle="tooltip" data-placement="top" title="Edit Survey" class="edit-icon" href="'.$this->view->serverUrl().
                        '/survey/question/'.$req_action_name.'/id/'.$value['questionid'].
                        '/event_typeid/'. $eventTypeId .'">'.ucfirst($req_action_name).'</a>&nbsp;&nbsp;&nbsp;';
                $link .= ($req_action_name == 'edit') ? '<a data-toggle="tooltip" data-placement="top" title="Delete Survey" href="#" onclick="deleteQuestion('.$value['questionid'].')" class="delete-survey">Delete</a>' : '' ;
                //var_dump($linkstr);die;
                
                //$link = str_replace('dummy_id', $value['questionid'], $linkstr);
                //$link = $linkstr;
                //var_dump($link);die;
                $temp = array(
                        $value['question'],
                        ($value['input_type']) ? ucfirst($value['input_type']) : 'Label',
                        $link,
                );
                $data['data'][]=$temp;
            } 
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
    
    public function addAction(){
        $baseUrl = rtrim(Zend_Controller_Front::getInstance()->getBaseUrl(),'/');
        $eventTypeId = $this->_request->getParam('eventtypeid');
        if(!$eventTypeId){
            $this->_flashMessenger->addMessage(array(
                        'error' => 'No event type id was given'
                    ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        if ($this->_request->isPost()) {
            
            if ($this->_formObj->isValid($this->_request->getPost())) {
                    switch($this->_request->getParam('input_type')){
                        case 'radio':
                            $valid = $this->_validateResponse($this->_request->getParams());
                            $this->_request->setParam('question_type', 'Q');
                            break;
                        case 'textarea':
                            $valid = $this->_validateResponse($this->_request->getParams());
                            $this->_request->setParam('question_type', 'Q');
                            break;
                        case 'date':
                            $valid = $this->_validateResponse($this->_request->getParams());
                            $this->_request->setParam('question_type', 'Q');
                            break;
                        case 'label':
                            $valid = 1;
                            $this->_request->setParam('input_type', '');
                            $this->_request->setParam('question_type', 'T');
                            break;
                        case 'checkbox':
                            $valid = $this->_validateResponse($this->_request->getParams());
                            $this->_request->setParam('question_type', 'Q');
                            break;
                        default:
                            break;
                    }
                    
                    if(!$valid){
                        $this->_flashMessenger->addMessage(array(
                            'error' => 'Validation failed'
                        ));
                    } else{
                        $data = $this->_request->getParams();
                        if($data['max_res_limit'] == '')
                        {
                            $data['max_res_limit'] = 0;
                        }
                    
                        for($i = 1;$i<=20;$i++)
                        {
                            if($i > $data['max_res_limit'])
                            {
                                $data['response'.$i] = '';
                            }
                        }
                        if($data['input_type'] == 'textarea' || $data['input_type'] == 'date')
                        {
                            $data['response1'] = '1';
                        }

                        // added for score value for quiz
                        $data = $this->getScoreValue($data);
                        $this->_setParams($data);
                        
                        if($this->_modelObj->insertRecord()){
                            $this->_flashMessenger->addMessage(array(
                                'success' => 'Question has been added successfully'
                            ));
                            if(isset($data['submit']))
                                $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$eventTypeId);
                            else
                                $this->_redirect($this->view->serverUrl() . '/survey/question/add/eventtypeid/'.$eventTypeId);
                        } else{
                            $this->_flashMessenger->addMessage(array(
                                'error' => 'Question was not added'
                            ));
                            $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$eventTypeId);
                        }
                    }
                } else {
                    
                    $this->view->messages = $this->_formObj->getMessages();
                    
                }
            }
        $this->view->eventTypeId = $eventTypeId;
        $this->_formObj->event_typeid->setValue($eventTypeId);
        $this->view->form = $this->_formObj;
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
        
        
    }
    
    public function editAction(){
        $eventtypeid = $this->_request->getParam("event_typeid");
        if(!$eventtypeid){
            $this->_flashMessenger->addMessage(array(
                'error' => 'No survey id was given'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        $id = $this->_request->getParam('id');
        if(!$id){
            $this->_flashMessenger->addMessage(array(
                        'error' => 'No question id was given'
                    ));
            $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$eventtypeid);
        }
        $this->_modelObj->setPrimaryId($id);
        $editNotAllowed = $this->_modelObj->showEditLink($id,'edit');
        if($editNotAllowed){
            $this->_flashMessenger->addMessage(array(
                'error' => 'Can\'t edit this question'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$eventtypeid);
        }
        if ($this->_request->isPost()) {
            
            if ($this->_formObj->isValid($this->_request->getPost())) {
                switch($this->_request->getParam('input_type')){
                    case 'radio':
                        $valid = $this->_validateResponse($this->_request->getParams());
                        $this->_request->setParam('question_type', 'Q');
                        break;
                    case 'textarea':
                        $valid = $this->_validateResponse($this->_request->getParams());
                        $this->_request->setParam('question_type', 'Q');
                        break;
                    case 'date':
                        $valid = $this->_validateResponse($this->_request->getParams());
                        $this->_request->setParam('question_type', 'Q');
                        break;
                    case 'label':
                        $valid = 1;
                        $this->_request->setParam('input_type', '');
                        $this->_request->setParam('question_type', 'T');
                        break;
                    case 'checkbox':
                            $valid = $this->_validateResponse($this->_request->getParams());
                            $this->_request->setParam('question_type', 'Q');
                            break;
                    default:
                        break;
                }
                if(!$valid){
                        $this->_flashMessenger->addMessage(array(
                            'error' => 'Validation failed'
                        ));
                } else{
                    $data = $this->_request->getParams();
                    
                    
                    for($i = 1;$i<=20;$i++)
                    {
                        if($i > $data['max_res_limit'])
                        {
                            $data['response'.$i] = '';
                        }
                    }
                    
                    if($data['input_type'] == 'textarea' || $data['input_type'] == 'date')
                    {
                        $data['response1'] = '1';
                    }

                    $data = $this->getScoreValue($data);
                    // echo "<pre>"; print_r($data); die;
                    $this->_setParams($data);
                    if($this->_modelObj->updateRecord()){
                        $this->_flashMessenger->addMessage(array(
                            'success' => 'Question has been successfully updated'
                        ));
                        $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$eventtypeid);
                    } else{
                        $this->_flashMessenger->addMessage(array(
                            'error' => 'Question not updated'
                        ));
                        $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$eventtypeid);
                    }
                }
            } else {
                $this->_flashMessenger->addMessage(array(
                    'error' => $this->_formObj->getMessages()
                ));
                $this->view->message = $this->_formObj->getMessages();
            }
        } else{
            
            $data = $this->_modelObj->getRecordById();
            // echo "<pre>"; print_r($data); die;
            $count = 0;
            foreach($data as $key => $val){
                if(strpos($key,'spons') && $val != ''){
                    $count++;
                }
            }
            
            $data['max_res_limit'] = ($count > 0) ? $count : '' ;
            $this->_formObj->max_res_limit->setValue($data['max_res_limit']);
            
            if($data['input_type'] == '' &&  $data['question'] != '')
            {
                $data['input_type'] = 'label';
            }

            if($data['max_score'] > 0){
                if($data['input_type'] == 'radio')
                {
                    for($i = 1; $i <= 11; $i++){
                        $values = 'optionresponse'.$i;
                        $scoreNames = $this->_formObj->$values->options;
                        $scoreKey = array_keys($scoreNames);
                        if($data[$scoreKey[0]] > 0){
                            $this->_formObj->$values->setAttrib('checked', 'checked');
                        }
                    }
                }
            }

            if($data['max_score'] > 0){
                if($data['input_type'] == 'checkbox')
                {
                    $scoreArray = [];
                    for($i = 1; $i <= 11; $i++){
                        if(!empty($data['score'.$i])){
                            $scoreArray[] = $data['score'.$i];
                        }
                        $values = 'optionresponse'.$i;
                        $scoreNames = $this->_formObj->$values->options;
                        $scoreKey = array_keys($scoreNames);
                        if(in_array($data[$scoreKey[0]], $scoreArray)){
                            $this->_formObj->$values->setAttrib('checked', 'checked');
                        }
                    }
                }
            }
            // echo "<pre>"; print_r($this->_formObj); die;
            $this->_formObj->populate($data);
            
        }
        $this->view->heading = 'Edit Question';
		$this->view->button = 'Update';		
        $this->view->form = $this->_formObj;
    }
    
    public function viewAction(){
        //$this->_helper->layout()->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);
        
        $id = $this->_request->getParam('id');
        if(!$id){
            $this->_flashMessenger->addMessage(array(
                'error' => 'No question id was given'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$this->_request->getParam('eventtypeid'));
        }
        $this->_modelObj->setPrimaryId($id);
        $data = $this->_modelObj->getRecordById();
        $count = 0;
        foreach($data as $key => $val){

            if(strpos($key,'spons') && $val != ''){
                $count++;
            }
        }
        $data['max_res_limit'] = ($count > 0) ? $count : '' ;
        if($data['input_type'] == '' &&  $data['question'] != '')
        {
            $data['input_type'] = 'label';
        }

        $this->_formObj->populate($data);
        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH. '/modules/survey/views/scripts/question');
        $this->view->heading = 'View Question';
        $this->view->form = $this->_formObj;
//        var_dump($view);die;
        $this->render('edit');
    }
    
    public function deleteAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $id = $this->_request->getParam('id');
        if(!$id){
            $this->_flashMessenger->addMessage(array(
                'error' => 'No question id was given'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$this->_request->getParam('eventtypeid'));
        }
        $editNotAllowed = $this->_modelObj->showEditLink($id,'edit');
        if($editNotAllowed){
            $this->_flashMessenger->addMessage(array(
                'error' => 'Can\'t delete this question. Survey for this question exist'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$editNotAllowed['event_typeid']);
        }
        $this->_modelObj->setPrimaryId($id);
        $delete = $this->_modelObj->deleteRecord();
        if($delete){
            $this->_flashMessenger->addMessage(array(
                'success' => 'Question has been deleted successfully'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$this->_request->getParam('eventtypeid'));
        } else{
            $this->_flashMessenger->addMessage(array(
                'error' => 'Question not deleted'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$this->_request->getParam('eventtypeid'));
        }
        
    }
    
    
    
    private function _setParams($params){
		
        $this->_modelObj->setQuestionNumber($params['question_number']);
        $this->_modelObj->setInputType($params['input_type']);
        $this->_modelObj->setEventType($params['event_typeid']);
        $this->_modelObj->setQuestionType($params['question_type']);
        $this->_modelObj->setInputType($params['input_type']);
        $this->_modelObj->setMaxResLimit($params['max_res_limit']);
        $this->_modelObj->setQuestion($params['question']);
        $this->_modelObj->setWeightage($params['weightage']);
        $this->_modelObj->setMaxUser($params['max_user']);
        $this->_modelObj->setQuestionScore($params['max_score']);
        $this->_modelObj->setResponse1($params['response1']);
        $this->_modelObj->setResponse2($params['response2']);
        $this->_modelObj->setResponse3($params['response3']);
        $this->_modelObj->setResponse4($params['response4']);
        $this->_modelObj->setResponse5($params['response5']);
        $this->_modelObj->setResponse6($params['response6']);
        $this->_modelObj->setResponse7($params['response7']);
        $this->_modelObj->setResponse8($params['response8']);
        $this->_modelObj->setResponse9($params['response9']);
        $this->_modelObj->setResponse10($params['response10']);
        $this->_modelObj->setResponse11($params['response11']);
        $this->_modelObj->setResponse12($params['response12']);
        $this->_modelObj->setResponse13($params['response13']);
        $this->_modelObj->setResponse14($params['response14']);
        $this->_modelObj->setResponse15($params['response15']);
        $this->_modelObj->setResponse16($params['response16']);
        $this->_modelObj->setResponse17($params['response17']);
        $this->_modelObj->setResponse18($params['response18']);
        $this->_modelObj->setResponse19($params['response19']);
        $this->_modelObj->setResponse20($params['response20']);
        $this->_modelObj->setScore1($params['score1']);
        $this->_modelObj->setScore2($params['score2']);
        $this->_modelObj->setScore3($params['score3']);
        $this->_modelObj->setScore4($params['score4']);
        $this->_modelObj->setScore5($params['score5']);
        $this->_modelObj->setScore6($params['score6']);
        $this->_modelObj->setScore7($params['score7']);
        $this->_modelObj->setScore8($params['score8']);
        $this->_modelObj->setScore9($params['score9']);
        $this->_modelObj->setScore10($params['score10']);
        $this->_modelObj->setScore11($params['score11']);
    }

    private function commentQuestionParams($data) {

        if ($data['input_type'] !== 'radio') {
            return false;
        }

        $params['input_type'] = 'textarea';
        $params['max_res_limit'] = 0;
        $params['question'] = $data['comment_for_question_placeholder'];
        $params['weightage'] = '';
        $params['max_user'] = '';
        $params['response1'] = '1';
        $params['event_typeid'] = $data['event_typeid'];
        $params['question_type'] = $data['question_type'];
        
        $this->_modelObj->setQuestionNumber($params['question_number']);
        $this->_modelObj->setInputType($params['input_type']);
        $this->_modelObj->setEventType($params['event_typeid']);
        $this->_modelObj->setQuestionType($params['question_type']);
        $this->_modelObj->setInputType($params['input_type']);
        $this->_modelObj->setMaxResLimit($params['max_res_limit']);
        $this->_modelObj->setQuestion($params['question']);
        $this->_modelObj->setWeightage($params['weightage']);
        $this->_modelObj->setMaxUser($params['max_user']);
        $this->_modelObj->setResponse1($params['response1']);
        $this->_modelObj->setResponse2($params['response2']);
        $this->_modelObj->setResponse3($params['response3']);
        $this->_modelObj->setResponse4($params['response4']);
        $this->_modelObj->setResponse5($params['response5']);
        $this->_modelObj->setResponse6($params['response6']);
        $this->_modelObj->setResponse7($params['response7']);
        $this->_modelObj->setResponse8($params['response8']);
        $this->_modelObj->setResponse9($params['response9']);
        $this->_modelObj->setResponse10($params['response10']);
        $this->_modelObj->setResponse11($params['response11']);
        $this->_modelObj->setResponse12($params['response12']);
        $this->_modelObj->setResponse13($params['response13']);
        $this->_modelObj->setResponse14($params['response14']);
        $this->_modelObj->setResponse15($params['response15']);
        $this->_modelObj->setResponse16($params['response16']);
        $this->_modelObj->setResponse17($params['response17']);
        $this->_modelObj->setResponse18($params['response18']);
        $this->_modelObj->setResponse19($params['response19']);
        $this->_modelObj->setResponse20($params['response20']);
        
        if (!empty($data['show_comment_for_question_id'])) {
            $params['show_comment_for_question_id'] = $data['show_comment_for_question_id'];
            $this->_modelObj->setCommentForQuestion($params['show_comment_for_question_id']);
        }

        return true;
    }

    /*
     * function for validation of responses 
     */
    private function _validateResponse($data){
        for($i = 1; $i <= $data['max_res_limit']; $i++){
            if($data["response".$i] == ''){
                return false;
            }
        }
        return true;
    }
    
    public function importAction()
    {
        
        $eventTypeId = $this->_request->getParam('eventtypeid');
        
        if(!$eventTypeId){
            $this->_flashMessenger->addMessage(array(
                        'error' => 'No event type id was given'
                    ));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        
        if ($this->_request->isPost()) 
        {
            $questionPostData = $this->_request->getParams();
            
            $newEventTypeID = $questionPostData['eventtypeid'];
            foreach($questionPostData['question-details'] as $questionID)
            {
                $questionDetails = $this->_modelObj->getQuestionDetailsByQuestionID($questionID);
                
                $questionDetails[0]['event_typeid'] = $newEventTypeID;
                
                $this->_setParams($questionDetails[0]);
                
                
                
                $this->_modelObj->insertRecord();
            }
            $this->_flashMessenger->addMessage(array(
                    'success' => 'Questions has been imported successfully'
            ));
            $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$newEventTypeID);
        }
            
            
        $eventTypeData = $this->_eventTypeObj->getAllEventTypesByID($eventTypeId);
		$surveyCategoryData = $this->_surveyCategoryObj->getAllSurveyCategoriesName();
        
        
        
        $this->view->eventTypeData = $eventTypeData;
        $this->view->surveyCategoryData = $surveyCategoryData;
        $this->view->eventTypeId = $eventTypeId;
        $this->_formObj->event_typeid->setValue($eventTypeId);
        $this->view->form = $this->_formObj;
        
        
    }

    public function getScoreValue($data){
        $scoreArray = ['score1', 'score2', 'score3', 'score4', 'score5', 'score6', 
                                        'score7', 'score8', 'score9', 'score10', 'score11'];
        if($data['input_type'] == 'radio' && !empty($data['max_score'])){
            foreach ($scoreArray as $value) {
                if($data['correctanswer'] == $value){
                    $data[$value] = $data['max_score'];
                }
            }
        }
        if($data['input_type'] == 'checkbox' && !empty($data['max_score'])){
            foreach ($scoreArray as $value) {
                if(in_array($value, $data['correctanswer'])){
                     $data[$value] = round((float) ($data['max_score'] / count($data['correctanswer'])), 2);
                }
            }
        }
        return $data;
    }


    public function excelimportAction()
    {
        
        $eventTypeId = $this->_request->getParam('eventtypeid');
        $upload = new Zend_File_Transfer();
        $files = $upload->getFileInfo();
       
        if(!$eventTypeId){
            $this->_flashMessenger->addMessage(array(
                        'error' => 'No event type id was given'));
            $this->_redirect($this->view->serverUrl() . '/survey/eventtype/index');
        }
        
        if ($this->_request->isPost()) 
        {
            $questionPostData = $this->_request->getParams();
            $eventTypeId = $questionPostData['event_typeid'];
            if(isset($files['add_record']['name']) && !empty($files['add_record']['name'])) {
                    $extension = explode('.',$files['add_record']['name']);
                    if($extension['1']=='csv') {
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/uploads/questioncsv')) {
                            mkdir($_SERVER['DOCUMENT_ROOT'].'/uploads/questioncsv', 0777, true);
                        }
                        $file_path = $_SERVER['DOCUMENT_ROOT'].'/uploads/questioncsv/'.$files['add_record']['name'];
                        $tmp_name = $files['add_record']['tmp_name'];
                        move_uploaded_file($tmp_name, $file_path) ;
                        $fp = fopen($file_path,'r') or die("can't open file");
                        $countRespLimit = 0;
                        $firstRow = true;
                        $csv = array();
                        $i = 0;
                        $status = 0;
                        if (($handle = $fp) !== false) {
                            $columns = fgetcsv($handle, 1000, ",");
                            $num = count($columns);
                            $countRespLimit = $num - 4;
                            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                                $csv[$i] = array_combine($columns, $row);
                                $newData = $this->getDataParamsforExcelEntry($csv[$i], $countRespLimit);
                                $this->_setParams($newData);
                                $status = $this->_modelObj->insertRecord();
                                $i++;
                            }
                            fclose($handle) or die("can't close file");
                        }
                        if($status){
                            $this->_flashMessenger->addMessage(array(
                                    'success' => 'File has been imported successfully'
                            ));
                            $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$eventTypeId);
                        }       
                    }
                    else {
                        $this->_flashMessenger->addMessage(array(
                                'error' => 'File format is not csv!!!'
                            ));
                            $this->_redirect($this->view->serverUrl() . '/survey/question/index/eventtypeid/'.$eventTypeId);
                    }
            } else {
                $this->_flashMessenger->addMessage(array(
                                'error' => 'Please select a csv file to upload!!!'
                            ));
                $this->_redirect($this->view->serverUrl() . '/survey/question/excelimport/eventtypeid/'.$eventTypeId);
            }
        }
        
        $this->view->eventTypeId = $eventTypeId;
        $this->_formObj->event_typeid->setValue($eventTypeId);
        $this->view->form = $this->_formObj;
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
        
    }

    public function getDataParamsforExcelEntry($inputArray, $respLimit){
            
            $data = $this->_request->getParams();

            switch(strtolower($inputArray['input type'])){
                case 'radio':
                    $data['input_type'] = 'radio';
                    $data['question_type'] = 'Q';
                    break;
                case 'textarea':
                    $data['input_type'] = 'textarea';
                    $data['question_type'] = 'Q';
                    break;
                case 'date':
                    $data['input_type'] = 'date';
                    $data['question_type'] = 'Q';
                    break;
                case 'label':
                    $data['input_type'] = '';
                    $data['question_type'] = 'T';
                    break;
                case 'checkbox':
                    $data['input_type'] = 'checkbox';
                    $data['question_type'] = 'Q';
                    break;
                default:
                    break;
            }

            $data['question'] = $inputArray['question'];
            
            if($inputArray['max_res_limit'] == '')
            {
                $data['max_res_limit'] = $respLimit;
            }

            if($inputArray['weightage'] == '')
            {
                $data['weightage'] = 0;
            }

            if($inputArray['max_user'] == '')
            {
                $data['max_user'] = 0;
            }

            if($inputArray['max_score'] == '')
            {
                $data['max_score'] = $inputArray['max score'];
            }
        
            for($i = 1; $i <= $respLimit; $i++)
            {
                $data['response'.$i] = $inputArray['res'.$i];
                
            }

            if(strtolower($inputArray['input type']) == 'radio'){
                $data['correctanswer'] = 'score'.$inputArray['correct option'];
            }

            if(strtolower($inputArray['input type']) == 'checkbox'){
                $scores = explode(',', $inputArray['correct option']);
                foreach ($scores as $value) {
                    $data['correctanswer'][] = 'score'.$value;
                }
            }

            if(strtolower($inputArray['input type']) == 'textarea')
            {
                $data['response1'] = '1';
            }

            if(strtolower($inputArray['input type']) == 'date')
            {
                $data['response1'] = '1';
            }

            if(!empty($data['max_score'])){
                // added for score value for quiz
                $data = $this->getScoreValue($data);
            }
            // echo "<pre>"; print_r($data); die;
            return $data;
    }

    public function uploadimagetinymceAction(){
        $upload = new Zend_File_Transfer();
        $files = $upload->getFileInfo();
        reset($files);
        $temp = current($files);
        if(is_uploaded_file($temp['tmp_name']))
        {
            if(preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])){
                header("HTTP/1.1 400 Invalid file name,Bad request");
                return;
            }
          
            // Validating Image file type by extensions
            if(!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "png"))){
                header("HTTP/1.1 400 Invalid extension,Bad request");
                return;
            }

            if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/uploads/question-images')) {
                mkdir($_SERVER['DOCUMENT_ROOT'].'/uploads/question-images', 0777, true);
            }

            // echo "<pre>"; print_r($_SERVER['DOCUMENT_ROOT']); die;
            $fileName = $_SERVER['DOCUMENT_ROOT'].'/uploads/question-images/'. $temp['name'];
            // $fileName = $this->view->serverUrl().'/uploads/question-images/'. $temp['name'];
            move_uploaded_file($temp['tmp_name'], $fileName);

            $newFileName = $this->view->serverUrl().'/../../uploads/question-images/'. $temp['name'];
            // $newFileName = $this->view->serverUrl().'/uploads/question-images/'. $temp['name'];
            echo json_encode(array('location' => $newFileName));
            die;
        }
    }

}
?>
