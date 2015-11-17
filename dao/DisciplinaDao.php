<?php
require_once DAO.'/Dao.php';

class DisciplinaDao extends Dao {

    public function __construct() {
        parent::__construct();        
    }

    public function getList(){
        return $this->repo->findAll();
    }

    
    
}

?>