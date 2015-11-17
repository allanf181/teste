<?php

require_once DAO.'/MatriculaDao.php';
require_once CONTROLLER.'/doctrine/Controller.php';

class MatriculaController extends Controller {
    
    private $dao;

    public function __construct() {
        parent::__construct($this->dao = new MatriculaDao());        
    }
    
    
    
}

