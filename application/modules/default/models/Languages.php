<?php

class Default_Model_Languages extends Default_Model_Core {

    /**
     * Constructor to initialize events model class
     */
    public function __construct() {
        parent::__construct();

        $this->_name = 'languages';
    }

    public function getLanguages($used_for = '', $selected_langid = '') {
        $rs_language = $this->select()//->setIntegrityCheck(false)
                ->from(array('lang' => "languages"), '*');
        switch ($used_for) {
            case "assessment":
                $rs_language->where("`is_survey` = 1");

                break;
            case "report":
                $rs_language->where("`is_reporting` = 1");
                break;
             default : //do nothing
                break;
        }

        if (!empty($selected_langid)) {
            $rs_language->where("`langid` = ?", $selected_langid);
        }
        $rs_language->order("local_name asc");
        //  echo $rs_language->__toString();die; 
        //->where("is_survey = 1")//->__toString();die;		
        return $rs_language->query()
                        ->fetchAll();
    }

    public function getLanguageByCode($code) {
        if ($code != '') {
            $condition = array(
                'UPPER(lang_code) = ?' => strtoupper($code)
            );
            $languageId = $this->fetchRow($condition);
            if (!empty($languageId)) {
                $data = $languageId->toArray();
                return $data['langid'];
            } else {
                return 1;
            }
        }
    }

    public function getAllLanguages() {
        $result = $this->select()->from('languages', array('langid', 'lang_name'))->where('langid != 1')->query()->fetchAll();
        return !(empty($result)) ? $result : FALSE;
    }

    public function saveLanguages($data) {
        if (!$this->_checkQuestionLangExist($data['questionid'], $data['langid'])) {
            $this->_db->insert('assessment_event_question_langs', $data);
        } else {
            $this->_db->update('assessment_event_question_langs', $data, "langid ='$data[langid]' AND questionid=$data[questionid]");
        }
    }

    public function _checkQuestionLangExist($questionId, $langId) {
        $select = $this->select()->setIntegrityCheck(FALSE)->from('assessment_event_question_langs')->where('langid = ?', $langId)->where('questionid = ?', $questionId);
        $result = $select->query()->fetchAll();
        return !empty($result) ? true : false;
    }
    
    public function getFooterSignatures( ) {
        return $this->select()
                    ->from( $this->_name, array('langid') )
                    ->query( )
                    ->fetchAll( );
    }

}
