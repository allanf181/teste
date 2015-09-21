<?php
require "inc/config.inc.php";

class Academico5 extends Ruckusing_Migration_Base
{
    public function up()
    {
        // ALTER TABLE
        $this->execute("ALTER TABLE  `SituacoesTrabalho` CHANGE  `nome`  `nome` VARCHAR( 40 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");

        // ALTER TABLE
        $this->execute("ALTER TABLE  `EstadosCivis` CHANGE  `nome`  `nome` VARCHAR( 15 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");

        // INSERT
        $this->execute("INSERT INTO EstadosCivis (codigo, nome) VALUES (NULL , 'União Estável')");
        $this->execute("INSERT INTO SituacoesTrabalho (codigo, nome) VALUES (NULL ,'Trabalhando'), "
                . "(NULL ,  'Aposentado ou com benefício INSS'), "
                . "(NULL ,  'Não trabalha por opção')");
       
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='387', versaoAtual='387'");
        printf("<br>Patch Academico5: OK");
    }//up()

    public function down()
    {
    }//down()
}
