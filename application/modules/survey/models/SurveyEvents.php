<?php

/**
 * Model class to handle Alerts Log Table operations
 */
/*
 * Created On : 17/06/2014
 * Created By : Sandeep Pathak
 * Email : sandeepp@damcogroup.com
 */

class Survey_Model_SurveyEvents extends Default_Model_Core {

    /**
     * Constructor to initialize Scheduled Emails model class
     */
    public function __construct() 
    {
        parent::__construct();
        $this->_name = 'survey_events';
    }
    
    public function saveData($data)
    {//print_r($data);die;
        $rs = $this->db->insert($this->_name,$data);
        $lastInsertId = $this->db->lastInsertId();
        
        return $lastInsertId;
    }
    
    public function getSurveyStatusData($data)
    {
       $whereString = '';
       $orderString = '';
       if(isset($data['start_date']) && $data['start_date']!='' && isset($data['end_date']) && $data['end_date']!='')
       {
           //$whereString =  "AND (se.survey_date BETWEEN '".$data['start_date']."' AND '".$data['end_date']."')";
           $whereString = "AND (se.survey_date BETWEEN '".$data['start_date']."' AND '".$data['end_date']."')";
       }
       if(isset($data['orderBy']) && $data['orderBy']!='' && isset($data['start']) && $data['start'] != '' && isset($data['length']) && $data['length']!='')
       {
           $orderString .=  " Order By ".$data['orderBy']." LIMIT ".$data['start'].",".$data['length'];
       }
        $sql = "SELECT
                        s.survey_id,
                        s.survey_name AS SurveyName,
                        COUNT(se.event_status) AS Total,
                        SUM(IF(se.event_status = 'Closed',1,0)) AS Closed,
                        SUM(IF(se.event_status = 'Open',1,0)) AS Open,
                        SUM(IF(se.event_status = 'In progress',1,0)) AS InProgress,
					    (CASE WHEN CURDATE() < s.start_date THEN 'Scheduled'
				WHEN CURDATE() <= s.end_date THEN 'In Progress'	ELSE 'Closed' END) AS STATUS,
				s.start_date,s.end_date
                FROM
                        survey_events se
                INNER JOIN
                        surveys	s ON s.survey_id = se.survey_id	
                WHERE
                    se.event_status<>'Preview' ";
				if($data['event_type_id']!="") {
					$sql .= " AND se.event_typeid = ".$data['event_type_id']." ";
				}
				$sql .=' '.$whereString. " GROUP BY
                        s.survey_name ".$orderString;
        
        
        
        $result = $this->db->query($sql)->fetchAll();         
        
        return $result;    
       
       
       /* $rs = $this->db->select()
                ->from(array($this->_name),array('event_typeid','event_type','description','department','department_id'))
                //->where("event_typeid = ? ", $eventTypeId)
                ->order($data['orderBy'])
                ->limit($data['length'],$data['start']);
                
        $result= $rs->query()->fetchAll();
        return $result; */
    }
    
    public function getCountData($data)
    {
        $sql = "SELECT
                         COUNT(DISTINCT(se.survey_id)) AS COUNT
                FROM
                         survey_events se
                 INNER JOIN
                        surveys	s ON s.survey_id = se.survey_id	
                WHERE
						se.event_status<>'Preview'" ;
		if($data['event_type_id'] != "") {
			$sql .= " AND se.event_typeid =  ".$data['event_type_id'];
		}
        
        
        $result = $this->db->query($sql)->fetchAll();     
        
        
	return $result;    
    }
    
