<?php

/**
 * @author  Harpreet Singh
 * @date    13 August, 2014
 * @version 1.0
 * 
 * Reporting functions library for common functions  
 */
class Damco_Core_ReportingFunctions {

    protected $_dbName = null;
    protected $_translate = null;
    protected $_user = null;
    protected $_lang = null;
    
    /**
     * Method to initialize reporting functions library
     */
    public function init() {
        $identity = Zend_Auth::getInstance();
        $this->_user = $identity->getIdentity();
        $this->_translate = Zend_Registry::get('Zend_Translate');
        
        $config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $resources = $config->getOption('resources');
        $this->_dbName = $resources['db']['params']['dbname'];
        
    }

    /**
     * Method to get markets
     * @param type $type
     * @param type $typeID
     * @return boolean
     */
    public function getMarkets( $type, $typeID ) {
        $dealerModelObj = new Dealer_Model_Dealers();
        $nationMarketsModelObj = new Dealer_Model_NationMarket();
        switch ( $type ) {
            case 'dealer':
                $result = $dealerModelObj->getDealerMarkets( $typeID );
                
                $markets = array();
                foreach ( $result as $value ) {
                    $markets[] = $value['market_name'];
                }
                return $markets;
                
            case 'nation':
                $result = $nationMarketsModelObj->fetchAll(' nation_id = "'.$typeID.'" ');
                if ( $result->count() > 0 ) {
                    $markets = array();
                    foreach ( $result->toArray() as $value ) {
                        $markets[] = $value['market_structid'];
                    }
                    return $markets;
                }
                else {
                    return FALSE;
                }
            default :
                return FALSE;
        }
    }

    /**
     * Method to get dealer ranking data
     * @param type $marketIDs
     * @param type $eventTypeID
     * @param type $startDate
     * @param type $endDate
     * @return type
     */
    public function getDealerRankingData( $marketIDs, $eventTypeID, $startDate, $endDate ) {
        $dealerModelObj = new Dealer_Model_Dealers();
        $result = $dealerModelObj->getDealerRankingData(
            'usp_getDealerRanking',
            array(
                $this->_dbName,
                $marketIDs,
                $eventTypeID,
                $startDate,
                $endDate,
            ),
            FALSE
        );
        return array(
            'dealer_data' => $result,
            'dealer_ranking' => $this->setDealerRanking($result),
        );
    }
    
    /**
     * Method to set dealer rank
     * @param type $dealerRankingData
     * @return type
     */    
    public function setDealerRanking( $dealerRankingData ) {
        if ( !empty( $dealerRankingData ) ) {
            $i = 1;  
            $condevnt= 4;
            $repeatrank =0;
            $samerank='n';
            $rank = array();
            foreach ($dealerRankingData as $key => $value) {
                if ( $key > 0 && $value['sample_size'] > $condevnt ) {
                    if ( $dealerRankingData[$key-1]['nps'] == $value['nps']
                         && $dealerRankingData[$key-1]['promoter_percent'] 
                            == $value['promoter_percent']
                        /* commented as per client 8/19/14 4:38 PM
                          && $dealerRankingData[$key-1]['promoter'] == $value['promoter'] */) {                         
                            if($samerank == "n"){
                                $i = $i-1;
                            }
                            $samerank='y';
                            $repeatrank++;
                            $rank[$value['dealer_id']] = $i; // added by dipa as per Harpreet 8/14/14 8:51 PM             
                        }
                    else {
                        if($samerank == "y"){
                            $i++;
                            ////
                            $i = $i+$repeatrank;
                            $samerank='n';
                            $repeatrank=0;
                        }                          
                        $rank[$value['dealer_id']] = $i++;                        
                    }
                }
                elseif ( $value['sample_size'] > $condevnt ) {                    
                    $rank[$value['dealer_id']] = $i++;
                }
                
            }          
            return $rank;
        }
        return array();
    }
    
