<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 * Created On : 17/06/2014
 * Created By : Sandeep Pathak
 * Email : sandeepp@damcogroup.com
 */

class Damco_Email {

    var $objTransport;
    var $Subject;
    var $objConfig;
    var $objRegistry;
    var $objEmail;
    protected $client;
    protected $_HTTP_PATH;
    protected $_objAlertErrorLogs;

    function __construct() {
        $this->objRegistry = Zend_Registry::getInstance();
        $config = $this->objRegistry->get("config");
        $this->objConfig = $config;
        $this->_configTableObj = new Survey_Model_Config();
        $this->ObjQuestionLang = new Survey_Model_SurveyQuestionLang();
        $this->dbObj = Zend_Db_Table::getDefaultAdapter();
        $configData = $this->_configTableObj->fetchAll()->toArray();
        
        
        
        
        foreach ($configData as $configData) {
            if ($configData['config_var'] == 'SMTP_HOST') {
                $host = $configData['config_val'];
            }
            if ($configData['config_var'] == 'SMTP_PASSWORD') {
                $password = $configData['config_val'];
            }
            if ($configData['config_var'] == 'SMTP_PORT') {
                $port = $configData['config_val'];
            }
            if ($configData['config_var'] == 'SMTP_USERNAME') {
                $username = $configData['config_val'];
            }
            if ($configData['config_var'] == 'SMTP_AUTH') {
                $auth = $configData['config_val'];
            }
            if ($configData['config_var'] == 'SOAP_URL') {
                $this->SOAPURL = $configData['config_val'];
            }
            if ($configData['config_var'] == 'SOAP_GUID') {
                $this->SOAPGUID = $configData['config_val'];
            }
            if ($configData['config_var'] == 'FROM_MAIL') {
                $this->FROMMAIL = $configData['config_val'];
            }
            if ($configData['config_var'] == 'FROM_NAME') {
                $this->FROMNAME = $configData['config_val'];
            }
        }

        $configSMTP = array(
            'auth' => $auth,
            'port' => $port,
            'username' => $username,
            'password' => $password);

        $this->objTransport = new Zend_Mail_Transport_Smtp($host, $configSMTP);
        $this->objEmail = new Zend_Mail('UTF-8');
        $this->objAlertType = new Survey_Model_AlertType();
        $this->objAlertEmailTemplate = new Event_Model_EmailTemplates();
        $this->objServeyEvents = new Event_Model_Events();
        $this->_parserObject = new Damco_Core_EmailParser();
        $this->_scheduledEmailObject = new Event_Model_ScheduledEmails();

        $this->_obj = new Survey_Model_Questions();
        $this->_name = 'dealers';
        $this->client = new Zend_Soap_Client($this->SOAPURL);
		
		$this->_configObject = new Survey_Model_Config();
        $url = $this->_configObject->fetchRow('config_var = "BASE_URL"')->toArray();
        $this->_HTTP_PATH = $url['config_val'];
    }
 
    function SendEmail($toEmail = '', $fromEmail = '', $ccEmail = '', $bccEmail = '', $content = '', $subject = '', $sendEmail = 'Y',$attachement = '',$attachement1 = '') {
        if (empty($toEmail) && empty($ccEmail) && strlen($toEmail) == 0 && strlen($ccEmail) == 0) {
            $sendEmail = 'N';
        }
        $arrmails = explode(',', $toEmail);
        
        $arrccmails = explode(',', $ccEmail);
        $arrbccmails = explode(',', $bccEmail);
        
        $this->objEmail->setBodyHtml(stripslashes($content));
        
        $this->objEmail->clearFrom();
        $this->objEmail->clearSubject();
        
        
        
        if ($fromEmail == '') {
            $this->objEmail->setFrom($this->FROMMAIL, $this->FROMNAME);
        } else {
            $this->objEmail->setFrom($fromEmail, $fromEmail);
        }
        $this->objEmail->setSubject($subject);
        
        
        
        //add to email address 
        $testMode = FALSE;
        $testEmails = '';
        $configObj = new Survey_Model_Config();
        $testData = $configObj->fetchAll(' config_var = "EMAIL_TEST_MODE"'
                        . ' OR config_var = "TEST_EMAILS" '
                        . ' OR config_var = "BCC_EMAILS" ')->toArray();
        
        $bccEmails = array( );
        
        foreach ($testData as $value) {
            if ($value['config_var'] == 'EMAIL_TEST_MODE' && $value['config_val'] == '1') {
                $testMode = TRUE;
            }

            if ($value['config_var'] == 'TEST_EMAILS') {
                $testEmails = explode(',', $value['config_val']);
            }

            if ($value['config_var'] == 'BCC_EMAILS') {
                $bccEmails = explode(',', $value['config_val']);
            }
        }
        

        if ($testMode) {
            foreach ($arrmails as $key => $value) {
                if (!in_array($value, $testEmails)) {
                    unset($arrmails[$key]);
                }
            }

            foreach ($arrccmails as $key => $value) {
                if (!in_array($value, $testEmails)) {
                    unset($arrccmails[$key]);
                }
            }

            foreach ($arrbccmails as $key => $value) {
                if (!in_array($value, $testEmails)) {
                    unset($arrbccmails[$key]);
                }
            }
        }

        if (empty($arrmails)) {
            return '"To Email Address" is empty :: TEST MODE-' . $testMode;
        }

        foreach ($arrmails as $value) {
            $strMail = trim($value);
            $strMail = str_replace('\r', '', $strMail);
            $strMail = str_replace('\n', '', $strMail);
            if ($strMail != '') {
                $this->objEmail->addTo($strMail);
            }
        }

        //add cc email
        foreach ($arrccmails as $value) {
            $strMail = trim($value);
            $strMail = str_replace('\r', '', $strMail);
            $strMail = str_replace('\n', '', $strMail);
            if ($strMail != '') {
                $this->objEmail->addCc($strMail);
            }
        }

        //add bcc email
        $arrbccmails = array_merge($bccEmails, $arrbccmails);
        foreach ($arrbccmails as $value) {
            $strMail = trim($value);
            $strMail = str_replace('\r', '', $strMail);
            $strMail = str_replace('\n', '', $strMail);
            if ($strMail != '') {
                $this->objEmail->addBcc($strMail);
            }
        }
        
        //add Attachment
        if($attachement){
            $this->objEmail->createAttachment($attachement,Zend_Mime::TYPE_OCTETSTREAM,
                                    Zend_Mime::DISPOSITION_ATTACHMENT,
                                    Zend_Mime::ENCODING_BASE64,'sales_mdr.pdf');
        }
        if($attachement1){
            $this->objEmail->createAttachment($attachement1,Zend_Mime::TYPE_OCTETSTREAM,
                                    Zend_Mime::DISPOSITION_ATTACHMENT,
                                    Zend_Mime::ENCODING_BASE64,'service_mdr.pdf');
        }

        try {
            if ($sendEmail == 'Y') {
                $this->objEmail->send($this->objTransport);
                return 'sent';
            } else {
                return 'Error';
            }
        } catch (Zend_Mail_Exception $e) {
            return $e->getMessage();
        }
    }

    
    

   

