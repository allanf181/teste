<?php

require "inc/config.inc.php";

class Academico25 extends Ruckusing_Migration_Base {

    public function up() {
        //Removendo Registros em Branco
        $this->execute("DELETE FROM `Notas` WHERE nota = '';");
        
        //Alterando campo
        $this->execute("ALTER TABLE  `PlanosAula` CHANGE  `semana`  `semana` FLOAT( 4 ) NOT NULL;");


        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='408', versaoAtual='408'");
        printf("<br>Patch Academico25: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
