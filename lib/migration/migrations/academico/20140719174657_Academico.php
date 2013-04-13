<?php
require "inc/config.inc.php";

class Academico extends Ruckusing_Migration_Base
{
    public function up()
    {
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='385'");
    }//up()

    public function down()
    {
    }//down()
}
