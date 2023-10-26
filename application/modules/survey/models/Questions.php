<?php

/**
 * Model_DbTable_Custom class.
 * 
 * @extends Model_DbTable_Praxis_Db
 */ 

class Survey_Model_Questions extends Default_Model_Core{
    
    protected $_name = 'survey_event_questions';
    protected $_primary = 'questionid';
    protected $eventType;
    protected $inputType;
    protected $questionNumber;
    protected $questionType;
    protected $maxResLimit;
    protected $question;
    protected $response1;
    protected $response2;
    protected $response3;
    protected $response4;
    protected $response5;
    protected $response6;
    protected $response7;
    protected $response8;
    protected $response9;
    protected $response10;
    protected $response11;
    protected $response12;
    protected $response13;
    protected $response14;
    protected $response15;
    protected $response16;
    protected $response17;
    protected $response18;
    protected $response19;
    protected $response20;    
    protected $primaryId;
	protected $weightage;
    protected $maxUser;
    protected $questionScore;
    protected $score1;
    protected $score2;
    protected $score3;
    protected $score4;
    protected $score5;
    protected $score6;
    protected $score7;
    protected $score8;
    protected $score9;
    protected $score10;
    protected $score11;
    //protected $childQuestions = array();
    
     public function __construct() {
        parent::__construct();
        $this->_objconfig = new Survey_Model_Config();
    }
    
    public function _checkNextQuestion($temparr_n,$i){
        for($cnt = ($i+1) ; $cnt<=count($temparr_n); $cnt++){
            if($temparr_n[$cnt]['input_type'] != ''){
                //$sort_order = ' sort_order = '. ($prev_sort_order) .',';
                $action_go_to = 'ID '.$temparr_n[$cnt]['ID'];
                break;
            } else{
                //$sort_order = ' sort_order = '. ($i+1) .',';

            }
        }
        return $action_go_to;
    }
    
    public function _checkPreviousQuestion($temparr_n,$i,$prev_sort_order){
        //check if previous question is first question and input_type is label then set current question as first_question
                
                if($temparr_n[$i-1]['input_type'] == '' && $temparr_n[$i-1]['first_question'] == 'yes'){
                    $str_sort_order = ' sort_order = '. ($prev_sort_order) .',';
                    $first_que_str = " first_question = 'yes', ";
                    
                } 
                else if($temparr_n[$i-1]['input_type'] == ''){
                    
                    $str_sort_order = ' sort_order = '. ($prev_sort_order) .',';
                    $first_que_str = " first_question = 'no',";
                }
                else {
                    $str_sort_order = ' sort_order = '. ($prev_sort_order+1) .',';
                    $prev_sort_order = $prev_sort_order + 1;
                    $first_que_str = " first_question = 'no',";
                }
                $str = $first_que_str.$str_sort_order.'&'.$prev_sort_order;
                //echo $str;
                return $str;
    }
    public function updateBranching($sort_order,$eventtypeid){
      
        $qry = "Select questionid, ID, question_type, first_question, input_type from survey_event_questions 
            where event_typeid = ". $eventtypeid ." and is_deleted = '0'";
        
        $res = $this->db->query($qry)->fetchAll();
       // print_r($sort_order);die;
        foreach($res as $key=>$val){
            $temparr[$key] = array("questionid"=>$val['questionid'],"ID"=>$val['ID'],
                "question_type"=>$val['question_type'],"first_question"=>$val['first_question'],
                "input_type"=>$val['input_type']);
            if(in_array($val['questionid'], $sort_order))
            {
                $req_key = array_search($val['questionid'], $sort_order);
                if($req_key !== false)
                {
                    $temparr_n[$req_key] = $temparr[$key];
                    
                }
            }
            
        }
        ksort($temparr_n);
         // echo "<pre>";print_r($temparr_n);die;
        $prev_sort_order = 0;
        for($i = 0;$i < count($sort_order);$i++){
            if($i == (count($sort_order)-1)){
                if(count($sort_order) == 1)
                { 
                    $update_last_question = "UPDATE survey_event_questions set sort_order = ". ($i+1) .", first_question = 'yes', 
                    action_response_a = 'ALL', action_goto_a = 'Submit Survey (event is closed)' where questionid = ".$sort_order[$i];
                }
                else if(count($sort_order) == 2)
                {
					$prevRes = $this->_checkPreviousQuestion($temparr_n, $i, $prev_sort_order);
					$arr_prev = explode('&', $prevRes);
					
					
                    $update_last_question = "UPDATE survey_event_questions set ".$arr_prev[0]." 
                    action_response_a = 'ALL', action_goto_a = 'Submit Survey (event is closed)' where questionid = ".$sort_order[$i];
                }
                else 
                {
					$prevRes = $this->_checkPreviousQuestion($temparr_n, $i, $prev_sort_order);
					$arr_prev = explode('&', $prevRes);
                    $update_last_question = "UPDATE survey_event_questions set ".$arr_prev[0]." 
                    action_response_a = 'ALL', action_goto_a = 'Submit Survey (event is closed)' where questionid = ".$sort_order[$i];
                }
                
                $this->db->query($update_last_question)->execute();
            } else if($i == 0){
				
                if($temparr_n[$i]['input_type'] == '')
                {
                    $ID = $this->_checkNextQuestion($temparr_n,$i);
                    $update_first_question = "UPDATE survey_event_questions set sort_order = ". ($i+1) .",
                        first_question = 'yes', action_response_a = 'ALL', action_goto_a = '".$ID."'
                        where questionid = ".$sort_order[$i];
                    $prev_sort_order = $prev_sort_order + 1;
                    $this->db->query($update_first_question)->execute();
                    $temparr_n[$i]['first_question'] = 'yes';
                }
                else
                {
                    $ID = $this->_checkNextQuestion($temparr_n,$i);
                    $update_first_question = "UPDATE survey_event_questions set sort_order = ". ($i+1) .",
                        first_question = 'yes', action_response_a = 'ALL', action_goto_a = '".$ID."'
                        where questionid = ".$sort_order[$i];
                    $prev_sort_order = $prev_sort_order + 1;
                    $this->db->query($update_first_question)->execute();
                    $temparr_n[$i]['first_question'] = 'yes';
                }
            }
            else{
				
                $prevRes = $this->_checkPreviousQuestion($temparr_n, $i, $prev_sort_order);
                $arr_prev = explode('&', $prevRes);
                
                
                $action_go_to = $this->_checkNextQuestion($temparr_n, $i);
                $prev_sort_order = $arr_prev[1];
				
              
                $update_question = "UPDATE survey_event_questions set ". $arr_prev[0] ."  action_response_a = 'ALL', 
                    action_goto_a = '".$action_go_to."'  
                        where questionid = ".$sort_order[$i];
                $this->db->query($update_question)->execute();
				if(strpos($arr_prev[0],'yes')){
					$temparr_n[$i]['first_question'] = 'yes';
				}
            }
        }
		
		// echo "<pre>";print_r($temparr_n);   
		
//echo "<pre>";print_r($temparr_n);die;		
    }
    
    
    public function deleteRecord(){
        $this->_db->beginTransaction();
        $query = "UPDATE survey_event_questions s set s.is_deleted = 1 where s.questionid = ".$this->getPrimaryId();
        
        $result = $this->db->query($query)->execute();
        
        
        $sub_query = "select first_question, action_goto_a,questionid ,`ID` from survey_event_questions where questionid = ".$this->getPrimaryId();
        $temp_res = $this->db->query($sub_query)->fetch();
        
        //check if branching is done
        if($temp_res['action_goto_a'] != ''){
            
        //check if this is first question and update the second question and increment it to became first.
            if($temp_res['first_question'] == 'yes'){
                //check if only one question in the survey if no update the second question
                if(!strpos('Submit Survey',$temp_res['ID'])){
                    $change_branching = "UPDATE survey_event_questions set first_question = 'yes' where action_goto_a = '".str_replace("ID ","",$temp_res['questionid'])."'";
                    $change_branching_res = $this->db->query($change_branching)->execute();
                    if($change_branching_res){
                        
                        $this->db->commit();
                        return true;
                    } else{
                        $this->db->rollBack();
                        return false;
                    }
                } else{
                        
                }

            } else{                        //change action_goto_a and skip the deleted question
                
                $change_branching = "UPDATE survey_event_questions set action_goto_a = '".$temp_res['action_goto_a']. "' where action_goto_a = 'ID ". $temp_res['ID']."'";
                $change_brnach_res = $this->db->query($change_branching)->execute();
                if($change_brnach_res){
                    $this->db->commit();
                    return true;
                } else{
                    $this->db->rollBack();
                    return false;
                }
            }
        } else{
            $this->db->commit();
        }
        
        return $result;
    }

