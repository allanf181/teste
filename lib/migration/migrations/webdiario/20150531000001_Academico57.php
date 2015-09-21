<?php

include "inc/config.inc.php";

class Academico57 extends Ruckusing_Migration_Base {

    public function up() {
        // ALTER COLUMN ON PESSOAS
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Pessoas' 
                                    AND COLUMN_NAME = 'numero'");
        if (!$result)
            $this->execute("ALTER TABLE  `Pessoas` ADD  `numero` VARCHAR( 10 ) NULL AFTER  `endereco` ;");

        // ALTER COLUMN ON NOTAS FINAIS
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'NotasFinais' 
                                    AND COLUMN_NAME = 'recuperacao'");
        if (!$result)
            $this->execute("ALTER TABLE  `NotasFinais` ADD  `recuperacao` CHAR( 1 ) NULL AFTER  `retorno` ;");
        
        $this->execute("ALTER TABLE `Pessoas` CHANGE `endereco` `endereco` VARCHAR( 60 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;");

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='440', versaoAtual='440'");
        printf("<br>Patch Academico57: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
