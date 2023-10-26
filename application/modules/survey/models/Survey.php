<?php

/**
 * Model class to handle survey events operations
 * 
 * @author Harpreet Singh
 * @date   26 May, 2014
 * @version 1.0
 */
class Survey_Model_Survey extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();
        $this->_name = 'surveys';
    }

    
    public function getRecordsByEventType($eventTypeId, $order_by = '') {
        $rs = $this->db->select()
                ->from(array($this->_name))
                ->where("event_typeid = ?", $eventTypeId)
		->where("survey_name != 'Preview Survey'");
        if( !empty($order_by) ) {
            $rs->order($order_by);
        }
        else {
            $rs->order('survey_name asc');
        }
                
        $result= $rs->query()->fetchAll();
        return $result;
    }

    public function checkRecordExist($eventTypeId,$surveyName)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('survey_id'))
                ->where("event_typeid = ?", $eventTypeId)
                ->where("survey_name = ?", $surveyName);
        $result= $rs->query()->fetchAll();
        if(count($result)>0)
            return true;
        else
            return false;
    }
    
    public function checkRecordExistByEventType($eventTypeId)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('survey_id','survey_name'))
                ->where("event_typeid = ?", $eventTypeId);
        $result= $rs->query()->fetchAll();
        if(count($result)>0)
            return true;
        else
            return false;
    }
    
    
    
    public function getEventTypesData($data)
    {
        //echo "<pre>";print_r($data);
        
       $rs = $this->db->select()
                ->from(array('s'=>'surveys'),array
				('s.survey_id','s.event_typeid','s.survey_name','sc.survey_category_name','s.start_date','s.end_date','set.event_type',new 
				Zend_Db_Expr('Group_Concat(sev.event_status) as event_status_comb'),new Zend_Db_Expr('(CASE WHEN CURDATE() < s.start_date THEN "Scheduled"
				WHEN CURDATE() <= s.end_date THEN "In Progress"	ELSE "Closed" END) AS STATUS'),'IFNULL(COUNT(sev.eventid),0) AS TotalCount',
				'IFNULL(sev.event_status,"") AS EventStatus'))
                ->joinInner(array('set' => 'survey_event_types'),'set.event_typeid = s.event_typeid',array())
                ->joinLeft(array('sc' => 'survey_categories'),'sc.survey_category_id = set.survey_category_id',array())
                ->joinLeft(array('sev' => 'survey_events'),'sev.survey_id = s.survey_id',array())   
                            //->where("event_typeid = ? ", $eventTypeId)
				->where('s.survey_name!="Preview Survey" ')
                ->group('sev.event_status')
				->group('s.survey_id');

        if(isset($data['searchBy']) && $data['searchBy']!='')
        {
         //$rs->where('s.survey_name LIKE ?', '%'.$data['searchBy'].'%')->ORwhere('set.event_type LIKE ?', '%'.$data['searchBy'].'%');
				$rs->where("s.survey_name LIKE '%".$data['searchBy']."%' or set.event_type LIKE '%".$data['searchBy']."%'");
			//->ORwhere('s.start_date LIKE ?','%'.$data['searchBy'].'%')->ORwhere('s.end_date LIKE ?', '%'.$data['searchBy'].'%')->__toString();
        }
		if(isset($data['start_date']) && $data['start_date']!='')
        {
           $rs->where('s.start_date >= "'.$data['start_date'].'"' );
		   
        }
		if(isset($data['end_date']) && $data['end_date']!='')
        {
           
		   $rs->where('s.end_date <= "'.$data['end_date'].'"');
        }
		if((isset($data['category'])  && $data['category']!=''))
        {
			 $rs->where('sc.survey_category_id = "'.$data['category'].'"');//die;
		 
        }
		
		$select = $this->db->select()->from(array('tbl' => $rs),array('*','SUM(CASE WHEN EventStatus = "open" THEN TotalCount ELSE 0 END ) AS OPEN', 
		'SUM(CASE WHEN EventStatus = "in progress" THEN TotalCount ELSE 0 END ) AS IN Progress', 'SUM(CASE WHEN EventStatus = "closed" THEN TotalCount ELSE 0 END ) AS Closed'))
        ->group('tbl.survey_id')
        ->limit($data['length'],$data['start'])
        ->order($data['orderBy']);
		
        $result= $select->query()->fetchAll();
        
        return $result;
    }
    
    
    public function getCountData($data)
    {
        /*$rs = $this->db->select()
                ->from(array($this->_name),'count(*) as COUNT'); */
        //change query as above
         $rs = $this->db->select()
         ->from(array('s'=>'surveys'),array
         ('s.survey_id','s.event_typeid','s.survey_name','sc.survey_category_name','s.start_date','s.end_date','set.event_type',new 
         Zend_Db_Expr('Group_Concat(sev.event_status) as event_status_comb'),new Zend_Db_Expr('(CASE WHEN CURDATE() < s.start_date THEN "Scheduled"
         WHEN CURDATE() <= s.end_date THEN "In Progress"	ELSE "Closed" END) AS STATUS'),'IFNULL(COUNT(sev.eventid),0) AS TotalCount',
         'IFNULL(sev.event_status,"") AS EventStatus'))
                ->joinInner(array('set' => 'survey_event_types'),'set.event_typeid = s.event_typeid',array())
                ->joinLeft(array('sc' => 'survey_categories'),'sc.survey_category_id = set.survey_category_id',array())
                ->joinLeft(array('sev' => 'survey_events'),'sev.survey_id = s.survey_id',array())   
                            //->where("event_typeid = ? ", $eventTypeId)
				->where('s.survey_name!="Preview Survey" ')
                ->group('sev.event_status')
				->group('s.survey_id');
                            
        if(isset($data['searchBy']) && $data['searchBy']!='')
        {
         $rs->where("s.survey_name LIKE '%".$data['searchBy']."%' or set.event_type LIKE '%".$data['searchBy']."%'");
			 //->ORwhere('s.start_date LIKE ?','%'.$data['searchBy'].'%')->ORwhere('s.end_date LIKE ?', '%'.$data['searchBy'].'%')->__toString();
        }
		if(isset($data['start_date']) && $data['start_date']!='')
        {
           $rs->where('s.start_date >= "'.$data['start_date'].'"' );
		   
        }
		if(isset($data['end_date']) && $data['end_date']!='')
        {
           
		   $rs->where('s.end_date <= "'.$data['end_date'].'"');
        }
		if((isset($data['category'])  && $data['category']!=''))
        {
			 $rs->where('sc.survey_category_id = "'.$data['category'].'"');//->__toString();die;
		 
        }
        
        $select = $this->db->select()->from(array('tbl' => $rs),array('*','SUM(CASE WHEN EventStatus = "open" THEN TotalCount ELSE 0 END ) AS OPEN', 
		'SUM(CASE WHEN EventStatus = "in progress" THEN TotalCount ELSE 0 END ) AS IN Progress', 'SUM(CASE WHEN EventStatus = "closed" THEN TotalCount ELSE 0 END ) AS Closed'))
		->group('tbl.survey_id');
        $result= $select->query()->fetchAll();
        
         
        return $result;
    }
    
    public function saveData($data)
    {
        if(array_key_exists('survey_id',$data)) {
            $this->_db->update($this->_name, $data, ' survey_id = ' . $data['survey_id']);
        }
        else {
            $rs = $this->db->insert($this->_name,$data);
            $surveyId = $this->db->lastInsertId();
            return $surveyId;
        }
        
    }
    
    public function getSurveyByID($surveyId)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('survey_id','event_typeid','survey_name','start_date','end_date','required_time','email_subject'
				,'thanks_message','landing_page_content','reminder_content','reminder_subject'))
                ->where("survey_id = ? ", $surveyId);
                
        $result= $rs->query()->fetchAll();
        
        return $result;
    }
    
    public function checkRecordExistById($surveyID,$eventType,$surveyName)
    {  
        $rs = $this->db->select()
                ->from(array($this->_name),array('survey_id'))
                ->where("event_typeid = ?", $eventType)
                ->where("survey_name = ?", $surveyName)
                ->where(" survey_id <> ?", $surveyID);
        
        $result= $rs->query()->fetchAll();
        
        if(count($result)>0)
            return true;
        else
            return false;
    }
    
    public function checkRecordExistByServeyID($surveyID)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('survey_id'))
                ->where(" survey_id =  ?", $surveyID);
        
        $result= $rs->query()->fetchAll();
        
        if(count($result)>0)
            return true;
        else
            return false;
    }
    
    public function delete($ID)
    {
        $rs = $this->db->delete($this->_name,'survey_id = '.$ID);
        
        return true;
    }
    
    public function getAllByEventType($eventTypeID) {
        
        $rs = $this->select();
        $rs->from($this->_name);
        $rs->where("event_typeid = ?", $eventTypeID)
		   ->where("event_typeid = ?", $eventTypeID)
		   ->where("survey_name != 'Preview Survey'")
           ->order('survey_name asc');//->__toString();          die;
        

        return $rs->query()->fetchAll();
    }
    
    public function getSurveyAndEventTypeName($eventTypeId)
    {
        
        $rs = $this->db->select()
                ->from(array('s'=>$this->_name),array('s.survey_name'))
                             ->join(array('set' => 'survey_event_types',array('set.event_type')),
                                'set.event_typeid = s.event_typeid')
                            ->where("s.event_typeid = ? ", $eventTypeId);
        
        $result= $rs->query()->fetchAll();
        return $result;
    }
	
	public function getSurveyAndEventTypeNameforConsolidate($SurveyId)
    {
        
        $rs = $this->db->select()
                ->from(array('s'=>$this->_name),array('s.survey_name'))
                             ->join(array('set' => 'survey_event_types',array('set.event_type')),
                                'set.event_typeid = s.event_typeid')
                            ->where("s.survey_id = ? ", $SurveyId);
        
        $result= $rs->query()->fetchAll();
        return $result;
    }
	
	
    public function getSurveyContents($eventTypeId) {
        $rs = $this->db->select()
                ->from(array('s'=>$this->_name),array('s.survey_id','s.invite_subject',
                    's.invite_content','s.reminder_content','s.reminder_subject'
                    ,'s.landing_page_content','s.thanks_message'))
                ->join(array('set' => 'survey_event_types',array('set.event_type')),
                    'set.event_typeid = s.event_typeid')
                ->where("s.event_typeid = ? ", $eventTypeId)
                ->order("s.survey_id DESC");
		//->where("s.is_test_email = 1 ");
        $result= $rs->query()->fetchAll();
        return $result;
    }
    
    public function getSurveyEventID($surveyid) {
        $rs = $this->db->select()
                ->from(array('s'=>$this->_name),array('s.event_typeid'))
                ->join(array('set' => 'survey_event_types',array('set.event_type')),
                                'set.event_typeid = s.event_typeid')
                ->where("s.survey_id = ? ",$surveyid);
        $result= $rs->query()->fetchAll();
        return $result;
    }
    
}
