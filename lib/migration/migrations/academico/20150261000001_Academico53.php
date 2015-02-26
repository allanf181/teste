<?php

include "inc/config.inc.php";

class Academico53 extends Ruckusing_Migration_Base {

    public function up() {
        // CORRIGINDO ERRO DE REGISTROS DUPLICADOS EM MATRÍCULAS
        $result = $this->select_all("SELECT * FROM information_schema.TABLE_CONSTRAINTS 
                                WHERE
                                CONSTRAINT_SCHEMA = DATABASE() AND
                                CONSTRAINT_NAME   = 'aluno_atribuicao' AND
                                CONSTRAINT_TYPE   = 'FOREIGN KEY'");
        if (!$result) {
            $this->execute("ALTER TABLE Matriculas DROP PRIMARY KEY, ADD PRIMARY KEY (codigo);
                        
                            ALTER TABLE  `Matriculas` ADD UNIQUE aluno_atribuicao (
                                `aluno` ,
                                `atribuicao`
                            );");
        }
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='436', versaoAtual='436'");
        printf("<br>Patch Academico53: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
