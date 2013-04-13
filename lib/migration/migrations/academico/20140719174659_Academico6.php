<?php
require "inc/config.inc.php";

class Academico6 extends Ruckusing_Migration_Base
{
    public function up()
    {
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='388', versaoAtual='388'");
        printf("<br>Patch Academico6: OK");
    }//up()

    public function down()
    {
    }//down()
}
