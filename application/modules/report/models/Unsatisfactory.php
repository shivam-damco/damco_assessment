<?php

/**
 * Model class to handle Unsatisfactory  reporting data
 * 
 * @author Anuj
 * @date   01 Sep, 2014
 * @version 1.0
 */
class Report_Model_Unsatisfactory extends Default_Model_Core {

    /**
     * Constructor to initialize Unsatisfactory model class
     */
    public function __construct() {
        parent::__construct();

        $this->_name = 'survey_events';
    }

    /**
     * Method to call SP and get unsatisfactory data
     * @param type $spName
     * @param type $inParam
     * @return type
     */
    public function getUnsatisfactoryData($spName, $inParam, $debug = FALSE) {
       // print_r($spName);print_r($inParam);die;
        return $this->_spObj->getSpData($spName, $inParam, $debug);
    }

}
