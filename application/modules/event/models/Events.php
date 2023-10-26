<?php

/**
 * Model class to handle survey events operations
 * 
 * @author Harpreet Singh
 * @date   26 May, 2014
 * @version 1.0
 */
class Event_Model_Events extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();

        $this->_name = 'survey_events';
    }
    
    public function getCountEventsBySurveyName($params){
//        $query = $this->select()->setIntegrityCheck(false)
//                ->from(array('surveys'),'count(*) as COUNT')
//                ->joinInner(array('ea' => 'survey_event_answers'), 'se.eventid = ea.eventid',array());
//        
        $query = $this->select()->setIntegrityCheck(false)
                ->from(array('se' => $this->_name),array())
                ->joinInner(array('s' => 'surveys'), 's.survey_id = se.survey_id',array('count(*) AS COUNT'))
                ->joinInner(array('ea' => 'survey_event_answers'), 'se.eventid = ea.eventid',array());
        
        
        if(is_array($params)){
             foreach($params as $field => $val){
                if((!is_array($val) && !empty($val)) && $field!='ea.response_options'){
                    $query->where($field. $val );
                }
		else{
                    if(!empty($val)) {
                        $query->where(' FIND_IN_SET('.$val.','.$field.')');
                    }
                }
             }
         }  
         $result = $query->query()->fetchAll();
        
        return $result;
    }
    
    public function getCountEventsStatusBySurveyName($params){
//        $query = $this->select()->setIntegrityCheck(false)
//                ->from(array('surveys'),'count(*) as COUNT')
//                ->joinInner(array('ea' => 'survey_event_answers'), 'se.eventid = ea.eventid',array());
//        
        $query = $this->select()->setIntegrityCheck(false)
                ->from(array('se' => $this->_name),array())
                ->joinInner(array('s' => 'surveys'), 's.survey_id = se.survey_id',array('count(*) AS COUNT'))
				 ->where('se.event_status <> "preview"');
                //->joinInner(array('ea' => 'survey_event_answers'), 'se.eventid = ea.eventid',array());
        
        
        if(is_array($params)){
             foreach($params as $field => $val){
                 if(!is_array($val) && !empty($val)){
                    $query->where($field. $val );
                 }
             }
         }   
         $result = $query->query()->fetchAll();
        
        return $result;
    }
    
    public function checkRecordExist($surveyid = 0){ 
        $query = $this->select()->setIntegrityCheck(false)
                ->from('surveys')
                
                ->where('survey_id = '.$surveyid)//->__tostring();
                ->query()->fetch();
        return $query;
    }


    public function getEventsBySurveyName($where, $params = array()){
        $query = $this->select()->setIntegrityCheck(false)
                ->from(array('se' => $this->_name),array('event_id' => 'se.eventid','employee_name' => 'se.employee_name','employee_department' => 'se.employee_department','employee_id' => 'se.employee_id'
                    ,'email'=>'se.email','event_status' => 'se.event_status','event_date' => 'se.event_date','survey_date' => 'se.survey_date'))
                ->joinInner(array('s' => 'surveys'), 's.survey_id = se.survey_id',array('survey_name'))
                ->joinLeft(array('ea' => 'survey_event_answers'), 'se.eventid = ea.eventid',array());
//                ->where('se.survey_id = '.$params['surveyid']);
        if(is_array($where)) {
            foreach($where as $field => $val) {
                if((!is_array($val) && !empty($val)) && $field!='ea.response_options') {
                   $query->where($field. $val );
                }
                else {
                    if(!empty($val)) {
                        $query->where(' FIND_IN_SET('.$val.','.$field.')');
                    }
                }
            }
        }
        if((array_key_exists("se.event_status", $where)) && $where["se.event_status"] != ' = "Open"') {
            $query->group('ea.eventid');
        }
		
        if ( !empty($params) ) {
                $query->order($params['orderBy'])
                        ->limit($params['length'],$params['start']);        	
        }
        $result = $query->query()->fetchAll();
        return $result;
                
    }
    
    public function getEventsStatusBySurveyName($params,$where){
        
         $query = $this->select()->setIntegrityCheck(false)
                ->from(array('se' => $this->_name),array('event_id' => 'se.eventid','employee_name' => 'se.employee_name','employee_department' => 'se.employee_department','employee_id' => 'se.employee_id'
                    ,'email'=>'se.email','event_status' => 'se.event_status','event_date' => 'se.event_date','survey_date' => 'se.survey_date'))
                ->joinInner(array('s' => 'surveys'), 's.survey_id = se.survey_id',array('survey_name'))
                //->joinLeft(array('ea' => 'survey_event_answers'), 'se.eventid = ea.eventid',array());
               ->where('se.event_status <> "preview"');
         if(is_array($where)){
             foreach($where as $field => $val){
                 if(!is_array($val) && !empty($val)){
                    $query->where($field. $val );
                 }
             }
         }   
        $query ->order($params['orderBy'])
                ->limit($params['length'],$params['start']);
       //  echo $query;die;
       //  $query->group('ea.eventid');
        $result = $query->query()->fetchAll();
        return $result;
                
    }

        /**
     * Method to call SP and get events data
     * @param type $spName
     * @param type $inParam
     * @return type
     */
    public function getEventsData($spName, $inParam, $debug = FALSE) {
        return $this->_spObj->getSpData($spName, $inParam, $debug);
    }

    /**
     * Method to return data range for the specified column
     * @param type $column
     * @return type
     */
    public function getDateRange($column) {
        $rs = $this->select()
                ->from($this->_name, array('min_date' => 'MIN(' . $column . ')',
                    'max_date' => 'MAX(' . $column . ')'))
                ->where($column . ' <> "0000-00-00 00:00:00" ');
        return $rs->query()->fetch();
    }

    /**
     * Method to return vehicle models
     * @return type
     */
    public function getModels() {
        $rs = $this->select()
                ->from('customers', array('model' => 'DISTINCT(vehicle_code_desc)'))
                ->setIntegrityCheck(FALSE)
                ->order(array('model ASC'));
        return $rs->query()->fetchAll();
    }

    /**
     * Method to return survey invitees
     * @return type
     */
    public function getSurveyInvitees() {
        $rs = $this->select()
                ->from(array('se' => $this->_name), array('se.eventid', 'cus.email_address', 'se.event_typeid',
                    'cus.langid', 'cus.first_name', 'cus.surname', 'cus.title',
                    'cus.vehicle_code_desc', 'cus.dealer_name'))
                ->joinleft(array('cus' => 'customers'), 'se.customerid = cus.id', array())
                ->joinleft(array('o' => 'survey_event_optouts'), 'se.eventid = o.eventid AND cus.email_address = o.email', array())
                ->where('se.event_status = "Open" AND se.email_sent != "Yes"'
                        . ' AND o.email IS NULL')
                ->setIntegrityCheck(FALSE);
        return $rs->query()->fetchAll();
    }

    public function getEventDetail($eventid, $arrcond = '' ,$preview = '') {
		
         $resource = $this->db->select()
                ->from(array('se' => 'survey_events'), array('eventid', 'event_typeid',  'email_sent',
                    'event_status', 'survey_date', 'event_date',  'reminder_date','survey_id','email','survey_code'))
                ->joinInner(array('et' => 'survey_event_types'), 'se.event_typeid=et.event_typeid', array('event_type'))
                ->joinInner(array('s' => 'surveys'), 's.survey_id=se.survey_id', array(''))
                ->where("eventid = ?", $eventid);

		if($preview == ''){
			$resource->where("s.end_date >= ?", Date('Y-m-d'));
		}
                //->__tostring();die;


        if (!empty($arrcond)) {//echo "zsfdfd";die;
            foreach ($arrcond as $key => $val) {
                if (is_array($val)) {
                    $resource->where($key . " in (?) ", array($val));
                } elseif (!empty($val)) {
                    $resource->where($key . " = ? ", $val);
                } else {
                    $resource->where($key);
                }
            }
            //echo $resource->__tostring();die;
        } else {
            // echo "wwwwwwwwwwwwwwwwwwww";die;
        }
        
        $resource = $resource->query()
                ->fetch(); /**/
        return $resource;
    }
    
    public function getEventDetailScoreCard($eventid, $arrcond = '') {
         $resource = $this->db->select()
                ->from(array('se' => 'survey_events'), array('eventid', 'event_typeid',  'email_sent',
                    'event_status', 'survey_date', 'event_date',  'reminder_date','survey_id','email','survey_code'))
                ->joinInner(array('et' => 'survey_event_types'), 'se.event_typeid=et.event_typeid', array('event_type'))
                ->joinInner(array('s' => 'surveys'), 's.survey_id=se.survey_id', array(''))
                ->where("eventid = ?", $eventid);
                //->where("s.end_date >= ?", Date('Y-m-d'));//->__tostring();die;
                //->__tostring();die;


        if (!empty($arrcond)) {//echo "zsfdfd";die;
            foreach ($arrcond as $key => $val) {
                if (is_array($val)) {
                    $resource->where($key . " in (?) ", array($val));
                } elseif (!empty($val)) {
                    $resource->where($key . " = ? ", $val);
                } else {
                    $resource->where($key);
                }
            }
            //echo $resource->__tostring();die;
        } else {
            // echo "wwwwwwwwwwwwwwwwwwww";die;
        }
        
        $resource = $resource->query()
                ->fetch(); /**/
        return $resource;
    }

    /**
     * Method to return customer for survey reminders
     * @return type
     */
    public function getCustomersForReminder() {
        $rs = $this->select()
                ->from(array('se' => $this->_name), array('se.eventid', 'cus.email_address', 'se.event_typeid',
                    'cus.langid', 'cus.first_name', 'cus.surname', 'cus.title',
                    'cus.vehicle_code_desc', 'cus.dealer_name', 'se.email_send_date',
                    'se.num_reminders_sent'))
                ->joinleft(array('cus' => 'customers'), 'se.customerid = cus.id', array())
                ->joinleft(array('o' => 'survey_event_optouts'), 'se.eventid = o.eventid AND cus.email_address = o.email', array())
                ->where(' ( se.event_status = "Open" OR se.event_status = "In progress" ) '
                        . ' AND DATEDIFF(NOW(), se.`email_send_date`) > "7" '
                        . ' AND o.email IS NULL AND se.is_service_event = "0" ')
                ->setIntegrityCheck(FALSE);
        return $rs->query()->fetchAll();
    }

    /**
     * Method to return customer details for event using event id
     * @return type
     */
    public function getEventCustomer($eventID) {
        $rs = $this->select()
                ->from(array('se' => $this->_name), array('se.eventid', 'cus.email_address', 'se.event_typeid',
                    'cus.langid', 'cus.first_name', 'cus.surname', 'cus.title',
                    'cus.vehicle_code_desc', 'cus.dealer_name', 'se.email_send_date'))
                ->joininner(array('cus' => 'customers'), 'se.customerid = cus.id', array("id"))
                ->where('se.eventid = "' . $eventID . '"')
                ->setIntegrityCheck(FALSE);
        return $rs->query()->fetch();
    }

    /**
     * Method to return customer details for event using customer id
     * @return type
     */
    public function getCustomerEventDetails($customerid,$roleid='',$dealerlist='') {
        $fetchrows = $this->db->select()
                ->from(array('s' => $this->_name), array('s.eventid', 's.event_typeid', 's.event_status',
                    'event_date' => new Zend_Db_Expr("if(s.`event_date`='0000-00-00','',date_format(s.`event_date` ,'%d/%m/%Y'))"), 's.survey_date', 's.dealer_id'))
                ->joinInner(array('cust' => 'customers'), 'cust.id=s.customerid', array('cust.id', 'cust.title', 'cust.first_name', 'cust.surname', 'cust.vin', 'cust.email_address'))
                ->joinInner(array('d' => 'dealers'), 'd.id=s.dealer_id', array('dealer_name'))
                ->joinInner(array('e' => 'survey_event_types'), 's.event_typeid=e.event_typeid', array('e.event_type'))
                ->where("s.customerid = ?", $customerid)
                ->where("d.is_deleted = '0' ")
                // ->where("s.is_anonymous IS NULL ") 
                //->where("s.is_anonymous IS NULL or  s.is_anonymous = 0")
                 ->where("if($roleid = 4, (s.is_anonymous IS NULL OR  s.is_anonymous=0),1) AND if(s.event_status='Expired' AND (s.num_reminders_sent<2),0,1)") 
                ->where("if( $roleid = 4, (s.event_typeid <> 2),1)") 
                ->where('s.event_status <>?', 'Supressed');
        
        if ($dealerlist != '') {
            $fetchrows = $fetchrows->where(' s.dealer_id IN (' . $dealerlist . ') ');//->__tostring();;die;
        }
                
        $fetchrows = $fetchrows->order(array('s.eventid DESC'))//->__tostring();die;
                ->query()
                ->fetchall();
        return $fetchrows;
    }

    public function getDealerEventDetails($dealerid,$searchkeyword='',$startdate='',$enddate='',$eventtype='') {
      
        $rs = $this->db->select()
                ->from(array('s' => $this->_name), array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.eventid'), 's.event_typeid', 's.event_status','s.dealer_id',
                    'event_date' => new Zend_Db_Expr("if(s.`event_date`='0000-00-00','',date_format(s.`event_date` ,'%d/%m/%Y'))"), 's.survey_date', 's.dealer_id','s.actual_score'))
                ->joinInner(array('cust' => 'customers'), 'cust.id=s.customerid', array('cust.id', 'cust.title', 'cust.first_name', 'cust.surname', 'cust.vin', 'cust.email_address','cust.vehicle_code_desc'))
                ->joinInner(array('d' => 'dealers'), 'd.id=s.dealer_id', array('dealer_name'))
                ->joinInner(array('e' => 'survey_event_types'), 's.event_typeid=e.event_typeid', array('e.event_type'))
                ->where("s.dealer_id = ?", $dealerid)
                ->where("d.is_deleted = '0' ")
                ->where("s.event_status = 'Closed' ")
                
                ->order(array('s.eventid DESC'));
       if ($searchkeyword != '') {
           $searchkeyword = trim($searchkeyword);
             $rs = $rs->where("s.eventid like '%" . addslashes($searchkeyword) . "%'"
                    . " OR cust.title like '%" . addslashes($searchkeyword) . "%'"
                    . " OR cust.first_name like '%" . addslashes($searchkeyword) . "%'"
                    . " OR cust.surname like '%" . addslashes($searchkeyword) . "%'"
                    . " OR cust.registration_number like '%" . addslashes($searchkeyword) . "%'"
                    . " OR cust.vin like '%" . addslashes($searchkeyword) . "%'"
                    . " OR concat(IF(cust.title IS NULL,'',cust.title),' ',IF(cust.first_name IS NULL,'',cust.first_name),' ',IF(cust.surname IS NULL,'',cust.surname)) LIKE '%". addslashes($searchkeyword) ."%'"
                    . " OR cust.vehicle_code_desc like '%" . addslashes($searchkeyword) . "%'");
        }
        if($startdate!=''&& $enddate!='')
        {
            $rs=$rs->where('s.survey_date BETWEEN "' . $startdate . '" AND "' . $enddate . '"');
        }
        if(!empty($eventtype))
        {
            $rs=$rs->where('s.event_typeid ="'.$eventtype.'"');
        }
    else {
             $rs=$rs->where("s.event_typeid <> '2' ");
         }
                // ->where("s.is_anonymous IS NULL ") 
              //  ->where("s.is_anonymous IS NULL or  s.is_anonymous = 0")
         //echo $rs->__tostring();;
           $fetchrows[0]=$rs//->__tostring();die;
                ->query()
                ->fetchall();
        $fetchrows[1]['totalcount'] = $this->db->fetchOne('select FOUND_ROWS()');
        
        return $fetchrows;
    }
    
    /**
     * Method to return ip tracker report details
     * @return type
     */
    public function getIpTrackerData($spName, $inParam, $debug = FALSE) {
        return $this->_spObj->getSpData($spName, $inParam, $debug);
    }
    
    
    public function getRawData($eventtype,$model,$dealerid,$daterangefield='survey_submission_date',$startdate,$enddate,$searchkeyword,$order) {
          
        $startdate=$startdate." 00:00:00";
        $enddate=$enddate." 23:59:59";
        
	if($order== '')
        {
             $order = ' s.`survey_date` DESC';
        }
        if($daterangefield=='email_send_date')
        {
            $daterangefield="s.email_send_date";
        }
        elseif($daterangefield=='event_date')
        {
            $daterangefield="s.event_date";
        }
        else
        {
            $daterangefield="s.survey_date";
        }
        $rs = $this->select()
              ->from(array('s' => $this->_name), array('cus.vin','cus.vehicle_code','cus.vehicle_code_desc','l.lang_name','s.eventid','date_format(s.event_date , \'%d/%m/%Y\') as event_date',
                                                       'date_format(s.survey_date , \'%d/%m/%Y\') as survey_date','c.country_name','d.dealershipid','d.dealer_name'))
              ->joininner(array('cus' => 'customers'), 's.customerid = cus.id', array())
              ->joinInner(array('d' => 'dealers'),'d.id=s.dealer_id', array())
              ->joinInner(array('l' => 'languages'),'l.langid=s.langid',array())
              ->joinInner(array('c' => 'countries'),'c.country_code=cus.country_code',array())
              ->joinInner(array('sea' => 'survey_event_answers'),'sea.eventid=s.eventid',
                      array('GROUP_CONCAT("q_",sea.questionid,"|a_",sea.response_options SEPARATOR "#~#") AS question_answers',
                            'GROUP_CONCAT("q_",sea.questionid,"|a_",sea.answer1 SEPARATOR "#~#")   AS answer1'
                          ))
              ->where($daterangefield.' BETWEEN "' . $startdate . '" AND "' . $enddate . '"')
              ->where('s.event_status="Closed"')
              ->order($order)
              ->group('s.eventid')
              ->setIntegrityCheck(FALSE);
        if(!empty($eventtype))
        {
            $rs=$rs->where('s.event_typeid ="'.$eventtype.'"');
        }
        if(!empty($model))
        {
            $rs=$rs->where('cus.vehicle_code_desc ="'.$model.'"');
        }
        if(!empty($dealerid))
        {
            $rs=$rs->where("s.dealer_id IN (" . $dealerid . ")");
        }
        if ($searchkeyword != '') {
          $searchkeyword = trim($searchkeyword);
            $rs = $rs->where("s.eventid like '%" . addslashes($searchkeyword) . "%'"
                   . " OR cus.title like '%" . addslashes($searchkeyword) . "%'"
                   . " OR cus.first_name like '%" . addslashes($searchkeyword) . "%'"
                   . " OR cus.surname like '%" . addslashes($searchkeyword) . "%'"
                   . " OR cus.registration_number like '%" . addslashes($searchkeyword) . "%'"
                   . " OR cus.vin like '%" . addslashes($searchkeyword) . "%'"
                   . " OR concat(IF(cus.title IS NULL,'',cus.title),' ',IF(cus.first_name IS NULL,'',cus.first_name),' ',"
                   . "IF(cus.surname IS NULL,'',cus.surname)) LIKE '%". addslashes($searchkeyword) ."%'"
                   . " OR cus.vehicle_code_desc like '%" . addslashes($searchkeyword) . "%'");
        }
      // echo $rs->__toString();//die;
        return $rs->query()->fetchall();
    }
    
    public function getInvalidRecords( ) {
        $subSelect = $this->select()
                        ->from(array('e' => $this->_name), array('e.eventid'))
                        ->where(' e.event_status = "Closed" ')
                        ->where(' e.survey_date > "2014-07-28" ')
                        ->where(' e.event_typeid = "1" ');
        
        $subSelect2 = $this->select()
                        ->from(array('sea' => 'survey_event_answers'), array('sea.eventid'))
                        ->where(' sea.questionid = "2" ')
                        ->where(' sea.response_options > "1" ')
                        ->setIntegrityCheck(FALSE);
        
        $rs = $this->select()
                ->from(array( 'sa' => 'survey_event_answers' ), array('sa.eventid') )
                ->where(" sa.eventid IN ($subSelect) ")
                ->where(" sa.eventid NOT IN ($subSelect2) ")
                ->group('sa.eventid')
                ->having(" SUM( IF ( sa.questionid = '7', '1', '0' ) ) < 1 ")
                ->setIntegrityCheck(FALSE);
//        echo $rs->__toString();die;
        return $rs->query()->fetchall();
    }
    
    public function updateInvalidRecord( $eventID ) {
        $subSelect = $this->select()
                        ->from(array('sea' => 'survey_event_answers'), array(new Zend_Db_Expr($eventID),
                            'questionid', 'response_options','answer1','score', 'answer_date',
                            'code_status', 'questiontext'))
                        ->where(' eventid = "83860" ')
                        ->where(' questionid = "7" ')
                        ->order('eventid')
                        ->setIntegrityCheck(FALSE)
                        ->__toString();
        
        $this->_db->query(' INSERT INTO `survey_event_answers` ( `eventid`, 
            `questionid`, `response_options`,`answer1`, `score`, `answer_date`, 
            `code_status`,  `questiontext` ) ' . $subSelect );
        
        $this->update(array('code_status' => 'Green',
            'satisfaction_percent' => '100.00',
            'actual_score' => '10.00',
            'max_score' => '10.00'), ' eventid = ' . $eventID );
    }
    
    public function getEventDetailByID($eventid, $arrcond = '') {
         $resource = $this->db->select()
                ->from(array('se' => 'survey_events'), array('eventid', 'event_typeid',  'email_sent','email_send_date',
                    'event_status', 'survey_date', 'event_date',  'reminder_date','survey_id','email','survey_code','event_typeid','employee_name'))
                ->joinInner(array('et' => 'survey_event_types'), 'se.event_typeid=et.event_typeid', array('event_type'))
                ->joinInner(array('s' => 'surveys'), 's.survey_id=se.survey_id', array('survey_name','start_date','end_date')) 
                ->where("eventid = ?", $eventid);//->__tostring();die;
                //->__tostring();die;

        return $resource->query()->fetchall();
    }

}
