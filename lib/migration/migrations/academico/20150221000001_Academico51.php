<?php

include "inc/config.inc.php";

class Academico51 extends Ruckusing_Migration_Base {

    public function up() {
        //ARRUMANDO VALORES NA TABELA AVISOS
        $this->execute("UPDATE `Avisos` SET atribuicao = NULL WHERE atribuicao = 0;
                        UPDATE `Avisos` SET curso = NULL WHERE curso = 0;
                        UPDATE `Avisos` SET turma = NULL WHERE turma = 0;
                        UPDATE `Avisos` SET destinatario = NULL WHERE destinatario = 0;");

        // CRIAR TIPO PARA AVISOS
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Avisos' 
                                    AND COLUMN_NAME = 'tipo'");
        if (!$result) {
            $this->execute("ALTER TABLE  `Avisos` ADD  `tipo` INT( 11 ) NULL AFTER  `curso` ;
                            ALTER TABLE  `Avisos` ADD INDEX (  `tipo` ) ;
                            ALTER TABLE  `Avisos` ADD INDEX (  `curso` ) ;
                            ALTER TABLE  `Avisos` ADD INDEX (  `destinatario` ) ;
                            ALTER TABLE  `Avisos` ADD INDEX (  `turma` ) ;

                            ALTER TABLE  `Avisos` ADD FOREIGN KEY (  `atribuicao` ) REFERENCES  `academico`.`Atribuicoes` (
                            `codigo`
                            ) ON DELETE CASCADE ON UPDATE CASCADE ;

                            ALTER TABLE  `Avisos` ADD FOREIGN KEY (  `turma` ) REFERENCES  `academico`.`Turmas` (
                            `codigo`
                            ) ON DELETE CASCADE ON UPDATE CASCADE ;

                            ALTER TABLE  `Avisos` ADD FOREIGN KEY (  `curso` ) REFERENCES  `academico`.`Cursos` (
                            `codigo`
                            ) ON DELETE CASCADE ON UPDATE CASCADE ;

                            ALTER TABLE  `Avisos` ADD FOREIGN KEY (  `tipo` ) REFERENCES  `academico`.`Tipos` (
                            `codigo`
                            ) ON DELETE CASCADE ON UPDATE CASCADE ;

                            ALTER TABLE  `Avisos` ADD FOREIGN KEY (  `destinatario` ) REFERENCES  `academico`.`Pessoas` (
                            `codigo`
                            ) ON DELETE CASCADE ON UPDATE CASCADE ;
                            ");
        }

        // CREATE TABLE QUESTIONARIOS - SE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Questionarios'");
        if (!$result) {
            $this->execute("
                        CREATE TABLE IF NOT EXISTS `Questionarios` (
                          `codigo` int(11) NOT NULL AUTO_INCREMENT,
                          `nome` varchar(45) NOT NULL,
                          `descricao` varchar(145) DEFAULT NULL,
                          `dataCriacao` datetime NOT NULL,
                          `dataFechamento` datetime DEFAULT NULL,
                          `valorTotal` float DEFAULT NULL,
                          `situacao` tinyint(4) NOT NULL DEFAULT '0',
                          `criador` int(11) NOT NULL DEFAULT '0',
                          PRIMARY KEY (`codigo`),
                          KEY `criador` (`criador`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


                        CREATE TABLE IF NOT EXISTS `QuestionariosCategorias` (
                          `codigo` int(11) NOT NULL AUTO_INCREMENT,
                          `nome` varchar(45) NOT NULL,
                          `descricao` varchar(145) DEFAULT NULL,
                          PRIMARY KEY (`codigo`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


                        CREATE TABLE IF NOT EXISTS `QuestionariosPessoas` (
                          `codigo` int(11) NOT NULL AUTO_INCREMENT,
                          `finalizado` datetime DEFAULT NULL,
                          `questionario` int(11) NOT NULL,
                          `destinatario` int(11) DEFAULT NULL,
                          `atribuicao` int(11) DEFAULT NULL,
                          `tipo` int(11) DEFAULT NULL,
                          `turma` int(11) DEFAULT NULL,
                          `curso` int(11) DEFAULT NULL,
                          PRIMARY KEY (`codigo`),
                          KEY `fk_QuestionarioPessoas_Questionario1_idx` (`questionario`),
                          KEY `destinatario` (`destinatario`),
                          KEY `destinatario_2` (`destinatario`),
                          KEY `atribuicao` (`atribuicao`),
                          KEY `tipo` (`tipo`),
                          KEY `turma` (`turma`),
                          KEY `curso` (`curso`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


                        CREATE TABLE IF NOT EXISTS `QuestionariosQuestoes` (
                          `codigo` int(11) NOT NULL AUTO_INCREMENT,
                          `nome` varchar(145) NOT NULL,
                          `obrigatorio` char(1) DEFAULT NULL,
                          `questionario` int(11) NOT NULL,
                          `categoria` int(11) NOT NULL,
                          PRIMARY KEY (`codigo`),
                          KEY `fk_QuestionarioQuestoes_Questionario_idx` (`questionario`),
                          KEY `fk_QuestionarioQuestoes_QuestionarioCategorias1_idx` (`categoria`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


                        CREATE TABLE IF NOT EXISTS `QuestionariosQuestoesItens` (
                          `codigo` int(11) NOT NULL AUTO_INCREMENT,
                          `nome` varchar(145) DEFAULT NULL,
                          `valor` float DEFAULT NULL,
                          `questao` int(11) NOT NULL,
                          PRIMARY KEY (`codigo`),
                          KEY `fk_QuestionarioQuestoesItens_QuestionarioQuestoes1_idx` (`questao`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


                        CREATE TABLE IF NOT EXISTS `QuestionariosRespostas` (
                          `codigo` int(11) NOT NULL AUTO_INCREMENT,
                          `resposta` varchar(200) DEFAULT NULL,
                          `pessoa` int(11) NOT NULL,
                          `questao` int(11) NOT NULL,
                          PRIMARY KEY (`codigo`),
                          UNIQUE KEY `pessoa_2` (`pessoa`,`questao`),
                          KEY `fk_QuestionarioRespostas_QuestionarioQuestoes1_idx` (`questao`),
                          KEY `pessoa` (`pessoa`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                        ALTER TABLE `Questionarios`
                          ADD CONSTRAINT `Questionarios_ibfk_1` FOREIGN KEY (`criador`) REFERENCES `Pessoas` (`codigo`);

                        ALTER TABLE `QuestionariosPessoas`
                          ADD CONSTRAINT `QuestionariosPessoas_ibfk_5` FOREIGN KEY (`curso`) REFERENCES `Cursos` (`codigo`) ON DELETE SET NULL ON UPDATE NO ACTION,
                          ADD CONSTRAINT `fk_QuestionarioPessoas_Questionario1` FOREIGN KEY (`questionario`) REFERENCES `Questionarios` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
                          ADD CONSTRAINT `QuestionariosPessoas_ibfk_1` FOREIGN KEY (`destinatario`) REFERENCES `Pessoas` (`codigo`) ON DELETE SET NULL ON UPDATE NO ACTION,
                          ADD CONSTRAINT `QuestionariosPessoas_ibfk_2` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE SET NULL ON UPDATE NO ACTION,
                          ADD CONSTRAINT `QuestionariosPessoas_ibfk_3` FOREIGN KEY (`tipo`) REFERENCES `Tipos` (`codigo`) ON DELETE SET NULL ON UPDATE NO ACTION,
                          ADD CONSTRAINT `QuestionariosPessoas_ibfk_4` FOREIGN KEY (`turma`) REFERENCES `Turmas` (`codigo`) ON DELETE SET NULL ON UPDATE NO ACTION;

                        ALTER TABLE `QuestionariosQuestoes`
                          ADD CONSTRAINT `fk_QuestionarioQuestoes_Questionario` FOREIGN KEY (`questionario`) REFERENCES `Questionarios` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
                          ADD CONSTRAINT `fk_QuestionarioQuestoes_QuestionarioCategorias1` FOREIGN KEY (`categoria`) REFERENCES `QuestionariosCategorias` (`codigo`) ON DELETE NO ACTION ON UPDATE NO ACTION;

                        ALTER TABLE `QuestionariosQuestoesItens`
                          ADD CONSTRAINT `fk_QuestionarioQuestoesItens_QuestionarioQuestoes1` FOREIGN KEY (`questao`) REFERENCES `QuestionariosQuestoes` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

                        ALTER TABLE `QuestionariosRespostas`
                          ADD CONSTRAINT `QuestionariosRespostas_ibfk_1` FOREIGN KEY (`pessoa`) REFERENCES `Pessoas` (`codigo`),
                          ADD CONSTRAINT `fk_QuestionarioRespostas_QuestionarioQuestoes1` FOREIGN KEY (`questao`) REFERENCES `QuestionariosQuestoes` (`codigo`) ON DELETE NO ACTION ON UPDATE NO ACTION;
                          
                        INSERT INTO `QuestionariosCategorias` (`codigo`, `nome`, `descricao`) VALUES
                        (1, 'Escolha em uma lista', 'Permite selecionar o item em uma lista de itens'),
                        (2, 'Múltipla Escolha', 'Permite selecionar um item entre várias opções'),
                        (3, 'Múltiplas respostas', 'Permite selecionar varios itens para uma questão'),
                        (4, 'Texto', 'Caixa de texto de uma linha'),
                        (5, 'Parágrafo', 'Caixa de texto com várias linhas'),
                        (6, 'Data', 'Caixa de texto específica para digitar e selecionar datas');
                    ");
        }

        // CRIAR CAMPO PROVISORIO PARA SEXO
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Pessoas' 
                                    AND COLUMN_NAME = 'sexo2'");
        if (!$result) {
            $this->execute("ALTER TABLE  `Pessoas` ADD  `sexo2` CHAR( 1 ) NULL AFTER  `sexo` ;");
        }

        // CORREÇÃO DOS COLLATIONS DO BANCO
        if (!$result) {
            $this->execute("
                        ALTER TABLE  `Bolsas` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
                        ALTER TABLE  `Bolsas` CHANGE  `titulo`  `titulo` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
                        CHANGE  `observacao`  `observacao` VARCHAR( 500 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;

                        ALTER TABLE  `BolsasAlunos` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;

                        ALTER TABLE  `BolsasDisciplinas` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;

                        ALTER TABLE  `BolsasRelatorios` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
                        ALTER TABLE  `BolsasRelatorios` CHANGE  `assunto`  `assunto` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
                        CHANGE  `descricao`  `descricao` VARCHAR( 1000 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;

                        ALTER TABLE  `Chat` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
                        ALTER TABLE  `Chat` CHANGE  `origem`  `origem` VARCHAR( 45 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ,
                        CHANGE  `prontuario`  `prontuario` VARCHAR( 45 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
                        CHANGE  `para`  `para` VARCHAR( 45 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
                        CHANGE  `mensagem`  `mensagem` VARCHAR( 1000 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
                        CHANGE  `visualizado`  `visualizado` CHAR( 1 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;

                        ALTER TABLE  `Pessoas` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
                        ALTER TABLE `Pessoas` CHANGE `nome` `nome` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `prontuario` `prontuario` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `senha` `senha` VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `cpf` `cpf` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `rg` `rg` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `endereco` `endereco` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `bairro` `bairro` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `cep` `cep` VARCHAR(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `telefone` `telefone` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `celular` `celular` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `email` `email` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `observacoes` `observacoes` MEDIUMTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `empresaTrabalha` `empresaTrabalha` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `cargoEmpresa` `cargoEmpresa` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `transporteGratuito` `transporteGratuito` CHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `necessidadesEspeciais` `necessidadesEspeciais` CHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `descricaoNecessidadesEspeciais` `descricaoNecessidadesEspeciais` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `recuperaSenha` `recuperaSenha` VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `ano1g` `ano1g` CHAR(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `escola1g` `escola1g` VARCHAR(145) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `escolaPublica` `escolaPublica` CHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `lattes` `lattes` VARCHAR(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `bloqueioFoto` `bloqueioFoto` CHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
                        
                        ALTER TABLE  `Instituicoes` CHANGE  `ged`  `ged` INT( 11 ) NULL DEFAULT NULL ,
                        CHANGE  `adm`  `adm` INT( 11 ) NULL DEFAULT NULL ,
                        CHANGE  `sec`  `sec` INT( 11 ) NULL DEFAULT NULL ;

                        ALTER TABLE  `Instituicoes` ADD INDEX (  `ged` ) ;
                        ALTER TABLE  `Instituicoes` ADD INDEX (  `adm` ) ;
                        ALTER TABLE  `Instituicoes` ADD INDEX (  `sec` ) ;
                        ALTER TABLE  `Instituicoes` ADD INDEX (  `prof` ) ;
                        ALTER TABLE  `Instituicoes` ADD INDEX (  `aluno` ) ;
                        ALTER TABLE  `Instituicoes` ADD INDEX (  `coord` ) ;
                        ALTER TABLE  `Instituicoes` ADD INDEX (  `ssp` ) ;

                        ALTER TABLE  `Instituicoes` ADD FOREIGN KEY (  `ged` ) REFERENCES `academico`.`Tipos` (
                        `codigo`
                        ) ON DELETE SET NULL ON UPDATE NO ACTION ;

                        ALTER TABLE  `Instituicoes` ADD FOREIGN KEY (  `adm` ) REFERENCES `academico`.`Tipos` (
                        `codigo`
                        ) ON DELETE SET NULL ON UPDATE NO ACTION ;

                        ALTER TABLE  `Instituicoes` ADD FOREIGN KEY (  `sec` ) REFERENCES `academico`.`Tipos` (
                        `codigo`
                        ) ON DELETE SET NULL ON UPDATE NO ACTION ;

                        ALTER TABLE  `Instituicoes` ADD FOREIGN KEY (  `prof` ) REFERENCES `academico`.`Tipos` (
                        `codigo`
                        ) ON DELETE SET NULL ON UPDATE NO ACTION ;

                        ALTER TABLE  `Instituicoes` ADD FOREIGN KEY (  `aluno` ) REFERENCES `academico`.`Tipos` (
                        `codigo`
                        ) ON DELETE SET NULL ON UPDATE NO ACTION ;

                        ALTER TABLE  `Instituicoes` ADD FOREIGN KEY (  `coord` ) REFERENCES `academico`.`Tipos` (
                        `codigo`
                        ) ON DELETE SET NULL ON UPDATE NO ACTION ;

                        ALTER TABLE  `Instituicoes` ADD FOREIGN KEY (  `ssp` ) REFERENCES `academico`.`Tipos` (
                        `codigo`
                        ) ON DELETE SET NULL ON UPDATE NO ACTION ;


                        ALTER TABLE  `Chat` ADD INDEX (  `prontuario` ) ;
                        ALTER TABLE  `Chat` ADD INDEX (  `para` ) ;
                        ALTER TABLE  `Chat` ADD FOREIGN KEY (  `prontuario` ) REFERENCES `academico`.`Pessoas` (
                        `prontuario`
                        ) ON DELETE CASCADE ON UPDATE CASCADE ;

                        ALTER TABLE  `Chat` ADD FOREIGN KEY (  `para` ) REFERENCES  `academico`.`Pessoas` (
                        `prontuario`
                        ) ON DELETE CASCADE ON UPDATE CASCADE ;


                        ALTER TABLE  `Coordenadores` ADD INDEX (  `area` ) ;
                        ALTER TABLE  `Coordenadores` CHANGE  `area`  `area` INT( 11 ) NULL DEFAULT NULL ;
                        UPDATE `Coordenadores` SET area = NULL WHERE area = 0;
                        ALTER TABLE  `Coordenadores` ADD FOREIGN KEY (  `area` ) REFERENCES `academico`.`Areas` (
                        `codigo`
                        ) ON DELETE RESTRICT ON UPDATE RESTRICT ;


                        ALTER TABLE  `Calendarios` ENGINE = INNODB;
                        ALTER TABLE  `Calendarios` CHANGE  `curso`  `curso` INT( 11 ) NULL DEFAULT NULL ;
                        ALTER TABLE  `Calendarios` CHANGE  `tipo`  `tipo` INT( 11 ) NULL DEFAULT NULL ;
                        ALTER TABLE  `Calendarios` ADD INDEX (  `curso` ) ;
                        ALTER TABLE  `Calendarios` ADD INDEX (  `tipo` ) ;
                        UPDATE Calendarios SET curso = NULL WHERE curso = 0;
                        UPDATE Calendarios SET tipo = NULL WHERE tipo = 0;
                        ALTER TABLE  `Calendarios` ADD FOREIGN KEY (  `curso` ) REFERENCES `academico`.`Cursos` (
                        `codigo`
                        ) ON DELETE SET NULL ON UPDATE NO ACTION ;

                        ALTER TABLE  `Calendarios` ADD FOREIGN KEY (  `tipo` ) REFERENCES `academico`.`Tipos` (
                        `codigo`
                        ) ON DELETE SET NULL ON UPDATE NO ACTION ;

                        UPDATE FrequenciasAbonos SET atribuicao = NULL WHERE atribuicao = 0;
                        ALTER TABLE  `FrequenciasAbonos` ADD INDEX (  `atribuicao` ) ;
                        ALTER TABLE  `FrequenciasAbonos` ADD FOREIGN KEY (  `atribuicao` ) REFERENCES `academico`.`Atribuicoes` (
                        `codigo`
                        ) ON DELETE SET NULL ON UPDATE NO ACTION ;
                        ");
        }

        // REMOVENDO MENUS MIGRACAO E SOCIOECONOMICO.
        $result = $this->select_all("SELECT codigo,tipo,nome,menu,permissao FROM Permissoes");
        if ($result) {
            $rmenu1 = 'view/secretaria/socioEconomico.php';
            $rmenu2 = 'view/admin/migracao.php';
            $bolsa = 'view/secretaria/bolsas/bolsa.php';
            $bolsaAluno = 'view/secretaria/bolsas/bolsaAluno.php';
            $bolsaDisciplina = 'view/secretaria/bolsas/bolsaDisciplina.php';
            $bolsaRelatorio = 'view/secretaria/bolsas/bolsaRelatorio.php';

            foreach ($result as $P) {
                $P1 = array();

                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                $i = 0;
                foreach ($P['permissao'] as $menu) {
                    // REMOVENDO MENU
                    if ($menu != $rmenu1 && $menu != $rmenu2 && $menu != $bolsa && $menu != $bolsaAluno && $menu != $bolsaDisciplina && $menu != $bolsaRelatorio) {
                        $P1['permissao'][] = $menu;
                        $P1['menu'][] = $P['menu'][$i];
                        $P1['nome'][] = $P['nome'][$i];
                    }
                    $i++;
                }
                $P2 = implode(",", $P1['permissao']);
                $M1 = implode(",", $P1['menu']);
                $N1 = implode(",", $P1['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P2',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }

        //ADD QUESTIONARIOS PARA ALUNO, PROFESSOR, ADM, SEC E GED, SSP
        $result = $this->select_all("SELECT prof,aluno,adm,sec,ged,ssp FROM Instituicoes");
        $prof = $result[0]['prof'];
        $aluno = $result[0]['aluno'];
        $adm = $result[0]['adm'];
        $sec = $result[0]['sec'];
        $ged = $result[0]['ged'];
        $ssp = $result[0]['ssp'];
        $sspQ = ($ssp) ? ',' . $ssp : '';

        $result = $this->select_all("SELECT codigo,tipo,nome,menu,permissao FROM Permissoes WHERE tipo IN ($aluno,$prof,$adm,$sec,$ged $sspQ)");

        if ($result) {
            $q1 = 'view/secretaria/questionario.php';
            $q2 = 'view/aluno/questionario.php';
            $q3 = 'view/aluno/bolsa.php';
            $q4 = 'view/secretaria/bolsa.php';
            $q5 = 'view/professor/questionario.php';
            $q6 = 'view/professor/bolsa.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if ($P['tipo'] == $aluno) {
                    if (!in_array($q2, $P['permissao'])) {
                        $P['permissao'][] = $q2;
                        $P['menu'][] = $q2;
                        $P['nome'][] = 'Questionários';
                    }
                    if (!in_array($q3, $P['permissao'])) {
                        $P['permissao'][] = $q3;
                        $P['menu'][] = $q3;
                        $P['nome'][] = 'Bolsas';
                    }
                }

                if ($P['tipo'] == $prof) {
                    if (!in_array($q1, $P['permissao'])) {
                        $P['permissao'][] = $q5;
                        $P['menu'][] = $q5;
                        $P['nome'][] = 'Questionários';
                    }
                    if (!in_array($q6, $P['permissao'])) {
                        $P['permissao'][] = $q6;
                        $P['menu'][] = $q6;
                        $P['nome'][] = 'Bolsas';
                    }
                }

                if ($P['tipo'] == $adm || $P['tipo'] == $sec || $P['tipo'] == $ged || $P['tipo'] == $ssp) {
                    if (!in_array($q1, $P['permissao'])) {
                        $P['permissao'][] = $q1;
                        $P['menu'][] = $q1;
                        $P['nome'][] = 'Questionários';
                    }
                    if (!in_array($q4, $P['permissao'])) {
                        $P['permissao'][] = $q4;
                        $P['menu'][] = $q4;
                        $P['nome'][] = 'Bolsas';
                    }
                }

                $P1 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }


        // COPIANDO DADOS PARA AS NOVAS TABELAS DO QUESTIONARIO SOCIOECONOMICO
        $tabelas[0]['tabelaVirtual'] = 'EstadosCivis';
        $tabelas[0]['pergunta'] = 'Estado Civil';
        $tabelas[1]['tabelaVirtual'] = 'MeiosTransporte';
        $tabelas[1]['pergunta'] = 'Meios de Transporte';
        $tabelas[2]['tabelaVirtual'] = 'Racas';
        $tabelas[2]['pergunta'] = 'Cor';
        $tabelas[3]['tabelaVirtual'] = 'Rendas';
        $tabelas[3]['pergunta'] = 'Qual a renda mensal bruta de sua família (soma dos rendimentos de todos que residem com você, incluindo você)';
        $tabelas[4]['tabelaVirtual'] = 'SituacoesTrabalho';
        $tabelas[4]['pergunta'] = 'Qual sua situação profissional atual';
        $tabelas[5]['tabelaVirtual'] = 'TemposPesquisa';
        $tabelas[5]['pergunta'] = 'Tempo de trabalho';
        $tabelas[6]['tabelaVirtual'] = 'TiposTrabalho';
        $tabelas[6]['pergunta'] = 'Se trabalha, em qual característica se enquadra';

        if ($codigo = $this->execute("INSERT INTO Questionarios VALUES(NULL, 'Socioeconômico', '', NOW(), NULL, '0', 1, 1)")) {
            $this->execute("INSERT INTO QuestionariosPessoas VALUES(NULL, NULL, $codigo, NULL, NULL, $aluno, NULL, NULL)");
        }

        //TABELAS DE SELECT
        foreach ($tabelas as $t) {
            $result = $this->select_all("SELECT codigo,nome FROM " . $t['tabelaVirtual'] . " GROUP BY nome");
            $questao = $this->execute("INSERT INTO QuestionariosQuestoes VALUES(NULL, '" . $t['pergunta'] . "', '1', '$codigo', 1 )");

            foreach ($result as $r) {
                $this->execute("INSERT INTO QuestionariosQuestoesItens VALUES(NULL, '" . $r['nome'] . "', '', $questao )");
            }
        }

        // TABELAS DE INPUT
        $tabelas = array();
        $tabelas[0]['tabelaVirtual'] = 'empresaTrabalha';
        $tabelas[0]['pergunta'] = 'Empresa';
        $tabelas[1]['tabelaVirtual'] = 'cargoEmpresa';
        $tabelas[1]['pergunta'] = 'Cargo na Empresa';
        $tabelas[2]['tabelaVirtual'] = 'numeroPessoasNaResidencia';
        $tabelas[2]['pergunta'] = 'Quantas pessoas residem com você, incluindo você?';
        $tabelas[3]['tabelaVirtual'] = 'descricaoNecessidadesEspeciais';
        $tabelas[3]['pergunta'] = 'Se possui, descreva suas necessidades especiais';

        foreach ($tabelas as $t) {
            $this->execute("INSERT INTO QuestionariosQuestoes VALUES(NULL, '" . $t['pergunta'] . "', '', '$codigo', 4 )");
        }

        // TABELAS DE CHOICE - SEM PADRÃO
        $tabelas = array();
        $tabelas[0]['tabelaVirtual'] = 'transporteGratuito';
        $tabelas[0]['pergunta'] = 'Transporte Gratuito';
        $tabelas[1]['tabelaVirtual'] = 'necessidadesEspeciais';
        $tabelas[1]['pergunta'] = 'Possui necessidades especiais';
        $tabelas[2]['tabelaVirtual'] = 'escolaPublica';
        $tabelas[2]['pergunta'] = 'Estudou em escola pública';

        $resp[]['nome'] = 'Não';
        $resp[]['nome'] = 'Sim';

        foreach ($tabelas as $t) {
            $questao = $this->execute("INSERT INTO QuestionariosQuestoes VALUES(NULL, '" . $t['pergunta'] . "', '1', '$codigo', 1 )");

            foreach ($resp as $r) {
                $this->execute("INSERT INTO QuestionariosQuestoesItens VALUES(NULL, '" . $r['nome'] . "', '', $questao )");
            }
        }

        // RECUPERANDO OS DADOS DE PESSOAS E POPULANDO AS NOVAS TABELAS
        $tabelas = array();
        $tabelas[0]['tabela'] = 'EstadosCivis';
        $tabelas[0]['pergunta'] = 'Estado Civil';
        $tabelas[0]['coluna'] = 'estadoCivil';
        $tabelas[1]['tabela'] = 'MeiosTransporte';
        $tabelas[1]['pergunta'] = 'Meios de Transporte';
        $tabelas[1]['coluna'] = 'meioTransporte';
        $tabelas[2]['tabela'] = 'Racas';
        $tabelas[2]['coluna'] = 'raca';
        $tabelas[2]['pergunta'] = 'Cor';
        $tabelas[3]['tabela'] = 'Rendas';
        $tabelas[3]['coluna'] = 'renda';
        $tabelas[3]['pergunta'] = 'Qual a renda mensal bruta de sua família (soma dos rendimentos de todos que residem com você, incluindo você)';
        $tabelas[4]['tabela'] = 'SituacoesTrabalho';
        $tabelas[4]['coluna'] = 'situacaoTrabalho';
        $tabelas[4]['pergunta'] = 'Qual sua situação profissional atual';
        $tabelas[5]['tabela'] = 'TemposPesquisa';
        $tabelas[5]['coluna'] = 'tempo';
        $tabelas[5]['pergunta'] = 'Tempo de trabalho';
        $tabelas[6]['tabela'] = 'TiposTrabalho';
        $tabelas[6]['coluna'] = 'tipoTrabalho';
        $tabelas[6]['pergunta'] = 'Se trabalha, em qual característica se enquadra';

        $tabelas2 = array();
        $tabelas2[0]['tabela'] = 'transporteGratuito';
        $tabelas2[0]['pergunta'] = 'Transporte Gratuito';
        $tabelas2[1]['tabela'] = 'necessidadesEspeciais';
        $tabelas2[1]['pergunta'] = 'Possui necessidades especiais';
        $tabelas2[2]['tabela'] = 'escolaPublica';
        $tabelas2[2]['pergunta'] = 'Estudou em escola pública';

        $tabelas3 = array();
        $tabelas3[0]['tabela'] = 'empresaTrabalha';
        $tabelas3[0]['pergunta'] = 'Empresa';
        $tabelas3[1]['tabela'] = 'cargoEmpresa';
        $tabelas3[1]['pergunta'] = 'Cargo na Empresa';
        $tabelas3[2]['tabela'] = 'numeroPessoasNaResidencia';
        $tabelas3[2]['pergunta'] = 'Quantas pessoas residem com você, incluindo você?';
        $tabelas3[3]['tabela'] = 'descricaoNecessidadesEspeciais';
        $tabelas3[3]['pergunta'] = 'Se possui, descreva suas necessidades especiais';

        $result = $this->select_all("SELECT codigo,sexo,raca,estadoCivil,numeroPessoasNaResidencia,renda,situacaoTrabalho,tipoTrabalho,empresaTrabalha,cargoEmpresa,tempo,meioTransporte,transporteGratuito,necessidadesEspeciais,descricaoNecessidadesEspeciais,escolaPublica FROM Pessoas");

        function mres($value) {
            $search = array("\\", "\x00", "\n", "\r", "'", '"', "\x1a");
            $replace = array("\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z");

            return str_replace($search, $replace, $value);
        }

        foreach ($result as $c) {
            foreach ($tabelas as $t) {
                $reg_new = null;
                $reg = null;
                if ($c[$t['coluna']] && $c[$t['coluna']] != '0') {
                    $reg = $this->select_all("SELECT nome FROM " . $t['tabela'] . " WHERE codigo = " . $c[$t['coluna']]);
                    $reg_new = $this->select_all("SELECT qq.codigo, qi.nome FROM QuestionariosQuestoes qq, QuestionariosQuestoesItens qi "
                            . "WHERE qq.codigo = qi.questao "
                            . "AND qq.nome = '" . $t['pergunta'] . "' "
                            . "AND qi.nome = '" . $reg[0]['nome'] . "'");
                    $this->execute("INSERT INTO QuestionariosRespostas VALUES(NULL, '" . $reg_new[0]['nome'] . "', '" . $c['codigo'] . "', '" . $reg_new[0]['codigo'] . "' )");
                }
            }

            foreach ($tabelas2 as $t) {
                $reg_new = null;
                if ($c[$t['tabela']] != '' && $c[$t['tabela']] != '0') {
                    $resp = ($c[$t['tabela']] == 'n') ? 'Não' : 'Sim';
                    $reg_new = $this->select_all("SELECT qq.codigo, qi.nome FROM QuestionariosQuestoes qq, QuestionariosQuestoesItens qi "
                            . "WHERE qq.codigo = qi.questao "
                            . "AND qq.nome = '" . $t['pergunta'] . "' "
                            . "AND qi.nome = '$resp'");

                    $this->execute("INSERT INTO QuestionariosRespostas VALUES(NULL, '$resp', '" . $c['codigo'] . "', '" . $reg_new[0]['codigo'] . "' )");
                }
            }

            foreach ($tabelas3 as $t) {
                $reg_new = null;
                if ($c[$t['tabela']] != '' && $c[$t['tabela']] != '0') {
                    $reg_new = $this->select_all("SELECT qq.codigo FROM QuestionariosQuestoes qq "
                            . "WHERE qq.nome = '" . $t['pergunta'] . "'");

                    $this->execute("INSERT INTO QuestionariosRespostas VALUES(NULL, '" . mres($c[$t['tabela']]) . "', '" . $c['codigo'] . "', '" . $reg_new[0]['codigo'] . "' )");
                }
            }

            if ($c['sexo'] != '') {
                $reg = $this->select_all("SELECT SUBSTR(nome, 1, 1) as nome FROM Sexos WHERE codigo = " . $c['sexo']);
                $this->execute("UPDATE Pessoas SET sexo2 = '" . $reg[0]['nome'] . "' WHERE codigo = '" . $c['codigo'] . "'");
            }
        }

        // REMOVENDO SEXO E ALTERANDO SEXO2 para SEXO
        if ($this->execute("ALTER TABLE `Pessoas` DROP `sexo`")) {
            $this->execute("ALTER TABLE  `Pessoas` CHANGE  `sexo2`  `sexo` CHAR( 1 )");
        }

        // REMOVENDO COLUNAS DE PESSOAS
        $this->execute("ALTER TABLE `Pessoas`
                        DROP `raca`,
                        DROP `estadoCivil`,
                        DROP `numeroPessoasNaResidencia`,
                        DROP `renda`,
                        DROP `situacaoTrabalho`,
                        DROP `tipoTrabalho`,
                        DROP `empresaTrabalha`,
                        DROP `cargoEmpresa`,
                        DROP `tempo`,
                        DROP `meioTransporte`,
                        DROP `transporteGratuito`,
                        DROP `necessidadesEspeciais`,
                        DROP `descricaoNecessidadesEspeciais`,
                        DROP `escolaPublica`;");

        // REMOVENDO TABELAS
        $this->execute("DROP TABLE `EstadosCivis`, `MeiosTransporte`, `Racas`, `Rendas`, `Sexos`, `SituacoesTrabalho`, `TemposPesquisa`, `TiposTrabalho`;");

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='434', versaoAtual='434'");
        printf("<br>Patch Academico51: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
