<?php

require "inc/config.inc.php";

class Academico14 extends Ruckusing_Migration_Base {

    public function up() {
       
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='397', versaoAtual='397'");
        printf("<br>Patch Academico14: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
