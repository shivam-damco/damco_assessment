<?php

/**
 * @author  Harpreet Singh
 * @date    03 June, 2014
 * @version 1.0
 * 
 * Core Controller to handle common operations  
 */

class Damco_Core_CoreController extends Zend_Controller_Action {

    protected $_dbName = null;
    protected $_flashMessenger = null;
    protected $_translate = null;
     protected $_auth;
    
    protected $_user = null;
    protected $_lang = null;
    
    protected $_status = array();

    /**
     * Method to initialize core controller
     */
    public function init() {
        
        $this->_auth = Zend_Auth::getInstance();
        if (!$this->_auth->hasIdentity() && $this->getRequest()->getModuleName() != 'survey' && $this->getRequest()->getControllerName() != 'index') 
        {
           // $this->_redirector->gotoSimple('index', '', '');
            $this->redirect(HTTP_PATH);
        }
        $module = $this->getRequest()->getModuleName();
        $this->view->controller = $controller = $this->getRequest()->getControllerName(); //added by Dipa for ESR PDF generation
        $action = $this->getRequest()->getActionName(); //added by Dipa for ESR PDF generation
        $identity = Zend_Auth::getInstance();        
        $this->_user = $identity->getIdentity();
        $session_path = new Zend_Session_Namespace('url_path');
        $fromDate = $this->getRequest()->getParam('fromDate');
        $toDate = $this->getRequest()->getParam('toDate');
        if (!empty($fromDate)) {
            $fromDate = str_replace('/', '-', $fromDate);
            $this->getRequest()->setParam('fromDate', date('Y-m-d', strtotime($fromDate)));
        }
        if (!empty($toDate)) {
            $toDate = str_replace('/', '-', $toDate);
            $this->getRequest()->setParam('toDate', date('Y-m-d', strtotime($toDate)));
        }

        $pdfFlag = 0;
        if ( !$identity->hasIdentity() 
             && $module != 'survey'
             ) {
                    $qryString = $this->getRequest()->getQuery("chkme");                   
                        if(($controller="esr" || $controller="mdr" || $controller="allquestion"  || $controller="questionanalysis"  || $controller="ranking"  || $controller="avgbyquestion")
                        && ($action = "index")
                        && !empty($qryString) ) //added by Dipa for ESR PDF generation                
                    {
                        
                        $actualData = unserialize(urldecode($qryString));
                       // print_R($actualData);die;
                        if ( isset($actualData["userinfo"]) 
                                && !empty($actualData["userinfo"])
                           /* &&  isset($actualData["accessHierarchy"]) */                               
                          )
                        {
                            if($actualData["userinfo"]["role_id"] !=1 
                                &&  !isset($actualData["accessHierarchy"])
                                )
                            {
                                $this->redirect(HTTP_PATH);
                            }
                            else
                            {
                                $storage = new Zend_Auth_Storage_Session();
                                $logindata = (object) 'auth';
                                if (!isset($this->_user)) 
                                {
                                    $this->_user = new stdClass();
                                }
                                //echo"here";die;
                                $logindata->id = $this->_user->id = $actualData["userinfo"]["id"];
                                $logindata->role_id = $this->_user->role_id =  $actualData["userinfo"]["role_id"];
                                $logindata->dealer_id = $this->_user->dealer_id = isset($actualData["userinfo"]["dealer_id"]) ? $actualData["userinfo"]["dealer_id"] : '';
                                $logindata->branch_id = $this->_user->branch_id = isset($actualData["userinfo"]["branch_id"]) ? $actualData["userinfo"]["branch_id"] : '';
                                $logindata->sales_region_id = $this->_user->sales_region_id = isset($actualData["userinfo"]["sales_region_id"]) ? $actualData["userinfo"]["sales_region_id"] : '';
                                $logindata->lang_id = $this->_user->lang_id = isset($actualData["userinfo"]["langid"]) ? $actualData["userinfo"]["langid"] : '1';

                                $storage->write($logindata);
                                //do nothing
                                //$pdfFlag = 1;
                            }
                        }
                        else
                        { //echo"there";die;
                            $this->redirect(HTTP_PATH);
                        }
                    }
                    else { //echo"nowhere";die;
                         $this->redirect(HTTP_PATH);                
                    }
           
        }
        ///changes made by dipa EOC 9/15/14 1:21 PM
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->_translate = Zend_Registry::get('Zend_Translate');
        
        $config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $resources = $config->getOption('resources');
        $this->_dbName = $resources['db']['params']['dbname'];
        
        $session = new Zend_Session_Namespace('APD');
        $session->dbname = $this->_dbName;
        
        $eventStatusObj = new Event_Model_EventStatus();
        $result = $eventStatusObj->getAll( );
        if (is_array($result) ) {
            foreach ($result as $value) {
                $this->_status[$value['status']] = $value['label'];
            }
        }
        
        if ( $module != 'survey') { 
            if ( $this->getParam( 'langid' ) ) {
               $this->_user->lang_id = $this->getParam( 'langid' );
            }
            else {
               $this->_user->lang_id = isset($this->_user->lang_id)
                       ?$this->_user->lang_id:'1';
            }
            $this->view->lang_id =  $this->_user->lang_id;
        }
        
        $this->_lang = array(
            'lang_name' => isset($this->_user->lang_name)?$this->_user->lang_name:'English',
            'lang_code' => isset($this->_user->lang_code)?$this->_user->lang_code:'en-GB',
            'langid' => isset($this->_user->lang_id)?$this->_user->lang_id:'1',
            'direction' => isset($this->_user->direction)?$this->_user->direction:'ltr',
        );
        
        $this->view->lang = $this->_lang;
        
        $this->_getlanguagefile();

        $module =  Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
        $controller =  Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        $action =  Zend_Controller_Front::getInstance()->getRequest()->getActionName();
        
        if ( $controller != 'ajax' ) {
            $session_path->module = $module;
            $session_path->controller =$controller;
            $session_path->action =$action;
        }
    }
    
