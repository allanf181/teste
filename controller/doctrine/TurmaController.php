<?php

require_once DAO.'/TurmaDao.php';
require_once CONTROLLER.'/doctrine/Controller.php';

class TurmaController extends Controller {
    
    private $dao;

    public function __construct() {
        parent::__construct($this->dao = new TurmaDao());        
    }
    
    
    
}

