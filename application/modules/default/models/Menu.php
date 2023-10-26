<?php

/**
 * Model class to handle navigation menu operations
 * 
 * @author Harpreet Singh
 * @date   18 June, 2014
 * @version 1.0
 */

class Default_Model_Menu extends Default_Model_Core {
    
    public function __construct() {
        parent::__construct();
        $this->_name = 'acl_menu';
    }
    
    public function getNavigation( $roleID ) {
        $rs = $this->select()
                ->from(array('m' => $this->_name),
                    array('id' => new Zend_Db_Expr('DISTINCT m.id'), 'm.parent_id',
                        'm.resource_id', 'm.menu_name', 'm.menu_url'))
                ->joinleft(array('rr' => 'acl_roles_resources'),
                    'm.resource_id = rr.resource_id',
                    array())
                ->where('m.is_active = "1" '
                        . ' AND (rr.role_id = "'.$roleID.'")'
                        . ' OR ( m.parent_id = "0" AND m.is_active = "1" )')
                ->setIntegrityCheck(FALSE)
                ->order('m.sort_order ASC');
//        die($rs->__toString());
        return $rs->query()->fetchAll();
    }
    
     public function getSelect($cols='*') {
        return $this->select()->from($this->_name, $cols);
    }
}