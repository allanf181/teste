<?php

require "inc/config.inc.php";

class Academico32 extends Ruckusing_Migration_Base {

    public function up() {
        // REMOVENDO NOTAS PARA IMPORTAR CORRETAMENTE
        $this->execute("DELETE FROM  `NotasFinais` WHERE flag =  '5';");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='415', versaoAtual='415'");
        printf("<br>Patch Academico32: OK");
    }

    //up()
    public function down() {
        
    }

//down()
}
