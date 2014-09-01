<?php

require "inc/config.inc.php";

class Academico22 extends Ruckusing_Migration_Base {

    public function up() {
        // ALTERANDO TABELA CALENDARIOS
        $this->execute("ALTER TABLE  `Calendarios` CHANGE  `data`  `dataInicio` DATE NOT NULL;");
        $this->execute("ALTER TABLE  `Calendarios` ADD  `dataFim` DATE NULL AFTER  `dataInicio`;");

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='405', versaoAtual='405'");
        printf("<br>Patch Academico22: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
