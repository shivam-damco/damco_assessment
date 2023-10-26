<?php

/**
 * Model class to handle alert error logs operations
 * 
 * @author Harpreet Singh
 * @date   11 September, 2014
 * @version 1.0
 */

class Default_Model_AlertErrorLogs extends Default_Model_Core {

    /**
     * Constructor to initialize alert error logs model class
     */
    public function __construct() {
        parent::__construct();
        $this->_name = 'alert_error_logs';
    }
    
}