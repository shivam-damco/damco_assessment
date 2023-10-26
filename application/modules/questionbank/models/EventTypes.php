<?php

/**
 * Model class to handle survey events operations
 * 
 * @author Harpreet Singh
 * @date   26 May, 2014
 * @version 1.0
 */
class Questionbank_Model_EventTypes extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();
        $this->_name = 'survey_event_types';
    }

    /**
     * method to insert or update
     */
    public function saveData($data)
    {
        if(array_key_exists('event_typeid',$data))
        {
            $this->_db->update($this->_name, $data, ' event_typeid = ' . $data['event_typeid']);
        }
        else 
            $rs = $this->db->insert($this->_name,$data);
    }
    
    public function checkRecordExist($eventType)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('event_typeid'))
                ->where("event_type = ?", $eventType);
        $result= $rs->query()->fetchAll();
        if(count($result)>0)
            return true;
        else
            return false;
    }
    
    public function checkRecordExistById($eventType,$eventId)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('event_typeid'))
                ->where("event_type = ?", $eventType)
                ->where(" event_typeid <> ?", $eventId);
        $result= $rs->query()->fetchAll();
        
        if(count($result)>0)
            return true;
        else
            return false;
    }
    
    
    
    public function getAllEventTypes()
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('event_typeid','event_type','description','department','department_id'));
        $result= $rs->query()->fetchAll();
        
        return $result;
    }
    
    public function getEventTypesByID($eventTypeId)
    {  
        $rs = $this->db->select()
                ->from(array($this->_name),array('event_typeid','event_type','description','department','department_id','survey_category_id','required_time'))
                //->joinLeft(array('scat'=>'survey_categories'),'scat.survey_category_id = survey_event_types.survey_category_id',array('survey_category_name'))
                ->where("event_typeid = ? ", $eventTypeId);    
        $result= $rs->query()->fetchAll();
        
        return $result;
    }

    
    public function getEventTypesData($data)
    {
       $rs = $this->db->select()
                ->from(array($this->_name),array('event_typeid','event_type','description','department','department_id','added_date',
				new Zend_Db_Expr('Group_Concat(sev.event_status) as event_status_comb'),
				/*new Zend_Db_Expr('"" as event_status_comb'),*/
				new Zend_Db_Expr('(SELECT COUNT(*)  FROM `surveys` s WHERE s.`event_typeid` = `survey_event_types`.`event_typeid` AND s.`survey_name`!="Preview Survey" AND s.`is_test_email` != 1)  AS surveyInstanceCount')))
                ->joinLeft(array('sev' => 'survey_events'),'sev.event_typeid = '.$this->_name.'.event_typeid',array())
                
                ->joinLeft(array('scat'=>'survey_categories'),'scat.survey_category_id = survey_event_types.survey_category_id',array('survey_category_name'))
                //->where("s. = ? ", $eventTypeId)
                ->group($this->_name.'.event_typeid')
                ->order($data['orderBy'])
                ->limit($data['length'],$data['start']);//->__tostring();die;
        if(isset($data['searchBy']) && $data['searchBy']!='')
        {
           $rs->where('`survey_event_types`.`event_type` LIKE ?', '%'.$data['searchBy'].'%')->ORwhere('`survey_event_types`.`department` LIKE ?', '%'.$data['searchBy'].'%')->ORwhere('scat.survey_category_name LIKE ?', '%'.$data['searchBy'].'%');
        }
        
        $result= $rs->query()->fetchAll();
        
        return $result;
    }
    
    public function getCountData($data)
    {
        /*$rs = $this->db->select()
                ->from(array($this->_name),'count(*) as COUNT');
                
        $result= $rs->query()->fetchAll(); */
        $rs = $this->db->select()
                ->from(array($this->_name),'count(*) as COUNT')
                ->joinLeft(array('scat'=>'survey_categories'),'scat.survey_category_id = survey_event_types.survey_category_id',array());//->__tostring();die;
        if(isset($data['searchBy']) && $data['searchBy']!='')
        {
           $rs->where('`survey_event_types`.`event_type` LIKE ?', '%'.$data['searchBy'].'%')->ORwhere('`survey_event_types`.`department` LIKE ?', '%'.$data['searchBy'].'%')->ORwhere('scat.survey_category_name LIKE ?', '%'.$data['searchBy'].'%');
        }
        
        $result= $rs->query()->fetchAll();
        
        
        return $result;
    }
    
    public function delete($ID)
    {
        $rs = $this->db->delete($this->_name,'event_typeid = '.$ID);
        
        return true;
    }
    
    public function getAllEventTypesName()
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('event_typeid','event_type'));
        $result= $rs->query()->fetchAll();
        
        return $result;
    }
    
    public function getEventTypesNameByID($eventTypeID)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('event_type'))
                ->where("event_typeid = ? ", $eventTypeID)
                ;
        $result= $rs->query()->fetchAll();
        
        return $result;
    }
    /*
     * Do not include event type id from which its getting called
     */
    public function getAllEventTypesByID($eventTypeID)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('event_typeid','event_type','description','department','department_id'))
                ->where("event_typeid <> ? ", $eventTypeID)
                ;
        $result= $rs->query()->fetchAll();
        
        return $result;
    }
	
	public function getAllEventTypesBySurveyCategoryId($data)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('event_typeid','event_type'))
                ->where("survey_category_id = ? ", $data['survey-category-id'])
                ->where("event_typeid <> ? ", $data['eventtypeid']);
        if(isset($data['fromDate']) && $data['fromDate']!= '')
                $rs->where("added_date >= ? ", $data['fromDate']);
        if(isset($data['toDate']) && $data['toDate']!= '')
            $rs->where("added_date <= ? ", $data['toDate']);
                
        $result= $rs->query()->fetchAll();
        
        return $result;
    }
	public function getSurveyCategoryNameByEventTypeId($eventTypeId)
    {
        $rs = $this->db->select()
                ->from(array('survey_categories'),array('survey_category_name'))
                ->where("survey_category_id = ? ", $surveyCategoryId);
         
        $result= $rs->query()->fetchAll();
        
        return $result;
    }
    
    public function getEventTypesByCategory($categoryId)
    {   
        $rs = $this->db->select()
                ->from(array($this->_name),array('event_typeid','event_type'))
                //->joinLeft(array('scat'=>'survey_categories'),'scat.survey_category_id = survey_event_types.survey_category_id',array('survey_category_name'))
                ->where("survey_category_id = ? ", $categoryId)
                ->order('event_type asc');    
        
        $result= $rs->query()->fetchAll();
        
        
        return $result;
    }

        public function checkOldPassword($userId, $password) {
            $rs = $this->db->select()
                    ->from(array('users'), array('password'))
                    ->where("id = ?", $userId)
                    ->Where("password = ?", $password);
            $result = $rs->query()->fetchAll();
            if (count($result) > 0)
                return true;
            else
                return false;
        }
        
        public function updatePassword($data) {
           
                $this->_db->update('users', $data, ' id = ' . $data['id']);
        }
}
