<?php

require "inc/config.inc.php";

class Academico13 extends Ruckusing_Migration_Base {

    public function up() {
        // CHAVES EM AVALIACOES
        $this->execute("ALTER TABLE  `academico`.`Avaliacoes` DROP PRIMARY KEY , ADD PRIMARY KEY (  `codigo` )");
        $this->execute("ALTER TABLE  `Avaliacoes` ADD  `substitutiva` INT NULL AFTER  `tipo`");
        $this->execute("ALTER TABLE  `Avaliacoes` ADD INDEX (  `substitutiva` )");
        $this->execute("ALTER TABLE  `Avaliacoes` ADD FOREIGN KEY (  `substitutiva` ) REFERENCES  `academico`.`Avaliacoes` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE ");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='396', versaoAtual='396'");
        printf("<br>Patch Academico13: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
