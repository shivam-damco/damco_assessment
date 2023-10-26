<?php

class Default_Model_DbTable_Resources extends Zend_Db_Table_Abstract {

    protected $_name = 'acl_resources';
    protected $_dependentTables = array('Default_Model_DbTable_RolesResources');
    protected $_referenceMap = array(
        'Modules' => array(
            'columns' => array('module_id'),
            'refTableClass' => 'Default_Model_DbTable_Modules',
            'refColumns' => array('id')
        )
    );

    public function getSelect($cols='*') {
        return $this->select()->from($this->_name, $cols);
    }
}
