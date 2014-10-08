<?php

require "inc/config.inc.php";

class Academico33 extends Ruckusing_Migration_Base {

    public function up() {
        //ALTERANDO AVALIACOES PARA ARREDONDAMENTO DE NOTAS
        $this->execute("UPDATE TiposAvaliacoes SET arredondar=1 WHERE tipo = 'pontoExtra' OR tipo = 'substitutiva'");

        //AUMENTANDO VALOR DE CAMPO
        $this->execute("ALTER TABLE  `Atribuicoes` CHANGE  `formula`  `formula` VARCHAR( 100 ) NULL DEFAULT NULL ;");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='416', versaoAtual='416'");
        printf("<br>Patch Academico33: OK");
    }

    //up()
    public function down() {
        
    }

//down()
}
