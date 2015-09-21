<?php

require "inc/config.inc.php";

class Academico42 extends Ruckusing_Migration_Base {

    public function up() {
        //RETIRANDO AS REAVALIACOES FINAIS
        $this->execute("ALTER TABLE  `Atribuicoes` CHANGE  `observacoes`  `observacoes` VARCHAR( 1500 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ,
                        CHANGE  `competencias`  `competencias` VARCHAR( 1500 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;
                        ");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='425', versaoAtual='425'");
        printf("<br>Patch Academico42: OK");
    }
    //up()
    public function down() {
    }
    //down()
}
