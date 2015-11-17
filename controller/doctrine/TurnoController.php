<?php

require_once DAO.'/TurnoDao.php';
require_once CONTROLLER.'/doctrine/Controller.php';

class TurnoController extends Controller {
    
    private $dao;

    public function __construct() {
        parent::__construct($this->dao = new TurnoDao());        
    }
    
    
    
}

