<?php

/**
 * Model class to handle Location details Table operations
 * @author  Harpreet Singh
 * @date    4th July, 2014
 * @version 1.0
 */

class Survey_Model_LocationDetails extends Default_Model_Core {

    /**
     * Constructor to initialize Location details model class
     */
    public function __construct() {
        parent::__construct();
        $this->_name = 'location_details';
    }

}
