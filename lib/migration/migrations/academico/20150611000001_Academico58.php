<?php

include "inc/config.inc.php";

class Academico58 extends Ruckusing_Migration_Base {

    public function up() {
        // ALTER COLUMN ON PESSOAS
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'PlanosAula' 
                                    AND COLUMN_NAME = 'criterio'");
        if (!$result)
            $this->execute("ALTER TABLE `PlanosAula` ADD `criterio` VARCHAR( 400 ) NULL AFTER `conteudo` ,
                                ADD `metodologia` VARCHAR( 400 ) NULL AFTER `criterio` ;");

        // ALTER COLUMN ON NOTAS FINAIS
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'NotasFinais' 
                                    AND COLUMN_NAME = 'situacao'");
        if (!$result)
            $this->execute("ALTER TABLE `NotasFinais` ADD `situacao` VARCHAR( 200 ) NULL AFTER `recuperacao` ;");

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='441', versaoAtual='441'");
        printf("<br>Patch Academico58: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
