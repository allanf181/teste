<?php

require "inc/config.inc.php";

class Academico26 extends Ruckusing_Migration_Base {

    public function up() {
        //Alterando campo
        $result = $this->select_all("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                WHERE information_schema.TABLE_CONSTRAINTS.CONSTRAINT_TYPE = 'FOREIGN KEY' 
                AND information_schema.TABLE_CONSTRAINTS.TABLE_SCHEMA = '" . MY_DB . "'
                AND information_schema.TABLE_CONSTRAINTS.TABLE_NAME = 'NotasFinais';");
        if ($result) {
            $fk1 = $result[0]['CONSTRAINT_NAME'];
            $fk2 = $result[1]['CONSTRAINT_NAME'];

            $this->execute("ALTER TABLE  `NotasFinais` DROP FOREIGN KEY  `$fk1` ,
                        ADD FOREIGN KEY (  `atribuicao` ) REFERENCES  `academico`.`Atribuicoes` (
                        `codigo`
                        ) ON DELETE CASCADE ON UPDATE RESTRICT ;

                        ALTER TABLE  `NotasFinais` DROP FOREIGN KEY  `$fk2` ,
                        ADD FOREIGN KEY (  `matricula` ) REFERENCES  `academico`.`Matriculas` (
                        `codigo`
                        ) ON DELETE CASCADE ON UPDATE RESTRICT ; ");

            // ATUALIZAR VERSAO ATUAL
            $this->execute("UPDATE Instituicoes SET versao='409', versaoAtual='409'");
            printf("<br>Patch Academico26: OK");
        }
    }

    //up()

    public function down() {
        
    }

    //down()
}
