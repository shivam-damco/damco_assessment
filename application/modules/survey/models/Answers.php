<?php

/**
 * Model class to handle survey events operations
 * 
 * @author Harpreet Singh
 * @date   26 May, 2014
 * @version 1.0
 */

class Survey_Model_Answers extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();
        $this->_objconfig = new Survey_Model_Config();
        $this->_name = 'survey_event_answers';
    }
    
    //get user response for a particular question
    public function getAnswers($tbl_nm,$eventid,$qid)
    {
        $rs_answer = $this->select()->setIntegrityCheck(false)
			->from(array('sea'=>$tbl_nm),'*')
			->where("sea.eventid = ?",$eventid)
			->where("sea.questionid in (?) ",$qid)//->__toString();die;		
			->query();
        
        if(is_array($qid))
        {
            $rs_answer =$rs_answer->fetchAll();
        }
        else
        {
            $rs_answer =$rs_answer->fetch();
        }
		
        return $rs_answer;
    }
    
    //get user response for a particular question
    public function getQuestionIDBasedAnswers($eventid,$ID)
    {
            
        $rs_answer = $this->select()->setIntegrityCheck(false)
			->from(array('seq'=>'survey_event_questions'),array("questionid", "event_typeid","ID", "question_type","question_number", "display_page", "input_type","action_response_a", 
	"action_goto_a","action_response_b", "action_goto_b", "action_response_c", "action_goto_c", "is_archive", 
	"groupid", "group_last_ques", "is_branching", "is_participate_in_branching","is_parent", "parent_id"))
			->joinInner(array('seql'=>'survey_event_question_langs'), 'seq.questionid = seql.questionid',array('question','response1', 'response2', 'response3', 'response4', 'response5', 'response6', 
	'response7', 'response8', 'response9','response10', 'response11'))
                        ->joinInner(array('sea' => 'survey_event_answers'), 'seq.questionid = sea.questionid')
                        ->where("langid = ?", 1)
			->where("sea.eventid = ?",$eventid)
			->where("seq.ID = ?", $ID)//->__toString();die;		
			->query()
                        ->fetch();      
			
        return $rs_answer;
    }
    
    //get count of answers
     public function getCountAnswers($tbl_nm,$eventid,$qid)
    {
         $arrQanswd = $this->select()->setIntegrityCheck(false)
                    ->from(array('sea'=>$tbl_nm), 'count(*) as cnt')
                    ->where("sea.eventid = ?",$eventid)
                  //  ->where('questionid < ?', $qid)//->__toString();die;
                    ->query()
                   ->fetch();          
        return $arrQanswd;
    }
    
   public function deleteExtraAnswers($eventid,$questionids)
   {
       $sql = "delete from survey_event_answers where eventid='".$eventid."' and questionid not in (".$questionids.");";
       $this->db->query($sql);       
       
   }
   
   public function deleteAnswers($eventid)
   {
       $this->db->delete('survey_event_answers', 'eventid = ' . $eventid);
       //$this->db->query($sql);       
       
   }

   public function isScoringQuestionAttempted($event_typeid,$eventid) 
   {
        switch($event_typeid)
        {
            case "1":  $config_token ="sales_NPS_question_id";    break;
            //case "2": $config_token ="product_NPS_question_id";     break;
            case "3": $config_token ="service_NPS_question_id";     break;
            default : $config_token =""; break;

        }
       // echo "xdgdf".$config_token;die;
        if(!empty($config_token))
        {
            $qid = $this->_objconfig->findRow("config_val",array("config_var"=>$config_token));
            $attempt = "N";   
            if($qid["config_val"] > 0)
            {
                $getAnswer = $this->getAnswers("survey_event_answers",$eventid,$qid["config_val"]);
             // echo "dxfgd";  var_dump($getAnswer);die;
                if(isset($getAnswer["response_options"]) && !empty($getAnswer["response_options"])) 
                {
                    $attempt = "Y";
                }

            }
            return $attempt;
        }
   }    
   
   public function getAllExcelData($surveyID,$startDate,$endDate)
   {
       $whereString = '';
       if(isset($startDate) && $startDate!='' && isset($endDate) && $endDate!='')
       {
           $startDate=$startDate. ' 00:00:00';
		   $endDate=$endDate. ' 23:59:59';
		   //$whereString =  "AND (se.survey_date BETWEEN '".$data['start_date']."' AND '".$data['end_date']."')";
           $whereString = "AND (se.survey_date BETWEEN '".$startDate."' AND '".$endDate."')";
       }
       $sql = "SELECT
                    se.employee_id AS EmployeeId,           
                    se.employee_name AS EmployeeName,
                    se.email AS Employeeemail,
                    seq.question AS Qusetion,
                    seq.question AS Qusetion,
                    #IF(sea.answer1 <> '',sea.answer1,IF(sea.answer2 <> '',sea.answer2,IF(sea.answer3 <> '',sea.answer3,IF(sea.answer4 <> '',sea.answer4,IF(sea.answer5 <> '',sea.answer5,IF(sea.answer6 <> '',sea.answer6,IF(sea.answer7 <> '',sea.answer7,IF(sea.answer8 <> '',sea.answer8,IF(sea.answer9 <> '',sea.answer9,IF(sea.answer10 <> '',sea.answer10,sea.answer11)))))))))) AS Answer
                    IF(seql.`input_type` <> 'checkbox',(IF(sea.answer1 <> '',sea.answer1,IF(sea.answer2 <> '',sea.answer2,IF(sea.answer3<> '',sea.answer3,IF(sea.answer4 <> '',sea.answer4,IF(sea.answer5 <> '',sea.answer5,IF(sea.answer6 <> '',sea.answer6,IF(sea.answer7 <> '',sea.answer7,IF(sea.answer8 <> '',sea.answer8,IF(sea.answer9 <> '',sea.answer9,IF(sea.answer10 <> '',sea.answer10,sea.answer11))))))))))),(CONCAT(IF(sea.answer1 <> '',CONCAT(sea.answer1,','),''),IF(sea.answer2 <> '',CONCAT(sea.answer2,','),''),IF(sea.answer3 <> '',CONCAT(sea.answer3,','),''),IF(sea.answer4 <> '',CONCAT(sea.answer4,','),''),IF(sea.answer5 <> '',CONCAT(sea.answer5,','),''),IF(sea.answer6 <> '',CONCAT(sea.answer6,','),''),IF(sea.answer7 <> '',CONCAT(sea.answer7,','),''),IF(sea.answer8 <> '',CONCAT(sea.answer8,','),''),IF(sea.answer9 <> '',CONCAT(sea.answer9,','),''),IF(sea.answer10 <> '',CONCAT(sea.answer10,','),''),IF(sea.answer11 <> '',CONCAT(sea.answer11,','),'')))) AS Answer
                FROM
                    survey_event_answers sea
                INNER JOIN
                    `survey_event_question_langs` seq 	
                    ON seq.questionid = sea.questionid
                INNER JOIN
                    `survey_events`	se
                    ON se.eventid = sea.eventid
                INNER JOIN
                    `surveys` s
                    ON s.survey_id = se.survey_id
                INNER JOIN
                    `survey_event_types` seta
                    ON seta.event_typeid = 	se.event_typeid
                INNER JOIN
                    `survey_event_questions` seql
                    ON seql.questionid = 	sea.questionid     
                WHERE
					se.event_status='Closed' AND
                    s.survey_id = ".$surveyID." ".$whereString." ORDER BY EmployeeId";
        $result = $this->db->query($sql)->fetchAll();         
       
	return $result;
   }
   
   public function getEventTypesData($data)
   {
       $whereString = '';
       if(isset($data['start_date']) && $data['start_date']!='' && isset($data['end_date']) && $data['end_date']!='')
       {
           //$whereString =  "AND (se.survey_date BETWEEN '".$data['start_date']."' AND '".$data['end_date']."')";
           $whereString = "AND (se.survey_date BETWEEN '".$data['start_date']." 00:00:00' AND '".$data['end_date']." 23:59:59')";
       }
       if(isset($data['orderBy']) && $data['orderBy']!='' && isset($data['start']) && $data['start'] != '' && isset($data['length']) && $data['length']!='')
       {
           $whereString .=  " Order By ".$data['orderBy']." LIMIT ".$data['start'].",".$data['length'];
       }
        $sql = "SELECT
                    se.employee_id AS EmployeeId,           
                    se.employee_name AS EmployeeName,
                    se.email AS Employeeemail,
                    seq.question AS Qusetion,
                    #IF(sea.answer1 <> '',sea.answer1,IF(sea.answer2 <> '',sea.answer2,IF(sea.answer3 <> '',sea.answer3,IF(sea.answer4 <> '',sea.answer4,IF(sea.answer5 <> '',sea.answer5,IF(sea.answer6 <> '',sea.answer6,IF(sea.answer7 <> '',sea.answer7,IF(sea.answer8 <> '',sea.answer8,IF(sea.answer9 <> '',sea.answer9,IF(sea.answer10 <> '',sea.answer10,sea.answer11)))))))))) AS Answer
                    IF(seql.`input_type` <> 'checkbox',(IF(sea.answer1 <> '',sea.answer1,IF(sea.answer2 <> '',sea.answer2,IF(sea.answer3<> '',sea.answer3,IF(sea.answer4 <> '',sea.answer4,IF(sea.answer5 <> '',sea.answer5,IF(sea.answer6 <> '',sea.answer6,IF(sea.answer7 <> '',sea.answer7,IF(sea.answer8 <> '',sea.answer8,IF(sea.answer9 <> '',sea.answer9,IF(sea.answer10 <> '',sea.answer10,sea.answer11))))))))))),(CONCAT(IF(sea.answer1 <> '',CONCAT(sea.answer1,','),''),IF(sea.answer2 <> '',CONCAT(sea.answer2,','),''),IF(sea.answer3 <> '',CONCAT(sea.answer3,','),''),IF(sea.answer4 <> '',CONCAT(sea.answer4,','),''),IF(sea.answer5 <> '',CONCAT(sea.answer5,','),''),IF(sea.answer6 <> '',CONCAT(sea.answer6,','),''),IF(sea.answer7 <> '',CONCAT(sea.answer7,','),''),IF(sea.answer8 <> '',CONCAT(sea.answer8,','),''),IF(sea.answer9 <> '',CONCAT(sea.answer9,','),''),IF(sea.answer10 <> '',CONCAT(sea.answer10,','),''),IF(sea.answer11 <> '',CONCAT(sea.answer11,','),'')))) AS Answer
                FROM
                    survey_event_answers sea
                INNER JOIN
                    `survey_event_question_langs` seq 	
                    ON seq.questionid = sea.questionid
                INNER JOIN
                    `survey_events`	se
                    ON se.eventid = sea.eventid
                INNER JOIN
                    `surveys` s
                    ON s.survey_id = se.survey_id
                INNER JOIN
                    `survey_event_types` seta
                    ON seta.event_typeid = 	se.event_typeid
                 INNER JOIN
                    `survey_event_questions` seql
                    ON seql.questionid = 	sea.questionid    
                WHERE
					se.event_status='Closed' AND
                    s.survey_id = ".$data['survey_id']." ".$whereString;
        
        
        
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
       $whereString = '';
  	   if(isset($data['start_date']) && $data['start_date']!='' && isset($data['end_date']) && $data['end_date']!=''){
             
             $whereString = "AND (se.survey_date BETWEEN '".$data['start_date']." 00:00:00' AND '".$data['end_date']." 23:59:59')";
        }
  		 $sql = "SELECT
                      count(se.employee_name) as COUNT
                  FROM
                      survey_event_answers sea
                  INNER JOIN
                      `survey_event_question_langs` seq 	
                      ON seq.questionid = sea.questionid
                  INNER JOIN
                      `survey_events`	se
                      ON se.eventid = sea.eventid
                  INNER JOIN
                      `surveys` s
                      ON s.survey_id = se.survey_id
                  INNER JOIN
                      `survey_event_types` seta
                      ON seta.event_typeid = 	se.event_typeid
                  WHERE se.event_status='Closed' AND
                      s.survey_id = ".$data['survey_id']." ".$whereString;
          
        $result = $this->db->query($sql)->fetchAll();         
            
        return $result;
    }
	
	public function getAnswersByEventId($eventid)
    {
        $rs_answer = $this->select()->setIntegrityCheck(false)
			->from(array('sea'=>$this->_name),array('sea.*', 'seq.max_score', 'seq.weightage'))
                        ->join(array('seq' => 'survey_event_questions'),'seq.questionid = sea.questionid', array())
                        //->joinInner(array('seql' => 'survey_event_question_langs'),'seql.questionid = seq.questionid',array('question'))
			->where("sea.eventid = ?",$eventid)//->__toString();die;	
                        ->order('seq.sort_order')
                        ->order('seq.questionid')
			->query();
        
      $rs_answer = $rs_answer->fetchAll();
       
			
        return $rs_answer;
    }
    
    
}