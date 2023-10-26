<?php

/**
 * Model class to handle All question reporting data
 * 
 * @author Amit kumar
 * @date   01 Sep, 2014
 * @version 1.0
 */
class Report_Model_Allquestion extends Default_Model_Core
{
    const MAX_QUE_RESPONCE = 25;

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();

        $this->_name = 'survey_events';
    }

    /**
     * Method to call SP and get events data
     * @param type $spName
     * @param type $inParam
     * @return type
     */
    public function getAllquestionData($spName, $inParam, $debug = FALSE) {
        return $this->_spObj->getSpData($spName, $inParam, $debug);
    }

    public function getDateRange($column) {
        $rs = $this->select()
                ->from($this->_name, array('min_date' => 'MIN(' . $column . ')',
            'max_date' => 'MAX(' . $column . ')')
        );
        return $rs->query()->fetch();
    }
    
    /**
     *
     Method to Get all questions
     * */
    public function getQuestions($langid,$allQuestions) {

         $questions =implode(',', $allQuestions);
 
    	 $details = $this->select()
    	->from(array('q' => 'survey_event_questions'),array('q.ID','q.question_type','q.groupid','q.questionid','q.question_number','q.input_type'))
    	->joinInner(array('ql' => 'survey_event_question_langs'), 'ql.questionid = q.questionid ', array('ql.question','ql.response1',
    			'ql.response2','ql.response3','ql.response4','ql.response5','ql.response6','ql.response7','ql.response8','ql.response9',
    			'ql.response10','ql.response11'    			
    	))
    	->where('ql.langid =?', $langid)
    	->where("q.questionid in (" .$questions.")")
    	->order('q.ID')
    	->setIntegrityCheck(FALSE);//->__toString();
    	return $details->query()->fetchall();
    
    }
    
      /*
      Method to Get all questions
     * */
    public function getallQuestions($langid,$eventtypeid='') {
    	//echo $eventtypeid;die;
    	 $details = $this->select()
    	->from(array('q' => 'survey_event_questions'),array('q.ID','q.question_type','q.groupid','q.questionid','q.question_number','q.input_type'))
    	->joinInner(array('ql' => 'survey_event_question_langs'), 'ql.questionid = q.questionid ', array('ql.question','ql.response1',
    			'ql.response2','ql.response3','ql.response4','ql.response5','ql.response6','ql.response7','ql.response8','ql.response9',
    			'ql.response10','ql.response11'    			
    	))
        ->where('ql.langid =?', $langid)
        ->where('q.event_typeid=?',$eventtypeid)
        ->where('q.question_type="Q"')
        ->order('q.ID')
        ->setIntegrityCheck(FALSE);//->__toString();
    	return $details->query()->fetchall();
    
    }

    /*
     * get all question responses array
     *
     * @param array $questionsData
     * @param bool $getCount
     * @param mixed $getCount
     *
     * @return array
     *
     * @author Kuldeep Dangi (kuldeepd@damcogroup.com)
     */
    public function getQuestionResponsesAssoc($questionsData, $getCount = true, $groupBy = false)
    {
        $responseData = array();
        $tempData = array();
        foreach ($questionsData as $questionData) {
            $tempData[$questionData['questionid']]['totalCount'] = 0;
            for ($i = 1; $i < self::MAX_QUE_RESPONCE; $i++) {
                if (!empty($questionData['response' . $i])) {
                    $tempData[$questionData['questionid']]['response' . $i] =
                        $questionData['response' . $i];
                    if ($getCount) {
                        $tempData[$questionData['questionid']]['response' . $i . '_cnt'] =
                            $questionData['response' . $i . '_cnt'];
                        $tempData[$questionData['questionid']]['totalCount'] +=
                            $questionData['response' . $i . '_cnt'];
                    }
                }
            }
        }
        if ($groupBy) {
            $responseData[$groupBy] = $tempData;
        } else {
            $responseData = $tempData;
        }
        return $responseData;
    }

}
