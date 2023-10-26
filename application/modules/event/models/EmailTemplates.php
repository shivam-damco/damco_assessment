<?php

/**
 * Model class to handle email templates operations
 * 
 * @author Harpreet Singh
 * @date   12 June, 2014
 * @version 1.0
 */

class Event_Model_EmailTemplates extends Default_Model_Core {

    /**
     * Constructor to initialize email templates model class
     */
    public function __construct() {
        parent::__construct();
        
        $this->_name = 'alert_email_templates';
    }

    /**
     * Method to return all email templates
     * @return type
     */
    public function getEmailTemplates( ) {
        $rs = $this->select()
                ->from(array('et' => $this->_name),
                    array('et.alert_id', 'et.lang_id', 'et.subject', 'et.content',
                        'at.email_token'))
                ->joinleft(array('at' => 'alert_types'),
                    'et.alert_id = at.id', array())
                ->setIntegrityCheck(FALSE)
                ->where('at.is_active = "1"');
        return $rs->query()->fetchAll();        
    }

    /**
     * Method to return all email templates for passed language ID
     * @return type
     */
    public function getLanguageEmailTemplates( $langID ) {
        $rs = $this->select()
                ->from(array('et' => $this->_name),
                    array('et.alert_id', 'et.lang_id', 'et.subject', 'et.content',
                        'at.email_token', 'at.title'))
                ->joinInner(array('at' => 'alert_types'),
                    'et.alert_id = at.id', array('at.email_token'))
                ->joinInner(array('l' => 'languages'),
                    'l.langid = et.lang_id', array('l.lang_name', 'l.lang_code'))
                ->setIntegrityCheck(FALSE)
                ->where('at.is_active = "1"')
                ->where('et.lang_id = "'.$langID.'"')
                ->order('et.alert_id', 'ASC');
        return $rs->query()->fetchAll();        
    }
}