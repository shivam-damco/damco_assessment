<?php

class Default_Model_RolesResourcesMapper
{

	protected $_db_table;
	protected $_model_obj;
	
	public function __construct()
	{
		//Instantiate the Table Data Gateway for the RolesResources table
		$this->_db_table = new Default_Model_DbTable_RolesResources();
		//Instantiate the object for the Resources table
		$this->_model_obj = new Default_Model_RolesResources();
	}
	
	/**
	 * This function is used to get db table object
	 * @author Neeraj Garg
	 */
	public function getDbTable()
	{
		return $this->_db_table;
	}
	
	/**
	 * This function  is used to get model object (*of RolesResources model)
	 * @author Neeraj Garg
	 */
	public function getModel()
	{
		return $this->_model_obj;
	}
	
	/**
	* This function is used to set attributes of RolesResources Model object
	**/
	protected function _hydrate($row)
    {
		$modelObj = new Default_Model_RolesResources();
        $modelObj->role_id = $row['role_id'];
        $modelObj->resource_id = $row['resource_id'];
        $modelObj->roleName = $row['roleName'];
        $modelObj->module = $row['module'];
        $modelObj->controller = $row['controller'];
        $modelObj->action = $row['action'];
		
        return $modelObj;
    }
	
	/**
	* This function is used to fetch all Resources records
	**/
	/*public function fetchAll()
    {
        $resultSet = $this->_db_table->fetchAll();
		//echo"<pre>"; print_r($resultSet); die();
        $entries   = array();
        foreach ($resultSet as $row) {
			$parentRole = $row->findParentRow('Default_Model_DbTable_Roles');
			$parentResource = $row->findParentRow('Default_Model_DbTable_Resources');
		echo"Roles<pre>"; print_r($parentRole); 
		echo"Resource<pre>"; print_r($parentResource); die();
            $entries[] = $this->_hydrate($row, $parentRec);
        }

        return $entries;
    }*/
	
	public function fetchAll() {
	
      $select = $this->_db_table->select()
                     ->from( array('arr' => 'acl_roles_resources'), array('role_id', 'resource_id') )
                     ->join( array('rl' => 'acl_roles'), 'arr.role_id = rl.id', array('roleName'=>'name'))
                     ->join( array('rs' => 'acl_resources'), 'arr.resource_id = rs.id', array('controller', 'action') )
					  ->join( array('md' => 'acl_modules'), 'rs.module_id = md.id', array('module') )
                     ->setIntegrityCheck(false);
      $resultSet = $this->_db_table->fetchAll($select);
	  
	   $entries   = array();
        foreach ($resultSet as $row) {
            $entries[] = $this->_hydrate($row);
        }

        return $entries;
   }
}

