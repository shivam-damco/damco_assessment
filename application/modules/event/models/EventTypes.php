<?php

/**
 * Model class to handle survey event types operations
 * 
 * @author Harpreet Singh
 * @date   27 May, 2014
 * @version 1.0
 */

class Event_Model_EventTypes extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();
        $this->_name = 'survey_event_types';
    }
    
    public function getEventType(){
         $rs = $this->db->select()
                ->from(array('survey_event_types'),
                array('event_type','event_typeid'))
                ->where("event_type != ?", "Product");
               
       $result= $rs->query()->fetchAll();
        return $result;
    }
    
    public function getEventTypeInOrder(){
         $rs = $this->db->select()
                ->from(array('survey_event_types'),
                array('event_type','event_typeid'))
                ->where("event_type != ?", "Product")
               ->order('event_type asc');
               
       $result= $rs->query()->fetchAll();
        return $result;
    }
    
    

    /*
     * To get Name of a Event type
     *
     * @param int $selectedEventTypeId
     *
     * @return string
     *
     * @author Kuldeep Dangi <kuldeepd@damcogroup.com>
     */
    public function getEventTypeNameById($selectedEventTypeId)
    {
        $allEventTypes = $this->getAll();
        $eventTypeName = '';
        foreach ($allEventTypes as $eventType) {
            if ($eventType['event_typeid'] == $selectedEventTypeId) {
                $eventTypeName = $eventType['event_type'];
            }
        }
        return $eventTypeName;
    }
}
