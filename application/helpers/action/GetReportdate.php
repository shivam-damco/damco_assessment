<?php

class Damco_Action_Helper_GetReportdate extends Zend_Controller_Action_Helper_Abstract{

    
    function GetReportdate($date='',$retType) {
        
        $retdt = "";
        switch($retType)
        {
            case "rolling12_first_date":   
              
                $d = new DateTime( $date );                 
                $monthStartingDate = $d->sub(new DateInterval('P1Y'));               
                $retdt = $monthStartingDate->format('Y-m-d'); 
                
            break;
            case "enddate":               
                $dt = new DateTime( $date ); 
                $lastMonth = $dt->sub(new DateInterval('P1M'));
                $retdt = $lastMonth->format('Y-m-t');               
            break;
            case "previous3month":               
                $dt = new DateTime( $date ); 
                $lastMonth = $dt->sub(new DateInterval('P3M'));
                $retdt = $lastMonth->format('Y-m-d');               
            break;
            case "currentmonthname":               
                $dateObj   = DateTime::createFromFormat('!m', $date);
                $retdt = $dateObj->format('F');              
            break;
            case "currentyear":
                
            break;
           
        }
        return $retdt;
    }

     
    function direct($date='',$retType) {
        
        return $this->GetReportdate($date,$retType);
    }

}