    function _savescheduledEmailData($eventId, $toEmail, $bccEmail, $emailData,
        $type, $token, $browserCode, $eventTypeId = false, $langCode = false,$startDate) {
        
         if (is_array($emailData)) {
            // Insert scheduled emails data
             $subject = $emailData['subject'];
             $bccEmail = '';
             
            $insertedData = array(
                'email_to' => $toEmail,
                'email_bcc' => $bccEmail,
                'subject' => $subject,
                'content' => $emailData['content'],
                'alert_type' => $token,
                'event_type' => $type,
                'object_id' => $eventId,
                'browser_code' => $browserCode,
                'email_send_date' => $startDate
            );
            if ($this->_scheduledEmailObject->dbinsert($insertedData)) {
                return "saved";
            }
        }
    }

    
    
    
    public function surveyInvitationMail($eventId,$emailContent,$emailSubject,$requiredTime,$templatename='') 
    {
        //$emailToken = 'csn_not_contacted';
        if (isset($eventId) && !empty($eventId)) {   
            $eventDetails = $this->objServeyEvents->getEventDetailByID($eventId);
           
            
            if (isset($eventDetails) && !empty($eventDetails)) 
            {   
                $browserCode = md5(microtime() . $eventId);
                
                $toEmailId = $eventDetails[0]['email'];

                //$toEmailId = 'gauravh@damcogroup.com';
                
                //$toEmailId = 'rahula@damcogroup.com';

                $emailArray = array();
                $emailArray['subject'] = $emailSubject;
                $emailContent[0]['content']  = str_replace('{EMPLOYEENAME}',$eventDetails[0]['employee_name'],$emailContent[0]['content']);
                $emailContent[0]['content']  = str_replace('{SURVEYNAME}',$eventDetails[0]['survey_name'],$emailContent[0]['content']);
                $emailContent[0]['content']  = str_replace('{SURVEYNAME}',$eventDetails[0]['survey_name'],$emailContent[0]['content']);
                $surveyLink = HTTP_PATH.'/survey/index/?survey='.$eventDetails[0]['survey_code'];
                $emailContent[0]['content']  = str_replace('{SURVEY_LINK}',$surveyLink,$emailContent[0]['content']);
                $emailContent[0]['content']  = str_replace('{REQUIREDTIME}',$requiredTime,$emailContent[0]['content']);
                $emailContent[0]['content']  = str_replace('{YEAR}',date('Y'),$emailContent[0]['content']);
				$emailContent[0]['content']  = str_replace('{END_DATE}', date('l, d M Y', strtotime($eventDetails[0]['end_date'])), $emailContent[0]['content']);
				if($templatename=='remindermail'){
					$emailContent[0]['content']  = str_replace('{SURVEYSUBJECT}',$emailSubject,$emailContent[0]['content']);					
					$emailContent[0]['content']  = str_replace('{SENT_DATE}', date('l, d M Y', strtotime($eventDetails[0]['email_send_date'])), $emailContent[0]['content']);
                
				}
				
                $emailArray['content'] = $emailContent[0]['content'];
                $emailToken = '';
                $eventTypeId  = $eventDetails[0]['event_typeid'];
                $langCode = $emailContent['0']['lang_code'];
                $startDate = $eventDetails[0]['start_date'];
                
               
                
                $this->_savescheduledEmailData($eventId, $toEmailId,'',$emailArray, 'alert', $emailToken, $browserCode, $eventTypeId, $langCode,$startDate);
            }
        }
    }
}
