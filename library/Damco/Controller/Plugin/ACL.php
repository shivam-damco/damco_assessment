<?php

class Damco_Controller_Plugin_ACL extends Zend_Controller_Plugin_Abstract {

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        $objAuth = Zend_Auth::getInstance();
        $clearACL = false;

        // initially treat the user as a guest so we can determine if the current
        // resource is accessible by guest users
        $role = 'admin';

        // if its not accessible then we need to check for a user login
        // if the user is logged in then we check if the role of the logged
        // in user has the credentials to access the current resource

        try {
            $mypdfval = $request->getParam("chkme");
            
            //Dipa 5/26/14 3:12 PM
            if (in_array($request->getModuleName(), array('tolfeed', 'survey', 'cron'))
                || in_array($request->getControllerName(), array('error'))
                || ( $request->getModuleName() == 'default' 
                     && $request->getControllerName() == 'index'
                     && $request->getActionName() == 'index')
                || ( $request->getModuleName() == 'report' 
                     && $request->getControllerName() == 'esr'
                     && $request->getActionName() == 'index'
                     && !empty($mypdfval)
                             )/* */
                || in_array($request->getActionName(), array('logout', 'login', 'loginlocal'))) {
                //do nothing
            }
            elseif ($objAuth->hasIdentity()) {
                $objUser = $objAuth->getIdentity();
                $roleID = $objUser->role_id;
                $roleModel = new Default_Model_RolesMapper();
                $roleObj = $roleModel->find($roleID);

                if (!$roleObj) {
                    die('Role does not exist');
                }
                $roleName = $roleObj->name;

                $sess = new Zend_Session_Namespace('Damco_ACL');
               
                if ($sess->clearACL) {
                    $clearACL = true;
                    unset($sess->clearACL);
                }

                $objAcl = Damco_ACL_Factory::get($objAuth, $clearACL);
                ///// 11/3/14 11:57 AM              
                if (!$objAcl->has($request->getModuleName() . '::' . $request->getControllerName(). '::' . $request->getActionName())) {
                  // action/resource does not exist in ACL
                    $request->setModuleName('default');
                    $request->setControllerName('error');
                    $request->setActionName('noresource');
                } 
                ////11/3/14 11:57 AM
                /*
                 * Added By Amit kumar
                 * Code For add report access log
                 */
                
                //Get all parameters
                $get = $request->getParams();
                
                $rptType = (isset($get['rpt_type'])?$get['rpt_type']:'');
                
                if($rptType=='rawdata'){
                    $slug='rawdata';
                } elseif($rptType=='reopen'){
                    $slug='reopened';
                }elseif($rptType=='iptrack'){
                    $slug='iptracker';
                }else{
                    $slug='';
                }
                //add report access log
                $model =  new Report_Model_ReportActivity();
                $reportData = $model->getResourceData( $request->getModuleName(),$request->getControllerName(),  $request->getActionName(),$slug);
                
                //added by Dipa & Harpreet to chk if reportid exist perform insertion 10/10/14 1:42 PM 
                if ( isset( $reportData['id'] )
                     && !$request->isXmlHttpRequest( )
                     && empty( $mypdfval )
                     && ( $objUser->username != 'corporate_user'
                          && $objUser->username != 'branch_user'
                          && $objUser->username != 'branch2_user'
                          && $objUser->username != 'asm_user'
                          && $objUser->username != 'dealer' ) )
                {
                    //Add report log data into table
                    $this->_reportActivityModel =  new Report_Model_ReportActivity();

                    //Add user activity
                    $this->_reportActivityModel->dbinsert(array(
                        'session_id' => session_id(),
                        'username' => $objUser->username,
                        'role_id' => $objUser->role_id,
                        'subsidiaryid' => isset($objUser->branch_id)?$objUser->branch_id:'',
                        'dealer_id' => isset($objUser->dealer_id)?$objUser->dealer_id:'',
                        'report_id' => $reportData['id'],
                        'logged_in_time'=>date('y-m-d G:i:s')
                    ));
                }

                if (!$objAcl->isAllowed($roleName, $request->getModuleName() . '::' . $request->getControllerName() . '::' . $request->getActionName())) {
                    $request->setModuleName('default'); 
                    $request->setControllerName('error');
                    //$request->setActionName('noresource');
                    $request->setActionName('noauth');//11/3/14 12:42 PM
                }
            }
            else {
                $objAcl = Damco_ACL_Factory::get($objAuth, $clearACL);
                ///// 11/3/14 11:57 AM
                if (!$objAcl->has($request->getModuleName() . '::' . $request->getControllerName(). '::' . $request->getActionName())) {
                    // action/resource does not exist in ACL
                    $request->setModuleName('default');
                    $request->setControllerName('error');
                    $request->setActionName('noresource');
                    $request->setParam('langid','1');
                } 
                ////11/3/14 11:57 AM
                else if (!$objAcl->isAllowed($role, $request->getModuleName() . '::' . $request->getControllerName() . '::' . $request->getActionName())) {
//                    $this->getResponse()->setRedirect('index/login');
//                    return Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->setGotoRoute(array(), "login");
                     return Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->setGotoSimple("login", "index");
                }
            }
        } catch (Zend_Exception $e) {
                     
          // echo $e->getMessage();die;
//            echo"<pre>";
//            print_r($e);
//            die('error: ' . __LINE__);
            $request->setModuleName('default');
            $request->setControllerName('error');
            $request->setActionName('customerror');
           
           $request->setParam("exceptions",$e->getMessage());
           //  $request->setParam("exceptions",$e->getException());
            
            // $request->setActionName('error');
        }
    }
}
