<?php

include "inc/config.inc.php";

class Academico56 extends Ruckusing_Migration_Base {

    public function up() {
        // ALTER COLUMN ON INSTITUICOES
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'cae'");
        if (!$result)
            $this->execute("ALTER TABLE  `Instituicoes` ADD  `cae` INT( 11 ) NULL AFTER  `ssp` ;

                            ALTER TABLE  `Instituicoes` ADD INDEX (  `cae` ) ;

                            ALTER TABLE  `Instituicoes` ADD FOREIGN KEY (  `cae` ) REFERENCES  `academico`.`Tipos` (
                            `codigo`
                            ) ON DELETE SET NULL ON UPDATE NO ACTION ;");

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='439', versaoAtual='439'");
        printf("<br>Patch Academico56: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
