<?php

class Academico59_1 extends Ruckusing_Migration_Base
{
    public function up()
    {
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='443', versaoAtual='443'");
        printf("\nPatch Academico59_1: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
