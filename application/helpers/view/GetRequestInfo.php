<?php

/**
 * Helper class to create required layouts
 * 
 * @author Dipanwita Kundu
 * @date   27 May, 2014
 * @version 1.0
 */
class Damco_View_Helper_GetRequestinfo  extends Zend_View_Helper_Abstract
{

    /**
     * Constructor to initialize Layouts class
     */
    function __construct() {

    }

    public function getRequestInfo() {

       return Zend_Controller_Front::getInstance()
            ->getRequest();
    }

}
