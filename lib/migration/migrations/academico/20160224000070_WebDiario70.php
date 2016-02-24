<?php

class WebDiario70 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE Instituicoes SET versao='449', versaoAtual='449'");

        printf("\nPatch WebDiario 70: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
