<?php
class Damco_ACL_Factory {
    private static $_sessionNameSpace = 'Damco_ACL_Namespace';
    private static $_objAuth;
    private static $_objAclSession;
    private static $_objAcl;
 
    public static function get(Zend_Auth $objAuth,$clearACL=false) {
 
    self::$_objAuth = $objAuth;
	self::$_objAclSession = new Zend_Session_Namespace(self::$_sessionNameSpace);
 
	if($clearACL) {self::_clear();}
 
	    if(isset(self::$_objAclSession->acl)) {
		return self::$_objAclSession->acl;
	    } else {
	        return self::_loadAclFromDB();
	    }
	}
 
    private static function _clear() {
        unset(self::$_objAclSession->acl);
    }
 
    private static function _saveAclToSession() {
        self::$_objAclSession->acl = self::$_objAcl;
    }
 
    private static function _loadAclFromDB() {
	
	$roleModel = new Default_Model_RolesMapper();
	$resourceModel = new Default_Model_ResourcesMapper();
	$roleResourceModel = new Default_Model_RolesResourcesMapper();
	
    $arrRoles = $roleModel->fetchAll();
	$arrResources = $resourceModel->fetchAll();
	$arrRoleResources = $roleResourceModel->fetchAll();
 
	self::$_objAcl = new Zend_Acl();
	
	// add all roles to the acl
	//self::$_objAcl->addRole(new Zend_Acl_Role('guest'));
	foreach($arrRoles as $role) {
            self::$_objAcl->addRole(new Zend_Acl_Role($role->name));
        }
 
	// add all resources to the acl
	foreach($arrResources as $resource) {
		self::$_objAcl->add(new Zend_Acl_Resource($resource->module_name .'::' .$resource->controller .'::' .$resource->action));
	}

	// allow roles to resources
	foreach($arrRoleResources as $roleResource) {
		self::$_objAcl->allow($roleResource->roleName,$roleResource->module .'::' .$roleResource->controller .'::' .$roleResource->action);
	}
 
	self::_saveAclToSession();
	return self::$_objAcl;
    }
}