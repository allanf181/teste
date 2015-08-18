<?php

require_once DAO.'/ModalidadeDao.php';
require_once CONTROLLER.'/doctrine/Controller.php';

class ModalidadeController extends Controller {
    
    private $dao;

    public function __construct() {
        parent::__construct($this->dao = new ModalidadeDao());        
    }
    
    
    
}

