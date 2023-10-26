<?php

class Default_Model_Modules
{
	protected $id;
	protected $module;
	protected $module_label;
	
	/**	
	*	upon construction, map the values from the $modules_row if available
	**/
	public function __construct($modules_row = null)
	{
		if( !is_null($modules_row) && $modules_row instanceof Zend_Db_Table_Row ) {
			$this->id = $modules_row->id;
			$this->module = $modules_row->module;
			$this->module_label = $modules_row->module_label;
		}
	}
    
	/**
	*	magic function __set to set the attributes of the Modules model
	**/
	public function __set($name, $value)
	{       
		$this->$name = $value;
	}
   
	/**
	*	magic function __get to get the attributes of the Modules model
	**/
	public function __get($name)
	{
		return $this->$name;
	}
}

