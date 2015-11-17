<?php

class Academico59 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("update Permissoes set permissao=CONCAT(permissao,',db2/lib/inc/aguardandoRoda.php') where tipo=3");
        $this->execute("update Permissoes set permissao=CONCAT(permissao,',db2/lib/inc/aguardandoRoda.php') where tipo=4");
        $this->execute("update Permissoes set permissao=CONCAT(permissao,',db2/lib/inc/aguardandoRoda.php') where tipo=5");
        $this->execute("update Permissoes set permissao=CONCAT(permissao,',view/secretaria/cursos/disciplina.php') where tipo=2");
        $this->execute("alter table Pessoas add column anoPadrao char(4)");
        $this->execute("alter table Pessoas add column semPadrao char(1)");
        $this->execute("update Tipos set alteraAnoSem=1");

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='442', versaoAtual='442'");
        printf("\nPatch Academico59: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
