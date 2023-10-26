<?php

/**
 * Model class to handle survey events reporting data
 * 
 * @author Amit kumar
 * @date   20 Aug, 2014
 * @version 1.0
 */
class Report_Model_Alertlog extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();

        $this->_name = 'alerts_log';
    }

    /**
     * Method to call SP and get events data
     * @param type $spName
     * @param type $inParam
     * @return type
     */
    public function getAlertLogData($spName, $inParam, $debug = FALSE) {
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
     * Method to return Type of alerts
     * @return type
     */
    public function getAlertTypes() {
    	$rs = $this->select()
    	->from('alert_types', array('title','email_token'))
    	->order(array('title ASC'))
    	->setIntegrityCheck(FALSE);//->__toString();
    	return $rs->query()->fetchAll();
    }
    
    /**
     * Method to return Type of alerts
     * @return type
     */
    public function getAlertData($alertid) {
    	$rs = $this->select()
    	->from($this->_name, array('to_addess','alert_content','email_subject','bcc_address'))
    	->where('alertid = ?',$alertid)
    	->setIntegrityCheck(FALSE);//->__toString();
    	return $rs->query()->fetch();
    }
}
