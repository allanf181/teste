<?php

require_once DAO.'/DisciplinaDao.php';
require_once CONTROLLER.'/doctrine/Controller.php';

class DisciplinaController extends Controller {
    
    private $dao;

    public function __construct() {
        parent::__construct($this->dao = new DisciplinaDao());        
    }
    
    
    
}

