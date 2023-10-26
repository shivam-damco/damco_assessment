<?php


class Damco_Action_Helper_GetEventID extends Zend_Controller_Action_Helper_Abstract {
    function getEventID( $string, $type ) {
        $eventModelObj = new Event_Model_Events( );
        switch ( $type ) {
            case 'decrypt':
                $result = $eventModelObj->fetchRow(' assessment_code = "'.$string.'" ');
                
                if (is_object($result) ) {
                    $result = $result->toArray();
                    
                    return $result['eventid'];
                }
                return FALSE;
            case 'encrypt':
            default:
                $result = $eventModelObj->fetchRow(' eventid = "'.$string.'" ');
                if (is_object($result) ) {
                    $result = $result->toArray();
                    
                    if ( empty( $result['assessment_code'] ) ) {
                        $assessmentCode = md5(microtime().$result['customerid']);
                        $eventModelObj->update(array(
                            'assessment_code' => $assessmentCode,
                        ), ' eventid = "'.$string.'" ');
                        return $assessmentCode;
                    }
                    
                    return $result['assessment_code'];
                }
                return FALSE;
        }
    }

    function direct($text, $type = 'encrypt' ) {
        return $this->getEventID($text, $type);
    }
}