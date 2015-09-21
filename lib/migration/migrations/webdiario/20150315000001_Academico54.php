<?php

include "inc/config.inc.php";

class Academico54 extends Ruckusing_Migration_Base {

    public function up() {
        //AUMENTANDO OS TAMANHOS DOS CAMPOS
        $this->execute("ALTER TABLE  `Disciplinas` CHANGE  `nome`  `nome` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
                        ALTER TABLE  `Pessoas` CHANGE  `nome`  `nome` VARCHAR( 60 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL
                        ");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='437', versaoAtual='437'");
        printf("<br>Patch Academico54: OK");
    }

    //up()
    public function down() {
    }

    //down()
}
