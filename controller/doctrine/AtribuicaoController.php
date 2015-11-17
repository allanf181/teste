<?php

require_once DAO.'/AtribuicaoDao.php';
require_once CONTROLLER.'/doctrine/Controller.php';

class AtribuicaoController extends Controller {
    
    private $dao;

    public function __construct() {
        parent::__construct($this->dao = new AtribuicaoDao());        
    }
    
    
    
}

