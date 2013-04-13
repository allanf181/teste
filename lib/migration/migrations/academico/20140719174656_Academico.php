<?php
require "inc/config.inc.php";

class Academico extends Ruckusing_Migration_Base
{
    public function up()
    {
        // ADD COLUMN ON NOTASFINAIS
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '".MY_DB."'
                                    AND TABLE_NAME = 'NotasFinais' 
                                    AND COLUMN_NAME = 'flag'");
        if (!$result) {
            $this->execute("ALTER TABLE `NotasFinais` ADD `flag` CHAR( 1 ) NOT NULL DEFAULT '0' AFTER `sincronizado`");
            printf("TABLE COLUMN ADD: (flag)\n\n");
        }
        
        // DROP TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES
                                    WHERE 
                                        TABLE_SCHEMA = '".MY_DB."' 
                                    AND TABLE_NAME = 'AtualizacoesSistema' ");
        if ($result) {
            $this->execute("DROP TABLE AtualizacoesSistema");
            printf("DROP TABLE: (AtualizacoesSistema)\n\n");
        }
        
        // ADD COLUMN ON INSTITUICOES
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '".MY_DB."'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'versaoAtual'");
        if (!$result) {
            $this->execute("ALTER TABLE  `Instituicoes` ADD  `versaoAtual` VARCHAR( 20 ) NULL AFTER  `versao`");
            printf("TABLE COLUMN ADD: (flag)\n\n");
        }
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='380'");
    }//up()

    public function down()
    {
    }//down()
}
