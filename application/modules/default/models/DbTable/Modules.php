<?php

class Default_Model_DbTable_Modules extends Zend_Db_Table_Abstract
{

    protected $_name = 'acl_modules';
    protected $_dependentTables = array('Default_Model_DbTable_Resources');
    
     public function getSelect($cols='*') {
        return $this->select()->from($this->_name, $cols);
    }
}

