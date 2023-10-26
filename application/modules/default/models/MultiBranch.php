<?php

class Default_Model_MultiBranch extends Default_Model_Core
{
    protected $_name;
    public function __construct() {
        parent::__construct();

        $this->_name = 'multi_branch_users';
    }
    public function getbranch($username){
        $details = $this->select()
                ->from(array('mb' => $this->_name), array('GROUP_CONCAT(mb.subsidiaryid) AS subsidiary_id'))
                ->where('mb.username =?', $username);

        return $details->query()->fetch();
    }
}


