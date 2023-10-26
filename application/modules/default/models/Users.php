<?php

class Default_Model_Users
{

	protected $id; 
	protected $username; 
	protected $password; 
	protected $first_name; 
	protected $last_name; 
	protected $email; 
	protected $is_superadmin; 
	protected $is_active; 
	protected $password_token; 
	protected $created_by; 
	protected $created; 
	protected $modified_by; 
	protected $modified; 
	protected $role_id;
	
	/**	
	*	upon construction, map the values from the $users_row if available
	**/
	public function __construct($users_row = null)
	{
		if( !is_null($users_row) && $users_row instanceof Zend_Db_Table_Row ) {
			$this->id = $users_row->id;
			$this->username = $users_row->username;
			$this->password = $users_row->password;
			$this->first_name = $users_row->first_name;
			$this->last_name = $users_row->last_name;
			$this->email = $users_row->email;
			$this->is_superadmin = $users_row->is_superadmin;
			$this->is_active = $users_row->is_active;
			$this->password_token = $users_row->password_token;
			$this->created_by = $users_row->created_by;
			$this->created = $users_row->created;
			$this->modified = $users_row->modified;
			$this->modified_by = $users_row->modified_by;
			$this->role_id = $users_row->role_id;
                        
		}
	}
    
	/**
	*	magic function __set to set the attributes of the Users model
	**/
	public function __set($name, $value)
	{       
		$this->$name = $value;
	}
   
	/**
	*	magic function __get to get the attributes of the Users model
	**/
	public function __get($name)
	{
		return $this->$name;
	}
        
        

}

