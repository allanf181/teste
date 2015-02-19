<?php

require "inc/config.inc.php";

class Academico50 extends Ruckusing_Migration_Base {

    public function up() {
        // ADD COLUMN ON INSTITUICOES
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'link_recuperar_senha'");
        if (!$result)
            $this->execute("ALTER TABLE  `Instituicoes` ADD  `link_recuperar_senha` VARCHAR( 255 ) NULL AFTER `email_password` ;");

        // ADD COLUMN ON INSTITUICOES
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'ssp'");
        if (!$result)
            $this->execute("ALTER TABLE  `Instituicoes` ADD  `ssp` INT( 11 ) NULL AFTER  `coord` ;");

        //EXCLUINDO REGISTROS EM BRANCO DA TABELA PESSOAS
        $this->execute("DELETE FROM Pessoas WHERE nome IS NULL AND prontuario IS NULL AND senha IS NULL");


        // ADICIONANDO TABELA BOLSAS
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Bolsas'");
        if (!$result) {
            $this->execute("CREATE TABLE IF NOT EXISTS `Bolsas` (
                        `codigo` int(11) NOT NULL AUTO_INCREMENT,
                        `titulo` varchar(100) COLLATE latin1_general_ci NOT NULL,
                        `professor` int(11) NOT NULL,
                        `dataInicio` date NOT NULL,
                        `dataFim` date NOT NULL,
                        `observacao` varchar(500) COLLATE latin1_general_ci DEFAULT NULL,
                        PRIMARY KEY (`codigo`),
                        KEY `professor` (`professor`)
                      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

                      ALTER TABLE `Bolsas`
                        ADD CONSTRAINT `Bolsas_ibfk_1` FOREIGN KEY (`professor`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;
                      ");
        }

        // ADICIONANDO TABELA BOLSASALUNOS
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'BolsasAlunos'");
        if (!$result) {
            $this->execute("CREATE TABLE IF NOT EXISTS `BolsasAlunos` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `bolsa` int(11) NOT NULL,
                            `aluno` int(11) NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `aluno` (`aluno`),
                            KEY `bolsa` (`bolsa`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

                          ALTER TABLE `BolsasAlunos`
                            ADD CONSTRAINT `BolsasAlunos_ibfk_1` FOREIGN KEY (`aluno`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
                            ADD CONSTRAINT `BolsasAlunos_ibfk_2` FOREIGN KEY (`bolsa`) REFERENCES `Bolsas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;
                           ");
        }

        // ADICIONANDO TABELA BOLSASDISCIPLINAS
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'BolsasDisciplinas'");
        if (!$result) {
            $this->execute("CREATE TABLE IF NOT EXISTS `BolsasDisciplinas` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `bolsa` int(11) NOT NULL,
                            `disciplina` int(11) NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `disciplina` (`disciplina`),
                            KEY `bolsa` (`bolsa`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

                          ALTER TABLE `BolsasDisciplinas`
                            ADD CONSTRAINT `BolsasDisciplinas_ibfk_1` FOREIGN KEY (`bolsa`) REFERENCES `Bolsas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
                            ADD CONSTRAINT `BolsasDisciplinas_ibfk_2` FOREIGN KEY (`disciplina`) REFERENCES `Disciplinas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;
                          ");
        }

        // ADICIONANDO TABELA BOLSASRELATORIOS
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'BolsasRelatorios'");
        if (!$result) {
            $this->execute("CREATE TABLE IF NOT EXISTS `BolsasRelatorios` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `bolsa` int(11) NOT NULL,
                            `aluno` int(11) NOT NULL,
                            `data` date NOT NULL,
                            `assunto` varchar(100) COLLATE latin1_general_ci NOT NULL,
                            `descricao` varchar(1000) COLLATE latin1_general_ci NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `aluno` (`aluno`),
                            KEY `bolsa` (`bolsa`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

                          ALTER TABLE `BolsasRelatorios`
                            ADD CONSTRAINT `BolsasRelatorios_ibfk_1` FOREIGN KEY (`bolsa`) REFERENCES `Bolsas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
                            ADD CONSTRAINT `BolsasRelatorios_ibfk_2` FOREIGN KEY (`aluno`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;
                          ");
        }


        // TENTANDO IDENTIFICAR SE ALGUM TIPO PARECIDO COM SÓCIOPEGAGÓGICO EXISTE
        $result = $this->select_all("SELECT codigo FROM Tipos WHERE nome = 'Sociopedagógico'"
                . " OR nome = 'Sociopedagogico' OR nome = 'Socio pedagógico' OR nome = 'Socio pedagogico'"
                . " OR nome = 'SSP' OR nome = 'ssp' OR nome = 'Serviço Sociopedagógico'"
                . " OR nome = 'Servico Sociopedagógico' OR nome = 'Serviço Sociopedagogico'"
                . " OR nome = 'Servico Sociopedagogico' OR nome = 'Serviço Socio pedagógico'"
                . " OR nome = 'Servico Socio pedagogico' OR nome = 'Serviço Socio pedagogico'"
                . " OR nome = 'Servico Socio pedagogico' ");
        if ($result) {
            $this->execute("UPDATE Instituicoes SET ssp=" . $result[0]['codigo']);
            $this->execute("UPDATE `academico`.`Permissoes` SET `permissao` = 'view/secretaria/relatorios/atendimento.php,view/secretaria/relatorios/ausencias.php,view/secretaria/relatorios/boletim.php,view/secretaria/relatorios/boletimTurma.php,view/secretaria/relatorios/frequencias.php,view/secretaria/relatorios/listagem.php,view/secretaria/bolsas/bolsa.php,view/secretaria/bolsas/bolsaAluno.php,view/secretaria/bolsas/bolsaDisciplina.php,view/secretaria/bolsas/bolsaRelatorio.php,view/secretaria/calendario.php,view/secretaria/socioEconomico.php,view/admin/usoSistema.php', `nome` = 'Atendimento ao Aluno,Aus&ecirc;ncias,Boletim do Aluno,Boletim Turma,Frequ&ecirc;ncias,Relat&oacute;rios (em PDF),Bolsas,Alunos,Disciplinas,Relat&oacute;rios,Calend&aacute;rio,Socioecon&ocirc;mico,Uso do Sistema', `menu` = 'view/secretaria/relatorios/atendimento.php,view/secretaria/relatorios/ausencias.php,view/secretaria/relatorios/boletim.php,view/secretaria/relatorios/boletimTurma.php,view/secretaria/relatorios/frequencias.php,view/secretaria/relatorios/listagem.php,view/secretaria/bolsas/bolsa.php,view/secretaria/bolsas/bolsaAluno.php,view/secretaria/bolsas/bolsaDisciplina.php,view/secretaria/bolsas/bolsaRelatorio.php,view/secretaria/calendario.php,view/secretaria/socioEconomico.php,view/admin/usoSistema.php' WHERE `Permissoes`.`tipo` = ".$result[0]['codigo']);
        } else {
            $codSSP = $this->execute("INSERT INTO Tipos VALUES(NULL, 'Sociopedagógico', '1')");
            $this->execute("INSERT INTO `Permissoes` (`codigo`, `tipo`, `permissao`, `nome`, `menu`) VALUES
                            (NULL, $codSSP, 'view/secretaria/relatorios/atendimento.php,view/secretaria/relatorios/ausencias.php,view/secretaria/relatorios/boletim.php,view/secretaria/relatorios/boletimTurma.php,view/secretaria/relatorios/frequencias.php,view/secretaria/relatorios/listagem.php,view/secretaria/bolsas/bolsa.php,view/secretaria/bolsas/bolsaAluno.php,view/secretaria/bolsas/bolsaDisciplina.php,view/secretaria/bolsas/bolsaRelatorio.php,view/secretaria/calendario.php,view/secretaria/socioEconomico.php,view/admin/usoSistema.php', 'Atendimento ao Aluno,Aus&ecirc;ncias,Boletim do Aluno,Boletim Turma,Frequ&ecirc;ncias,Relat&oacute;rios (em PDF),Bolsas,Alunos,Disciplinas,Relat&oacute;rios,Calend&aacute;rio,Socioecon&ocirc;mico,Uso do Sistema', 'view/secretaria/relatorios/atendimento.php,view/secretaria/relatorios/ausencias.php,view/secretaria/relatorios/boletim.php,view/secretaria/relatorios/boletimTurma.php,view/secretaria/relatorios/frequencias.php,view/secretaria/relatorios/listagem.php,view/secretaria/bolsas/bolsa.php,view/secretaria/bolsas/bolsaAluno.php,view/secretaria/bolsas/bolsaDisciplina.php,view/secretaria/bolsas/bolsaRelatorio.php,view/secretaria/calendario.php,view/secretaria/socioEconomico.php,view/admin/usoSistema.php')
                            ");
        }
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='433', versaoAtual='433'");
        printf("<br>Patch Academico50: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
