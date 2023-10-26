<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 * Created On : 20/06/2014
 * Created By : Sandeep Pathak
 * Email : sandeepp@damcogroup.com
 */

class Survey_EmailController extends Damco_Core_CoreController {

    public function init() {
        $this->ObjScheduleMail = new Event_Model_ScheduledEmails();
        $this->ObjAlertLog = new Survey_Model_AlertsLog();
        $this->objServeyEvents = new Event_Model_Events();
        $this->ObjEmailLib = new Damco_Email();
        $this->objCustomer = new Customer_Model_Customers();
        $this->_surveyOptoutObject = new Survey_Model_SurveyOptout();
        $this->customerresolutionObj = new Event_Model_CustomerResolution();
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }

    public function preDispatch() {

        //$this->_helper->layout()->disableLayout();
    }

    /* Action to check email of browser 
     * Created On : 20 June 2014
     * Created By : Sandeep Pathak
     * Email : sandeepp@damcogroup.com
     */

    public function indexAction() {
          $id = $this->_request->getParam('eventid');
		  $this->_helper->layout()->disableLayout();

        $token = $this->_request->getParam('emailcode');

        //Fetching event details according to given event Id
        if ($token != '') {
            $alertLog = array(
                'browser_code = ?' => trim($token)
            );
            $alertLogData = $this->ObjAlertLog->fetchRow($alertLog);

            if (isset($alertLogData) && !empty($alertLogData)) {
                $data = $alertLogData->toArray();
                $this->view->content = $data['alert_content'];
            } else {
                $this->view->content = "There is no data with refrence to this email code";
            }
        }
    }

    /* Action to save data of a customer in survey event optout table
     * Created On : 20 June 2014
     * Created By : Sandeep Pathak
     * Email : sandeepp@damcogroup.com
     */

    public function optoutAction() {

        $this->_helper->layout()->disableLayout();
        $eventToken = $this->_request->getParam('token');
        //Condition to find customer id from survey event
        if ($eventToken != '') {
            $customerCondition = array(
                'MD5(eventid) LIKE ?' => $eventToken
            );
            $customerId = $this->objServeyEvents->fetchRow($customerCondition)->toArray();

            if ($customerId['customerid'] != '') {
                $eventId = $customerId['eventid'];
                //condition to find customer details 
                $getCustomerCon = array(
                    'id = ?' => $customerId['customerid']
                );
                $customerDetails = $this->objCustomer->fetchRow($getCustomerCon)->toArray();
                if ($customerDetails['email_address'] != '') {
                    //Saving data related to customer in optout table
                    $this->_surveyOptoutObject->dbinsert(array(
                        'eventid' => $eventId,
                        'email' => $customerDetails['email_address'],
                        'added_date' => new Zend_Db_Expr('NOW()')
                    ));

                    //set event status as decline when customer optout
                    if ($customerId['event_status'] == 'In progress' || $customerId['event_status'] == 'Open') {
                        $data = array('event_status' => 'Decline');
                        $whr[] = "eventid = $eventId";
                        $this->objServeyEvents->update($data, $whr);
                    }

                    //Redirecting to Thakyou page
                    $this->_redirector->gotoSimple('thankyou', 'index', 'survey', array());
                }
            }
        }
    }

    /* Method to set code rat status as Closed if CSN problem being solved
     * Created On : 23 June 2014
     * Created By : Sandeep Pathak
     * Email : sandeepp@damcogroup.com
     */

    public function closedstatusAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_redirector->gotoSimple('thankyou', 'index', 'survey', array());
    }

    /* Method to set code rat status as Closed if CSN still facing any problem
     * Created On : 23 June 2014
     * Created By : Sandeep Pathak
     * Email : sandeepp@damcogroup.com
     */

    public function problemnotsolvedAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $eventToken = base64_decode($this->_request->getParam('token'));
        if ($eventToken != '') {
            $eventCon = array(
                'eventid =?' => $eventToken
            );
            $result = $this->objServeyEvents->fetchRow($eventCon)->toArray();
            
            if (is_array($result) && !empty($result) ) {
                if ( isset($result['is_reopened'])
                     && $result['is_reopened'] == '1' ) {
                    $this->_redirector->gotoSimple('thankyou', 'index', 'survey', array());
                }

                $this->ObjEmailLib->csnProblemNotResolved($eventToken);
                $customerCon = array(
                    'id =?' => $result['customerid']
                );
                $customerData = $this->objCustomer->fetchRow($customerCon)->toArray();
                $this->customerresolutionObj->dbinsert(array(
                    'eventid' => $eventToken,
                    'added_by' => $customerData['first_name'],
                    'added_date' => new Zend_Db_Expr('NOW()'),
                    'status' => 'Reopened'
                ));
                $data = array(
                    'code_red_status' => 'Reopened',
                    'is_reopened' => '1',
                );
                $whr[] = "eventid = $eventToken";
                $this->objServeyEvents->update($data, $whr);
            }
        }
        $this->_redirector->gotoSimple('thankyou', 'index', 'survey', array());
    }

    /* Method to set code rat status as Closed if CSN not contacted
     * Created On : 23 June 2014
     * Created By : Sandeep Pathak
     * Email : sandeepp@damcogroup.com
     */

    public function notcontactedAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $eventToken = base64_decode($this->_request->getParam('token'));

        if ($eventToken != '') {
            $eventCon = array(
                'eventid =?' => $eventToken
            );
            $result = $this->objServeyEvents->fetchRow($eventCon)->toArray();
            
            if (is_array($result) && !empty($result) ) {
                if ( isset($result['is_reopened'])
                     && $result['is_reopened'] == '1' ) {
                    $this->_redirector->gotoSimple('thankyou', 'index', 'survey', array());
                }

                $this->ObjEmailLib->csnNotContacted($eventToken);
                $customerCon = array(
                    'id =?' => $result['customerid']
                );
                $customerData = $this->objCustomer->fetchRow($customerCon)->toArray();
                $this->customerresolutionObj->dbinsert(array(
                    'eventid' => $eventToken,
                    'added_by' => $customerData['first_name'],
                    'added_date' => new Zend_Db_Expr('NOW()'),
                    'status' => 'Reopened'
                ));
                $data = array(
                    'code_red_status' => 'Reopened',
                    'is_reopened' => '1',
                );
                $whr[] = "eventid = $eventToken";
                $this->objServeyEvents->update($data, $whr);
            }
        }
        $this->_redirector->gotoSimple('thankyou', 'index', 'survey', array());
    }
}
