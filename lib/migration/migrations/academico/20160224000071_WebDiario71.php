<?php

class WebDiario71 extends Ruckusing_Migration_Base
{
    public function up()
    {
        
        if ($this->select_one("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'TDComponente' AND column_name = 'prioridade'"))
            print "\nok"; 
        else
            $this->execute("alter table TDComponente add column prioridade char(1);");
        if ($this->select_one("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'TDComponente' AND column_name = 'referencia'"))
            print "\nok";
        else 
            $this->execute("alter table TDComponente add column referencia char(1);");
        if ($this->select_one("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'TDComponente' AND column_name = 'turno'"))
            print "\nok";
        else
            $this->execute("alter table TDComponente change periodo turno char(1);");
        if ($this->select_one("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'TDAtvECmt' AND column_name = 'referencia'"))
            print "\nok";
        else
            $this->execute("alter table TDAtvECmt add column referencia char(1);");
 
        $this->execute("UPDATE Instituicoes SET versao='449.1', versaoAtual='449.1'");

        printf("\nPatch WebDiario 71: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
