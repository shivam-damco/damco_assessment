<?php

class Default_Model_RolesResources
{
	protected $id;
	protected $role_id;
	protected $resource_id;
	protected $created;
	protected $modified;
         
	/**	
	*	upon construction, map the values from the $roles_resources_row if available
	**/
	public function __construct($roles_resources_row = null)
	{
		if( !is_null($roles_resources_row) && $roles_resources_row instanceof Zend_Db_Table_Row ) {
			$this->id = $roles_resources_row->id;
			$this->role_id = $roles_resources_row->role_id;
			$this->resource_id = $roles_resources_row->resource_id;
			$this->created = $roles_resources_row->created;
			$this->modified = $roles_resources_row->modified;
		}
	}
    
	/**
	*	magic function __set to set the attributes of the RolesResources model
	**/
	public function __set($name, $value)
	{       
		$this->$name = $value;
	}
   
	/**
	*	magic function __get to get the attributes of the RolesResources model
	**/
	public function __get($name)
	{
		return $this->$name;
	}

}

