<?php

/**
 * Model class to handle Bounced Mail operations
 * 
 * @author Amit Kumar
 * @date   19 Aug, 2014
 * @version 1.0
 */
class Cron_Model_ScheduledEmails extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();

        $this->_name = 'scheduled_emails';
    }
    
    /**
     *
     Method to Get Emails for today
     * */
    public function getEmailDetails($todayDate) {
    	$details = $this->select()
    	->from(array('se' => 'scheduled_emails'),array('se.subject','se.email_to','se.content','se.id','se.object_id'))
    	->where('se.email_send_date <= ?', $todayDate)
        ->where('se.is_email_sent <> 1')
        ->where('se.error <> 1')
    	->setIntegrityCheck(FALSE);
    	return $details->query()->fetchAll();

    }
    
    public function setEmailSent($todayDate)
    {
        
        $data = array('is_email_sent' => '1');
        $result = $this->_db->update($this->_name,$data , 'email_send_date <= "'.$todayDate.'"');
        
        return true;
    }
    
    
  }