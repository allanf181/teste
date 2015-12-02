<?php

class WebDiario66 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("alter table Instituicoes add column mensagem text;");

        printf("\nPatch WebDiario 66: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
