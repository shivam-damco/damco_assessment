<?php

/**
 * Model class to handle dynamic pages operations
 * 
 * @author Harpreet Singh
 * @date   1 July, 2014
 * @version 1.0
 */

class Default_Model_Pages extends Default_Model_Core {
    
    public function __construct() {
        parent::__construct();
        $this->_name = 'pages';
    }
    
    public function getPageContent( $slug, $langID ) {
        $rs = $this->select()
                ->from(array('p' => $this->_name),
                    array('p.id', 'p.page_slug'))
                ->joinleft(array('pc' => 'page_content'),
                    'p.id = pc.page_id', array('pc.title', 'pc.content'))
                ->where('p.is_active = "1" '
                        . ' AND p.page_slug = "'.$slug.'" '
                        . ' AND ( pc.lang_id = "'.$langID.'"'
                        . ' OR pc.lang_id = "1" )')
                ->setIntegrityCheck(FALSE)
                ->order('pc.lang_id DESC');
//        die($rs->__toString());
        return $rs->query()->fetchAll();
    }
}