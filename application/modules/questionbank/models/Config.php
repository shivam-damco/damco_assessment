<?php

/**
 * Model class to handle configuration operations
 */
/*
 * Created On : 17/06/2014
 * Created By : Sandeep Pathak
 * Email : sandeepp@damcogroup.com
 */
class Questionbank_Model_Config extends Default_Model_Core {

    /**
     * Constructor to initialize Scheduled Emails model class
     */
    public function __construct() {
        parent::__construct();
        $this->_name = 'config';
    }
    
    /**
     * Get variable values from config
     * 
     * @param  array | string $cols Coloumns to fetch
     * @param string $where Where clause
     * @author Maninder Bali
     * return mixed
     */
    public function getConfig($cols='*',$where=null){
        $sql = $this->select()->from($this->_name, $cols);
        if($where){
            $sql->where($where);
        }
        $result = $sql->query()->fetchAll();
        return !empty($result) ? $result[0]['config_val'] : false;
    }
    
     /**
     * 
      Method to Get the Config Ids
     * */
    
    public function getConfigQueIds($values)
    {
         /* $values=array('sales_survey_introtext','survey_notexist_orclosed','survey_proceed','survey_progress','survey_thank_you_text','survey_submit_button'
                        ,'survey_ok_button','survey_pre_submit_text','survey_notselected_text'); */
         $resultset = $this->select()->setIntegrityCheck(false)
                ->from(array('conf' => 'config'), '*')
                ->where("conf.config_var in (?)",$values)//->__toString();die;
                ->query()
                ->fetchall();
         foreach($resultset as $result)
         {
             $questionids[$result['config_var']]=$result['config_val'];
         }
         
         return $questionids;
    
    }

}
