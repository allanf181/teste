<?php

class WebDiario72 extends Ruckusing_Migration_Base
{
    public function up()
    {
        
        $this->execute("UPDATE Instituicoes SET versao='449.4', versaoAtual='449.4'");

        printf("\nPatch WebDiario 72: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
