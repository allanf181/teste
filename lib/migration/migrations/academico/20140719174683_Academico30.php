<?php

require "inc/config.inc.php";

class Academico30 extends Ruckusing_Migration_Base {

    public function up() {
        // ALTER COLUMN ON Instituicoes
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'email_smtp'");
        if (!$result)
            $this->execute("ALTER TABLE  `Instituicoes` ADD  `email_smtp` VARCHAR( 100 ) NULL AFTER  `ldap_cache` ,
                            ADD  `email_port` VARCHAR( 5 ) NULL AFTER  `email_smtp` ,
                            ADD  `email_secure` VARCHAR( 3 ) NULL AFTER  `email_port` ,
                            ADD  `email_account` VARCHAR( 100 ) NULL AFTER  `email_secure` ,
                            ADD  `email_password` VARCHAR( 50 ) NULL AFTER  `email_account` ;");

        // DROP COLUMN ON Instituicoes
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'ipServidorAtualizacao'");
        if ($result)
            $this->execute("ALTER TABLE  `Instituicoes` DROP  `ipServidorAtualizacao`;");

        // DROP COLUMN ON Instituicoes
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'usuarioServidorAtualizacao'");
        if ($result)
            $this->execute("ALTER TABLE  `Instituicoes` DROP  `usuarioServidorAtualizacao`;");
        
        // DROP COLUMN ON Instituicoes
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'senhaServidorAtualizacao'");
        if ($result)
            $this->execute("ALTER TABLE  `Instituicoes` DROP  `senhaServidorAtualizacao`;");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='413', versaoAtual='413'");
        printf("<br>Patch Academico30: OK");
    }

    //up()
    public function down() {
        
    }

//down()
}
