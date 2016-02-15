<?php

class WebDiario68 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute(" alter table PlanosEnsino add column instrumentos text;");
        $this->execute("UPDATE Instituicoes SET versao='448', versaoAtual='448'");

        printf("\nPatch WebDiario 68: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
