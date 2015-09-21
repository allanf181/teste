<?php

require "inc/config.inc.php";

class Academico11 extends Ruckusing_Migration_Base {

    public function up() {
        // ALTER
        $this->execute("ALTER TABLE  `Avisos` CHANGE  `data`  `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='393', versaoAtual='393'");
        printf("<br>Patch Academico11: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
