<?php

/**
 * Model class to handle settings privileges operations
 * 
 * @author Harpreet Singh
 * @date   26 May, 2014
 * @version 1.0
 */
class Settings_Model_Settings extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Method to call SP and get events data
     * @param type $spName
     * @param type $inParam
     * @return type
     */
    public function getroledtls($where=null) {

        $resource = $this->db->select()
                ->from(array('se' => 'acl_roles'), array('name', 'id','label')); //->__tostring();die;

        if($where){
            $resource->where($where);
        }
        return $resource->query()
                        ->fetchAll(); /**/
    }

    public function getData() {

        $resource = $this->db->select()
                ->from(array('m' => 'acl_modules'), array('m.id', 'm.module_label'))
                ->joinLeft(array('r' => 'acl_resources'), 'm.id = r.module_id', array('r.action_label', 'resource_id' => 'r.id'))
                ->joinLeft(array('rr' => 'acl_roles_resources'), 'r.id = rr.resource_id', array('rr.role_id', 'role_resource_id' => 'rr.id'));
//                ->group('.resource_id'); //->__tostring();die;
        return $resource->query()
                        ->fetchAll(); /**/
    }
    
    public function getAclResources($cols='*',$where=null,$groupBy=null){
        $select = $this->_db->select()->from('acl_resources', $cols);
        if($where){
            $select->where($where);
        }
        if($groupBy){
            $select->group($groupBy);
        }
                
        $result = $select->query()->fetchAll();
        return !empty($result)? $result : FALSE;
    }

    /**
     * Get Resource Id on basis of role
     * 
     * @param int $roleId
     */
    public function getResources($roleId=null) {
        $sql = $this->_db->select()
                ->from('acl_roles', array('id as role_id'))
                ->joinLeft('acl_roles_resources', 'acl_roles.id = acl_roles_resources.role_id', array(new Zend_Db_Expr('GROUP_CONCAT(resource_id ORDER BY resource_id) as resource_ids')))
                ->group('acl_roles.id');
        if($roleId){
            $sql->where('acl_roles.id = ?', $roleId);
        }
//        echo $sql->__toString();die;
        return $this->_db->query($sql)->fetchAll(zend_db::FETCH_ASSOC); 
    }

   /**
    * Saving resources with their roles
    * 
    * @param array $resourceId Resource Ids
    * @param int $getRoleId Role Id
    * @Author Maninder Bali
    * @return 
    */
    public function saveResources($resourceId = array(), $getRoleId) {
        $this->_db->delete('acl_roles_resources', 'role_id = ' . $getRoleId);
         if($getRoleId==4 || $getRoleId==1){
             foreach ($resourceId as $key => $value) {
                 if($value==44 || $value==45 || $value==50){
                     unset($resourceId[$key]);
                 }
             }
        }
        if($getRoleId==2 || $getRoleId==3){
             $resourceId[]=45;
             $resourceId[]=44;
             $resourceId[]=50;
        }
       
        $resourceId[]=5;
        $resourceId[]=32;
        $resourceId[]=33;
        $resourceId[]=34;
        $resourceId[]=35;
        $resourceId[]=47;
        $resourceId[]=53;
        
        $resourceId= array_unique($resourceId);
        foreach ($resourceId as $role_ids => $resource_ids) {
            $data = array(
            'role_id' => $getRoleId,
            'resource_id' => $resource_ids,
            );
            $this->db->insert('acl_roles_resources', $data);
          }
          return true;
    }

}
