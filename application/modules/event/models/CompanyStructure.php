<?php

/**
 * Model class to handle access hierarchy operations
 * 
 * @author Harpreet Singh
 * @date   04 June, 2014
 * @version 1.0
 */

class Event_Model_CompanyStructure extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();
        
        $this->_name = 'company_structure';
    }
    
    /**
     * Method to call SP and get access hierarchy
     * @param type $spName
     * @param type $inParam
     * @param type $debug
     * @return type
     */
    public function getAccessHierarchy( $spName, $inParam, $debug = FALSE ) {
        return $this->_spObj->getSpData($spName, $inParam, $debug);
    }
    
    /*
     * get contry/market name on the basis of language choosen
     */
    public function getmarketName($hierarchy_level_id,$lang_id)
    {
        $marketObj = new Event_Model_CompanyStructure();
        $marketcolumns = array("id","structid","struct_name");
        //$arrAllMarkets = $marketObj->getWhere($marketcolumns, array("hierarchy_level_id" => $hierarchy_level_id),array("struct_name"=>array("Distributors","Office Account","Belgium-Dutch","Export Sales","Delhi India","Off Shore Dealers","Other","X-Sport","Manaus Brazil")),"struct_name");
        $arrnotIn=array("Distributors","Office Account","Belgium-Dutch","Export Sales","Delhi India","Off Shore Dealers","Other","X-Sport","Manaus Brazil");
        $sqlAllLanguageBasedMarkets = $this->_db->select()
                                            //->setIntegrityCheck(false)
                                            ->from(array('cs'=>'company_structure'),array("id","structid","struct_name"))
                                            ->joinLeft(array('cl'=>'country_langs'),'cs.structid = cl.struct_id',array("country_name"))
                                            ->where("hierarchy_level_id= ? ", $hierarchy_level_id)
                                            ->where("lang_id= ? ", $lang_id)
                                            ->where("struct_name not in (?) ", $arrnotIn)//->__tostring()
                                            ->order("country_name")   // ->__tostring();
                                            ->query()
                                            ->fetchAll();
       // print_r($sqlAllLanguageBasedMarkets);die;
         return $sqlAllLanguageBasedMarkets;                           
        
    }
    
    public function getnationlist($branchid='',$flag='all',$cond="")
    { 
        $query="SELECT 
                        IF ( n.nation IS NULL, cs.`struct_name`, n.`nation` ) AS nation_name,
                        IF ( n.`id` IS NULL, cs.`structid`, n.`id` ) AS nationid
                        FROM `company_structure` AS cs
                        LEFT JOIN `nation_markets` AS nm ON cs.`structid` = nm.`market_structid`
                        LEFT JOIN `nations` AS n ON n.`id` = nm.`nation_id`
                        WHERE cs.`hierarchy_level_id` = '3' AND cs.`structid` != 'BE\',\'LU'";
       if($branchid!='')
       { 
           $query.=" and cs.`parent_structid` in (".$branchid.")";
       }
           $query.="GROUP BY nationid ";
       if(!empty($cond))
       {
             $query.= " having ".$cond;      
       }
       $query.="ORDER BY nation_name ASC";       

        $fetchrows=$this->db->query($query);
       if($flag=='one') {
          $result=$fetchrows->fetch();
       }
       else {
           $result=$fetchrows->fetchall();
       }
           
        return $result;
    }
    
    public function getSalesRegions( $branch ) {
        return $this->select()
                //->setIntegrityCheck(false)
                ->from( array( 'p' => 'company_structure' ), array( ) )
                ->joinLeft( array( 'c' => 'company_structure' ), 'c.parent_structid = p.structid', 
                        array( ) )
                ->joinLeft( array( 'g' => 'company_structure' ), 'g.parent_structid = c.structid', 
                        array( 'g.structid', 'g.struct_name' ) )
                ->where( ' g.hierarchy_level_id = ? ', '4' )
                ->where( ' p.structid = ? ', $branch )
                ->order( 'struct_name' )
                ->query( )
                ->fetchAll();
    }

    /*
     * Fetch name of Nation on basis of nation ID.
     * 
     * @param array $nationList
     * @param mixed $nationID
     *
     * @return string
     * 
     * @author Kuldeep Dangi <kuldeepd@damcogroup.com>
     */
    public function getNationName($nationList, $nationID)
    {
        $nationName = '-';
        foreach ($nationList as $nation) {
            if ($nationID == $nation['nationid']) {
                return $nation['nation_name'];
            }
        }
        return $nationName;
    }

    /*
     * Fetch name of Nation on basis of nation ID.
     *
     * @param array $nationIDs
     * @param int $langiId
     *
     * @return string
     *
     * @author Kuldeep Dangi <kuldeepd@damcogroup.com>
     */
    public function getNationByLanguage($nationIDs, $langId)
    {
        $tableNames = array('country_langs', 'nation_langs');
        $data = $result = array();
        foreach ($tableNames as $table) {
            $data[] = $this->_db->select()
                        ->from( array($table), array('nation_name' => 'country_name',
                            'nationid' => 'struct_id') )
                        ->where('struct_id IN(?)',  $nationIDs)
                        ->where('lang_id = ?', $langId )
                        ->query()
                        ->fetchAll();
        }
        $result = array_merge($data[0],$data[1]);
        usort($result, function($a, $b) {
            return strcmp($a['nation_name'], $b['nation_name']);
        });
        return $result;
    }

}