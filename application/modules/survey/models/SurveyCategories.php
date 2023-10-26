<?php

/**
 * Model class to handle survey categories operations
 * 
 * @author Mayank Dargan
 * @date   20 Jan, 2016
 * @version 1.0
 */
class Survey_Model_SurveyCategories extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();
        $this->_name = 'survey_categories';
    }

    public function checkRecordExist($surveyCategoryName)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('survey_category_id'))
                ->where("survey_category_name = ?", $surveyCategoryName);
        $result= $rs->query()->fetchAll();
        if(count($result)>0)
            return true;
        else
            return false;
    }
     
    public function getCountData()
    {
        $rs = $this->db->select()
                ->from(array($this->_name),'count(*) as COUNT');
                
        $result= $rs->query()->fetchAll();
        
        
        return $result;
    }
	
	public function getSurveyCategoriesData($data)
    {
        $rs = $this->db->select()
                ->from(array('s'=>$this->_name),array('s.survey_category_name','s.survey_category_id'))
                ->order($data['orderBy'])
                ->limit($data['length'],$data['start']);
        
        $result= $rs->query()->fetchAll();
		return $result;
    }
    
    public function saveData($data)
    {
        if(array_key_exists('survey_category_id',$data))
        {
            $this->_db->update($this->_name, $data, ' survey_category_id = ' . $data['survey_category_id']);
        }
        else 
        {
            $rs = $this->db->insert($this->_name,$data);
            $surveyCategoryId = $this->db->lastInsertId();
            return $surveyCategoryId;
        }
        
    }
    
    public function getSurveyCategoryByID($surveyCategoryId)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('survey_category_id','survey_category_name'))
                ->where("survey_category_id = ? ", $surveyCategoryId);
                
        $result= $rs->query()->fetchAll();
        
        return $result;
    }
    
    public function checkRecordExistById($surveyCategoryID,$surveyCategoryName)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('survey_category_id'))
                ->where("survey_category_name = ?", $surveyCategoryName)
                ->where(" survey_category_id <> ?", $surveyCategoryID);
        $result= $rs->query()->fetchAll();
        
        if(count($result)>0)
            return true;
        else
            return false;
    }
    
    public function checkRecordExistBySurveyCategoryID($surveyCategoryID)
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('survey_category_id'))
                ->where(" survey_category_id =  ?", $surveyCategoryID);
        
        $result= $rs->query()->fetchAll();
        
        if(count($result)>0)
            return true;
        else
            return false;
    }
    
    public function delete($ID)
    {
        $rs = $this->db->delete($this->_name,'survey_category_id = '.$ID);
        
        return true;
    }

    public function getAllSurveyCategoriesName()
    {
        $rs = $this->db->select()
                ->from(array($this->_name),array('survey_category_id','survey_category_name'))
                ->order('survey_category_name asc');
        $result= $rs->query()->fetchAll();
        
        return $result;
    }
}
