<?php

class Default_Model_UsersMapper
{

	protected $_db_table;
	protected $_model_obj;
	
	public function __construct()
	{
		//Instantiate the Table Data Gateway for the Users table
		$this->_db_table = new Default_Model_DbTable_Users();
		//Instantiate the object for the Users table
		$this->_model_obj = new Default_Model_Users();
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
	 * This function  is used to get model object (*of Users model)
	 * @author Neeraj Garg
	 */
	public function getModel()
	{
		return $this->_model_obj;
	}
	
	public function auth($data = array())
	{
		$username = $data['username'];
		$password = $data['password'];
		$auth = Zend_Auth::getInstance();
		$db = Zend_Db_Table::getDefaultAdapter(); 
		//Zend_Loader::loadClass ('Zend_Auth_Adapter_DbTable');
		$authAdapter = new Zend_Auth_Adapter_DbTable ($db); 
		$authAdapter->setTableName('users'); 
		$authAdapter->setIdentityColumn('username');
		$authAdapter->setCredentialColumn('password');
		$authAdapter->setIdentity($username);
		$authAdapter->setCredential(md5($password));
		$auth = Zend_Auth::getInstance();			
		$result = $auth->authenticate($authAdapter);
		
		if($result->isValid()){
			$storage	= new Zend_Auth_Storage_Session();
			$logindata	= $authAdapter->getResultRowObject(null,'password');
                        
                        
                        /* Temporary User resource assignment */
                    switch ( $logindata->role_id ) {
                        case '2':
                            if ( $logindata->id == '1' ) {
                                $logindata->branch_id = '02';
                            }
                            if ( $logindata->id == '3' ) {
                                $logindata->branch_id = '02,16';
                            }
                            $logindata->role_name = 'branch';
                            break;
                        case '3':
                            $logindata->role_name = 'asm';
                            $logindata->asm_id = 'asmfr.eclipse';
                            $logindata->branch_id = '02';
                            $logindata->sales_region_id = "'0116UKS','0116UKN'";
                            break;
                        case '4':
                            $logindata->role_name = 'dealer';
                            $logindata->dealer_id = '665';
                            break;
                        case '1':
                            $logindata->role_name = 'admin';
                            break;
                    }
                    /* Temporary User resource assignment ends */
//                        print_r($logindata->id);die;
                        
                        
			$storage->write($logindata);	
			return true;
		}
 		return false;
	}
}

