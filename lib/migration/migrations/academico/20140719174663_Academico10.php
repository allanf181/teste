<?php

require "inc/config.inc.php";

class Academico10 extends Ruckusing_Migration_Base {

    public function up() {
        // ADD COLUMN ON INSTITUICOES
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'ldap_ativado'");
        if (!$result) {
            $this->execute("ALTER TABLE  `Instituicoes` ADD  `ldap_ativado` VARCHAR( 1 ) NULL ,
                            ADD  `ldap_user` VARCHAR( 50 ) NULL ,
                            ADD  `ldap_password` VARCHAR( 50 ) NULL ,
                            ADD  `ldap_basedn` VARCHAR( 200 ) NULL ,
                            ADD  `ldap_dominio` VARCHAR( 200 ) NULL ,
                            ADD  `ldap_porta` VARCHAR( 5 ) NULL ,
                            ADD  `ldap_cache` VARCHAR( 2 ) NULL");
        }

        // CREATE TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES
                                    WHERE 
                                        TABLE_SCHEMA = '".MY_DB."' 
                                    AND TABLE_NAME = 'schema_ldap_cache' ");
        if (!$result) {
            $this->execute("CREATE TABLE IF NOT EXISTS `schema_ldap_cache` (
                            `codigo` int(5) NOT NULL AUTO_INCREMENT,
                            `prontuario` varchar(45) NOT NULL,
                            `senha` varchar(128) NOT NULL,
                            `data` datetime NOT NULL,
                            PRIMARY KEY (`codigo`)
                          ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");
        }
        // APAGANDO TIPOS DE AVALIACOES NAO UTILIZADAS
        $this->execute("DELETE FROM TiposAvaliacoes WHERE codigo NOT IN (SELECT tipo FROM Avaliacoes)");
        
        // CREATE KEY
        $result = $this->select_all("SHOW KEYS FROM TiposAvaliacoes WHERE Key_name = 'nome'");
        if (!$result) {
            $this->execute("ALTER TABLE  `academico`.`TiposAvaliacoes` ADD UNIQUE ( `nome` , `modalidade`) ");
        }
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='392', versaoAtual='392'");
        printf("<br>Patch Academico10: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