    public function getSurveyStatusExcelData($eventTypeID)
    {
        
        $sql = "SELECT
                        s.survey_name AS SurveyName,
                        COUNT(se.event_status) AS Total,
                        SUM(IF(se.event_status = 'Closed',1,0)) AS Closed,
                        SUM(IF(se.event_status = 'Open',1,0)) AS Open,
                        SUM(IF(se.event_status = 'In progress',1,0)) AS InProgress,
						(CASE WHEN CURDATE() < s.start_date THEN 'Scheduled'
				WHEN CURDATE() <= s.end_date THEN 'In Progress'	ELSE 'Closed' END) AS STATUS
                FROM
                        survey_events se
                INNER JOIN
                        surveys	s ON s.survey_id = se.survey_id	
                WHERE
                        se.event_typeid =  ".$eventTypeID.
                " GROUP BY
                        s.survey_name 
                  Order By 
                    SurveyName DESC";
        
        
        $result = $this->db->query($sql)->fetchAll();         
        
        return $result;    
       
       
       /* $rs = $this->db->select()
                ->from(array($this->_name),array('event_typeid','event_type','description','department','department_id'))
                //->where("event_typeid = ? ", $eventTypeId)
                ->order($data['orderBy'])
                ->limit($data['length'],$data['start']);
                
        $result= $rs->query()->fetchAll();
        return $result; */
    }

	public function getReminderMailById($surveyId){



		  $rs = $this->select()->setIntegrityCheck(false)
			->from(array('seq' => $this->_name))
			->joinInner(array('sea'=>'surveys'), 'seq.survey_id = sea.survey_id',array('email_subject'))                       
			->where("seq.survey_id = ?", $surveyId)
            ->where("seq.event_status ='Open' OR seq.event_status ='In progress'");//->__toString();die;		
			//->ORwhere("seq.event_status = ?", 'In progress')
		
			
                
        $result= $rs->query()->fetchAll();
        return $result;
	}

	public function getSurveyeventsDatabyId($Id){

		$rs = $this->select()
			->from(array('seq' => $this->_name))			                    
			->where("seq.eventid = ?", $Id);      
        $result= $rs->query()->fetchAll();
		
        return $result;

	}

	public function checkDataforpreview($SurveyId){

		$rs = $this->select()
			->from(array('seq' => $this->_name))			                    
			->where("seq.survey_id = ?", $SurveyId)
			->where("seq.event_status='preview'");//->__toString();   
        $result= $rs->query()->fetchAll();
		
        return $result;

	}
	
	public function exceldataconsolidate($SurveyId,$startDate,$endDate){
		
		$rs = $this->select()->setIntegrityCheck(false)
			->distinct()
			->from(array('se' => $this->_name),array('se.employee_id AS EmployeeId','se.eventid', 'se.employee_name AS EmployeeName',
				'se.email AS Employeeemail','se.survey_date'))
			->joinInner(array('sea'=>'survey_event_answers'), 'se.eventid = sea.eventid',array())
		    ->joinInner(array('seq'=>'survey_event_question_langs'), 'seq.questionid = sea.questionid',array())
			->joinInner(array('s'=>'surveys'), 's.survey_id = se.survey_id',array())
			->joinInner(array('seta'=>'survey_event_types'), 'seta.event_typeid = se.event_typeid',array())
			->joinInner(array('seql'=>'survey_event_questions'), 'seql.questionid = sea.questionid',array())
			->where("s.survey_id = ?", $SurveyId)
			->where("se.event_status = ?", 'Closed');//->__toString();die;
			
		if(isset($startDate) && $startDate!='' && isset($endDate) && $endDate!=''){
           $startDate = $startDate." 00:00:00";
		   $endDate = $endDate." 23:59:59";
		   $rs->where("se.survey_date >=?",$startDate);
		   $rs->where("se.survey_date <=?",$endDate); 
			
        }
	//echo $rs->__toString();die;
		 $result= $rs->query()->fetchAll();
		
		/*$sql = "SELECT   DISTINCT se.employee_id AS EmployeeId, se.eventid, se.employee_name AS EmployeeName,
			 se.email AS Employeeemail FROM  `survey_events` se
			 INNER JOIN survey_event_answers sea  ON se.eventid = sea.eventid 
			 INNER JOIN `survey_event_question_langs` seq ON seq.questionid = sea.questionid 
			INNER JOIN `surveys` s 	 ON s.survey_id = se.survey_id 
			INNER JOIN `survey_event_types` seta ON seta.event_typeid = se.event_typeid 
			INNER JOIN `survey_event_questions` seql ON seql.questionid = sea.questionid WHERE s.survey_id =".$SurveyId;
		$result = $this->db->query($sql)->fetchAll();    */
		
	    return $result;
		
		
	}

}