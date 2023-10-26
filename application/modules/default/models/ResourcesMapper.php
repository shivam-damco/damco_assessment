<?php

class Default_Model_ResourcesMapper
{

	protected $_db_table;
	protected $_model_obj;
	
	public function __construct()
	{
		//Instantiate the Table Data Gateway for the Resources table
		$this->_db_table = new Default_Model_DbTable_Resources();
		//Instantiate the object for the Resources table
		$this->_model_obj = new Default_Model_Resources();
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
	 * This function  is used to get model object (*of Resources model)
	 * @author Neeraj Garg
	 */
	public function getModel()
	{
		return $this->_model_obj;
	}
	
	/**
	* This function is used to set attributes of Resources Model object
	**/
	protected function _hydrate($row, $parentRec)
    {
		$modelObj = new Default_Model_Resources();
        $modelObj->id = $row->id;
        $modelObj->module_id = $row->module_id;
        $modelObj->controller = $row->controller;
        $modelObj->action = $row->action;
        $modelObj->action_label = $row->action_label;
        $modelObj->is_tool = $row->is_tool;
        $modelObj->created = $row->created;
        $modelObj->modified = $row->modified;
		
		//add parent fields to record
		$modelObj->module_name = $parentRec['module'];
		$modelObj->module_label = $parentRec['module_label'];
		
        return $modelObj;
    }
	
	/**
	* This function is used to fetch all Resources records
	**/
	public function fetchAll()
    {
        $resultSet = $this->_db_table->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
			$parentRec = $row->findParentRow('Default_Model_DbTable_Modules');

            $entries[] = $this->_hydrate($row, $parentRec);
        }

        return $entries;
    }
}

