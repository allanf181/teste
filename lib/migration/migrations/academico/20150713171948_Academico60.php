<?php

class Academico60 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("update Permissoes set permissao=CONCAT(permissao,',view/ajax/controleListagens.php') where tipo <> 1");
        $this->execute("create table if not exists ControleListagens(
                        codigo int primary key auto_increment,
                        pessoa int,
                        item char(1),    
                        data datetime,
                        foreign key (pessoa) references Pessoas(codigo))");

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='443', versaoAtual='443'");
        
        printf("\nPatch ".get_class().": OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
