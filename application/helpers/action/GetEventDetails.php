<?php

class Damco_Action_Helper_GetEventDetails extends Zend_Controller_Action_Helper_Abstract{

    
    function GetEventDetails($eventId,$arrUserResponsesQuestions,$arrLabelquestions, $user_role_id) {
        $this->_objConfig = Zend_Registry::get('config');
        $this->_eventModelObject = new Event_Model_Events();
//        $this->_customer = new Customer_Model_Customers();
        $this->_questionModelobj = new assessment_Model_Questions();
        $this->_config = new Survey_Model_Config(); //assessment_make_option_nootherbrand
//        $customerid = $this->_customer->getCustomerDetails($customerid);
        $where = array('eventid' => $eventId);
        $questions = array();
        $collectALLParentsID = array();
        $prevArraykeyId = $prevQType = "";
        //print_R($arrMakename);
        foreach ($arrUserResponsesQuestions as $k => $userResQues) {
            if ($userResQues['input_type'] == "textarea" && $userResQues['response_options'] == "1" && empty($userResQues['answer1'])) {
                unset($arrUserResponsesQuestions[$k]);
            } 
            else {
                $questions[$userResQues["ID"]] = $userResQues;
            } 
        }
        foreach ($questions as $k => $q) {
            if ($q["question_type"] != "Q" && $prevQType == $q["question_type"]) {
                unset($questions[$prevArraykeyId]);
            }
            $prevQType = $q["question_type"];
            $prevArraykeyId = $k;
        }
        //don't show last question if it is "T" || "V"
        $lastQues = key(array_slice($questions, -1, 1, TRUE));
        if ($questions[$lastQues]["question_type"] != "Q") {
            unset($questions[$lastQues]);
        }
       return $questions;
    }
    function direct($eventId,$resultSet,$arrLabelquestions, $user_role_id) {
        return $this->GetEventDetails($eventId,$resultSet,$arrLabelquestions, $user_role_id);
    }

}
