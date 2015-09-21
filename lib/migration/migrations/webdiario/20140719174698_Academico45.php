<?php

require "inc/config.inc.php";

class Academico45 extends Ruckusing_Migration_Base {

    public function up() {
        //EXCLUINDO REGISTROS EM BRANCO DA TABELA PESSOAS
        $this->execute("DELETE FROM Pessoas WHERE nome IS NULL AND prontuario IS NULL AND senha IS NULL");

        // CREATE TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'MatriculasAlteracoes'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `MatriculasAlteracoes` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `matricula` int(11) NOT NULL,
                            `situacao` int(11) NOT NULL,
                            `data` date NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `matricula` (`matricula`,`situacao`),
                            KEY `situacao` (`situacao`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                          ALTER TABLE `MatriculasAlteracoes`
                            ADD CONSTRAINT `MatriculasAlteracoes_ibfk_2` FOREIGN KEY (`situacao`) REFERENCES `Situacoes` (`codigo`),
                            ADD CONSTRAINT `MatriculasAlteracoes_ibfk_1` FOREIGN KEY (`matricula`) REFERENCES `Matriculas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;");

        // POPULANDO A TABELA NOVA PARA SUPORTA AS NOVAS FUNCIONALIDADES
        $result = $this->select_all("SELECT codigo,situacao,dataAlteracao FROM Matriculas");
        if ($result) {
            foreach ($result as $reg) {
                $this->execute("INSERT INTO MatriculasAlteracoes VALUES (NULL, " . $reg['codigo'] . ", " . $reg['situacao'] . ", '" . $reg['dataAlteracao'] . "')");
            }
        }

        // ALTERANDO AS TABELAS DEPOIS DE POPULAR
        $resultM = $this->select_all("SELECT COUNT(*) as total FROM Matriculas");
        $resultMA = $this->select_all("SELECT COUNT(*) as total FROM MatriculasAlteracoes");
        if ($resultM[0]['total'] == $resultMA[0]['total']) {
            // DROP COLUMN ON INSTITUICOES
            $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Matriculas' 
                                    AND COLUMN_NAME = 'situacao'");
            if ($result) {
                $this->execute("ALTER TABLE  `Matriculas` DROP FOREIGN KEY  `Matriculas_ibfk_3` ;");
                $this->execute("ALTER TABLE `Matriculas`
                                DROP `situacao`,
                                DROP `data`,
                                DROP `dataAlteracao`;");
            }
        }

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='428', versaoAtual='428'");
        printf("<br>Patch Academico45: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
