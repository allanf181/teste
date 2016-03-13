<?php

class WebDiario73 extends Ruckusing_Migration_Base
{
    public function up()
    {
        
        $this->execute("create table Caads(codigo int primary key auto_increment, area int, tipo int);");
        $this->execute("insert into PermissoesArquivos values(0,5,'view/secretaria/atribuicao_docente/caad.php', 'CAADs', 'view/secretaria/atribuicao_docente/caad.php');");
        $this->execute("UPDATE Instituicoes SET versao='450', versaoAtual='450'");
 
        printf("\nPatch WebDiario 73: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
