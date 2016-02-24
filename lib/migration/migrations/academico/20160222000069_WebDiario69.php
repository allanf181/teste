<?php

class WebDiario69 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("alter table TDComponente add column prioridade char(1);");
        $this->execute("alter table TDComponente add column referencia char(1);");
        $this->execute("alter table TDComponente change periodo turno char(1);");
        $this->execute("alter table TDAtvECmt add column referencia char(1);");
        $this->execute("UPDATE Instituicoes SET versao='449', versaoAtual='449'");

        printf("\nPatch WebDiario 69: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
