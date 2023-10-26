<?php

class Default_Model_ModulesMapper
{
	protected $_db_table;
	protected $_model_obj;
	
	public function __construct()
	{
		//Instantiate the Table Data Gateway for the Modules table
		$this->_db_table = new Default_Model_DbTable_Modules();
		//Instantiate the object for the Modules table
		$this->_model_obj = new Default_Model_Modules();
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
	 * This function  is used to get model object (*of Modules model)
	 * @author Neeraj Garg
	 */
	public function getModel()
	{
		return $this->_model_obj;
	}
	
	/**
	* This function is used to set attributes of Modules Model object
	**/
	protected function _hydrate($row)
    {
        $this->_model_obj->id = $row->id;
        $this->_model_obj->module = $row->module;
        $this->_model_obj->module_label = $row->module_label;
		
        return $this->_model_obj;
    }
	
	/**
	* This function is used to fetch all Modules records
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

}

