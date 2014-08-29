<?php

require "inc/config.inc.php";

class Academico20 extends Ruckusing_Migration_Base {

    public function up() {
        // ATUALIZANDO OS TIPOS DE AVALIACOES
        $this->execute("UPDATE `TiposAvaliacoes` SET notaMenor = '6', notaMaxima='10'  WHERE tipo='recuperacao'  AND nome = 'Recuperação - Adiciona valor na média'");
        $this->execute("UPDATE `TiposAvaliacoes` SET notaMenor = '6', notaMaxima='10'  WHERE tipo='recuperacao'  AND nome = 'Recuperação - Adiciona valor na menor nota'");
        $this->execute("UPDATE `TiposAvaliacoes` SET notaMaxima='10'  WHERE tipo='avaliacao'  AND nome = 'Avaliação'");
        
        // AUMENTAR TAMANHO DO CAMPO DE AULA
        $this->execute("ALTER TABLE  `Aulas` CHANGE  `conteudo`  `conteudo` VARCHAR( 400 ) NOT NULL");
        $this->execute("ALTER TABLE  `Aulas` CHANGE  `atividade`  `atividade` VARCHAR( 400 ) NULL DEFAULT NULL");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='403', versaoAtual='403'");
        printf("<br>Patch Academico20: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
