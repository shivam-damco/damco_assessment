<?php

/**
 * Model class to handle User Activity
 * 
 * @author  Amit kumar
 * @date    29 Sep, 2014
 * @version 1.0
 */
class Report_Model_UserActivity extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();

        $this->_name = 'user_activity';
    }
    
    /**
     * Method to call SP and get events data
     * @param type $spName
     * @param type $inParam
     * @return type
     */
    public function getUserActivityData($spName, $inParam, $debug = FALSE) {
        return $this->_spObj->getSpData($spName, $inParam, $debug);
    }
    
    /**
     * Method to return vehicle models
     * @return type
     */
    public function getUserRoles() {
        $rs = $this->select()
        ->from('acl_roles', array('id','label'))
        ->order(array('label'))
        ->setIntegrityCheck(FALSE);//->__toString();
        return $rs->query()->fetchAll();
    }
    
    public function getLastLogin( $userID ) {
        $rs = $this->select()
                ->from($this->_name, array('start_date' => 'logged_in_time') )
                ->where(' username = "'.$userID.'"')
                ->limit('1', '1')
                ->order(' logged_in_time DESC ');
        return $rs->query()->fetch();
    }
    
    
  }