    /**
     * Method to return dealer rank
     * @param type $dealerID
     * @param type $eventTypeID
     * @param type $startDate
     * @param type $endDate
     * @return boolean
     */
    public function getDealerRanking( $dealerID, $eventTypeID, $startDate, $endDate ) {
        $markets = $this->getMarkets('dealer', $dealerID);
        if ( $markets != FALSE ) {
            $result = $this->getDealerRankingData( '"'.implode('","', $markets).'"', 
                    $eventTypeID, $startDate, $endDate);
            
            if ( !empty( $result['dealer_ranking'] ) ) {
                return array(
                    'dealer_rank' => isset( $result['dealer_ranking'][$dealerID] )
                        ? $result['dealer_ranking'][$dealerID] : '',
                    'total_dealers' => count( $result['dealer_ranking'] ),
                );
            }
            return FALSE;
        }
        return FALSE;
    }
    /**
     * Method to return comparison dealer
     * @param type $dealerID
     * @param type $eventTypeID
     * @param type $startDate
     * @param type $endDate
     * @return boolean
     */
    public function getComparisionDealers( $dealerID = NULL, $marketID=NULL,
            $salesRegionID = NULL, $branchID = NULL)
    {
        $dealerModelObj = new Dealer_Model_Dealers();
        $result = $dealerModelObj->getComparisionDealers($dealerID, $marketID,
            $salesRegionID, $branchID);
        $retarr = array();
        foreach($result as $ar)
        {
            $retarr[]=$ar["id"];
        }
        //print_r($result);die;
        return $retarr;
    }
    
    
    public function createDrilldownQueryString($get,$arrSkip='',$skipStr='no')
    {
    	$retStr='';
    	if($skipStr=='no') {
        	$retStr .="date_range_field=survey_submission_date&rpt_type=npsreport";
    	}
        foreach($get as $k=>$v)
        {
            if(!empty($v) && ($k !="module" && $k !="controller" && $k !="action" && $k !="period"))
            {
                if(!empty($arrSkip))
                {
                    if(!in_array($k,$arrSkip))
                    {
                        $retStr .="&".$k."=".$v;
                    }
                }
                else {
                    $retStr .="&".$k."=".$v;                    
                }
                
            }
        }
        return $retStr;
    }
    
    public function getallDealernationBasedondealer($get,$roleid,$logged_dealerid=0,$logged_branch_id=0)
    {  
        $dealerObj = new Dealer_Model_Dealers();
        $_eventModelCompanyObject = new Event_Model_CompanyStructure();
       //print_R($this->_eventModelCompanyObject);die;
        if ($roleid == '4') {
                $where = array('id' => $logged_dealerid);                    
                $marketid = $this->getMarkets("dealer", $logged_dealerid);
                $get['nation'] = implode('","', $marketid);
            } else {
                if ($roleid == '2' || $roleid == '3') {
                    $nationresult = $_eventModelCompanyObject->getnationlist($logged_branch_id);
                } else {
                    $nationresult = $_eventModelCompanyObject->getnationlist();
                }
                
                if (!isset($get['nation'])) {
                    $get['nation'] = $nationresult[0]['nationid'];
                } else {                        
                    $nationExist = FALSE;
                    foreach ($nationresult as $arn) {
                        if ($get['nation'] == $arn["nationid"]) {
                            $nationExist = TRUE;
                            break;
                        }
                    }
                }
                //
                if (!$nationExist) {
                    $get['nation'] = $nationresult[0]['nationid'];
                }     
               
                if (is_numeric($get['nation'])) {
                    $marketid = $this->getMarkets('nation', $get['nation']);
                    $get['nation'] = implode('","', $marketid);
                }                    
            }

            $arrAllDealersRelatedToThisNation = $dealerObj->select()
                                                        ->from(array("dealers"), array("id"))
                                                        ->where(' marketid IN ( "'.$get['nation'].'")')//->__toString();die;
                                                        ->query()
                                                        ->fetchall();
           // var_dump($arrAllDealersRelatedToThisNation);die;
            $dlrcnt = 0;
            foreach($arrAllDealersRelatedToThisNation as $av)
            {
                $allDealersRelatedToThisNation[]=$av['id'];
                $dlrcnt++;
            }
           
            return $allDealersRelatedToThisNation;
    }
}
