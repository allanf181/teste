<?php

require "inc/config.inc.php";

class Academico38 extends Ruckusing_Migration_Base {

    public function up() {
        //RETIRANDO AS REAVALIACOES FINAIS
        $this->execute("DELETE FROM `TiposAvaliacoes` WHERE nome = 'Reavaliação Final' ");
            
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='421', versaoAtual='421'");
        printf("<br>Patch Academico38: OK");
    }
    //up()
    public function down() {
    }
    //down()
}
