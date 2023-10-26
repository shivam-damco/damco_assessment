<?php

class Default_Model_DbTable_RolesResources extends Zend_Db_Table_Abstract
{

    protected $_name = 'acl_roles_resources';

	protected $_referenceMap    = array(
        'Roles' => array(
            'columns'           => array('role_id'),
            'refTableClass'     => 'Default_Model_DbTable_Roles',
            'refColumns'        => array('id')
        ),
        'Resources' => array(
            'columns'           => array('resource_id'),
            'refTableClass'     => 'Default_Model_DbTable_Resources',
            'refColumns'        => array('id')
        )
    );
}