    public function showEditLink($event_typeid,$action_name = ''){
        $query = $this->db->select()
                ->from(array('s' => 'survey_events'),'event_typeid');
                
                
        if($action_name == 'edit'){
            $query->joinInner(array('q' => 'survey_event_questions'),'q.event_typeid = s.event_typeid',array());
            $query->where("q.questionid = ? ", $event_typeid);
        } else{
            $query->where("s.event_typeid = ? ", $event_typeid);
        }
        $query->where("s.event_status IN ('In Progress','Expired')");
        
        $result= $query->query()->fetch();
        return $result;
    }


    public function getQuestions($data)
    {
        $query = $this->select()->setIntegrityCheck(false)
                ->from(array('s' => $this->_name),array('input_type' => 's.input_type','event_typeid' => 'event_typeid','question_number'))
                ->joinInner(array('sl' => 'survey_event_question_langs'),'sl.questionid = s.questionid',array('question' => 'sl.question','questionid' => 'sl.questionid'))
                ->joinInner(array('et' => 'survey_event_types'),'et.event_typeid = s.event_typeid',array())
                ->where("s.event_typeid = ? ", $data['eventTypeid'])
                ->where("s.is_deleted = 0 ")
                ->order($data['orderBy']);
        if(isset($data['orderBySecondry'])){
            $query->order($data['orderBySecondry']);
        }
                
                $query->limit($data['length'],$data['start']);
        $result= $query->query()->fetchAll();
        return $result;
    }
    public function getEventTypeName($event_typeid){
        $query = $this->db->select()
                ->from(array('survey_event_types'),'event_type')
                ->where("event_typeid = ? ", $event_typeid);
                
        $result= $query->query()->fetch();
        
        
        return $result;
    }
    public function getCountData($event_typeid)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),'count(*) as COUNT')
                ->where("event_typeid = ? ", $event_typeid)
                ->where("is_deleted = 0 ");
                
        $result= $rs->query()->fetchAll();
        
        
        return $result;
    }
    
    public function insertRecord(){
        
        $arr_params = array();
        $arr_params = $this->_getParams();
        
        $get_ID = $this->getID();
        
        
        
        if($get_ID){
            $arr_params['ID'] = $get_ID[0]['ID'] + 10;
        } else{
            $arr_params['ID'] = 1000;
        }
        $this->_db->beginTransaction();
        $last_insert_id = $this->db->insert($this->_name,$arr_params);
        
        $last_insert_id = $this->db->lastInsertid();
        
//$x = $this->db->lastInsertId();
        if($last_insert_id){
            
            $params_for_ques_langs = $this->_getParamsForQuesLangs($last_insert_id);
            $params_for_ques_langs['questionid'] = $last_insert_id;
            $last_id = $this->db->insert('survey_event_question_langs',$params_for_ques_langs);
            
            if($last_id){
                $this->_db->commit();
                return true;
            } else{
                $this->_db->rollBack();
                return false;
            }
        } else {
            $this->_db->rollBack();
            return false;
        }
        
        
    }
    public function updateRecord(){
        $query = "UPDATE survey_event_questions s,survey_event_question_langs sl set s.input_type = '".$this->getInputType()."', "
                      ."s.question_type = '".$this->getQuestionType()."', "
                      ."s.question_number = '".$this->getQuestionNumber()."', "
                      ."s.weightage = '".$this->getWeightage()."', "
                      ."s.max_user = '".$this->getMaxUser()."', "
                      . "s.max_score = '" . $this->getQuestionScore() . "', "
                      . "s.score1 = '" . $this->getScore1() . "', "
                      . "s.score2 = '" . $this->getScore2() . "', "
                      . "s.score3 = '" . $this->getScore3() . "', "
                      . "s.score4 = '" . $this->getScore4() . "', "
                      . "s.score5 = '" . $this->getScore5() . "', "
                      . "s.score6 = '" . $this->getScore6() . "', "
                      . "s.score7 = '" . $this->getScore7() . "', "
                      . "s.score8 = '" . $this->getScore8() . "', "
                      . "s.score9 = '" . $this->getScore9() . "', "
                      . "s.score10 = '" . $this->getScore10() . "', "
                      . "s.score11 = '" . $this->getScore11() . "', "
                      ."sl.question = ".$this->db->quote($this->getQuestion()).", "
                      ."sl.response1 = ".$this->db->quote($this->getResponse1()).", "
                      ."sl.response2 = ".$this->db->quote($this->getResponse2()).", "
                      ."sl.response3 = ".$this->db->quote($this->getResponse3()).", "
                      ."sl.response4 = ".$this->db->quote($this->getResponse4()).", "
                      ."sl.response5 = ".$this->db->quote($this->getResponse5()).", "
                      ."sl.response6 = ".$this->db->quote($this->getResponse6()).", "
                      ."sl.response7 = ".$this->db->quote($this->getResponse7()).", "
                      ."sl.response8 = ".$this->db->quote($this->getResponse8()).", "
                      ."sl.response9 = ".$this->db->quote($this->getResponse9()).", "
                      ."sl.response10 = ".$this->db->quote($this->getResponse10()).", "
                      ."sl.response11 = ".$this->db->quote($this->getResponse11()).", "
                      ."sl.response12 = ".$this->db->quote($this->getResponse12()).", "
                      ."sl.response13 = ".$this->db->quote($this->getResponse13()).", "
                      ."sl.response14 = ".$this->db->quote($this->getResponse14()).", "
                      ."sl.response15 = ".$this->db->quote($this->getResponse15()).", "
                      ."sl.response16 = ".$this->db->quote($this->getResponse16()).", "
                      ."sl.response17 = ".$this->db->quote($this->getResponse17()).", "
                      ."sl.response18 = ".$this->db->quote($this->getResponse18()).", "
                      ."sl.response19 = ".$this->db->quote($this->getResponse19()).", "
                      ."sl.response20 = ".$this->db->quote($this->getResponse20())." "          
                      ." WHERE s.questionid = ".$this->getPrimaryId()." AND "
                      ." sl.questionid = ".$this->getPrimaryId()
                ;
        
        $result = $this->db->query($query)->execute();
        return $result;
    }
    public function getRecordById(){
        $query = $this->select()->setIntegrityCheck(false)->from(array('seq'=>'survey_event_questions'),array(
                   'question_number' => 'seq.question_number','input_type' => 'seq.input_type','event_typeid' => 'seq.event_typeid',
				   'weightage'=>'seq.weightage', 'max_user'=>'seq.max_user', 'max_score' => 'seq.max_score', 'score1' => 'seq.score1', 'score2' => 'seq.score2', 'score3' => 'seq.score3', 'score4' => 'seq.score4', 'score5' => 'seq.score5', 'score6' => 'seq.score6', 'score7' => 'seq.score7', 'score8' => 'seq.score8', 'score9' => 'seq.score9', 'score10' => 'seq.score10', 'score11' => 'seq.score11',))
               ->joinInner(array('seql'=>'survey_event_question_langs'),'seq.questionid = seql.questionid',array(
                   'question' => 'seql.question','response1' => 'seql.response1','response2' => 'seql.response2','response3' => 'seql.response3',
                   'response4' => 'seql.response4','response5' => 'seql.response5','response6' => 'seql.response6','response7' => 'seql.response7',
                   'response8' => 'seql.response8','response9' => 'seql.response9','response10' => 'seql.response10','response11' => 'seql.response11',
                   'response12' => 'seql.response12','response13' => 'seql.response13','response14' => 'seql.response14','response15' => 'seql.response15',
                   'response16' => 'seql.response16','response17' => 'seql.response17','response18' => 'seql.response18','response19' => 'seql.response19',
                   'response20' => 'seql.response20',
               )) 
               ->where("seql.questionid = ? ",$this->getPrimaryId());
        $result = $query->query()->fetch();
        return $result;
    }
    public function getID(){
        $getID = $this->select()
               ->from(array('seq'=>'survey_event_questions'),'ID')
               ->where("event_typeid = ? ",$this->getEventType())
               ->order("ID DESC")
               ->limit(1);
        
        $result = $getID->query()->fetchAll();
        return $result;
    }
    
    private function _getParams(){
        $temp_arr = array();
        $temp_arr['question_type'] = $this->getQuestionType();
        //$temp_arr['max_res_limit'] = $this->getMaxResLimit();
        $temp_arr['max_score'] = $this->getQuestionScore();
        $temp_arr['input_type'] = $this->getInputType();
        $temp_arr['event_typeid'] = $this->getEventType();
        $temp_arr['question_number'] = $this->getQuestionNumber();
	    $temp_arr['weightage'] = $this->getWeightage();
        $temp_arr['max_user'] = $this->getMaxUser();
        $temp_arr['score1'] = $this->getScore1();
        $temp_arr['score2'] = $this->getScore2();
        $temp_arr['score3'] = $this->getScore3();
        $temp_arr['score4'] = $this->getScore4();
        $temp_arr['score5'] = $this->getScore5();
        $temp_arr['score6'] = $this->getScore6();
        $temp_arr['score7'] = $this->getScore7();
        $temp_arr['score8'] = $this->getScore8();
        $temp_arr['score9'] = $this->getScore9();
        $temp_arr['score10'] = $this->getScore10();
        $temp_arr['score11'] = $this->getScore11();
        
        return $temp_arr;
    }
    private function _getParamsForQuesLangs($lastId){
        $temp_arr = array();
        
        $temp_arr['questionid'] = $lastId;
        $temp_arr['langid'] = 1;
        $temp_arr['question'] = $this->getQuestion();
        $temp_arr['response1'] = $this->getResponse1();
        $temp_arr['response2'] = $this->getResponse2();
        $temp_arr['response3'] = $this->getResponse3();
        $temp_arr['response4'] = $this->getResponse4();
        $temp_arr['response5'] = $this->getResponse5();
        $temp_arr['response6'] = $this->getResponse6();
        $temp_arr['response7'] = $this->getResponse7();
        $temp_arr['response8'] = $this->getResponse8();
        $temp_arr['response9'] = $this->getResponse9();
        $temp_arr['response10'] = $this->getResponse10();
        $temp_arr['response11'] = $this->getResponse11();
        $temp_arr['response12'] = $this->getResponse12();
        $temp_arr['response13'] = $this->getResponse13();
        $temp_arr['response14'] = $this->getResponse14();
        $temp_arr['response15'] = $this->getResponse15();
        $temp_arr['response16'] = $this->getResponse16();
        $temp_arr['response17'] = $this->getResponse17();
        $temp_arr['response18'] = $this->getResponse18();
        $temp_arr['response19'] = $this->getResponse19();
        $temp_arr['response20'] = $this->getResponse20();
        return $temp_arr;
    }

    public function getQuestionDetails($arrwhere,$type="")
    {
        
        //echo $event_typeid;die;
       //$lang_id = !empty($lang_id)? $lang_id : 1 ;
       //print_R($arrwhere);
        $rs_question = $this->select()->setIntegrityCheck(false)
               ->from(array('seq'=>'survey_event_questions'),'*')
                ->joinLeft(array('seql'=>'survey_event_question_langs'),'seq.questionid = seql.questionid')
               ->where("is_deleted=0");
               if(!empty($arrwhere))
               {
                   foreach($arrwhere as $k=>$v)
                   {
                      if(is_array($v) && !empty($v) && count($v)>0)
                      {
                        
                          $rs_question->where($k." in (?) ",$v);
                         // echo$rs_question->__toString();die;
                      }
                      else
                      {
                        $rs_question->where($k." = '".$v."'");
                      }
                   }
                   
               }  
               
               $rs_question->order(array( 'seq.sort_order','input_type'));
               
               
               if (empty($type))
               {
                   
                   $rs_question = $rs_question->query()->fetch();
               } else {
                   
                    $rs_question = $rs_question->query()->fetchAll();
               }
               
           
            $rs_question_eng = $this->select()->setIntegrityCheck(false)->from(array('seq'=>'survey_event_questions'),'*')
            ->joinLeft(array('seql'=>'survey_event_question_langs'),'seq.questionid = seql.questionid')
                    ;
            $rs_question_eng->where("seq.is_deleted = 0");
            if(!empty($arrwhere))
               {
                   foreach($arrwhere as $k=>$v)
                   {
                      if($k == "langid")
                      {
                          $v=1;
                      }
                      if(is_array($v) && !empty($v) && count($v)>0)
                      {                        
                          $rs_question_eng->where($k." in (?) ",$v);
                         // echo$rs_question->__toString();die;
                      }
                      else
                      {
                        $rs_question_eng->where($k." = '".$v."'");
                      }
                   }
                   
                   
               }   
           /* ->where("event_typeid = ?",$event_typeid)
            ->where("langid = ?",1)
            ->where('web_first_question = ?','Yes')
            ->where('related_to = ?','All' ) */
           $rs_question_eng = $rs_question_eng->order(array("seq.sort_order","input_type"));
           
          
          // echo $rs_question_eng->__tostring();die;
           if(empty($type))
               {
                   
                   $rs_question_eng =   $rs_question_eng->query()
                     ->fetch();
               }
               else
               {
                   
                    $rs_question_eng =   $rs_question_eng->query()
                        ->fetchAll();
               }
               
               if(empty($type))
               {
                    return array($rs_question,$rs_question_eng);
               }
               else
               {        
                  
                  
                   return array("Q".$type=>$rs_question,"QEng".$type=>$rs_question_eng);
                   
               }
    }
    
    //get next questions
    public function getCountNextQuestion($event_typeid,$qID)
    {
        $arrnextques = $this->select()->setIntegrityCheck(false)
			->from(array('seq'=>'survey_event_questions'),'count(ID) as num')
			->where("seq.event_typeid = ?",$event_typeid)
			->where("seq.ID > ?",$qID)
			->where("seq.question_type in ('Q','V')")
                        ->where("is_deleted=0")
			->query()
			->fetch();
        return $arrnextques;
    }
    
    public function getalloptions($qid,$langid)
    {
         $arrquesopts = $this->select()->setIntegrityCheck(false)
			->from(array('seql'=>'survey_event_question_langs'),
                                    array('response1', 'response2', 'response3', 'response4', 'response5', 'response6', 
	'response7', 'response8', 'response9','response10', 'response11','response12','response13','response14',
        'response15','response16','response17','response18','response19','response20'))
			->where("seql.questionid = ? ",$qid)
			->where("seql.langid = ? ",$langid)//->__toString();			
			->query()
			->fetch();
        return $arrquesopts;
    }
    
    public function getTotalScore($eventid)
    {
        $eventScores = $this->select()
                        ->from(array('seq' => 'survey_event_questions'), 'SUM(sea.score) actual_score,SUM(seq.max_score) AS max_score')
                        ->join(array('sea' => 'survey_event_answers'), 'sea.questionid = seq.questionid', array())
                        ->where("eventid = ?", $eventid)
                        ->where("is_deleted=0")
                        ->query()
                        ->fetch();
        return $eventScores;
    }
    
    public function getNextGroupId($event_typeid,$questionid,$curgroupid)
    {
        $nextgroupid = $this->select()
                        ->from(array('seq' => 'survey_event_questions'), 'groupid')                       
                        ->where("seq.event_typeid = ?",$event_typeid)                      
                        ->where("groupid > ?", $curgroupid)
                        ->where("is_deleted=0")
                        ->limit(1)               ////->__toString();die;
                        ->query()
                        ->fetch();
       // print_r()
        return $nextgroupid;
    }
    
    //to shift in event module
    Public function updateViewCustInfoField($eventid,$event_typeid) 
    { 
        
        switch($event_typeid)
        {
            case "1":  $config_token ="sales_anonymous_question_id";    break;
            case "2": $config_token ="";     break;
            case "3": $config_token ="service_anonymous_question_id";     break;
        }
        if(!empty($config_token))
        {
            $qid = $this->_objconfig->findRow("config_val",array("config_var"=>$config_token));
            //print_R($qid);die;
            if($qid["config_val"] > 0)
            {
                $updtSurveyEventsQry = $this->db->query("update survey_events e  
        INNER JOIN survey_event_answers sea ON e.eventid = sea.eventid
        set e.is_anonymous = IF(sea.response_options = 1,'0','1')
        where e.event_status='Closed'
        and e.eventid='".$eventid."'
        and sea.questionid='".$qid["config_val"]."';");
                /*;*/
            }
        }
   }
   
   public function isSendToDealerEmail($eventid,$event_typeid,$chkType) {
       
        $sendToDealerEmail = "N";
        $ansObj  = new Survey_Model_Answers();
        
        switch($chkType)
        {
            case "low_score" :            
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

                    if($qid["config_val"] > 0)
                    {
                        $getAnswer = $ansObj->getAnswers("survey_event_answers",$eventid,$qid["config_val"]);
                     // echo "dxfgd";  var_dump($getAnswer);die;
                        if(isset($getAnswer["response_options"]) && $getAnswer["response_options"] > 0) //7/29/14 1:03 PM
                        {
                            $sendToDealerEmail = ($getAnswer["response_options"] <= 7) ? "Y" : 'N'; //as it store position val
                        }
                        
                    }

                }
            break;
            
            case "is_contact" :             
                switch($event_typeid)
                {
                    case "1":  $config_token ="sales_contact_dealer_question_id";    break;
                    case "2": $config_token ="product_contact_dealer_question_id";     break;
                    case "3": $config_token ="service_contact_dealer_question_id";     break;
                }
                if(!empty($config_token))
                {
                    $qid = $this->_objconfig->findRow("config_val",array("config_var"=>$config_token));

                    if($qid["config_val"] > 0)
                    {
                        $getAnswer = $ansObj->getAnswers("survey_event_answers",$eventid,$qid["config_val"]);
                        //echo "gdfghf"; print_r($getAnswer);die;
                        if(isset($getAnswer["response_options"]) && $getAnswer["response_options"] > 0 ) //7/29/14 1:03 PM
                        {
                            $sendToDealerEmail = ($getAnswer["response_options"] == "1") ? "Y" : 'N';
                        }
                        
                    }

                }
             break;
            }
        
        return $sendToDealerEmail;
    }
    
    public function getPerformanceTargetQues($ques_id, $eventType,$langid=1) {
        if(count($ques_id) <= 0) return false;
//        $quesId=array();
//        if(is_array($ques_id_arr)){
//            foreach ($ques_id_arr as $key => $value) {
//                $quesId[] = $value['questionid'];
//            }
//        }
        //$questions = implode(',', $ques_id_arr);
        
        //array('question','response1','response2','response3','response4','response5','response6','response7','response8','response9','response10','response11')
        
        $sql = $this->select()
                        ->from(array('seq' => 'survey_event_questions'), array('questionid','question_number','event_typeid','is_parent','parent_id'))                       
                        ->join(array('se' => 'survey_event_question_langs'), 'seq.questionid = se.questionid', array('question','response1','response2','response3','response4','response5','response6','response7','response8','response9','response10','response11','response12','response13','response14','response15','response16','response17','response18','response19','response20'))
                        ->where("seq.questionid IN ( ? )", $ques_id)                      
                        ->where("event_typeid = ?", $eventType)
                        ->where("question_type IN ('Q','V')")
                        ->where("langid = ?", $langid)
                        ->where("is_deleted= ?" ,'0')
                        ->where("is_archive = ?", '0')
                        ->order(new Zend_Db_Expr('CAST(MID(seq.question_number,2) AS UNSIGNED)'))
                        ->setIntegrityCheck(false);
       $ques_info = $sql->query()
                        ->fetch();
        return $ques_info;
    }
        
     public function getEventdataUpdatableQuestion($eventid) {
        $all_response_questions = $this->select()->setIntegrityCheck(false)
                ->from(array('seq' => 'survey_event_questions'), '*')
                ->join(array('sea' => 'survey_event_answers'), 'sea.questionid = seq.questionid')
                ->where("eventid = ?", $eventid) 
                ->where("is_deleted= ?" ,"0")
                ->where("event_fields_tobe_updated is not null")  ////->__toString();die;
                ->query()
                ->fetchAll();
        return $all_response_questions;
    }
    
    public function getUserResponsebasedQuestions($eventid,$langid=1)
    {
         $all_response_questions = $this->select()->setIntegrityCheck(false)
                ->from(array('seq' => 'survey_event_questions'), '*')
                ->joinLeft(array('seql'=>'survey_event_question_langs'),'seq.questionid = seql.questionid and seql.langid="'.$langid.'"')
                ->join(array('sea' => 'survey_event_answers'), 'sea.questionid = seq.questionid')
                ->where("eventid = ?", $eventid)
                 ->where("is_deleted= ?" ,"0")
                 ->where("ID > 999" )
                 //->order("ID")
				 ->order("sort_order")
                 ////->__toString();die;
                ->query()
                ->fetchAll();
        return $all_response_questions;
        
    }
    
     public function getAllQuestionEventTypewise($event_typeid)
     {
         $arrTotQues = $this->select()->setIntegrityCheck(false)
			->from(array('seq'=>'survey_event_questions'),'count(ID) as num')
			->where("seq.event_typeid = ?",$event_typeid)			
			->where("seq.question_type in ('Q')")
                        ->where("is_deleted= ?" ,"0")
			->query()
			->fetch();
        return $arrTotQues;
     }
     
     public function getChildQuestion($questionId,$langId=1) {
         
           $ques_info = $this->select()
                        ->from(array('seq' => 'survey_event_questions'), array('questionid','question_number','event_typeid','is_parent','parent_id'))                       
                        ->join(array('se' => 'survey_event_question_langs'), 'seq.questionid = se.questionid', array('question','response1','response2','response3','response4','response5','response6','response7','response8','response9','response10','response11','response12','response13','response14','response15','response16','response17','response18','response19','response20'))
                        ->where('parent_id = ?',$questionId)
                        ->where("question_type IN ('Q','V')")
                        ->where("langid = ?", $langId)
                        ->where("is_archive = ?", '0')
                        ->where("is_deleted= ?" ,"0")
                        ->setIntegrityCheck(false)->query()->fetchAll();
        return $ques_info;
     }
     
     
     public function getStatictext($questionId,$langid=1) {
         
           /* commented by dipa as we will not use language specific static text. 12/29/15 11:15 AM
            * $ques_info = $this->select()
                        ->from(array('seq' => 'survey_event_questions'), array('questionid'))                       
                        ->join(array('se' => 'survey_event_question_langs'), 'seq.questionid = se.questionid',
                                    array('question'))
                        ->where('seq.questionid = ?',$questionId)
                        ->where("question_type IN ('T')")
                        ->where("langid = ?", $langid)
                        ->where("is_deleted= ?" ,"0")
                        //->where("is_archive = ?", '0')
                        ->setIntegrityCheck(false)
                        ->query()
                        ->fetchAll(); 
            *  return $ques_info;*/
          return array("0"=>array("question"=>$questionId));
       
     }
     
