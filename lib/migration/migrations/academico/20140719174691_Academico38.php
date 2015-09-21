<?php

require "inc/config.inc.php";

class Academico38 extends Ruckusing_Migration_Base {

    public function up() {
        //RETIRANDO AS REAVALIACOES FINAIS
        $this->execute("ALTER TABLE  `Avaliacoes` DROP FOREIGN KEY  `Avaliacoes_ibfk_2` ;
                        ALTER TABLE  `Avaliacoes` ADD CONSTRAINT  `Avaliacoes_ibfk_2` FOREIGN KEY (  `tipo` ) REFERENCES `academico`.`TiposAvaliacoes` (
                        `codigo`
                        ) ON DELETE CASCADE ON UPDATE CASCADE ;");
        
        $this->execute("DELETE FROM `TiposAvaliacoes` WHERE nome = 'Reavaliação Final' ");
            
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='421', versaoAtual='421'");
        printf("<br>Patch Academico38: OK");
    }
    //up()
    public function down() {
    }
    //down()
}
