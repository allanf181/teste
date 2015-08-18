<?php

require_once DAO.'/PessoaDao.php';
require_once CONTROLLER.'/doctrine/Controller.php';

class PessoaController extends Controller {
    
    private $dao;

    public function __construct() {
        parent::__construct($this->dao = new PessoaDao());        
    }
    
    
    
}

