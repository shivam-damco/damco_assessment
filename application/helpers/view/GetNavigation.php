<?php

/**
 * Helper class to show navigation menu
 * 
 * @author Harpreet Singh
 * @date   18 June, 2014
 * @version 1.0
 */
class Damco_View_Helper_GetNavigation extends Zend_View_Helper_Abstract {

    /**
     * Constructor to initialize navigation menu class
     */
    function __construct() {
        
    }

    public function getNavigation() {
        $user = Zend_Auth::getInstance()->getIdentity();
        $navObject = new Default_Model_Menu();
        $result = $navObject->getNavigation($user->role_id);
        $resourceId = $this->_getResourceIds();
        if (is_array($result)) {
            $data = array();
            foreach ($result as $value) {
                $value['class']='';
                $data[$value['parent_id']][] = $value;
            }
            $controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
            $module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();

            // Remove empty menus items
            foreach ($data['0'] as $key => $value) {
                if($this->_isMenuActive($value['id'],$resourceId)==true
                        || $value['resource_id'] == $resourceId ){
                    $data['0'][$key]['class']='active';
                }
                
                if (!isset($data[$value['id']]) && empty($value['menu_url'])) {
                    unset($data['0'][$key]);
                }
            }

            return $this->_buildNavigation($data, '0');
        }
        return $retString;
    }

    private function _buildNavigation($data, $parent) {
        static $i = 1;
        if (array_key_exists($parent, $data)) {
            $menu = ( $i == 1 ) ? '<ul class="nav navbar-nav">' : '<ul class="dropdown-menu">';
            $i++;
            foreach ($data[$parent] as $value) {
                $child = $this->_buildNavigation($data, $value['id']);
                $menu .= '<li>';
                $class = ($child) ? " class='dropdown-toggle $value[class]' data-toggle='dropdown'" 
                         : ' class="'.$value['class'].'" ';
                $menu .= '<a href="' . HTTP_PATH . $value['menu_url'] . '"' . $class . '>'
                        . $this->view->translate($value['menu_name']) . '</a>';
                if ($child) {
                    $i--;
                    $menu .= $child;
                }
                $menu .= '</li>';
            }
            $menu .= '</ul>';
            return $menu;
        } else {
            return FALSE;
        }
    }

    /**
     * Get resource Id of Current Controller And action
     * @return int
     */
    protected function _getResourceIds() {
        $controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        $module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
        $action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
        $objResource = new Default_Model_DbTable_Resources();
        $objModule = new Default_Model_DbTable_Modules();
        $resultMod = $objModule->getSelect('id')->where('module = ?',$module)->query()->fetch();
        $resourceId = $moduleId = null;
        if($resultMod){
            $moduleId = $resultMod['id'];
        }
        if($moduleId){
            $resourceId = $objResource->getSelect('id')->where('controller = ?',$controller)
                        ->where('module_id = ?',$moduleId)->where('action = ?',$action)->query()->fetch();
        }
        return !empty($resourceId) ? $resourceId['id'] :false;
    }

     /**
     * Tells which menu to Set Active
      * 
     * @return bool
     */
    private function _isMenuActive($menuId,$resourceId) {
       $navObject = new Default_Model_Menu();
       $result = $navObject->getSelect('id')->where('parent_id = ?',$menuId)->where('resource_id = ?',$resourceId)->query()->fetchAll();
       return !empty($result) ? TRUE : FALSE;
    }
}
