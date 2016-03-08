<?php

class WebDiario72 extends Ruckusing_Migration_Base
{
    public function up()
    {
        
        $this->execute("UPDATE Instituicoes SET versao='449.5', versaoAtual='449.5'");
 
        printf("\nPatch WebDiario 72: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
