<?php

require "inc/config.inc.php";

class Academico18 extends Ruckusing_Migration_Base {

    public function up() {
        // ADD COLUMN ON INSTITUICOES
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'ldap_filter'");
        if (!$result) {
            $this->execute("ALTER TABLE  `Instituicoes` ADD  `ldap_filter` VARCHAR( 50 ) NULL AFTER  `ldap_basedn`");
        }
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='401', versaoAtual='401'");
        printf("<br>Patch Academico18: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
