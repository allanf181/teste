<?php

require "inc/config.inc.php";

class Academico19 extends Ruckusing_Migration_Base {

    public function up() {
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='402', versaoAtual='402'");
        printf("<br>Patch Academico19: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
