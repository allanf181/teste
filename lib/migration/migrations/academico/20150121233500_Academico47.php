<?php

require "inc/config.inc.php";

class Academico47 extends Ruckusing_Migration_Base {

    public function up() {
        //ALTERANDO TABELA TIPOS
        $this->execute("ALTER TABLE  `Tipos` ADD  `alteraAnoSem` CHAR( 1 ) NULL AFTER  `nome` ;");

        //ATUALIZANDO REGISTROS DA TABELA TIPOS
        $this->execute("UPDATE Tipos SET alteraAnoSem=0 WHERE nome = 'Aluno' OR nome = 'Professor' ;");
        $this->execute("UPDATE Tipos SET alteraAnoSem=1 WHERE nome <> 'Aluno' AND nome <> 'Professor' ;");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='430', versaoAtual='430'");
        printf("<br>Patch Academico47: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
