<?php

/**
 * @author  Harpreet Singh
 * @date    14 June, 2014
 * @version 1.0
 * 
 * Class to handle Email Parsing and Email Template operations  
 */
class Damco_Core_EmailParser {
    protected $_emailTemplates;
    protected $_emailTempModelObject;
    protected $_footerLanguageArray = NULL;
    
    /**
     * Constructor to initialize Email Parser class
     */
    public function __construct( ) {
        $this->_emailTempModelObject = new Event_Model_EmailTemplates();
        $templates = $this->_emailTempModelObject->getEmailTemplates( );
        
        foreach ($templates as $value) {
            $this->_emailTemplates[$value['email_token']][$value['lang_id']] = 
                    array(
                        'subject' => $value['subject'],
                        'content' => $value['content']
                    );
        }
        
//        $this->_setFooterLanguageArray();
    }

    /**
     * Method to get subject and content for an email
     * @param type $token
     * @param type $langID
     * @param type $macros
     */
    final public function parse($token, $langID, $macros = array()) {
        $langID = (isset($this->_emailTemplates[$token][$langID]))?$langID:'1';
        if ( isset($this->_emailTemplates[$token][$langID]) ) {
            $subject = $this->_emailTemplates[$token][$langID]['subject'];
            $content = $this->_emailTemplates[$token][$langID]['content'];
            
            $macros['{FOOTER_SIGNATURE}'] = 
                $this->_footerLanguageArray[$langID]['footer_signature'];
            
            foreach ( $macros as $key => $val ) {
                $subject = str_replace($key, $val, $subject);
                $content = str_replace($key, $val, $content);
            }
            
            return array(
                'subject' => $subject,
                'content' => $content,
            );
        }
        return FALSE;
    }
    
    /**
     * Method to set array of footer text for each language
     */
    private function _setFooterLanguageArray( ) {
        if ( empty( $this->_footerLanguageArray ) ) {
            $langObj = new Default_Model_Languages();
            $result = $langObj->getFooterSignatures();
            foreach ( $result as $value ) {
                $this->_footerLanguageArray[$value['langid']]['footer_signature']
                       = $value['footer_signature'];
            }
        }
    }
}
