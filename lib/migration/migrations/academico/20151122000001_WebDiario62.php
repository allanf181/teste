<?php

class WebDiario62 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("update Permissoes set permissao=CONCAT(permissao,',view/secretaria/boletim.php') where tipo>=2");
        $this->execute("alter table Instituicoes add column email_auth char(1)");
        $this->execute("Update Instituicoes set email_auth=1");

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='445', versaoAtual='445'");
        printf("\nPatch Academico62: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
