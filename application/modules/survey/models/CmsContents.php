<?php

/**
 * Model class
 * 
 * @author Manoj
 * @date   03 DEC, 2019
 * @version 1.0
 */
class Survey_Model_CmsContents extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();
        $this->_name = 'cms_contents';
    }

    public function getContentTemplate($contentid = '')
    { 
        $rs = $this->db->select()
                ->from(array('aet'=>$this->_name),array('title','content_type','content'));                
		if($contentid != ""){
			$rs = $rs->where("contentid=".$contentid);//->__toString();die;
		}
		
        $result= $rs->query()->fetchAll();
        
        return $result;
        
    }
    
}
