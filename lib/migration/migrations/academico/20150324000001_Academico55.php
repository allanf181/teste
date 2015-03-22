<?php

include "inc/config.inc.php";

class Academico55 extends Ruckusing_Migration_Base {

    public function up() {
        // ALTER COLUMN ON INSTITUICOES
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'ldap_sync'");
        if (!$result)
            $this->execute("ALTER TABLE  `Instituicoes` ADD  `ldap_sync` CHAR( 1 ) NULL AFTER  `email_port` ;");

        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'ldap_pass'");
        if (!$result)
            $this->execute("ALTER TABLE  `Instituicoes` ADD  `ldap_pass` CHAR( 1 ) NULL AFTER  `ldap_sync` ;");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='438', versaoAtual='438'");
        printf("<br>Patch Academico55: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
