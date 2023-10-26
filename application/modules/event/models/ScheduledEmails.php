<?php

/**
 * Model class to handle Scheduled Emails operations
 * 
 * @author Harpreet Singh
 * @date   14 June, 2014
 * @version 1.0
 */

class Event_Model_ScheduledEmails extends Default_Model_Core {

    /**
     * Constructor to initialize Scheduled Emails model class
     */
    public function __construct() {
        parent::__construct();
        $this->_name = 'scheduled_emails';
    }
    
}