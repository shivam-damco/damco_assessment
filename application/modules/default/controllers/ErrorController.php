<?php

class Default_ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
       print_r($errors->exception);die;
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                $error = $errors->exception; 
                $this->_helper->viewRenderer('error/noresource', null, true);
                $this->sendemail($error); 
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                $error = $errors->exception;            
                $this->sendemail($error); 
                
                $this->_helper->viewRenderer('error/customerror', null, true);
                break;
        }
        
        // Log exception, if logger available
       /* if ($log = $this->getLog()) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->log('Request Parameters', $priority, $errors->request->getParams());
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request; */
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }

     public function noresourceAction()
    { 
       $objAuth = Zend_Auth::getInstance();
     
       if(!$objAuth->hasIdentity())
       {           
           $this->_helper->layout->setLayout('login');
       }
      
    }

    public function noauthAction()
    {
        //		
    }
    
    public function customerrorAction()
    {       
        $exceptionsdtls = $this->getParam('exceptions');        
        $this->sendemail($exceptionsdtls);
	
    }
    
    private function sendemail($exceptionsdtls)
    {
        //added by DIpa to track user details 9/10/14 3:33 PM
        $identity = Zend_Auth::getInstance();        
        $this->_user = $identity->getIdentity();
        $userdetails = "Not logged in user";
        if ( $identity->hasIdentity() )
        {
            $userdetails = print_r($this->_user, True);
        }
        //EOC by Dipa 9/10/14 3:36 PM
        $objEmail = new Damco_Email;
        $this->_objConfig = Zend_Registry::get('config');
        $subject= "Error Email from Triumph";
        $basicUrl  = HTTP_PATH.$this->view->getRequestInfo()->getRequestUri();
        $body = "ERROR: ".$exceptionsdtls;
        $errMsg = "<font face='Arial'>
           		<div>           			
           			<table border='0' width='540px' style='font-color:black;' cellspacing='2' >
           				<tr>
           					<td valign='top'><b>URL: </b></td>
           					<td valign='top'>".$basicUrl."</td>
           				</tr>
           				<tr>
           					<td valign='top'><b>Error Message: </b></td>
           					<td valign='top'>".$exceptionsdtls."</td>
           				</tr>           				
           				<tr>
           					<td valign='top'><b>Date: </b></td>
           					<td valign='top'>".date("F j, Y, g:i a")."</td>
           				</tr>         				
           				<tr>
           					<td valign='top'><b>Referer: </b></td>
           					<td valign='top'>". $_SERVER['HTTP_REFERER'] ."</td>
           				</tr>         				
					<tr>
           					<td valign='top'><b>Browser Used: </b></td>
           					<td valign='top'>". $_SERVER['HTTP_USER_AGENT'] ."</td>
           				</tr>	
					<tr>
           					<td valign='top'><b>requestparams: </b></td>
           					<td valign='top'>". $_SERVER['REDIRECT_QUERY_STRING'] ."</td>
           				</tr>	
					<tr>
           					<td valign='top'><b>User Details: </b></td>
           					<td valign='top'>". $userdetails ."</td>
           				</tr>	
           			</table>
           		</div>
           		</font>"; 
        
        $token = "error_email";
        $toEmail = $this->_objConfig["assessment"]["email"]["toemail"];
        $ccEmail = $this->_objConfig["assessment"]["email"]["ccemail"];
        $bccEmail = '';
        $emailData = array("subject"=>$subject,"content"=>$body);
        //print_R($objEmail);die;
        //SendEmail($toEmail = "", $fromEmail = "", $ccEmail = '', $bccEmail = '', $content = '', $subject = '', $sendEmail = "Y")
        $objEmail->SendEmail($toEmail,"", $ccEmail,$bccEmail, $errMsg, $subject, "Y");
    }


}







