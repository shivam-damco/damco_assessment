<?php

class Default_Model_Resources
{
	protected $id;
	protected $module_id;
	protected $controller;
	protected $action;
	protected $action_label;
	protected $is_tool;
	protected $created;
	protected $modified;
         
	/**	
	*	upon construction, map the values from the $resources_row if available
	**/
	public function __construct($resources_row = null)
	{
		if( !is_null($resources_row) && $resources_row instanceof Zend_Db_Table_Row ) {
			$this->id = $resources_row->id;
			$this->module_id = $resources_row->module_id;
			$this->controller = $resources_row->controller;
			$this->action = $resources_row->action;
			$this->action_label = $resources_row->action_label;
			$this->is_tool = $resources_row->is_tool;
			$this->created = $resources_row->created;
			$this->modified = $resources_row->modified;
		}
	}
    
	/**
	*	magic function __set to set the attributes of the Resources model
	**/
	public function __set($name, $value)
	{       
		$this->$name = $value;
	}
   
	/**
	*	magic function __get to get the attributes of the Resources model
	**/
	public function __get($name)
	{
		return $this->$name;
	}

}