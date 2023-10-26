<?php

/**
 * Model class to handle survey event types operations
 * 
 * @author Harpreet Singh
 * @date   28 May, 2014
 * @version 1.0
 */

class Event_Model_EventStatus extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();
        $this->_name = 'survey_event_status';
    }
    
}