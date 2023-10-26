<?php

/**
 * Model class to handle survey events reporting data
 * 
 * @author Tanuj Ahuja
 * @date   31 May, 2014
 * @version 1.0
 */
class Report_Model_Events extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();

        $this->_name = 'survey_events';
    }

    /**
     * Method to call SP and get events data
     * @param type $spName
     * @param type $inParam
     * @return type
     */
    public function getEventsData($spName, $inParam, $debug = FALSE) {
        return $this->_spObj->getSpData($spName, $inParam, $debug);
    }

    public function getDateRange($column) {
        $rs = $this->select()
                ->from($this->_name, array('min_date' => 'MIN(' . $column . ')',
            'max_date' => 'MAX(' . $column . ')')
        );
        return $rs->query()->fetch();
    }

    public function getEventDrillDownData($params=array(), $surveyPeriod, $event_type, $code_status) {
        
        $event_type_id = '1';   // for sales
        if(strtolower($event_type) == 'service')
            $event_type_id = '3';
        elseif(strtolower($event_type) == 'product')
            $event_type_id = '2';
        
        
        $startDate = "";
        $endDate = ""; 
        if(!empty($params['startDate'])){
            $startDate = $params['startDate'];
        }
        
        if(!empty($params['endDate'])){
            $endDate = $params['endDate'];
        }
        
        
        $custom_query = "SELECT SQL_CALC_FOUND_ROWS
            eventtype.event_type,
            eventtype.event_typeid,            
            concat(c.title, ' ', c.first_name, ' ', c.surname) as survey_owner,
            event.code_status,
            event.dealer_id,    
            event.eventid,	    
            c.registration_number,
            event.satisfaction_percent,
            DATE_FORMAT(event.event_date , '%d/%m/%Y')
            AS SurveyDate,
            event.event_date,
            supplier.dealer_name
            FROM survey_event_types eventtype
            LEFT JOIN ( survey_events event
            LEFT JOIN dealers supplier
            ON supplier.id=event.dealer_id
            LEFT JOIN `dealer_contacts` dc ON supplier.`id` = dc.`dealerid`
            LEFT JOIN customers c ON event.customerid = c.id
            )            
            ON eventtype.event_typeid=event.event_typeid 
            WHERE event.event_status='Closed' AND survey_date BETWEEN '".$startDate." 13:00:00' AND '".$endDate." 14:59:59' 
            AND event.`event_typeid` = '".$event_type_id."' AND event.`code_status` = '".$code_status."' ";
        
        if(!empty($params['dealerid'])){
            $custom_query .= " and supplier.id = '".$params['dealerid']."' "; // This is dealerid
        }
        
        if(!empty($params['model'])){
            $custom_query .= " and c.`vehicle_code_desc` = '".$params['model']."' "; 
        }
        
        if(!empty($params['market'])){
            $custom_query .= " and supplier.`marketid` = '".$params['market']."' "; 
        }
        
        if(!empty($params['branch'])){
            $custom_query .= " and supplier.`subsidiaryid` = '".$params['branch']."' "; 
        }
        
        if(!empty($params['asm'])){
            $custom_query .= " and dc.`userid` = '".$params['asm']."' "; 
        }
        
        $custom_query .= " GROUP BY event.dealer_id LIMIT  ".$params['start'].", ".$params['length']." ;";
        $stmt = $this->db->query($custom_query);
        $rows = $stmt->fetchAll();
        
        $stmt_tot_rows = $this->db->query("SELECT FOUND_ROWS() as tot;");
        $numRows = $stmt_tot_rows->fetchAll();
        
        $drillDownData = array(
            '0' => $rows,
            '1' => array($numRows[0])
        );
        
        return $drillDownData;

    }

}
