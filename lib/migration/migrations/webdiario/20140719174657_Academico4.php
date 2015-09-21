<?php
require "inc/config.inc.php";

class Academico4 extends Ruckusing_Migration_Base
{
    public function up()
    {
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='385'");
        printf("<br>Patch Academico4: OK");        
    }//up()

    public function down()
    {
    }//down()
}
