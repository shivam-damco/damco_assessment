<?php

class Default_Model_Roles
{
	protected $id;
	protected $name;
	protected $created;
	protected $modified;
         
	/**	
	*	upon construction, map the values from the $roles_row if available
	**/
	public function __construct($roles_row = null)
	{
		if( !is_null($roles_row) && $roles_row instanceof Zend_Db_Table_Row ) {
			$this->id = $roles_row->id;
			$this->name = $roles_row->name;
			$this->created = $roles_row->created;
			$this->modified = $roles_row->modified;
		}
	}
    
	/**
	*	magic function __set to set the attributes of the Roles model
	**/
	public function __set($name, $value)
	{       
		$this->$name = $value;
	}
   
	/**
	*	magic function __get to get the attributes of the Roles model
	**/
	public function __get($name)
	{
		return $this->$name;
	}

}

