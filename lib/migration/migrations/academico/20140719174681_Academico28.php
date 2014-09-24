<?php

require "inc/config.inc.php";

class Academico28 extends Ruckusing_Migration_Base {

    public function up() {
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='411', versaoAtual='411'");
        printf("<br>Patch Academico28: OK");
    }

    //up()

    public function down() {
        
    }

    //down()
}
