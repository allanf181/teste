<?php

require_once DAO.'/AreaDao.php';
require_once CONTROLLER.'/doctrine/Controller.php';

class AreaController extends Controller {
    
    private $dao;

    public function __construct() {
        parent::__construct($this->dao = new AreaDao());        
    }
    
    
    
}