    /**
     * Method to set access hierarchy resources in session
     */
    private function _accessHierarchy() {
        if ( !Zend_Session::namespaceIsset('access_heirarchy') ) {
            $objAccessHierarchy = new Event_Model_CompanyStructure( );
            
            $result = $objAccessHierarchy->getAccessHierarchy(
                'usp_getAccessHierarchy',
                array(
                    $this->_dbName,
                    $this->_user->id,
                    $this->_user->role_id,
                    (($this->_user->role_id == '2')?'':''),
                    (''),
                    (($this->_user->role_id == '3')?'':''),
                    (($this->_user->role_id == '4')?$this->_user->dealer_id:''),
                    (($this->_user->role_id == '2')?$this->_user->branch_id:''),
                    (($this->_user->role_id == '3')?$this->_user->sales_region_id:''),
                    (($this->_user->role_id == '3')?'':''),
                ), FALSE
            );

            $accessHierarchy = array(
                'branches' => array(),
                'markets' => array(),
                'sales_regions' => array(),
                'dealers' => array(),
                'asm' => array(),
            );

            if ( $this->_user->role_id <= '2' ) {
                foreach ( $result[0] as $value ) {
                    if ( !empty($value) ) {
                        $accessHierarchy['branches'][] = $value['structid'];
                    }
                }
            }
            if ( $this->_user->role_id <= '2' ) {
                foreach ( $result[1] as $value ) {
                    if ( !empty($value) ) {
                        $accessHierarchy['markets'][] = $value['structid'];
                    }
                }
            }
            if ( $this->_user->role_id <= '2' ) {
                foreach ( $result[2] as $value ) {
                    if ( !empty($value) ) {
                        $accessHierarchy['sales_regions'][] = $value['structid'];
                    }
                }
            }

            if ( $this->_user->role_id == '3' ) {
                foreach ( $result[0] as $value ) {
                    if ( !empty($value) ) {
                        $accessHierarchy['sales_regions'][] = $value['structid'];
                    }
                }
                
                $result = $result[1];
            }
            elseif ( $this->_user->role_id == '4' ) {
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

            foreach ($result as $value) {
                if ( !empty($value) ) {
                    $accessHierarchy['dealers'][] = $value['id'];
                }
            }

            $session = new Zend_Session_Namespace('access_heirarchy');
            $session->accessHierarchy = $accessHierarchy;
        }
    }
    
    private function _getlanguagefile( ) {
        if ( isset( $this->_user->lang_id ) ) {
            $langID = $this->_user->lang_id;
        }
        else {
            $langID = '1';
        }
        
        switch ( $langID ) {
            case '17':
            case '16':
            case '14':
            case '12':
            case '11':
            case '7':
            case '6':
            case '3':
                break;
            default :
                $langID = '1';
                break;
        }
        
        $this->view->dataTablesLanguage = HTTP_PATH.'/survey/ajax/getlanguage/?langid='.$langID;
    }
}
