<?php

/**
 * Model class to handle Alerts Log Table operations
 */
/*
 * Created On : 17/06/2014
 * Created By : Sandeep Pathak
 * Email : sandeepp@damcogroup.com
 */

class Survey_Model_AlertsLog extends Default_Model_Core {

    /**
     * Constructor to initialize Scheduled Emails model class
     */
    public function __construct() {
        parent::__construct();
        $this->_name = 'alerts_log';
    }
     public function getSelect($cols='*') {
        return $this->select()->from($this->_name, $cols);
    }

}
