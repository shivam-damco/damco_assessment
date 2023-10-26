<?php

/**
 * Model class to handle survey events operations
 * 
 * @author Harpreet Singh
 * @date   26 May, 2014
 * @version 1.0
 */

class Survey_Model_AnswersPreview extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();
       
        $this->_name = 'survey_event_answers_preview';
    }
	
	public function delete_allanswers(){
		$this->delete('','survey_event_answers_preview');
	}
    
  
    
    
}