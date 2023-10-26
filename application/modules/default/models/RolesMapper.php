<?php

class Default_Model_RolesMapper
{
	protected $_db_table;
	protected $_model_obj;
	
	public function __construct()
	{
		//Instantiate the Table Data Gateway for the Roles table
		$this->_db_table = new Default_Model_DbTable_Roles();
		//Instantiate the object for the Roles table
		$this->_model_obj = new Default_Model_Roles();
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
	 * This function  is used to get model object (*of Roles model)
	 * @author Neeraj Garg
	 */
	public function getModel()
	{
		return $this->_model_obj;
	}
	
	/**
	* This function is used to set attributes of Roles Model object
	**/
	protected function _hydrate($row)
    {
		$modelObj = new Default_Model_Roles();
        $modelObj->id = $row->id;
        $modelObj->name = $row->name;
        $modelObj->created = $row->created;
        $modelObj->modified = $row->modified;
 
        return $modelObj;
    }
	
	/**
	* This function is used to fetch all role records
	**/
	public function fetchAll()
    {
        $resultSet = $this->_db_table->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entries[] = $this->_hydrate($row);
        }
 
        return $entries;
    }
	
	public function find($id)
    {
        $result = $this->_db_table->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
 
        return $this->_hydrate($row);
    }

}

