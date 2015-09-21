<?php

class WebDiario61 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("update Permissoes set menu=replace(menu,'view/professor/questionario.php','');");

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='444', versaoAtual='444'");
        printf("\nPatch WebDiario61: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
