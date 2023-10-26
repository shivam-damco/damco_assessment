<?php
/**
 * Helper class to create required layouts
 * 
 * @author Harpreet Singh
 * @date   31 May, 2014
 * @version 1.0
 */

class Damco_View_Helper_ShowAnswers extends Zend_View_Helper_Abstract {

    /**
     * Constructor to initialize Get Dealers class
     */
    function __construct( ) {
    }

    /**
     * Method to return dealers array
     * @param type $dealerID
     */
    public function showAnswers( $scorecarddata,$langid ) {
        $answer = '';
        $class = '';
        $responseanswers = explode(',', $scorecarddata['response_options']);
        if (count($responseanswers) > 1) {//echo $scorecarddata['response_options'];print_R($responseanswers);
            foreach ($responseanswers as $response_answers) {
                if(!empty($response_answers))
                {
                   $response = "response" . $response_answers;
                    if (in_array($scorecarddata[$response], array("Make"))) {
                        //$response = "answer1";
                        $answer .=!empty($scorecarddata["answer1"]) ? (!empty($answer) ? ", " : "") . $scorecarddata[$response] : "";
                    } else {
                        $response = "answer" . $response_answers;
                        $manswer = !empty($scorecarddata[$response]) ? $scorecarddata[$response] : "";
                        $manswer = $this->getLanguageBasedResponse($manswer,$scorecarddata,$response_answers,"checkbox");
                        $answer .=(!empty($answer)) ? ", ".$manswer : $manswer;
                       // $answer ="p:". $answer ;
                    }
                }
            }
        } else {
            if ($scorecarddata['response_options'] >= 11 && isset($scorecarddata['response11'])
                && (strpos($scorecarddata['response11'], '|') !== false)) {
                $totalResponses = 11;
                $remainingResponses =  explode('|', $scorecarddata['response11']);
                unset($scorecarddata['response11']);
                foreach ($remainingResponses as $response) {
                    $scorecarddata['response'. $totalResponses] = $response;
                    $scorecarddata['answer'. $totalResponses] = $response;
                    $totalResponses++;
                }
                $totalResponses--;
            }
            $response = "response" . $scorecarddata['response_options'];
            if (isset($scorecarddata[$response])) {
                if ($scorecarddata['input_type'] == 'textarea' || $scorecarddata['input_type'] == 'text') {
                    if (!empty($scorecarddata['answer1'])) {
                        $answer = ($scorecarddata['answer1']); //8/20/14 8:22 PM
                    } else {
                        $answer = "";
                    }
                } elseif (in_array(trim($scorecarddata[$response]), array("Make"))) {
                    if ($scorecarddata['input_type'] == 'checkbox') {
                        $userResp = $scorecarddata['answer1'];
                        $arrUserresp = explode(",", $userResp);
                        foreach ($arrUserresp as $k => $mknm) {
                            $answer .= empty($answer) ? $this->arrmakes[$mknm] : ", " . $this->arrmakes[$mknm];
                        }
                         //$answer ="g". $answer ;
                    } else {
                        $answer = $scorecarddata['answer1'];
                        // $answer ="h". $answer ;
                    }
                    
                } elseif (!in_array(trim($scorecarddata[$response]), array("Nationname", "Marketname", "NationsName"))) {                                                        
                    $answer = $scorecarddata['answer' . $scorecarddata['response_options']];
                    $answer = $this->getLanguageBasedResponse($answer,$scorecarddata,$scorecarddata['response_options'],"radio");
                    // $answer ="c". $answer ;
                } else {
                    $ans = explode('::', $scorecarddata['answer1']);
                    $answer = $ans['1'];
                    // $answer ="d". $answer ;
                }
            } else {
                //$answer = "aa".$scorecarddata['response_options'];
                $answer = (($scorecarddata['response_options'] - 1) > 0) ?
                        $scorecarddata['answer' . ($scorecarddata['response_options'] - 1)] :
                        $scorecarddata['answer7'];
               // $answer ="e". $answer ;
            }
        }
        return $answer;
    }
    
    public function getLanguageBasedResponse($answer,$scorecarddata,$response_answers,$usefor='',$langid=1)
    {
        //get questionwise responses
        $this->_questionModelobj = new assessment_Model_Questions();
        $qdtls = $this->_questionModelobj->getalloptions($scorecarddata['questionid'],$langid);
        
        if($usefor == "radio" || $usefor == "checkbox" )
        {
            return $scorecarddata["response".$response_answers];
        }
        if(trim($qdtls["response".$response_answers]) == trim($scorecarddata["answer".$response_answers]))
        {
            //if($usefor == "checkbox"){ echo "ssss".$scorecarddata["response".$response_answers];die;}
            return $scorecarddata["response".$response_answers];
        }
        else
        {
           
            return $answer;
        }
        
    }
}
