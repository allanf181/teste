<?php
require "inc/config.inc.php";

class Academico8 extends Ruckusing_Migration_Base
{
    public function up()
    {
        // ADD COLUMN ON FREQUENCIASABONOS
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '".MY_DB."'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'envioFoto'");
        if (!$result) {
            $this->execute("ALTER TABLE  `Instituicoes` ADD  `envioFoto` VARCHAR( 1 ) NULL AFTER  `senhaServidorAtualizacao`");
        }
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='390', versaoAtual='390'");
        printf("<br>Patch Academico8: OK");
    }//up()

    public function down()
    {
    }//down()
}
