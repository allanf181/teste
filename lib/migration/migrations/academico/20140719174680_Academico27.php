<?php

require "inc/config.inc.php";

class Academico27 extends Ruckusing_Migration_Base {

    public function up() {
        //Alterando avaliacao
        $this->execute("UPDATE `TiposAvaliacoes` SET final=0, notaUltimBimestre=6, qdeMinima=1 WHERE tipo = 'recuperacao' AND modalidade = 1001");
        $this->execute("UPDATE `TiposAvaliacoes` SET final=0, notaUltimBimestre=6, qdeMinima=1 WHERE tipo = 'recuperacao' AND modalidade = 1003");

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='410', versaoAtual='410'");
        printf("<br>Patch Academico27: OK");
    }

    //up()

    public function down() {
        
    }

    //down()
}
