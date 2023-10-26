<?php

/**
 * @author  Harpreet Singh
 * @date    06 June, 2014
 * @version 1.0
 * 
 * Class to handle access hierarchy operations  
 */
class Damco_Core_AccessHierarchy {
    
    /**
     * Method to get access hierarchy resources
     */
    final public function get($branch = '', $market = '', $salesRegionID = '',
            $dealer = '', $asm = '') {
        $apd = new Zend_Session_Namespace('APD');
        $session = new Zend_Session_Namespace('access_heirarchy');
        $user = Zend_Auth::getInstance()->getIdentity();
        $objAccessHierarchy = new Event_Model_CompanyStructure( );
        if((empty($salesRegionID) && ($_SESSION['url_path']['module'].'_'.$_SESSION['url_path']['controller']=='performance_target')) && $user->role_id == '3'){
            $paramArray = array(
                $apd->dbname,
                $user->id,
                $user->role_id,$user->branch_id,'','','','','',''
               );
        }else{
            $paramArray =  array(
                $apd->dbname,
                $user->id,
                $user->role_id,
                (( !empty($branch)
                       && in_array($branch, $session->accessHierarchy['branches']))
                        ?($branch):
                        (($user->role_id == '2')?'':'')),
                ((!empty($market)
                       && in_array($market, $session->accessHierarchy['markets']))
                        ?$market:''),
                (($user->role_id == '3')?'':
                    (( !empty($asm)
                       && in_array($asm, $session->accessHierarchy['asm']))
                        ?$asm:'')),
                (($user->role_id == '4')?$user->dealer_id:
                    (( !empty($dealer)
                       && in_array($dealer, $session->accessHierarchy['dealers']))
                        ?$dealer:'')),
                (($user->role_id == '2')?$user->branch_id:''),
//                (($user->role_id == '3')?$user->sales_region_id:''),
                (($user->role_id == '3')?
                        ((!empty($salesRegionID) && ($_SESSION['url_path']['module'].'_'.$_SESSION['url_path']['controller']=='performance_target'))
                        ? "'".$salesRegionID."'":$user->sales_region_id):''),
                (( !empty($salesRegionID)
                       && in_array($salesRegionID, $session->accessHierarchy['sales_regions']))
                        ?($salesRegionID):
                        (($user->role_id == '3')?'':''))
            );
        }
        
        $result = $objAccessHierarchy->getAccessHierarchy(
            'usp_getAccessHierarchy',
            $paramArray, FALSE
        );

        $accessHierarchy = array(
            'branches' => array(),
            'markets' => array(),
            'sales_regions' => array(),
            'dealers' => array(),
            'asm' => array(),
        );
        if ( $user->role_id <= '2' ) {
            foreach ( $result[0] as $value ) {
                if ( !empty($value) ) {
                    $accessHierarchy['branches'][] = array(
                        'id' => $value['structid'],
                        'name' => ( $value['struct_name'] ),
                    );
                }
            }
        }
        if ( $user->role_id <= '2' ) {
            foreach ( $result[1] as $value ) {
                if ( !empty($value) ) {
                    $accessHierarchy['markets'][] = array(
                        'id' => $value['structid'],
                        'name' => ( $value['struct_name'] ),
                    );
                }
            }
        }
        
        /*if ( $user->role_id <= '2' ) {
             if ( !($user->role_id == '1'
                    && empty($branch)
                    && empty($market)
                    && empty($asm) ) ) {
                foreach ( $result[2] as $value ) {
                    if ( !empty($value) ) {
                        $accessHierarchy['asm'][] = array(
                            'id' => $value['userid'],
                            'name' => $value['first_name'] . ' ' . $value['last_name'],
                        );
                    }
                }
            }
        }*/
        
        if ( $user->role_id <= '2' ) {
             if ( !($user->role_id == '1'
                    && empty($branch)
                    && empty($market)
                    && empty($salesRegionID) ) ) {
                foreach ( $result[2] as $value ) {
                    if ( !empty($value) ) {
                        $accessHierarchy['sales_regions'][] = array(
                            'id' => $value['structid'],
                            'name' => ( $value['struct_name'] ),
                        );
                    }
                }
            }
        }

        if ( $user->role_id == '3' ) {
            foreach ( $result[0] as $value ) {
                if ( !empty($value) ) {
                    $accessHierarchy['sales_regions'][] = array(
                        'id' => $value['structid'],
                        'name' => ( $value['struct_name'] ),
                    );
                }
            }
            
            $result = $result[1];
        }
        elseif ( $user->role_id == '4' ) {
            $result = $result;
        }
        else {
            if ( isset( $result[3] ) ) {
                $result = $result[3];
            }
            elseif ( isset( $result[2] ) ) {
                $result = $result[2];
            }
            elseif ( isset( $result[1] ) ) {
                $result = $result[1];
            }
            else {
                $result = $result[0];
            }
        }

        if ( !($user->role_id == '1'
             && empty($branch)
             && empty($market)
             && empty($salesRegionID) ) ) {
            foreach ($result as $value) {
                if ( !empty($value) ) {
                    $accessHierarchy['dealers'][] = array(
                        'id' => $value['id'],
                        'name' => ( $value['dealer_name'] ),
                    );
                }
            }
        }
		
        if ( $user->role_id != '1'
             && empty( $accessHierarchy['dealers'] ) ) {
            $accessHierarchy['dealers'][] = array(
                'id' => '-1',
                'name' => '', 
            );
        }

        return $accessHierarchy;
    }
}
