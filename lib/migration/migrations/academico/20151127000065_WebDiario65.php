<?php

class WebDiario65 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("alter table PlanosAula drop foreign key PlanosAula_ibfk_1;");
        $this->execute("alter table PlanosAula add constraint PlanosAula_ibfk_1 foreign key(atribuicao) references Atribuicoes(codigo) on delete cascade;");
        $this->execute("alter table PlanosEnsino drop foreign key PlanosEnsino_ibfk_1;");
        $this->execute("alter table PlanosEnsino add constraint PlanosEnsino_ibfk_1 foreign key (atribuicao) references Atribuicoes(codigo) on delete cascade;");

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='447', versaoAtual='447'");
        printf("\nPatch WebDiario 65: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
