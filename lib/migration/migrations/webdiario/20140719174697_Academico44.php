<?php

require "inc/config.inc.php";

class Academico44 extends Ruckusing_Migration_Base {

    public function up() {
        //EXCLUINDO REGISTROS EM BRANCO DA TABELA PESSOAS
        $this->execute("DELETE FROM Pessoas WHERE nome IS NULL AND prontuario IS NULL AND senha IS NULL");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='427', versaoAtual='427'");
        printf("<br>Patch Academico44: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
