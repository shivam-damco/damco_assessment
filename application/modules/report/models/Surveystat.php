<?php

/**
 * Model class to handle survey events reporting data
 * 
 * @author Amit kumar
 * @date   20 Aug, 2014
 * @version 1.0
 */
class Report_Model_Surveystat extends Default_Model_Core {

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
    public function getSurveystatData($spName, $inParam, $debug = FALSE) {
        return $this->_spObj->getSpData($spName, $inParam, $debug);
    }

    public function getDateRange($column) {
        $rs = $this->select()
                ->from($this->_name, array('min_date' => 'MIN(' . $column . ')',
            'max_date' => 'MAX(' . $column . ')')
        );
        return $rs->query()->fetch();
    }

    public function getTotalSurveyStatics($eventType, $startDate, $endDate, $dealers, $model)
    {
        $condition = '';
        if(!empty($dealers)) {
            $condition .= " AND e.dealer_id in ($dealers)";
        }
        if (!empty($model)) {
            $condition .= " AND c.`vehicle_code_desc` ='" . $model . "'";
        }
        if (!empty($eventType)) {
            $condition .= " AND e.event_typeid ='" . $eventType . "'";
        }
        $sql = "SELECT
                    SUM(CASE WHEN e.email_sent='Yes' THEN 1 ELSE 0 END) AS  surveys_sent,
                    SUM(CASE WHEN e.event_status IN ('Closed','Did not qualify') THEN 1 ELSE 0 END) AS  surveys_completed,
                    SUM(CASE WHEN e.event_status = 'Decline' THEN 1 ELSE 0 END) AS surveys_decline,
                    SUM(CASE WHEN e.event_status = 'Bounce Removed' THEN 1 ELSE 0 END) AS surveys_error
                    FROM survey_events e
                    LEFT JOIN dealers d ON e.dealer_id=d.id
                    LEFT JOIN customers AS c ON c.`id`= e.`customerid`
                    WHERE  e.email_send_date BETWEEN '$startDate'
                    AND '$endDate' $condition";
        return $this->db->query($sql)->fetch();
    }
}
