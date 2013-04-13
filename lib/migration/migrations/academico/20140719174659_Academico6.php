<?php
require "inc/config.inc.php";

class Academico6 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `FrequenciasAbonos` CHANGE  `data`  `dataInicio` DATE NOT NULL");

        // ADD COLUMN ON FREQUENCIASABONOS
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '".MY_DB."'
                                    AND TABLE_NAME = 'FrequenciasAbonos' 
                                    AND COLUMN_NAME = 'dataFim'");
        if (!$result) {
            $this->execute("ALTER TABLE  `FrequenciasAbonos` ADD  `dataFim` DATE NULL AFTER  `dataInicio`");
        }
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='388', versaoAtual='388'");
        printf("<br>Patch Academico6: OK");
    }//up()

    public function down()
    {
    }//down()
}
