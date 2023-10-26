<?php

/**
 * Parent class for all models
 * 
 * @author Harpreet Singh
 * @date   26 May, 2014
 * @version 1.0 
 */
class Default_Model_Core extends Zend_Db_Table {

    protected $_spObj;
    protected $_name;
    public $db;

    /**
     * Constructor to initialize Core model class
     * It will create the default DB object and SP calling object
     */
    public function __construct() {
        parent::__construct();
        $this->db = $this->getDefaultAdapter();
        $dbConfig = $this->db->getConfig();
        $this->_spObj = Zend_Db::factory('Mysqli', array(
                    'host' => $dbConfig['host'],
                    'username' => $dbConfig['username'],
                    'password' => $dbConfig['password'],
                    'dbname' => $dbConfig['dbname'],
                    'adapterNamespace' => 'Damco_Db_Adapter'
        ));
    }

    public function getAll($columns = array()) {
        $rs = $this->select();
        if (empty($columns)) {
            $rs->from($this->_name);
        } else {
            $rs->from($this->_name, $columns);
        }

        return $rs->query()->fetchAll();
    }
    
    public function gettemplates($templateToken, $langid = 1) {
        $qry = $this->select()
                ->from('contents')
                ->where("content_token = ?", $templateToken)
                ->where("langid = ?", $langid)
                ->query()
                ->fetchrow();
        return $qry;
    }

    public function getLanguagesByName($lang_name) {
        $qry = $this->select() 
                ->from(array('ln' => 'languages'), '*')
                ->where("ln.lang_name like '" . $lang_name . "'")
                ->setIntegrityCheck(FALSE)
                ->query()
                ->fetchAll();
        return $qry;
    }

    public function dbupdate($arrcoulmn, $where = array()) {
        $this->update($arrcoulmn, $where);
      /*  Zend_Debug::dump($this->db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($this->db->getProfiler()->getLastQueryProfile()->getQueryParams());
$this->db->getProfiler()->setEnabled(false);die;*/

    }

    public function dbinsert($arrcoulmn = array()) {
        return $newinsertid = $this->insert($arrcoulmn);
    }

    // 
    public function getWhere($columns, $arrwhere = '',$arrLike = '' , $orderBy='') {
        $rs = $this->select();
        if (empty($columns)) {
            $rs->from($this->_name);
        } else {
            $rs->from($this->_name, $columns);
        }

        if (!empty($arrwhere)) {
            foreach ($arrwhere as $k => $v) {
                $k = $this->db->quoteIdentifier($k);
                $v = $this->db->quote($v); //dipa 9/24/14 1:10 PM
                $rs->where($k . " = " . $v . "");
            }
        }
        if (!empty($arrLike)) {   
            $cond="";          
            foreach ($arrLike as $k => $arrv) {
                foreach($arrv as $v)
                {
                    //$k = $this->db->quoteIdentifier($k);
                    $cond .= empty($cond) ? ("(".$k . " not like  '%" . $v . "%')") : " and (".$k . " not like  '%" . $v . "%')";
                }
                //$rs->orWhere($k . " = '" . $v . "'");
            } 
            $rs->Where($cond);
        }
        
        if(!empty($orderBy))
        {
            $rs->order(" $orderBy asc"); //echo $rs->__toString();die;
        }
       // echo $rs->__toString();//die;
        return $rs->query()->fetchAll();
    }
    
    public function findRow($columns,$arrwhere='')
    {
        $rs = $this->select();
        if ( empty( $columns ) ) {
            $rs->from($this->_name);
        }
        else {
            $rs->from($this->_name, $columns);
        }
        
        if(!empty($arrwhere))
        {
            foreach($arrwhere as $k=>$v)
            {
                $k = $this->db->quoteIdentifier($k);
                 $v = $this->db->quote($v); //dipa 9/24/14 1:10 PM
                $rs->where($k." = ".$v."");
            }

        } 
       // echo $rs->__toString();die;
        return $rs->query()->fetch();
    }
}
