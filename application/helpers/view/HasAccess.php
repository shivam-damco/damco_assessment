<?php

/**
 * Helper class to check access for logged in user to provided controller and action
 * 
 * @author Harpreet Singh
 * @date   2 June, 2014
 * @version 1.0
 */

class Damco_View_Helper_HasAccess {
    private $_acl;
    
    /**
     * Constructor to initialize Has Access class
     */
    function __construct( ) {
    }

    public function hasAccess( $role, $module, $controller, $action ) {
        if ( !$this->_acl ) {
            $objAuth = Zend_Auth::getInstance();
            $this->_acl = Damco_ACL_Factory::get($objAuth, FALSE);
        }
        return $this->_acl->isAllowed($role, $module.'::'.$controller.'::'.$action);
    }
}