<?php

class WebDiario64 extends Ruckusing_Migration_Base
{
    public function up()
    {

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='447', versaoAtual='447'");
        printf("\nPatch WebDiario 64: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
