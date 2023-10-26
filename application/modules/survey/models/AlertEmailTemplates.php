<?php

/**
 * Model class to handle survey events operations
 * 
 * @author Harpreet Singh
 * @date   26 May, 2014
 * @version 1.0
 */
class Survey_Model_AlertEmailTemplates extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();
        $this->_name = 'alert_email_templates';
    }

    public function getDamcoInternalSurveyEmailTemplate($templatename = '')
    { 
        $rs = $this->db->select()
                ->from(array('aet'=>$this->_name),array('content','subject'))
                ->joinInner(array('l' => 'languages'), 'l.langid=aet.lang_id', array('lang_code'))
                ->where("alert_id = 1");
		if($templatename=='remindermail'){
			$rs = $rs->where("id=50");//->__toString();die;
		}
		
        $result= $rs->query()->fetchAll();
        
        return $result;
        
    }
    
}
