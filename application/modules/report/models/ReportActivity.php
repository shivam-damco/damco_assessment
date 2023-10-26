<?php

/**
 * Model class to handle User Activity
 * 
 * @author  Amit kumar
 * @date    29 Sep, 2014
 * @version 1.0
 */
class Report_Model_ReportActivity extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();

        $this->_name = 'user_report_activity';
    }
    
    /**
    * Method to return report name
    * @return type
    */
    public function getUserReport() {
        $rs = $this->select()
        ->from('acl_roles', array('id','label'))
        ->order(array('label'))
        ->setIntegrityCheck(FALSE);//->__toString();
        return $rs->query()->fetchAll();
    }
    
    /**
     * Method to return report name
     * @return type
     */
    public function getResourceData($module,$controller,$action,$slug) {
       $rs = $this->select()
        ->from(array('ur'=>'user_reports'), array('ur.id','ur.report_name','ur.report_slug'))
        ->joinleft(array('r' => 'acl_resources'),
            'r.id = ur.resource_id',array())
        ->joinleft(array('m' => 'acl_modules'),
                'm.id = r.module_id',array())
        ->where('m.module="'.$module.'" and r.controller="'.$controller.'"
                         and r.action="'.$action.'" and ur.report_slug="'.$slug.'"')
        ->setIntegrityCheck(FALSE);//->__toString();
        return $rs->query()->fetch();
    }
    
  }