<?php

/**
 * 
 * @author Anuj
 * @date   31 May, 2014
 * @version 1.0
 */
class Event_Model_Scorecard extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();

        $this->_name = 'survey_events';
    }

    /**
     * 
      Method to Get the Survey Detail Data
     * */
    public function getSurveyDetails($eventId) {
        $resultset = $this->select()->setIntegrityCheck(false)
                ->from(array('evt' => 'survey_events'), '*')
                ->joinLeft(array('sup' => 'dealers'), 'sup.id = evt.dealer_id')
                ->joinLeft(array('etype' => 'survey_event_types'), 'etype.event_typeid= evt.event_typeid')
                ->where("evt.eventid= ?", $eventId)
                ->where("evt.event_status= 'Closed' OR evt.event_status= 'Did not qualify' ")
                ->query()
                ->fetch();

        return $resultset;
    }  
   
}