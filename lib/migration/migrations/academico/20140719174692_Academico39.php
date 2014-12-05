<?php

require "inc/config.inc.php";

class Academico39 extends Ruckusing_Migration_Base {

    public function up() {
        // DROP TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'TDFPAAtvECmt'");
        if ($result)
            $this->execute("DROP TABLE `TDFPAAtvECmt`;");

        // DROP TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'TDFPAComponente'");
        if ($result)
            $this->execute("DROP TABLE `TDFPAComponente`;");

        // DROP TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'TDDados' 
                                    AND COLUMN_NAME = 'finalizado'");
        if (!$result)
            $this->execute("DROP TABLE `TDDados`;");
        
        // DROP TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'PrazosDiarios'");
        if ($result)
            $this->execute("DROP TABLE `PrazosDiarios`;");

        // DROP COLUMN ON PlanosEnsino
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'PlanosEnsino' 
                                    AND COLUMN_NAME = 'solicitante'");
        if ($result)
            $this->execute("ALTER TABLE `PlanosEnsino` DROP `solicitante`, DROP `solicitacao`;");

        // CREATE TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Areas'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `Areas` (
                                `codigo` int(11) NOT NULL AUTO_INCREMENT,
                                `nome` varchar(200) NOT NULL,
                                PRIMARY KEY (`codigo`)
                              ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                              INSERT INTO `Areas` (`codigo`, `nome`) VALUES
                              (1, 'Letras'),
                              (2, 'Mecânica'),
                              (3, 'Gestão'),
                              (4, 'Matemática'),
                              (5, 'Informática'),
                              (6, 'Eletrônica '),
                              (7, 'Eletrotécnica'),
                              (8, 'Sociologia'),
                              (9, 'Indústria'),
                              (10, 'Química'),
                              (11, 'História'),
                              (12, 'Turismo, Hospitalidade e Lazer'),
                              (13, 'Edificações'),
                              (14, 'Arquitetura e Urbanismo'),
                              (15, 'Geografia'),
                              (16, 'Administração '),
                              (17, 'Educação'),
                              (18, 'Física'),
                              (19, 'Direito'),
                              (20, 'Elétrica'),
                              (21, 'Libras'),
                              (22, 'Manutenção de Aeronaves'),
                              (23, 'Automação'),
                              (24, 'Biologia');");

        // ADD COLUMN ON Coordenadores
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Coordenadores' 
                                    AND COLUMN_NAME = 'area'");
        if (!$result)
            $this->execute("ALTER TABLE  `Coordenadores` ADD  `area` INT( 11 ) NOT NULL AFTER  `curso` ;");

        // CREATE TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'TDDados'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `TDDados` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `pessoa` int(11) NOT NULL,
                            `ano` varchar(4) NOT NULL,
                            `semestre` varchar(2) NOT NULL,
                            `apelido` varchar(100) DEFAULT NULL,
                            `area` int(11) NOT NULL,
                            `regime` varchar(15) NOT NULL,
                            `duracaoAula` varchar(5) NOT NULL,
                            `dedicarEnsino` tinyint(1) NOT NULL,
                            `subHorario` tinyint(1) NOT NULL,
                            `horario1` varchar(20) DEFAULT NULL,
                            `horario2` varchar(20) DEFAULT NULL,
                            `horario3` varchar(20) DEFAULT NULL,
                            `horario` text,
                            `modelo` varchar(3) NOT NULL,
                            `finalizado` datetime DEFAULT NULL,
                            `valido` datetime DEFAULT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `pessoa` (`pessoa`),
                            KEY `area` (`area`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                          ALTER TABLE `TDDados`
                            ADD CONSTRAINT `TDDados_ibfk_2` FOREIGN KEY (`area`) REFERENCES `Areas` (`codigo`),
                            ADD CONSTRAINT `TDDados_ibfk_1` FOREIGN KEY (`pessoa`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;
                          ");

        // CREATE TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'TDComponente'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `TDComponente` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `TD` int(11) NOT NULL,
                            `sigla` varchar(45) NOT NULL,
                            `nome` varchar(45) NOT NULL,
                            `curso` varchar(145) NOT NULL,
                            `periodo` char(1) NOT NULL,
                            `aulas` int(2) NOT NULL,
                            `modelo` varchar(3) NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `TD` (`TD`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                          ALTER TABLE `TDComponente`
                            ADD CONSTRAINT `TDComponente_ibfk_1` FOREIGN KEY (`TD`) REFERENCES `TDDados` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;
                          ");

        // CREATE TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'TDAtvECmt'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `TDAtvECmt` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `TD` int(11) NOT NULL,
                            `descricao` varchar(200) NOT NULL,
                            `aulas` int(2) NOT NULL,
                            `tipo` varchar(3) NOT NULL,
                            `modelo` varchar(3) NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `TD` (`TD`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                          ALTER TABLE `TDAtvECmt`
                            ADD CONSTRAINT `TDAtvECmt_ibfk_1` FOREIGN KEY (`TD`) REFERENCES `TDDados` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;
                          ");

        // CREATE TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'LogSolicitacoes'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `LogSolicitacoes` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `codigoTabela` int(11) NOT NULL,
                            `nomeTabela` varchar(100) NOT NULL,
                            `solicitante` int(11) NOT NULL,
                            `solicitacao` text NOT NULL,
                            `dataSolicitacao` datetime NOT NULL,
                            `dataConcessao` datetime NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `solicitante` (`solicitante`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                          ALTER TABLE `LogSolicitacoes`
                            ADD CONSTRAINT `LogSolicitacoes_ibfk_1` FOREIGN KEY (`solicitante`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;
                          ");

        // CREATE TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'LogEmails'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `LogEmails` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `para` varchar(255) NOT NULL,
                            `mensagem` text NOT NULL,
                            `assunto` varchar(100) NOT NULL,
                            `data` datetime DEFAULT NULL,
                            PRIMARY KEY (`codigo`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
        
        
        // ADICIONANDO FPA, PIT, RIT PROFESSORES
        // REMOVER FTD PROFESSORES
        // ADICIONAR FPA, PIT, RIT COORDENADORES, GED, SEC e ADMIN
        $result = $this->select_all("SELECT prof, coord, ged, adm, sec FROM Instituicoes");
        $prof = $result[0]['prof'];
        $coord = $result[0]['coord'];
        $ged = $result[0]['ged'];
        $adm = $result[0]['adm'];
        $sec = $result[0]['sec'];

        // ATUALIZACAO DE MENU
        $result = $this->select_all("SELECT codigo,tipo,nome,menu,permissao FROM Permissoes");
        if ($result) {
            $remove_menu = 'view/professor/ftd.php';
            $novo_menu1 = 'view/professor/atribuicao/fpa.php';
            $novo_menu2 = 'view/professor/atribuicao/pit.php';
            $novo_menu3 = 'view/professor/atribuicao/rit.php';

            $novo_menu4 = 'view/secretaria/atribuicao_docente/fpa.php';
            $novo_menu5 = 'view/secretaria/atribuicao_docente/pit.php';
            $novo_menu6 = 'view/secretaria/atribuicao_docente/rit.php';
            $novo_menu7 = 'view/secretaria/cursos/area.php';

            $P = array();

            foreach ($result as $P) {
                $P1 = array();
                $P2 = array();
                $M1 = array();
                $N1 = array();

                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if ($prof == $P['tipo']) {
                    if (!in_array($novo_menu1, $P['permissao'])) {
                        $P['permissao'][] = $novo_menu1;
                        $P['menu'][] = $novo_menu1;
                        $P['nome'][] = 'FPA (Prefer&ecirc;ncia Atividades)';
                    }
                    if (!in_array($novo_menu2, $P['permissao'])) {
                        $P['permissao'][] = $novo_menu2;
                        $P['menu'][] = $novo_menu2;
                        $P['nome'][] = 'PIT (Plano I. Trabalho)';
                    }
                    if (!in_array($novo_menu3, $P['permissao'])) {
                        $P['permissao'][] = $novo_menu3;
                        $P['menu'][] = $novo_menu3;
                        $P['nome'][] = 'RIT (Relat&oacute;rio I. Trabalho)';
                    }                    
                }

                if ($coord == $P['tipo'] || $adm == $P['tipo'] || $ged == $P['tipo'] || $sec == $P['tipo']) {
                    if (!in_array($novo_menu4, $P['permissao'])) {
                        $P['permissao'][] = $novo_menu4;
                        $P['menu'][] = $novo_menu4;
                        $P['nome'][] = 'FPA (Prefer&ecirc;ncia Atividades)';
                    }
                    if (!in_array($novo_menu5, $P['permissao'])) {
                        $P['permissao'][] = $novo_menu5;
                        $P['menu'][] = $novo_menu5;
                        $P['nome'][] = 'PIT (Plano I. Trabalho)';
                    }
                    if (!in_array($novo_menu6, $P['permissao'])) {
                        $P['permissao'][] = $novo_menu6;
                        $P['menu'][] = $novo_menu6;
                        $P['nome'][] = 'RIT (Relat&oacute;rio I. Trabalho)';
                    }
                    if (!in_array($novo_menu7, $P['permissao'])) {
                        $P['permissao'][] = $novo_menu7;
                        $P['menu'][] = $novo_menu7;
                        $P['nome'][] = '&Aacute;reas';
                    }
                }
                
                $i = 0;
                foreach ($P['permissao'] as $menu) {
                    // REMOVENDO MENU
                    if ($menu != $remove_menu) {
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

        // REMOVENDO AVALIACOES BIMESTRAIS M
        $result = $this->select_all("SELECT n.codigo FROM NotasFinais n, Atribuicoes a WHERE a.codigo = n.atribuicao AND a.bimestre <> 0 AND n.bimestre = 'M';");
        if ($result) {
            foreach ($result as $c) {
                $this->execute("DELETE FROM NotasFinais WHERE codigo = ".$c['codigo']);
            }
        }
        
        //ATUALIZANDO DADOS NA TABELA NOTASFINAIS
        $this->execute("UPDATE NotasFinais SET bimestre = 1 WHERE bimestre = 0");
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='422', versaoAtual='422'");
        printf("<br>Patch Academico39: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
