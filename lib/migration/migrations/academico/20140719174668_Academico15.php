<?php

require "inc/config.inc.php";

class Academico15 extends Ruckusing_Migration_Base {

    public function up() {
        // ATUALIZAR CAMPOS DE AULAS E PLANOS DE AULAS
        $this->execute("ALTER TABLE  `PlanosAula` CHANGE  `conteudo`  `conteudo` VARCHAR( 200 )");
        $this->execute("ALTER TABLE  `Aulas` CHANGE  `anotacao`  `anotacao` VARCHAR( 500 )");

        // ALTERAR SITUACOES
        $this->execute("UPDATE  `academico`.`Situacoes` SET  `habilitar` =  '1' WHERE  `Situacoes`.`codigo` =3");
        $this->execute("UPDATE  `academico`.`Situacoes` SET  `habilitar` =  '1' WHERE  `Situacoes`.`codigo` =4");
        $this->execute("UPDATE  `academico`.`Situacoes` SET  `habilitar` =  '1' WHERE  `Situacoes`.`codigo` =7");
        $this->execute("UPDATE  `academico`.`Situacoes` SET  `habilitar` =  '1' WHERE  `Situacoes`.`codigo` =8");
        $this->execute("UPDATE  `academico`.`Situacoes` SET  `habilitar` =  '1' WHERE  `Situacoes`.`codigo` =9");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='398', versaoAtual='398'");
        printf("<br>Patch Academico15: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
