<?php

require_once DAO.'/CursoDao.php';
require_once CONTROLLER.'/doctrine/Controller.php';

class CursoController extends Controller {
    
    private $dao;

    public function __construct() {
        parent::__construct($this->dao = new CursoDao());        
    }
    
    /** @return Curso */
    public function getLista(){
        return $this->dao->getList();
    }

    
    
}

