<?php

/**
 * 
 * @author Anuj
 * @date   03 July, 2014
 * @version 1.0
 */
class Event_Model_CustomerResolution extends Default_Model_Core {

    /**
     * Constructor to initialize Customer Resolution model class
     */
    public function __construct() {
        parent::__construct();

        $this->_name = 'customer_alert_resolution';
    }
    
    public function GetCustomerResolution($eventId='',$order='',$record='')
    { 
        $rs = $this->select()
                ->from(array('cr' => $this->_name),
                    array('cr.id as resolutionid', 'cr.eventid', 'cr.problem',
                        'cr.solution', 'cr.role_id', 'cr.added_by', 'cr.added_date',
                        'cr.status'))
                
               
                ->where('cr.eventid ='.$eventId)
                ->order('cr.id '.$order)
               ->setIntegrityCheck(FALSE);
                 if($record=='single')
                 {
                    return $rs->query()->fetch();
                 }
                 else
                 {
                     return $rs->query()->fetchAll();
                 }
    }

}