//     public function getQuestionType($questionid,$langid='1')
//     {
//       $rs_question = $this->select()->setIntegrityCheck(false)
//                                    ->from(array('seq'=>'survey_event_questions'),'*')
//                                    ->joinLeft(array('seql'=>'survey_event_question_langs'),'seq.questionid = seql.questionid')
//                                    ->where("is_deleted=0")  
//                                    ->where("seql.langid=".$langid)  
//                                    ->where('seql.questionid IN (' . $questionid . ')')//->__toString();die;
//                                    ->setIntegrityCheck(false)
//                                    ->query()
//                                    ->fetchAll();   
//       return $rs_question;
//     }
     
    public function getAllquestions($event_type,$dealerID,$start_date,$end_date,$questionid,$model,$langid,$reportType='') {
        
        if(!empty($dealerID) && (count($dealerID)>1)){
            $dealer_str=implode(',', $dealerID);
            
        }else $dealer_str=$dealerID;
        
        if(!empty($questionid) && (count($questionid)>1)){
            $question_str=implode(',', $questionid);            
        }else $question_str=$questionid;
        
        $this->_objConfig = Zend_Registry::get('config');
        $db_name=$this->_objConfig['resources']['db']['params']['dbname'];
//        if($reportType == "qa")
//        {
//            $in_param=array($db_name, $event_type, $start_date, $end_date, $dealer_str,$question_str,$langid,$model,$reportType);
//            if(in_array($questionid,array(3,176)))
//            {
//               $result = $this->_spObj->getSpData('usp_repotAllQuestionAnalysis_1', $in_param, false);            
//            }
//            else
//            {
//                $result = $this->_spObj->getSpData('usp_repotAllQuestionAnalysis', $in_param, false);
//            }
//        }
//        else
//        {
            $in_param=array($db_name, $event_type, $start_date, $end_date, $dealer_str,$question_str,$langid,$model,$reportType);
            $result = $this->_spObj->getSpData('usp_repotAllQuestionAnswer', $in_param, false);
        //}
        return $result;        
    } 
    
    public function getAllquestionsforQA( $event_type, $start_date, $end_date, $questionid,
            $langid, $splQids, $survey_id) {
        if(!empty($questionid) && (count($questionid)>1)) {
            $question_str=implode(',', $questionid);            
        }
        else $question_str=$questionid;
        
        $this->_objConfig = Zend_Registry::get('config');
        $db_name=$this->_objConfig['resources']['db']['params']['dbname'];
        
        $in_param=array($db_name, $event_type, $start_date, $end_date, $question_str,$langid,'qa',$survey_id);
        
        if ( in_array ( $questionid, $splQids ) ) {
           $result = $this->_spObj->getSpData('usp_repotAllQuestionAnalysis_1', $in_param, false);            
        }
        else {
            $result = $this->_spObj->getSpData('usp_repotAllQuestionAnalysis', $in_param, false);
        }
        
        return $result;        
    }
    
    public function getQuestionsinOrder($arrcolumn="*",$arrwhere,$arrorder="")
    {
        $rs_question = $this->select()->setIntegrityCheck(false)
        ->from(array('seq'=>'survey_event_questions'),$arrcolumn)
        // ->joinLeft(array('seql'=>'survey_event_question_langs'),'seq.questionid = seql.questionid')
        ->where("is_deleted=0");
       
        if(!empty($arrwhere))
        {
            foreach($arrwhere as $k=>$v)
            {
               if(is_array($v) && !empty($v) && count($v)>0)
               {                        
                   $rs_question->where($k." in (?) ",$v);
               }
               else
               {
                 $rs_question->where($k." = '".$v."'");
               }
            }
        }  
        if(!empty($arrorder))
        {
            foreach($arrorder as $k=>$v)
            {
               $rs_question->order($k,$v);
            }
        }  
         //echo $rs_question->__toString();die;
        $rs_question =   $rs_question->query()->fetchAll();
         
        return $rs_question;
    }

    public function getMaxResponseQuestions($parentId, $eventId)
    {
        $this->setAllChildQuestions($parentId);
        $maxScoreQuestionIds = false;
        if (!empty($this->childQuestions)) {
            $questionsSql = "SELECT group_concat(questionid) as questionsIds FROM `survey_event_answers` 
                WHERE questionid IN ($this->childQuestions) AND eventid = $eventId AND
                response_options = (SELECT MAX(CAST(response_options AS UNSIGNED))
                FROM `survey_event_answers` WHERE questionid IN ($this->childQuestions)
                AND eventid = $eventId)";
            $maxScoreQuestionIds = $this->db->query($questionsSql)->fetchAll();
        }
        return $maxScoreQuestionIds;
    }

    public function availableDisplayOptions($selectedQuestions)
    {
        $selectedQuestions = explode(',', $selectedQuestions);
        $selectedOptions = array();
        $availableQuestion = explode(',', $this->childQuestions);
        array_unshift($availableQuestion, "---"); //to form sync indexing
        foreach ($availableQuestion as $key => $value) {
            if (in_array($value, $selectedQuestions)) {
                $selectedOptions[] = $key;
            }
        }
        return $selectedOptions;
    }

    protected function setAllChildQuestions($parentId)
    {
        $rs_question = $this->select()
                ->from(array('seq'=>'survey_event_questions'),
                    'group_concat(questionid) as questionsIds')
                ->where('is_deleted = 0')
                ->where('parent_id =' . $parentId)
                ->order('ID')
                ->query()
                ->fetchAll();
        if (!empty($rs_question[0])) {
            $this->childQuestions = $rs_question[0]['questionsIds'];
        }
    }
    
    public function getQuestionsForTemplate($eventTypeID, $langID)
    {
        $questionsSql = "SELECT seq.`event_typeid`, seq.`question_type`, 
            seq.`question_number`, seql.*, l.`lang_name`,
            REPLACE(REPLACE(seql.`grade_label_text`, '11:', ''), '1:', '') grade_label_text
            FROM `survey_event_question_langs` seql
            INNER JOIN `survey_event_questions` seq ON seq.`questionid` = seql.`questionid`
            INNER JOIN `languages` l ON l.`langid` = seql.`langid`
            WHERE seql.`langid`=$langID
            AND seq.`event_typeid`=$eventTypeID ORDER BY seq.`questionid` ASC ";
        return $this->db->query($questionsSql)->fetchAll();
    }
    
     public function getAllQuestionIDByEventType($eventTypeID) {
        $all_response_questions = $this->select()->setIntegrityCheck(false)
                ->from(array('seq' => 'survey_event_questions'), 'questionid')
                ->where("event_typeid = ?", $eventTypeID) 
                ->where("is_deleted= ?" ,"0")
                ->query()
                ->fetchAll();
        return $all_response_questions;
    }
    
    public function getAllQuestionIDByEventTypeNoTextArea($eventTypeID) {
        $all_response_questions = $this->select()->setIntegrityCheck(false)
                ->from(array('seq' => 'survey_event_questions'), 'questionid')
                ->where("event_typeid = ?", $eventTypeID) 
                ->where("is_deleted= ?" ,"0")
                ->where("input_type <> ?" ,"textarea")
                ->query()
                ->fetchAll();
        return $all_response_questions;
    }
    
     public function getAllQuestionDataByEventType($eventTypeID) {
        $all_response_questions = $this->select()->setIntegrityCheck(false)
                ->from(array('seq' => 'survey_event_questions'), array('questionid','input_type'))
                ->joinInner(array('seql' => 'survey_event_question_langs'),'seql.questionid = seq.questionid',array('question'))
                ->where("event_typeid = ?", $eventTypeID) 
                ->where("is_deleted= ?" ,"0")
                ->query()
                ->fetchAll();
        return $all_response_questions;
    }
    
    public function getQuestionDetailsByQuestionID($questionID)
    {
        $query = $this->select()->setIntegrityCheck(false)
                ->from(array('s' => $this->_name),array('question_type' => 's.question_type','input_type' => 's.input_type','s.question_number','s.weightage','s.max_user'))
                ->joinInner(array('sl' => 'survey_event_question_langs'),'sl.questionid = s.questionid',array('question' => 'sl.question','response1' => 'sl.questionid','response1' => 'sl.questionid','response1' => 'sl.response1','response2' => 'sl.response2','response3' => 'sl.response3','response4' => 'sl.response4','response5' => 'sl.response5','response6' => 'sl.response6','response7' => 'sl.response7','response8' => 'sl.response8','response9' => 'sl.response9','response10' => 'sl.response10','response11' => 'sl.response11','response12' => 'sl.response12','response13' => 'sl.response13','response14' => 'sl.response14','response15' => 'sl.response15','response16' => 'sl.response16','response17' => 'sl.response17','response18' => 'sl.response18','response19' => 'sl.response19','response20' => 'sl.response20'))
                //->joinInner(array('et' => 'survey_event_types'),'et.event_typeid = s.event_typeid',array())
                ->where("s.questionid = ? ", $questionID)
                ->where("s.is_deleted = 0 ");
        $result= $query->query()->fetchAll();
        return $result;
    }
    
    
    public function getQuestionDetailsForReports($arrwhere,$type="")
    {
        
        //echo $event_typeid;die;
       //$lang_id = !empty($lang_id)? $lang_id : 1 ;
       //print_R($arrwhere);
        $rs_question = $this->select()->setIntegrityCheck(false)
               ->from(array('seq'=>'survey_event_questions'),'*')
                ->joinLeft(array('seql'=>'survey_event_question_langs'),'seq.questionid = seql.questionid')
               ->where("is_deleted=0");
               if(!empty($arrwhere))
               {
                   foreach($arrwhere as $k=>$v)
                   {
                      if(is_array($v) && !empty($v) && count($v)>0)
                      {
                        
                          $rs_question->where($k." in (?) ",$v);
                         // echo$rs_question->__toString();die;
                      }
                      else
                      {
                        $rs_question->where($k." = '".$v."'");
                      }
                   }
                   
               }  
               
               $rs_question->order(array( 'seq.sort_order','input_type'));
               
               
               if (empty($type))
               {
                   
                   $rs_question = $rs_question->query()->fetchAll();
               } else {
                   
                    $rs_question = $rs_question->query()->fetchAll();
               }
               
           
            $rs_question_eng = $this->select()->setIntegrityCheck(false)->from(array('seq'=>'survey_event_questions'),'*')
            ->joinLeft(array('seql'=>'survey_event_question_langs'),'seq.questionid = seql.questionid')
                    ;
            $rs_question_eng->where("seq.is_deleted = 0");
            if(!empty($arrwhere))
               {
                   foreach($arrwhere as $k=>$v)
                   {
                      if($k == "langid")
                      {
                          $v=1;
                      }
                      if(is_array($v) && !empty($v) && count($v)>0)
                      {                        
                          $rs_question_eng->where($k." in (?) ",$v);
                         // echo$rs_question->__toString();die;
                      }
                      else
                      {
                        $rs_question_eng->where($k." = '".$v."'");
                      }
                   }
                   
                   
               }   
           /* ->where("event_typeid = ?",$event_typeid)
            ->where("langid = ?",1)
            ->where('web_first_question = ?','Yes')
            ->where('related_to = ?','All' ) */
           $rs_question_eng = $rs_question_eng->order(array("seq.sort_order","input_type"));
           
           
//           echo $rs_question_eng->__tostring();//die;
           if(empty($type))
               {
                   
                   $rs_question_eng =   $rs_question_eng->query()
                     ->fetchAll();
               }
               else
               {
                   
                    $rs_question_eng =   $rs_question_eng->query()
                        ->fetchAll();
               }
               
               if(empty($type))
               {
                    return array($rs_question,$rs_question_eng);
               }
               else
               {
                   return array("Q".$type=>$rs_question,"QEng".$type=>$rs_question_eng);
                   
               }
    }
    
    /*SETTER AND GETTER*/
    public function getQuestionNumber() {
        return $this->questionNumber;
    }

    public function setQuestionNumber($questionNumber) {
        $this->questionNumber = $questionNumber;
    }
    public function getQuestionType() {
        return $this->questionType;
    }

    public function setQuestionType($questionType) {
        $this->questionType = $questionType;
    }

    public function getMaxResLimit() {
        return $this->maxResLimit;
    }

    public function setMaxResLimit($maxResLimit) {
        $this->maxResLimit = $maxResLimit;
    }

    public function getQuestion() {
        return $this->question;
    }

    public function setQuestion($question) {
        $this->question = $question;
    }

    public function getResponse1() {
        return $this->response1;
    }

    public function setResponse1($response1) {
        $this->response1 = $response1;
    }

    public function getResponse2() {
        return $this->response2;
    }

    public function setResponse2($response2) {
        $this->response2 = $response2;
    }

    public function getResponse3() {
        return $this->response3;
    }

    public function setResponse3($response3) {
        $this->response3 = $response3;
    }

    public function getResponse4() {
        return $this->response4;
    }

    public function setResponse4($response4) {
        $this->response4 = $response4;
    }

    public function getResponse5() {
        return $this->response5;
    }

    public function setResponse5($response5) {
        $this->response5 = $response5;
    }

    public function getResponse6() {
        return $this->response6;
    }

    public function setResponse6($response6) {
        $this->response6 = $response6;
    }

    public function getResponse7() {
        return $this->response7;
    }

    public function setResponse7($response7) {
        $this->response7 = $response7;
    }

    public function getResponse8() {
        return $this->response8;
    }

    public function setResponse8($response8) {
        $this->response8 = $response8;
    }

    public function getResponse9() {
        return $this->response9;
    }

    public function setResponse9($response9) {
        $this->response9 = $response9;
    }

    public function getResponse10() {
        return $this->response10;
    }

    public function setResponse10($response10) {
        $this->response10 = $response10;
    }

    public function getResponse11() {
        return $this->response11;
    }

    public function setResponse11($response11) {
        $this->response11 = $response11;
    }

    public function setResponse12($response12) {
        $this->response12 = $response12;
    }
    
    public function getResponse12() {
        return $this->response12;
    }

    public function setResponse13($response13) {
        $this->response13 = $response13;
    }
    public function getResponse13() {
        return $this->response13;
    }

    public function setResponse14($response14) {
        $this->response14 = $response14;
    }
    public function getResponse14() {
        return $this->response14;
    }

    public function setResponse15($response15) {
        $this->response15 = $response15;
    }
    public function getResponse15() {
        return $this->response15;
    }

    public function setResponse16($response16) {
        $this->response16 = $response16;
    }
    public function getResponse16() {
        return $this->response16;
    }

    public function setResponse17($response17) {
        $this->response17 = $response17;
    }
    public function getResponse17() {
        return $this->response17;
    }
    
    public function setResponse18($response18) {
        $this->response18 = $response18;
    }
    public function getResponse18() {
        return $this->response18;
    }
    
    public function setResponse19($response19) {
        $this->response19 = $response19;
    }
    public function getResponse19() {
        return $this->response19;
    }
    
    public function getResponse20() {
        return $this->response20;
    }

    public function setResponse20($response20) {
        $this->response20 = $response20;
    }
    
    public function getEventType() {
        return $this->eventType;
    }

    public function setEventType($eventType) {
        $this->eventType = $eventType;
    }

    public function getInputType() {
        return $this->inputType;
    }

    public function setInputType($inputType) {
        $this->inputType = $inputType;
    }
    public function getPrimaryId() {
        return $this->primaryId;
    }

    public function setPrimaryId($primaryId) {
        $this->primaryId = $primaryId;
    }

	  public function getWeightage() {
        return $this->weightage;
    }
    
    public function getMaxUser() {
        return $this->maxUser;
    }

    public function setWeightage($weightage) {
        $this->weightage = $weightage;
    }
    public function setMaxUser($maxUser) {
        $this->maxUser = $maxUser;
    }

    public function getScore1(){
        return $this->score1;
    }

    public function getQuestionScore()
    {
        return $this->questionScore;
    }

    public function setQuestionScore($questionScore)
    {
        $this->questionScore = $questionScore;
    }
    
    public function setScore1($score1){
         $this->score1 = $score1;
    }

    public function getScore2(){
        return $this->score2;
    }

    public function setScore2($score2){
         $this->score2 = $score2;
    }

    public function getScore3(){
        return $this->score3;
    }

    public function setScore3($score3){
         $this->score3 = $score3;
    }

    public function getScore4(){
        return $this->score4;
    }

    public function setScore4($score4){
         $this->score4 = $score4;
    }

    public function getScore5(){
        return $this->score5;
    }

    public function setScore5($score5){
         $this->score5 = $score5;
    }

    public function getScore6(){
        return $this->score6;
    }

    public function setScore6($score6){
         $this->score6 = $score6;
    }

    public function getScore7(){
        return $this->score7;
    }

    public function setScore7($score7){
         $this->score7 = $score7;
    }

    public function getScore8(){
        return $this->score8;
    }

    public function setScore8($score8){
         $this->score8 = $score8;
    }

    public function getScore9(){
        return $this->score9;
    }

    public function setScore9($score9){
         $this->score9 = $score9;
    }

    public function getScore10(){
        return $this->score10;
    }

    public function setScore10($score10){
         $this->score10 = $score10;
    }

    public function getScore11(){
        return $this->score11;
    }

    public function setScore11($score11){
         $this->score11 = $score11;
    }

	public function getAllQuestionDataByEventTypeId($eventTypeID) {
            if($eventTypeID == 950) {
            $all_response_questions = $this->select()->setIntegrityCheck(false)
                ->from(array('seq' => 'survey_event_questions'), array('questionid','input_type'))
                ->joinInner(array('seql' => 'survey_event_question_langs'),'seql.questionid = seq.questionid',array('question'))
                ->joinInner(array('sea' => 'survey_event_answers'),'sea.questionid = seq.questionid',array())
                ->where("event_typeid = ?", $eventTypeID) 
                ->where("is_deleted= ?" ,"0")
		->where("question_type=?","Q")
                //->order('seq.sort_order')
                ->group("seq.questionid")
                ->order('sea.answer_date')
                ->query()
                ->fetchAll();
        }
        else {
            $all_response_questions = $this->select()->setIntegrityCheck(false)
                ->from(array('seq' => 'survey_event_questions'), array('questionid','input_type'))
                ->joinInner(array('seql' => 'survey_event_question_langs'),'seql.questionid = seq.questionid',array('question'))
                ->where("event_typeid = ?", $eventTypeID) 
                ->where("is_deleted= ?" ,"0")
		->where("question_type=?","Q")
                //->order('seq.sort_order')
                ->order('seq.sort_order')
		->order('seq.questionid')
                ->query()
                ->fetchAll();
        }
        return $all_response_questions;
    }
  
  
   /* getAnswerLoggedCount 
   * Author: Gaurav Narang - 21/7/16
   *
   * return questions with answer logged
   *
   * @param (array) ($questions) question detail array
   * @param (int) ($surveyid) event id   
   * @return (array) ($questions)
   */  
  public function getAnswerLoggedCount($questionsList,$surveyid) {       
     $retarr = array();
     
     if(is_array($questionsList))
     {
        $questions = $questionsList;
     }else{
        $questions[] = $questionsList;
     }
     foreach($questions as $question){
         if ($question['max_user'])
         {   
             for ($i=1; $i<=11; $i++){
                 $response = $this->getAnswerCountByIndex($question,$surveyid,$i);    
                 if ($response){ 
                    $question['responselog'.$i] = $response;
                  } 
             }
             $retarr[] = $question;
         }else {
             $retarr[] = $question;
         }  
      
     }
    
     
      return $retarr;
  
  }
  
  
  /* getAnswerCountByIndex 
   * Author: Gaurav Narang - 21/7/16
   *
   * fetch the answer count for questions
   *
   * @param (array) ($question) question detail array
   * @param (int) ($surveyid) event id  
   * @param (int) ($responseIndex) answer option index    
   * @return (int) ($count)
   */
  public function getAnswerCountByIndex($question,$surveyid,$responseIndex){
    
     if(!empty($question['response'.$responseIndex]))
     {
         
         $answerCount = $this->select()->setIntegrityCheck(false)
		     ->from(array('sea'=>'survey_event_answers'),'count(answerid) as num')
         ->join(array('se' => 'survey_events'), 'sea.eventid = se.eventid', array())
         ->where("se.survey_id = ?",$surveyid)
         ->where("se.event_status = ?",'Closed')
		     ->where("sea.questionid = ?",$question['questionid'])
         ->where("sea.response_options = ?",$responseIndex)
	 	     ->query()
		     ->fetch();
         return $answerCount['num'];   
      }
      return false;     
      
  }  



}
