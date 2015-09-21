<?php

require "inc/config.inc.php";

class Academico16 extends Ruckusing_Migration_Base {

    public function up() {
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='399', versaoAtual='399'");
        printf("<br>Patch Academico16: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
