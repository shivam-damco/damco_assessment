<?php
/**
 * @author  Harpreet Singh
 * @date    11 June, 2014
 * @version 1.0
 * 
 * Controller to handle cron/schedule job operations  
 */
class Cron_IndexController extends Zend_Controller_Action {
    protected $_cusModelObject;
    protected $_eventModelObject;
    protected $_configObject;
    protected $_parserObject;
    protected $_dbName;
    protected $_HTTP_PATH;
    protected $_scheduledEmailObj = null;
    protected $_emailObj = null;
    
    /**
     * Method to initialize event controller
     */
    public function init() {
        // if ( PHP_SAPI != 'cli' ) {
        //     $this->redirect(HTTP_PATH);
        //     exit;
        // }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_scheduledEmailObj = new Cron_Model_ScheduledEmails;
		$this->_surveyEventsModelObject = new Survey_Model_SurveyEvents();
    }

    /**
     * Method to handle index action operations
     */
    public function indexAction() {
        return true;
        // Sales survey invites
       
    }
    
    public function sendinternalsurveyemailAction()
    {
//die('in');       
        $todayDate = Date('Y-m-d');
        
        $result = $this->_scheduledEmailObj->getEmailDetails($todayDate);
       
        if(is_array($result) && !empty($result))
        {
            foreach($result as $data)
            {
                $this->_emailObj = new Damco_Email();
                $res = $this->_emailObj->SendEmail($data['email_to'],'','','',$data['content'],
                        $data['subject'],'Y','','');
                
                if ( $res == 'sent' ) {
                    $this->_scheduledEmailObj->update(array('is_email_sent' => '1'), ' id = "'.$data['id'].'" ');
					$this->_surveyEventsModelObject->update(array('email_send_date' => $todayDate,'email_sent'=>'Yes','invite_sent'=>'1'), ' eventid = "'.$data['object_id'].'" ');
                }
                else {
                    $this->_scheduledEmailObj->update(array('error' => '1', 'error_message' => '"'.$res.'"'),
                            ' id = "'.$data['id'].'" ');
                }
                
               
            }
        }
        
        //$this->_scheduledEmailObj->setEmailSent($todayDate);
        //exit;
    }
}
