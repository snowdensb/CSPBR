<?php

namespace NCSC;

class NCSCBase {
    
    protected $f3;
    protected $db;
    protected $permissions;
    
    /**
     * initialize controller
     *
     * @return void
     */
    public function __construct()
    {
        $this->f3 = \Base::instance();
        $this->db = $this->f3->get("DB");
        $this->permissions = new \NCSC\Permissions();
    }
    
    public function getUserId() {
        $userId = $this->f3->get('SESSION.user.userId');
        if (empty($userId)) {
            return null;
        }
        else {
            return $userId;
        }
    }
    
}