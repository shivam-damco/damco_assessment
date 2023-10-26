<?php

/**
 * Online Survey: Priviliges Controller
 *
 * @package     Truimp: Online Survey
 * @version     1.0
 * @author      Dipanwita Kundu
 * @copyright   Truimp
 *
 */
class Settings_PrivilegesController extends Damco_Core_CoreController {

    /**
     * object for configuration
     *
     * @var object
     */
    private $_objConfig;

    /**
     * stores the default session namespace object
     *
     * @var object
     */
    private $_objSession;
    private $_eventSettingsModelObject;

    /**
     * initialzing the varaibles for online survery controller
     *
     */
    public function init() {
        parent::init();
        if($this->_user->role_id!=1){
            $this->_redirect('/');
        }
       
        $this->_objConfig = Zend_Registry::get('config');
        $this->_obj = new Survey_Model_Questions();
        $this->_surveyobj = new Event_Model_Events();
        $this->_answerobj = new Survey_Model_Answers();
        $this->_dealerobj = new Dealer_Model_Dealers();
        $this->_eventobj = new Event_Model_Events();
        $this->_objSession = new Zend_Session_Namespace('Default');
    }

    /**
     * @return Settings_Form_Settings
     */
    public function getForm(){
        return new Settings_Form_Settings();
    }
    
    /**
     * @see Settings_Model_Settings
     * @return \Settings_Model_Settings
     */
    public function getModel(){
        return new Settings_Model_Settings();
    }
    
    Public function errorAction() {
        $this->render('survey_not_exist');
        return;
    }

    /**
     * default action of the controllers
     *
     */
    public function indexAction() {
         $get = $this->getRequest()->getParams();
         
        $getRoleId = 2;
       
        if (isset($_POST["role_id"])) {
            $getRoleId = $get['role_id'];
        }
        $this->view->roleID = $getRoleId;
        if (isset($_POST["chk-box"])) {
            $getRoleId = $_POST['roles_id'];
            $result = $this->saveResources($getRoleId, $get['chk-box']);
            $this->_helper->flashMessenger->addMessage($result);
            $this->_helper->redirector('index');
        }
        $this->view->flashMessages = $this->_helper->flashMessenger->getMessages();
        $rolelist = $this->getModel()->getroledtls('id!=1');
        $roleAndResources = $this->getModel()->getResources($getRoleId);
//         $getData = $this->getModel()->getData();
        $getData = $this->getModel()->getAclResources(array('id','controller_label'),'is_active=1','controller_label');
        
          $resources = array();
          $resourceAndActions = array();
        if(is_array($getData)){
            foreach ($getData as $key => $value) {
                $resources['name'] = $value['controller_label'];
                $actionResult = $this->getModel()->getAclResources(array('id','action_label'),"controller_label = '$value[controller_label]' AND is_active=1");
                $actionArray = array();
                if(is_array($actionResult)){
                    foreach ($actionResult as $value) {
                       $actionArray[$value['id']]=$value['action_label'];
                    }
                    $resources['actions'] = $actionArray;
                    $resourceAndActions[] = $resources;
                }
            }
        }
        $roleResources = array();
        
        foreach($roleAndResources as $value){
            if(!is_array($value['resource_ids'])){
                $value['resource_ids'] = explode(',',$value['resource_ids']);
             }
             $roleResources[$value['role_id']]['resource_ids'] = $value['resource_ids'];
        }
        $form = $this->getForm()->setRoles_options($rolelist,
                                                             true,
                                                             false);
        if($getRoleId){
            $form->getElement('role_id')->setValue($getRoleId);
        }
        
        $this->view->form = $form;
        $this->view->resources = $resourceAndActions;
        $this->view->roleResources = $roleResources;
        if (isset($getRoleId)) {
            $this->view->roleID = $getRoleId;
        } else {
            $this->view->roleID = '1';
        }
    }
    
    /**
     * It is used to save resources on basis of their roles.
     * 
     * @param int $roleId Role Id
     * @param array $resourceArray Array of Resources
     * @author Maninder Bali
     * @return boolean
     */
    private function saveResources($roleId,$resourceArray){
        $result = $this->getModel()->saveResources($resourceArray, $roleId);
        if($result==1) {
            return array('success'=>$this->view->translate('Privileges Saved Successfully'));
        }else{
            return array('error'=>$this->view->translate('Failed To Save Privileges'));
        }
    }
}
?>
