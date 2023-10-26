<?php

/**
 * class to for SOAP API
 * 
 * @author Gaurav Narang
 * @date   13 June, 2014
 * @version 1.0
 */
class Damco_Manager {

    // define filters and validators for input
    private $_filters = array(
        'dealer_name' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'email_address' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'first_name' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'language' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'surname' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'title' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'vehicle_code_desc' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'vin' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'vehicle_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'mobile' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'gender' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'telephone' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'address1' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'address2' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'address3' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'town_city' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'postal_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'date_of_birth' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'country_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'country_of_region' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'date_of_sale' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_locale' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_region' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_status' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_town' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_country_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_contact_email' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'sales_person' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'stock_codeid' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'registration_number' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'registration_date' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'vehicle_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'vehicle_range_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'vehicle_range_code_desc' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'colour_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'colour_code_desc' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'langid' => array('HtmlEntities', 'StripTags', 'StringTrim'),
    );
    private $_validators = array(
        'dealer_name' => array('NotEmpty'),
        'email_address' => array('allowEmpty' => TRUE),
        'first_name' => array('NotEmpty'),
        'language' => array('NotEmpty'),
        'surname' => array('allowEmpty' => TRUE),
        'title' => array('allowEmpty' => TRUE),
        'vehicle_code_desc' => array('NotEmpty'),
        'vin' => array('NotEmpty'),
        'vehicle_code' => array('allowEmpty' => TRUE),
        'mobile' => array('allowEmpty' => TRUE),
        'gender' => array('allowEmpty' => TRUE),
        'telephone' => array('allowEmpty' => TRUE),
        'address1' => array('NotEmpty'),
        'address2' => array('allowEmpty' => TRUE),
        'address3' => array('allowEmpty' => TRUE),
        'town_city' => array('allowEmpty' => TRUE),
        'postal_code' => array('allowEmpty' => TRUE),
        'date_of_birth' => array('allowEmpty' => TRUE),
        'country_code' => array('NotEmpty'),
        'country_of_region' => array('allowEmpty' => TRUE),
        'date_of_sale' => array('NotEmpty'),
        'dealer_code' => array('NotEmpty'),
        'dealer_locale' => array('allowEmpty' => TRUE),
        'dealer_region' => array('allowEmpty' => TRUE),
        'dealer_status' => array('allowEmpty' => TRUE),
        'dealer_town' => array('allowEmpty' => TRUE),
        'dealer_country_code' => array('allowEmpty' => TRUE),
        'dealer_contact_email' => array('allowEmpty' => TRUE),
        'sales_person' => array('allowEmpty' => TRUE),
        'stock_codeid' => array('NotEmpty'),
        'registration_number' => array('allowEmpty' => TRUE),
        'registration_date' => array('NotEmpty'),
        'vehicle_code' => array('NotEmpty'),
        'vehicle_range_code' => array('allowEmpty' => TRUE),
        'vehicle_range_code_desc' => array('allowEmpty' => TRUE),
        'colour_code' => array('allowEmpty' => TRUE),
        'colour_code_desc' => array('allowEmpty' => TRUE),
        'langid' => array('allowEmpty' => TRUE),
    );
    private $validArrayKeys = array('dealer_name', 'first_name', 'language', 'vehicle_code_desc', 'vin', 'vehicle_code', 'registration_date', 'stock_codeid', 'dealer_code', 'date_of_sale', 'country_code', 'address1');

