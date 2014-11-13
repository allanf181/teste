<?php

require "inc/config.inc.php";

class Academico35 extends Ruckusing_Migration_Base {

    public function up() {
        // ALTER COLUMN ON Calendarios
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Calendarios' 
                                    AND COLUMN_NAME = 'curso'");
        if (!$result)
            $this->execute("ALTER TABLE  `Calendarios` ADD  `curso` INT NOT NULL DEFAULT  '0', ADD  `tipo` INT NOT NULL DEFAULT  '0';");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='418', versaoAtual='418'");
        printf("<br>Patch Academico35: OK");
    }

    //up()
    public function down() {
        
    }

//down()
}
