<?php

require "inc/config.inc.php";

class Academico31 extends Ruckusing_Migration_Base {

    public function up() {
        // ALTER COLUMN ON Atribuicoes
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Atribuicoes' 
                                    AND COLUMN_NAME = 'grupo'");
        if ($result)
            $this->execute("ALTER TABLE  `Atribuicoes` CHANGE  `grupo`  `periodo` INT( 11 ) NULL DEFAULT NULL ;");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='414', versaoAtual='414'");
        printf("<br>Patch Academico31: OK");
    }

    //up()
    public function down() {
        
    }

//down()
}
