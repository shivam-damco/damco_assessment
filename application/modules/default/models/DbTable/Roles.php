<?php

class Default_Model_DbTable_Roles extends Zend_Db_Table_Abstract
{

    protected $_name = 'acl_roles';
	protected $_dependentTables = array('Default_Model_DbTable_RolesResources');

}