    /**
     * Adds new customer to database
     * @param string $authkey
     * @param  CustomerData[] $customerdata
     * @return ResultData[] $invalidrecords
     */
    public function addCustomer($authkey, $customerdata) {
        try {
            $date_regex = '#^(19|20)\d\d[\- /.](0[1-9]|1[012])[\- /.](0[1-9]|[12][0-9]|3[01])$#';
            $model = new Tolfeed_Model_Dealers();
            $dealer = new Dealer_Model_Dealers();
            $language = new Default_Model_Languages();
            $this->dbObj = Zend_Db_Table::getDefaultAdapter();
            if ($authkey != $model->getSoapKey()) {
                return array("invalid key");
            }
            $notInserted = array();
            $CustomerDataArray = json_decode(json_encode($customerdata), true);
            foreach ($CustomerDataArray as $mdata) {
                $input = new Zend_Filter_Input($this->_filters, $this->_validators, $mdata);
                $values = $input->getEscaped();
                $values["surname"] = !empty($mdata['surname'])?$mdata['surname']:'';
                $values["dealer_name"] = $mdata['dealer_name'];
                $values["first_name"] = $mdata['first_name'];
                $values["title"] = !empty($mdata['title'])?$mdata['title']:'';
                $values["vehicle_code_desc"] = $mdata['vehicle_code_desc'];
                $values["vin"] = $mdata['vin'];
                $values["gender"] = !empty($mdata['gender'])?$mdata['gender']:'';
                $values["address1"] = $mdata['address1'];
                $values["address2"] = !empty($mdata['address2'])?$mdata['address2']:'';
                $values["address3"] = !empty($mdata['address3'])?$mdata['address3']:'';
                $values["town_city"] = !empty($mdata['town_city'])?$mdata['town_city']:'';
                $values["postal_code"] = !empty($mdata['postal_code'])?$mdata['postal_code']:'';
                $values["country_of_region"] = !empty($mdata['country_of_region'])?$mdata['country_of_region']:'';
                $values["dealer_region"] = !empty($mdata['dealer_region'])?$mdata['dealer_region']:'';
                $values["dealer_town"] = !empty($mdata['dealer_town'])?$mdata['dealer_town']:'';
                $values["sales_person"] = !empty($mdata['sales_person'])?$mdata['sales_person']:'';
                $values["registration_number"] = !empty($mdata['registration_number'])?$mdata['registration_number']:'';
                $values["vehicle_range_code"] = !empty($mdata['vehicle_range_code'])?$mdata['vehicle_range_code']:'';
                $values["vehicle_range_code_desc"] = !empty($mdata['vehicle_range_code_desc'])?$mdata['vehicle_range_code_desc']:'';
                $values["colour_code_desc"] = !empty($mdata['colour_code_desc'])?$mdata['colour_code_desc']:'';
                $errorArray = array();
                foreach ($this->validArrayKeys as $val) {
                    if (!array_key_exists($val, $mdata)) {
                        $errorArray[$val] = "is empty";
                    }
                }
                if (count($errorArray)) {
                    $notInserted[] = $model->insertInvalidCustomers($values, $errorArray);
                } else {
                    if (!$input->isValid()) {
                        $notInserted[] = $model->insertInvalidCustomers($values, $input->getMessages());
                    } else {
                        $values['dealer_id'] = $dealer->getDealerIdByDealship($values['dealer_code']);
                        $values['langid'] = $language->getLanguageByCode($values['language']);
                        if ($dealer->getDealerIdByDealship($values['dealer_code']) == '') {
                            $mwessag['dealer_code'] = 'Dealer code ' . $values['dealer_code'] . ' doesnot exist in CSI platform';
                        }
                        if (!preg_match($date_regex, $values['date_of_sale']) && $values['date_of_sale'] != '') {
                            $mwessag['date_of_sale'] = 'Date of sale does not match the required format(YYYY-MM-DD)';
                        }
                        if (!preg_match($date_regex, $values['date_of_birth']) && $values['date_of_birth'] != '') {
                            $mwessag['date_of_birth'] = 'Date of birth does not match the required format(YYYY-MM-DD)';
                        }

                        if (!preg_match($date_regex, $values['registration_date']) && $values['registration_date'] != '') {
                            $mwessag['registration_date'] = 'Registration date does not match the required format(YYYY-MM-DD)';
                        }

                        if (count($mwessag)) {

                            $notInserted[] = $model->insertInvalidCustomers($values, $mwessag);
                            $mwessag = array();
                        } else {
                            $keydata[] = $model->insertUpdateCustomers($values);
                        }
                    }
                }
            }
            if (count($notInserted)) {
                $str = implode(",", $notInserted);
                $where = " id IN ($str)";
                $select = $this->dbObj->select();
                $select->from('customers_tolfeed')
                        ->where($where);
                $invalidrecords = $this->dbObj->fetchAll($select);
                $lastresult = array();
                if (!empty($invalidrecords)) {
                    foreach ($invalidrecords as $row) {
                        $errorAll['key'] = $row['stock_codeid'];
                        $errorAll['result'] = false;
                        $er = $row['error'];
                        $errors = array();
                        $errorAll['errors'] = $er;
                        $lastresult[] = $errorAll;
                    }
                }
            }
            if (count($keydata)) {
                foreach ($keydata as $key) {
                    $errorAll['key'] = $key;
                    $errorAll['result'] = true;
                    $errorAll['errors'] = '';
                    $lastresult[] = $errorAll;
                }
            }
            return $lastresult;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
    // define filters for input for service event type
    private $_serviceFilters = array(
        'email_address' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'first_name' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'language' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'surname' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'title' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'vehicle_code_desc' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'vin' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'vehicle_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'mobile' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'gender' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'telephone' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'address1' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'address2' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'address3' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'town_city' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'postal_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'date_of_birth' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'country_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'country_of_region' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'date_of_sale' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_locale' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_name' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_region' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_status' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_town' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_country_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'dealer_contact_email' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'registration_number' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'registration_date' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'vehicle_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'vehicle_range_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'vehicle_range_code_desc' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'colour_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'colour_code_desc' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'langid' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        
        
        'odometer_reading' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'service_event' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'service_date' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'additional_comments' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'date_submitted' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'bike_mileage' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'servicing_dealer_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'servicing_dealer_name' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'servicing_dealer_location' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'submitting_dealer_code' => array('HtmlEntities', 'StripTags', 'StringTrim'),
        'record_id' => array('HtmlEntities', 'StripTags', 'StringTrim'),
    );
    
    // define validators for input for service event type
    private $_serviceValidators = array(
        'email_address' => array('NotEmpty'),
        'first_name' => array('NotEmpty'),
        'language' => array('NotEmpty'),
        'surname' => array('NotEmpty'),
        'title' => array('NotEmpty'),
        'vehicle_code_desc' => array('NotEmpty'),
        'vin' => array('NotEmpty'),
        'vehicle_code' => array('allowEmpty' => TRUE),
        'mobile' => array('allowEmpty' => TRUE),
        'gender' => array('allowEmpty' => TRUE),
        'telephone' => array('allowEmpty' => TRUE),
        'address1' => array('NotEmpty'),
        'address2' => array('allowEmpty' => TRUE),
        'address3' => array('allowEmpty' => TRUE),
        'town_city' => array('allowEmpty' => TRUE),
        'postal_code' => array('allowEmpty' => TRUE),
        'date_of_birth' => array('allowEmpty' => TRUE),
        'country_code' => array('NotEmpty'),
        'country_of_region' => array('allowEmpty' => TRUE),
        'date_of_sale' => array('NotEmpty'),
        'dealer_name' => array('NotEmpty'),
        'dealer_locale' => array('allowEmpty' => TRUE),
        'dealer_region' => array('allowEmpty' => TRUE),
        'dealer_status' => array('allowEmpty' => TRUE),
        'dealer_town' => array('allowEmpty' => TRUE),
        'dealer_country_code' => array('allowEmpty' => TRUE),
        'dealer_contact_email' => array('allowEmpty' => TRUE),
        'registration_number' => array('allowEmpty' => TRUE),
        'registration_date' => array('NotEmpty'),
        'vehicle_code' => array('NotEmpty'),
        'vehicle_range_code' => array('allowEmpty' => TRUE),
        'vehicle_range_code_desc' => array('allowEmpty' => TRUE),
        'colour_code' => array('allowEmpty' => TRUE),
        'colour_code_desc' => array('allowEmpty' => TRUE),
        'langid' => array('allowEmpty' => TRUE),
        
        'odometer_reading' => array('allowEmpty' => TRUE),
        'service_event' => array('NotEmpty'),
        'service_date' => array('NotEmpty'),
        'additional_comments' => array('allowEmpty' => TRUE),
        'date_submitted' => array('allowEmpty' => TRUE),
        'bike_mileage' => array('allowEmpty' => TRUE),
        'servicing_dealer_code' => array('NotEmpty'),
        'servicing_dealer_name' => array('NotEmpty'),
        'servicing_dealer_location' => array('allowEmpty' => TRUE),
        'submitting_dealer_code' => array('allowEmpty' => TRUE),
        'record_id' => array('NotEmpty'),
    );
    
    private $_serviceValidArrayKeys = array(
        'email_address', 
        'title', 
        'first_name', 
        'surname', 
        'language', 
        'vehicle_code_desc', 
        'vin', 
        'vehicle_code', 
        'registration_date', 
        'date_of_sale', 
        'dealer_name',
        'country_code', 
        'address1',
        'service_event',
        'service_date',
        'servicing_dealer_code',
        'servicing_dealer_name',
        'record_id',
    );
    
    protected $_HTTP_PATH;

    /**
     * Adds new service event to database
     * @param string $authkey
     * @param  ServiceData[] $servicedata
     * @return ResultData[] $invalidrecords
     */
    public function addServiceEvent($authkey, $servicedata) {
        try {
            $this->_configObject = new Survey_Model_Config();
            $url = $this->_configObject->fetchRow('config_var = "BASE_URL"')->toArray();
            $this->_HTTP_PATH = $url['config_val'];
        
            $date_regex = '#^(19|20)\d\d[\- /.](0[1-9]|1[012])[\- /.](0[1-9]|[12][0-9]|3[01])$#';
            $model = new Tolfeed_Model_Dealers();
            $dealer = new Dealer_Model_Dealers();
            $language = new Default_Model_Languages();
            $this->dbObj = Zend_Db_Table::getDefaultAdapter();
            if ($authkey != $model->getSoapKey()) {
                $invalidrecords[] = array(
                    'key' => 'authkey',
                    'result' => false,
                    'errors' => 'Invalid Authorization Key',
                );
                return $invalidrecords;
            }
            $notInserted = array();
            $CustomerDataArray = json_decode(json_encode($servicedata), true);
            foreach ($CustomerDataArray as $mdata) {
                $input = new Zend_Filter_Input($this->_serviceFilters, $this->_serviceValidators,
                        $mdata);
                $values = $input->getEscaped();
                $values['surname'] = !empty($mdata['surname'])?$mdata['surname']:'';
                $values['first_name'] = $mdata['first_name'];
                $values['title'] = !empty($mdata['title'])?$mdata['title']:'';
                $values['vehicle_code_desc'] = $mdata['vehicle_code_desc'];
                $values['vin'] = $mdata['vin'];
                $values['gender'] = !empty($mdata['gender'])?$mdata['gender']:'';
                $values['address1'] = $mdata['address1'];
                $values['address2'] = !empty($mdata['address2'])?$mdata['address2']:'';
                $values['address3'] = !empty($mdata['address3'])?$mdata['address3']:'';
                $values['town_city'] = !empty($mdata['town_city'])?$mdata['town_city']:'';
                $values['postal_code'] = !empty($mdata['postal_code'])?$mdata['postal_code']:'';
                $values['country_of_region'] = !empty($mdata['country_of_region'])?$mdata['country_of_region']:'';
                $values['dealer_region'] = !empty($mdata['dealer_region'])?$mdata['dealer_region']:'';
                $values['dealer_name'] = !empty($mdata['dealer_name'])?$mdata['dealer_name']:'';
                $values['dealer_town'] = !empty($mdata['dealer_town'])?$mdata['dealer_town']:'';
                $values['registration_number'] = !empty($mdata['registration_number'])?$mdata['registration_number']:'';
                $values['vehicle_range_code'] = !empty($mdata['vehicle_range_code'])?$mdata['vehicle_range_code']:'';
                $values['vehicle_range_code_desc'] = !empty($mdata['vehicle_range_code_desc'])?$mdata['vehicle_range_code_desc']:'';
                $values['colour_code_desc'] = !empty($mdata['colour_code_desc'])?$mdata['colour_code_desc']:'';
                
                $values['odometer_reading'] = !empty($mdata['odometer_reading'])?$mdata['odometer_reading']:'';
                $values['service_event'] = !empty($mdata['service_event'])?$mdata['service_event']:'';
                $values['service_date'] = !empty($mdata['service_date'])?$mdata['service_date']:'';
                $values['additional_comments'] = !empty($mdata['additional_comments'])?$mdata['additional_comments']:'';
                $values['date_submitted'] = !empty($mdata['date_submitted'])?$mdata['date_submitted']:'';
                $values['bike_mileage'] = !empty($mdata['bike_mileage'])?$mdata['bike_mileage']:'';
                $values['servicing_dealer_code'] = !empty($mdata['servicing_dealer_code'])?$mdata['servicing_dealer_code']:'';
                $values['servicing_dealer_name'] = !empty($mdata['servicing_dealer_name'])?$mdata['servicing_dealer_name']:'';
                $values['servicing_dealer_location'] = !empty($mdata['servicing_dealer_location'])?$mdata['servicing_dealer_location']:'';
                $values['submitting_dealer_code'] = !empty($mdata['submitting_dealer_code'])?$mdata['submitting_dealer_code']:'';
                $values['record_id'] = $mdata['record_id'];
                $values['is_service_event'] = '1';
                
                $errorArray = array();
                foreach ( $this->_serviceValidArrayKeys as $val ) {
                    if ( !array_key_exists( $val, $mdata ) ) {
                        $errorArray[$val] = 'is empty';
                    }
                }
                
                if ( count( $errorArray ) ) {
                    $notInserted[] = $model->insertInvalidCustomers( $values, $errorArray );
                } 
                else {
                    if ( !$input->isValid( ) ) {
                        $notInserted[] = $model->insertInvalidCustomers( $values,
                                $input->getMessages( ) );
                    } 
                    else {
                        $values['dealer_id'] = $dealer->getDealerIdByDealship( $values['servicing_dealer_code'] );
                        $values['langid'] = $language->getLanguageByCode( $values['language'] );
                        
                        if ( empty( $values['dealer_id'] ) ) {
                            $mwessag['servicing_dealer_code'] = 'Dealer code ' . $values['servicing_dealer_code'] 
                                    . ' doesn\'t exist in CSI platform';
                        }
                        
                        if ( !empty( $values['submitting_dealer_code'] ) ) {
                            $submittingDealerID = $dealer->getDealerIdByDealship( $values['submitting_dealer_code'] );
                            
                            if ( empty( $submittingDealerID ) ) {
                                $mwessag['submitting_dealer_code'] = 'Dealer code ' . $values['submitting_dealer_code'] 
                                        . ' doesn\'t exist in CSI platform';
                            }
                        }
                        
                        if ( !preg_match( $date_regex, $values['date_of_birth'] ) 
                             && $values['date_of_birth'] != '' ) {
                            $mwessag['date_of_birth'] = 
                                    'Date of birth does not match the required format(YYYY-MM-DD)';
                        }

                        if ( !preg_match( $date_regex, $values['registration_date'] )
                             && $values['registration_date'] != '' ) {
                            $mwessag['registration_date'] = 
                                    'Registration date does not match the required format(YYYY-MM-DD)';
                        }

                        if ( !preg_match( $date_regex, $values['service_date'] ) ) {
                            $mwessag['service_date'] = 
                                    'Service date does not match the required format(YYYY-MM-DD)';
                        }

                        if ( count( $mwessag ) ) {
                            $notInserted[] = $model->insertInvalidCustomers( $values, $mwessag );
                            $mwessag = array();
                        } 
                        else {
                            
                            $values['survey_code'] = md5(microtime());
                            $browserCode = md5(microtime().$values['vin']);
                            
                            $eventID = $model->createServiceEvent( $values );
                            
                            $macros = array();
                            $macros['{BASE_URL}'] = $this->_HTTP_PATH.'/';
                            $macros['{TITLE}'] = ucwords(strtolower($values['title']));
                            $macros['{LASTNAME}'] = ( 
                                    ( !empty($values['surname'])
                                      ? ucwords(strtolower($values['surname']))
                                      : ucwords(strtolower($values['first_name'])) ) );//7/31/2014
                            $macros['{MODEL}'] = $values['vehicle_code_desc'];
                            $macros['{DEALER}'] = $values['servicing_dealer_name'];
                            $macros['{SURVEY_LINK}'] = $this->_HTTP_PATH.'/survey/index/?survey='
                                    .$values['survey_code'].'&langid='.$values['langid'];
                            $macros['{BROWSER_LINK}'] = $this->_HTTP_PATH.'/survey/email/?emailcode='
                                    .$browserCode;
                            $macros['{OPTOUT_LINK}'] = $this->_HTTP_PATH.'/survey/email/optout/?token='
                                    .md5($eventID);
                            $macros['{YEAR}'] = date('Y');

                            $values['eventid'] = $eventID;

                            $parserObject = new Damco_Core_EmailParser();
                            $emailArr = $parserObject->parse('service_survey_invite', 
                                    $values['langid'], $macros);
                            $this->_processInvite($values, $emailArr, 'service', 'service_survey_invite',
                                    $browserCode);
                            $keydata[] = $values['record_id'];
                        }
                    }
                }
            }
            
            if ( count( $notInserted ) ) {
                $str = implode( ',', $notInserted );
                $where = " id IN ($str)";
                $select = $this->dbObj->select();
                $select->from('customers_tolfeed')
                        ->where($where);
                $invalidrecords = $this->dbObj->fetchAll($select);
                $lastresult = array();
                if ( !empty( $invalidrecords ) ) {
                    foreach ( $invalidrecords as $row ) {
                        $lastresult[] = array(
                            'key' => $row['record_id'],
                            'result' => false,
                            'errors' => $row['error'],
                        );
                    }
                }
            }
            
            if ( count( $keydata ) ) {
                foreach ( $keydata as $key ) {
                    $lastresult[] = array(
                        'key' => $key,
                        'result' => true,
                        'errors' => 'SUCCESS',
                    );
                }
            }
            return $lastresult;
        } catch (Exception $e) {
            $invalidrecords[] = array(
                'key' => 'XMLParsing',
                'result' => false,
                'errors' => $e->getMessage(),
            );
            return $invalidrecords;
//            return $e->getMessage();
        }
    }
    
    /**
     * Method to process invite data
     * @param type $cusData
     * @param type $emailData
     * @param type $type
     * @param type $token
     * @return type
     */
    private function _processInvite( $cusData, $emailData, $type, $token, $browserCode ) {
        if ( is_array( $emailData ) ) {
            // Insert scheduled emails data
            $scheduledEmailObject = new Event_Model_ScheduledEmails();
            $scheduledEmailObject->dbinsert(array(
                'email_to' => $cusData['email_address'],
                'subject' => $emailData['subject'],
                'content' => $emailData['content'],
                'alert_type' => $token,
                'event_type' => $type,
                'object_id' => $cusData['eventid'],
                'browser_code' => $browserCode,
            ));
        }
    }
}

class CustomerData {

    /**
     * @var string
     * */
    public $surname;

    /**
     * @var string
     * */
    public $dealer_name = '';

    /**
     * @var string
     * */
    public $email_address;

    /**
     * @var string
     * */
    public $first_name = '';

    /**
     * @var string
     * */
    public $language = '';

    /**
     * @var string
     * */
    public $title;

    /**
     * @var string
     * */
    public $vehicle_code_desc = '';

    /**
     * @var string
     * */
    public $vin = '';

    /**
     * @var string
     * */
    public $gender;

    /**
     * @var string
     * */
    public $mobile;

    /**
     * @var string
     * */
    public $telephone;

    /**
     * @var string
     * */
    public $address1 = '';

    /**
     * @var string
     * */
    public $address2;

    /**
     * @var string
     * */
    public $address3;

    /**
     * @var string
     * */
    public $town_city;

    /**
     * @var string
     * */
    public $postal_code;

    /**
     * @var string
     * */
    public $date_of_birth;

    /**
     * @var string
     * */
    public $country_code = '';

    /**
     * @var string
     * */
    public $country_of_region;

    /**
     * @var string
     * */
    public $date_of_sale = '';

    /**
     * @var string
     * */
    public $dealer_code = '';

    /**
     * @var string
     * */
    public $dealer_locale;

    /**
     * @var string
     * */
    public $dealer_region;

    /**
     * @var string
     * */
    public $dealer_status;

    /**
     * @var string
     * */
    public $dealer_town;

    /**
     * @var string
     * */
    public $dealer_country_code;

    /**
     * @var string
     * */
    public $dealer_contact_email;

    /**
     * @var string
     * */
    public $sales_person;

    /**
     * @var string
     * */
    public $stock_codeid = '';

    /**
     * @var string
     * */
    public $registration_number;

    /**
     * @var string
     * */
    public $registration_date = '';

    /**
     * @var string
     * */
    public $vehicle_code = '';

    /**
     * @var string
     * */
    public $vehicle_range_code;

    /**
     * @var string
     * */
    public $vehicle_range_code_desc;

    /**
     * @var string
     * */
    public $colour_code;

    /**
     * @var string
     * */
    public $colour_code_desc;

    /**
     * @var int
     * */
    public $langid;

}

class ServiceData {

    /**
     * @var string
     * */
    public $record_id;

    /**
     * @var string
     * */
    public $surname;

    /**
     * @var string
     * */
    public $email_address;

    /**
     * @var string
     * */
    public $first_name = '';

    /**
     * @var string
     * */
    public $language = '';

    /**
     * @var string
     * */
    public $title;

    /**
     * @var string
     * */
    public $vehicle_code_desc = '';

    /**
     * @var string
     * */
    public $vin = '';

    /**
     * @var string
     * */
    public $gender;

    /**
     * @var string
     * */
    public $mobile;

    /**
     * @var string
     * */
    public $telephone;

    /**
     * @var string
     * */
    public $address1 = '';

    /**
     * @var string
     * */
    public $address2;

    /**
     * @var string
     * */
    public $address3;

    /**
     * @var string
     * */
    public $town_city;

    /**
     * @var string
     * */
    public $postal_code;

    /**
     * @var string
     * */
    public $date_of_birth;

    /**
     * @var string
     * */
    public $country_code = '';

    /**
     * @var string
     * */
    public $country_of_region;

    /**
     * @var string
     * */
    public $date_of_sale = '';

    /**
     * @var string
     * */
    public $dealer_name = '';

    /**
     * @var string
     * */
    public $dealer_locale;

    /**
     * @var string
     * */
    public $dealer_region;

    /**
     * @var string
     * */
    public $dealer_status;

    /**
     * @var string
     * */
    public $dealer_town;

    /**
     * @var string
     * */
    public $dealer_country_code;

    /**
     * @var string
     * */
    public $dealer_contact_email;

    /**
     * @var string
     * */
    public $registration_number;

    /**
     * @var string
     * */
    public $registration_date = '';

    /**
     * @var string
     * */
    public $vehicle_code = '';

    /**
     * @var string
     * */
    public $vehicle_range_code;

    /**
     * @var string
     * */
    public $vehicle_range_code_desc;

    /**
     * @var string
     * */
    public $colour_code;

    /**
     * @var string
     * */
    public $colour_code_desc;

    /**
     * @var int
     * */
    public $langid;

    /**
     * @var string
     * */
    public $odometer_reading;

    /**
     * @var string
     * */
    public $service_event;

    /**
     * @var string
     * */
    public $service_date;

    /**
     * @var string
     * */
    public $additional_comments;

    /**
     * @var string
     * */
    public $date_submitted;

    /**
     * @var string
     * */
    public $bike_mileage;

    /**
     * @var string
     * */
    public $servicing_dealer_code;

    /**
     * @var string
     * */
    public $servicing_dealer_name;

    /**
     * @var string
     * */
    public $servicing_dealer_location;

    /**
     * @var string
     * */
    public $submitting_dealer_code;

}

class ResultData {

    /**
     * @var string
     * */
    public $key;

    /**
     * @var boolean
     * */
    public $result;

    /**
     * @var string
     * */
    public $errors;

}
