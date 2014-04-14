-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: 14/04/2014 às 00h58min
-- Versão do Servidor: 5.5.34
-- Versão do PHP: 5.3.10-1ubuntu3.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Banco de Dados: `academico`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `Atribuicoes`
--

CREATE TABLE IF NOT EXISTS `Atribuicoes` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `disciplina` int(11) NOT NULL,
  `turma` int(11) NOT NULL,
  `bimestre` tinyint(4) DEFAULT NULL,
  `ementa` text,
  `dataInicio` date DEFAULT NULL,
  `dataFim` date DEFAULT NULL,
  `aulaPrevista` int(5) DEFAULT NULL,
  `observacoes` varchar(500) DEFAULT NULL,
  `status` char(1) DEFAULT NULL,
  `grupo` char(1) DEFAULT NULL,
  `prazo` datetime DEFAULT NULL,
  `competencias` varchar(500) DEFAULT NULL,
  `calculo` varchar(10) DEFAULT NULL,
  `formula` varchar(50) DEFAULT NULL,
  `subturma` varchar(4) DEFAULT NULL,
  `eventod` int(11) DEFAULT NULL,
  PRIMARY KEY (`codigo`,`disciplina`,`turma`),
  KEY `fk_Atribuicoes_Disciplinas1` (`disciplina`),
  KEY `fk_Atribuicoes_Turmas1` (`turma`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Atualizacoes`
--

CREATE TABLE IF NOT EXISTS `Atualizacoes` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` int(11) NOT NULL,
  `pessoa` int(11) NOT NULL,
  `data` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`,`tipo`,`pessoa`),
  KEY `fk_Atualizacoes_Pessoas1_idx` (`pessoa`),
  KEY `fk_Atualizacoes_TiposAtualizacoes1` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Aulas`
--

CREATE TABLE IF NOT EXISTS `Aulas` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `quantidade` tinyint(4) NOT NULL,
  `conteudo` varchar(200) DEFAULT NULL,
  `anotacao` varchar(200) DEFAULT NULL,
  `atividade` varchar(200) DEFAULT NULL,
  `atribuicao` int(11) NOT NULL,
  PRIMARY KEY (`codigo`,`atribuicao`),
  KEY `fk_Aulas_Atribuicoes1` (`atribuicao`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Avaliacoes`
--

CREATE TABLE IF NOT EXISTS `Avaliacoes` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `nome` varchar(145) NOT NULL,
  `sigla` varchar(2) DEFAULT NULL,
  `peso` float NOT NULL,
  `atribuicao` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  PRIMARY KEY (`codigo`,`atribuicao`,`tipo`),
  KEY `fk_Avaliacoes_Atribuicoes1` (`atribuicao`),
  KEY `fk_Avaliacoes_2` (`tipo`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Avisos`
--

CREATE TABLE IF NOT EXISTS `Avisos` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `pessoa` int(11) NOT NULL,
  `atribuicao` int(11) DEFAULT NULL,
  `turma` int(11) DEFAULT NULL,
  `curso` int(11) DEFAULT NULL,
  `destinatario` int(11) DEFAULT NULL,
  `data` datetime NOT NULL,
  `conteudo` varchar(500) NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `atribuicao` (`atribuicao`),
  KEY `pessoa` (`pessoa`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Calendarios`
--

CREATE TABLE IF NOT EXISTS `Calendarios` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `diaLetivo` int(1) NOT NULL,
  `ocorrencia` varchar(255) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Cidades`
--

CREATE TABLE IF NOT EXISTS `Cidades` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(145) NOT NULL,
  `estado` int(11) NOT NULL,
  PRIMARY KEY (`codigo`,`estado`),
  KEY `fk_Cidades_Estados1` (`estado`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Coordenadores`
--

CREATE TABLE IF NOT EXISTS `Coordenadores` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `coordenador` int(11) NOT NULL,
  `curso` int(11) NOT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `coordenador_2` (`coordenador`,`curso`),
  KEY `curso` (`curso`),
  KEY `coordenador` (`coordenador`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Cursos`
--

CREATE TABLE IF NOT EXISTS `Cursos` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(145) DEFAULT NULL,
  `modalidade` int(11) NOT NULL,
  `fechamento` char(1) DEFAULT NULL,
  `nomeAlternativo` varchar(145) DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  KEY `modalidade` (`modalidade`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Disciplinas`
--

CREATE TABLE IF NOT EXISTS `Disciplinas` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(45) NOT NULL DEFAULT '',
  `modulo` char(1) DEFAULT NULL,
  `nome` varchar(45) DEFAULT NULL,
  `ch` float DEFAULT NULL,
  `curso` int(11) NOT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `un_curso_numero` (`numero`,`curso`),
  KEY `fk_Disciplinas_Cursos` (`curso`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Ensalamentos`
--

CREATE TABLE IF NOT EXISTS `Ensalamentos` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `atribuicao` int(11) NOT NULL,
  `professor` int(11) NOT NULL,
  `sala` int(11) NOT NULL,
  `diaSemana` int(1) NOT NULL,
  `horario` int(11) NOT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `CHAVE` (`atribuicao`,`sala`,`diaSemana`,`horario`,`professor`),
  KEY `sala` (`sala`),
  KEY `horario` (`horario`),
  KEY `professor` (`professor`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Estados`
--

CREATE TABLE IF NOT EXISTS `Estados` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) DEFAULT NULL,
  `sigla` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `sigla` (`sigla`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `EstadosCivis`
--

CREATE TABLE IF NOT EXISTS `EstadosCivis` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Extraindo dados da tabela `EstadosCivis`
--

INSERT INTO `EstadosCivis` (`codigo`, `nome`) VALUES
(1, 'Solteiro(a)'),
(2, 'Casado(a)'),
(3, 'Separado(a)'),
(4, 'Viúvo(a)'),
(5, 'Outros');

-- --------------------------------------------------------

--
-- Estrutura da tabela `Frequencias`
--

CREATE TABLE IF NOT EXISTS `Frequencias` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `matricula` int(11) NOT NULL,
  `aula` int(11) NOT NULL,
  `quantidade` varchar(10) NOT NULL,
  PRIMARY KEY (`codigo`,`matricula`,`aula`),
  KEY `fk_Frequencias_Matriculas1` (`matricula`),
  KEY `fk_Frequencias_Aulas1` (`aula`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `FrequenciasAbonos`
--

CREATE TABLE IF NOT EXISTS `FrequenciasAbonos` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `aluno` int(11) NOT NULL,
  `data` date NOT NULL,
  `aula` varchar(50) DEFAULT NULL,
  `atribuicao` int(11) DEFAULT NULL,
  `motivo` varchar(200) NOT NULL,
  `tipo` char(1) NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `aluno` (`aluno`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `FTDDados`
--

CREATE TABLE IF NOT EXISTS `FTDDados` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `professor` int(11) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `semestre` char(1) NOT NULL,
  `telefone` varchar(45) DEFAULT NULL,
  `celular` varchar(45) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `area` varchar(60) NOT NULL,
  `regime` varchar(45) NOT NULL,
  `observacao` varchar(200) NOT NULL,
  `TP` varchar(200) DEFAULT NULL,
  `TPT` varchar(200) DEFAULT NULL,
  `TD` varchar(200) DEFAULT NULL,
  `TDT` varchar(200) DEFAULT NULL,
  `ITE` varchar(200) DEFAULT NULL,
  `ITS` varchar(200) DEFAULT NULL,
  `A` varchar(200) DEFAULT NULL,
  `AT` varchar(200) DEFAULT NULL,
  `AtvDocente` varchar(10) DEFAULT NULL,
  `Projetos` varchar(10) DEFAULT NULL,
  `Intervalos` varchar(10) DEFAULT NULL,
  `Total` varchar(10) DEFAULT NULL,
  `valido` datetime DEFAULT NULL,
  `solicitante` int(11) DEFAULT NULL,
  `solicitacao` varchar(200) DEFAULT NULL,
  `finalizado` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  KEY `professor` (`professor`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `FTDHorarios`
--

CREATE TABLE IF NOT EXISTS `FTDHorarios` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `ftd` int(11) NOT NULL,
  `registro` varchar(10) NOT NULL,
  `horario` varchar(5) NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `ftd` (`ftd`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Horarios`
--

CREATE TABLE IF NOT EXISTS `Horarios` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `inicio` time NOT NULL,
  `fim` time NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Instituicoes`
--

CREATE TABLE IF NOT EXISTS `Instituicoes` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `cidade` varchar(145) NOT NULL,
  `ged` int(11) NOT NULL,
  `adm` int(11) NOT NULL,
  `sec` int(11) NOT NULL,
  `prof` int(11) DEFAULT NULL,
  `aluno` int(11) DEFAULT NULL,
  `coord` int(11) DEFAULT NULL,
  `diasAlterarSenha` int(3) DEFAULT NULL,
  `limiteAltDiarioProf` int(3) DEFAULT NULL,
  `limiteInsAulaProf` int(3) DEFAULT NULL,
  `ipServidorAtualizacao` varchar(15) DEFAULT NULL,
  `usuarioServidorAtualizacao` varchar(50) DEFAULT NULL,
  `senhaServidorAtualizacao` varchar(50) DEFAULT NULL,
  `bloqueioFoto` varchar(1) DEFAULT NULL,
  `campiDigitaNotas` varchar(5) DEFAULT NULL,
  `versao` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Extraindo dados da tabela `Instituicoes`
--

INSERT INTO `Instituicoes` (`codigo`, `nome`, `cidade`, `ged`, `adm`, `sec`, `prof`, `aluno`, `coord`, `diasAlterarSenha`, `limiteAltDiarioProf`, `limiteInsAulaProf`, `ipServidorAtualizacao`, `usuarioServidorAtualizacao`, `senhaServidorAtualizacao`, `bloqueioFoto`, `campiDigitaNotas`, `versao`) VALUES
(1, '', '', 3, 5, 4, 2, 1, 10, 0, 7, 7, 'arq.ifsp.edu.br', 'academico', 'M3tr@t0n', '', NULL, '379');

-- --------------------------------------------------------

--
-- Estrutura da tabela `Logs`
--

CREATE TABLE IF NOT EXISTS `Logs` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `url` text NOT NULL,
  `data` datetime NOT NULL,
  `origem` varchar(15) DEFAULT NULL,
  `pessoa` int(11) NOT NULL,
  PRIMARY KEY (`codigo`,`pessoa`),
  KEY `fk_Logs_1` (`codigo`),
  KEY `fk_Logs_2` (`pessoa`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Matriculas`
--

CREATE TABLE IF NOT EXISTS `Matriculas` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `aluno` int(11) NOT NULL,
  `atribuicao` int(11) NOT NULL,
  `situacao` int(11) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `dataAlteracao` date DEFAULT NULL,
  PRIMARY KEY (`codigo`,`aluno`,`atribuicao`),
  KEY `fk_Matriculas_Pessoas1` (`aluno`),
  KEY `fk_Matriculas_Situacoes1` (`situacao`),
  KEY `fk_Matriculas_Atribuicoes1` (`atribuicao`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `MeiosTransporte`
--

CREATE TABLE IF NOT EXISTS `MeiosTransporte` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Extraindo dados da tabela `MeiosTransporte`
--

INSERT INTO `MeiosTransporte` (`codigo`, `nome`) VALUES
(1, 'Carro próprio'),
(2, 'Carona'),
(3, 'Bicicleta'),
(4, 'Moto'),
(5, 'Ônibus intermunicipal'),
(6, 'Ônibus local'),
(7, 'A pé');

-- --------------------------------------------------------

--
-- Estrutura da tabela `Modalidades`
--

CREATE TABLE IF NOT EXISTS `Modalidades` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(145) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Notas`
--

CREATE TABLE IF NOT EXISTS `Notas` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `matricula` int(11) NOT NULL,
  `avaliacao` int(11) NOT NULL,
  `nota` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`codigo`,`matricula`,`avaliacao`),
  KEY `fk_Notas_Avaliacoes1` (`avaliacao`),
  KEY `fk_Notas_Matriculas1` (`matricula`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `NotasFinais`
--

CREATE TABLE IF NOT EXISTS `NotasFinais` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `atribuicao` int(11) NOT NULL,
  `matricula` int(11) NOT NULL,
  `bimestre` varchar(1) NOT NULL,
  `mcc` float NOT NULL,
  `rec` float NOT NULL,
  `ncc` float NOT NULL,
  `falta` int(11) NOT NULL,
  `sincronizado` datetime DEFAULT NULL,
  `retorno` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  KEY `matricula` (`matricula`),
  KEY `atribuicao` (`atribuicao`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Permissoes`
--

CREATE TABLE IF NOT EXISTS `Permissoes` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` int(11) NOT NULL,
  `permissao` text,
  `nome` text,
  `menu` text,
  PRIMARY KEY (`codigo`),
  KEY `tipo` (`tipo`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Extraindo dados da tabela `Permissoes`
--

INSERT INTO `Permissoes` (`codigo`, `tipo`, `permissao`, `nome`, `menu`) VALUES
(1, 5, 'view/secretaria/relatorios/boletimTurma.php,view/secretaria/relatorios/frequencias.php,view/secretaria/relatorios/listagem.php,view/secretaria/prazos/aula.php,view/secretaria/prazos/diario.php,view/secretaria/ensalamento/ensalamento.php,view/secretaria/ensalamento/horario.php,view/secretaria/ensalamento/sala.php,view/secretaria/cursos/atribuicao.php,view/secretaria/cursos/coordenador.php,view/secretaria/cursos/curso.php,view/secretaria/cursos/disciplina.php,view/secretaria/cursos/matricula.php,view/secretaria/cursos/modalidade.php,view/secretaria/cursos/notasFinais.php,view/secretaria/cursos/professorAtribuicao.php,view/secretaria/cursos/situacao.php,view/secretaria/cursos/tipoAvaliacao.php,view/secretaria/cursos/turma.php,view/secretaria/cursos/turno.php,view/secretaria/abono.php,view/secretaria/atendimento.php,view/secretaria/aviso.php,view/secretaria/calendario.php,view/secretaria/cidade.php,view/secretaria/estado.php,view/secretaria/ftd.php,view/secretaria/pessoa.php,view/secretaria/plano.php,view/secretaria/socioEconomico.php,view/aluno/boletim.php,view/admin/instituicao.php,view/admin/logs.php,view/admin/migracao.php,view/admin/permissao.php,view/admin/sincronizadorNambei.php,view/admin/tipo.php,view/admin/usoSistema.php', 'Boletim por Turma,Frequ&ecirc;ncias,Relat&oacute;rios,Prazos Aulas,Di&aacute;rios,Ensalamento,Hor&aacute;rios,Salas,Atribui&ccedil;&otilde;es,Coordenadores,Cursos,Disciplinas,Matr&iacute;&shy;culas,Modalidades,Notas Finais,Professores,Situa&ccedil;&otilde;es,Tipos de Avalia&ccedil;&otilde;es,Turmas,Turnos,Abono de Faltas,Atendimento,Avisos,Calend&aacute;rio Acad&ecirc;mico,Cidades,Estados,FTDs,Pessoas,Planos de Ensino,Socioecon&ocirc;mico,Boletim,Institui&ccedil;&atilde;o,Logs,Migra&ccedil;&atilde;o BRT,Permiss&otilde;es,Sincronizar,Tipos,Uso do Sistema', 'view/secretaria/relatorios/boletimTurma.php,view/secretaria/relatorios/frequencias.php,view/secretaria/relatorios/listagem.php,view/secretaria/prazos/aula.php,view/secretaria/prazos/diario.php,view/secretaria/ensalamento/ensalamento.php,view/secretaria/ensalamento/horario.php,view/secretaria/ensalamento/sala.php,view/secretaria/cursos/atribuicao.php,view/secretaria/cursos/coordenador.php,view/secretaria/cursos/curso.php,view/secretaria/cursos/disciplina.php,view/secretaria/cursos/matricula.php,view/secretaria/cursos/modalidade.php,view/secretaria/cursos/notasFinais.php,view/secretaria/cursos/professorAtribuicao.php,view/secretaria/cursos/situacao.php,view/secretaria/cursos/tipoAvaliacao.php,view/secretaria/cursos/turma.php,view/secretaria/cursos/turno.php,view/secretaria/abono.php,view/secretaria/atendimento.php,view/secretaria/aviso.php,view/secretaria/calendario.php,view/secretaria/cidade.php,view/secretaria/estado.php,view/secretaria/ftd.php,view/secretaria/pessoa.php,view/secretaria/plano.php,view/secretaria/socioEconomico.php,,view/admin/instituicao.php,view/admin/logs.php,view/admin/migracao.php,view/admin/permissao.php,view/admin/sincronizadorNambei.php,view/admin/tipo.php,view/admin/usoSistema.php'),
(4, 2, 'view/professor/aula.php,view/professor/avaliacao.php,view/professor/aviso.php,view/professor/ensalamento.php,view/professor/frequencia.php,view/professor/ftd.php,view/professor/nota.php,view/professor/plano.php,view/professor/professor.php', 'Aulas,Avalia&ccedil;&otilde;es,Avisos,Ensalamento,Frequ&ecirc;ncias,FTD,Notas,Planos de Ensino,', ',,,view/professor/ensalamento.php,,view/professor/ftd.php,,,'),
(5, 3, 'view/secretaria/relatorios/boletimTurma.php,view/secretaria/relatorios/frequencias.php,view/secretaria/relatorios/listagem.php,view/secretaria/prazos/aula.php,view/secretaria/prazos/diario.php,view/secretaria/ensalamento/ensalamento.php,view/secretaria/ensalamento/horario.php,view/secretaria/ensalamento/sala.php,view/secretaria/cursos/curso.php,view/secretaria/cursos/disciplina.php,view/secretaria/cursos/matricula.php,view/secretaria/cursos/modalidade.php,view/secretaria/cursos/situacao.php,view/secretaria/cursos/tipoAvaliacao.php,view/secretaria/cursos/turma.php,view/secretaria/cursos/turno.php,view/secretaria/aviso.php,view/secretaria/calendario.php,view/secretaria/cidade.php,view/secretaria/estado.php,view/secretaria/ftd.php,view/secretaria/pessoa.php,view/secretaria/plano.php,view/secretaria/socioEconomico.php,view/aluno/boletim.php,view/admin/tipo.php,view/admin/usoSistema.php', 'Boletim - Turmas,Frequ&ecirc;ncias,Relat&oacute;rios,Lan&ccedil;amento de Aulas,Fechamento de Di&aacute;rios,Ensalamento,Hor&aacute;rios,Salas,Cursos,Disciplinas,Matr&iacute;culas,Modalidades,Situa&ccedil;&otilde;es,Tipos de Avalia&ccedil;&otilde;es,Turmas,Turnos,Avisos,Calend&aacute;rio Acad&ecirc;mico,Cidades,Estados,FTDs,Cadastro de Pessoas,Planos de Ensino,SocioEcon&ocirc;mico,Boletim,Tipos de Pessoas,Uso do Sistema', 'view/secretaria/relatorios/boletimTurma.php,view/secretaria/relatorios/frequencias.php,view/secretaria/relatorios/listagem.php,view/secretaria/prazos/aula.php,view/secretaria/prazos/diario.php,view/secretaria/ensalamento/ensalamento.php,view/secretaria/ensalamento/horario.php,view/secretaria/ensalamento/sala.php,view/secretaria/cursos/curso.php,view/secretaria/cursos/disciplina.php,view/secretaria/cursos/matricula.php,view/secretaria/cursos/modalidade.php,view/secretaria/cursos/situacao.php,view/secretaria/cursos/tipoAvaliacao.php,view/secretaria/cursos/turma.php,view/secretaria/cursos/turno.php,view/secretaria/aviso.php,view/secretaria/calendario.php,view/secretaria/cidade.php,view/secretaria/estado.php,view/secretaria/ftd.php,view/secretaria/pessoa.php,view/secretaria/plano.php,view/secretaria/socioEconomico.php,,view/admin/tipo.php,view/admin/usoSistema.php'),
(7, 10, 'view/secretaria/aviso.php,view/aluno/boletim.php,view/secretaria/relatorios/boletimTurma.php,view/secretaria/prazos/diario.php,view/secretaria/relatorios/frequencias.php,view/secretaria/ftd.php,view/secretaria/plano.php,view/secretaria/prazos/aula.php,view/secretaria/prazos/diario.php,view/secretaria/relatorios/listagem.php', 'Avisos,Boletim do Aluno,Boletim da Turma,Di&aacute;rios,Listas de Frequ&ecirc;ncias,FTDs,Planos de Ensino,Prazos Aulas,Prazos Di&aacute;rios,Relat&oacute;rios', 'view/secretaria/aviso.php,,view/secretaria/relatorios/boletimTurma.php,view/secretaria/prazos/diario.php,view/secretaria/relatorios/frequencias.php,view/secretaria/ftd.php,view/secretaria/plano.php,view/secretaria/prazos/aula.php,view/secretaria/prazos/diario.php,view/secretaria/relatorios/listagem.php'),
(8, 4, 'view/secretaria/relatorios/boletimTurma.php,view/secretaria/relatorios/frequencias.php,view/secretaria/relatorios/listagem.php,view/secretaria/prazos/aula.php,view/secretaria/prazos/diario.php,view/secretaria/ensalamento/ensalamento.php,view/secretaria/ensalamento/horario.php,view/secretaria/ensalamento/sala.php,view/secretaria/cursos/atribuicao.php,view/secretaria/cursos/coordenador.php,view/secretaria/cursos/curso.php,view/secretaria/cursos/disciplina.php,view/secretaria/cursos/matricula.php,view/secretaria/cursos/modalidade.php,view/secretaria/cursos/notasFinais.php,view/secretaria/cursos/professorAtribuicao.php,view/secretaria/cursos/situacao.php,view/secretaria/cursos/tipoAvaliacao.php,view/secretaria/cursos/turma.php,view/secretaria/cursos/turno.php,view/secretaria/abono.php,view/secretaria/atendimento.php,view/secretaria/aviso.php,view/secretaria/calendario.php,view/secretaria/cidade.php,view/secretaria/estado.php,view/secretaria/ftd.php,view/secretaria/pessoa.php,view/secretaria/plano.php,view/secretaria/socioEconomico.php,view/aluno/boletim.php,view/admin/migracao.php,view/admin/sincronizadorNambei.php,view/admin/usoSistema.php', 'Boletim - Turmas,Frequ&ecirc;ncias,Relat&oacute;rios,Lan&ccedil;amento de Aulas,Fechamento de Di&aacute;rios,Ensalamento,Hor&aacute;rios,Salas,Atribui&ccedil;&otilde;es,Coordenadores,Cursos,Disciplinas,Matr&iacute;culas,Modalidades,Notas Finais,Professores,Situa&ccedil;&otilde;es,Tipos de Avalia&ccedil;&otilde;es,Turmas,Turnos,Abono de Faltas,Atendimento,Avisos,Calend&aacute;rio Acad&ecirc;mico,Cidades,Estados,FTD,Cadastro de Pessoas,Planos de Ensino,SocioEcon&ocirc;mico,Boletim,Migra&ccedil;&atilde;o BRT,Sincronizar,Uso do Sistema', 'view/secretaria/relatorios/boletimTurma.php,view/secretaria/relatorios/frequencias.php,view/secretaria/relatorios/listagem.php,view/secretaria/prazos/aula.php,view/secretaria/prazos/diario.php,view/secretaria/ensalamento/ensalamento.php,view/secretaria/ensalamento/horario.php,view/secretaria/ensalamento/sala.php,view/secretaria/cursos/atribuicao.php,view/secretaria/cursos/coordenador.php,view/secretaria/cursos/curso.php,view/secretaria/cursos/disciplina.php,view/secretaria/cursos/matricula.php,view/secretaria/cursos/modalidade.php,view/secretaria/cursos/notasFinais.php,view/secretaria/cursos/professorAtribuicao.php,view/secretaria/cursos/situacao.php,view/secretaria/cursos/tipoAvaliacao.php,view/secretaria/cursos/turma.php,view/secretaria/cursos/turno.php,view/secretaria/abono.php,view/secretaria/atendimento.php,view/secretaria/aviso.php,view/secretaria/calendario.php,view/secretaria/cidade.php,view/secretaria/estado.php,view/secretaria/ftd.php,view/secretaria/pessoa.php,view/secretaria/plano.php,view/secretaria/socioEconomico.php,,view/admin/migracao.php,view/admin/sincronizadorNambei.php,view/admin/usoSistema.php'),
(9, 1, 'view/aluno/aluno.php,view/aluno/aula.php,view/aluno/avaliacao.php,view/aluno/aviso.php,view/aluno/boletim.php,view/aluno/ensalamento.php,view/aluno/planoEnsino.php,view/aluno/socioEconomico.php', ',Aulas,Avalia&ccedil;&otilde;es,Avisos,Boletim,Ensalamento,Plano de Ensino,Socioecon&ocirc;mico', ',,,,,view/aluno/ensalamento.php,,view/aluno/socioEconomico.php'),
(10, 11, 'view/secretaria/calendario.php,view/secretaria/relatorios/listagem.php', 'Calend&aacute;rio,Relat&oacute;rios', 'view/secretaria/calendario.php,view/secretaria/relatorios/listagem.php'),
(11, 12, 'view/secretaria/calendario.php,view/secretaria/cursos/coordenador.php,view/secretaria/cursos/professorAtribuicao.php,view/secretaria/relatorios/listagem.php,view/admin/sincronizadorNambei.php', 'Calend&aacute;rio,Coordenador,Professor,Relat&oacute;rios,Sincronizar', 'view/secretaria/calendario.php,view/secretaria/cursos/coordenador.php,view/secretaria/cursos/professorAtribuicao.php,view/secretaria/relatorios/listagem.php,view/admin/sincronizadorNambei.php');

-- --------------------------------------------------------

--
-- Estrutura da tabela `Pessoas`
--

CREATE TABLE IF NOT EXISTS `Pessoas` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) COLLATE latin1_general_ci DEFAULT NULL,
  `prontuario` varchar(45) COLLATE latin1_general_ci DEFAULT NULL,
  `senha` varchar(128) COLLATE latin1_general_ci DEFAULT NULL,
  `cpf` varchar(45) COLLATE latin1_general_ci DEFAULT NULL,
  `rg` varchar(45) COLLATE latin1_general_ci DEFAULT NULL,
  `naturalidade` int(11) DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `endereco` varchar(45) COLLATE latin1_general_ci DEFAULT NULL,
  `bairro` varchar(45) COLLATE latin1_general_ci DEFAULT NULL,
  `cidade` int(11) NOT NULL,
  `cep` varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  `telefone` varchar(45) COLLATE latin1_general_ci DEFAULT NULL,
  `celular` varchar(45) COLLATE latin1_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `observacoes` mediumtext COLLATE latin1_general_ci,
  `foto` mediumblob,
  `sexo` int(11) DEFAULT NULL,
  `raca` int(11) DEFAULT NULL,
  `estadoCivil` int(11) DEFAULT NULL,
  `numeroPessoasNaResidencia` tinyint(4) DEFAULT NULL,
  `renda` int(11) DEFAULT NULL,
  `situacaoTrabalho` int(11) DEFAULT NULL,
  `tipoTrabalho` int(11) DEFAULT NULL,
  `empresaTrabalha` varchar(45) COLLATE latin1_general_ci DEFAULT NULL,
  `cargoEmpresa` varchar(45) COLLATE latin1_general_ci DEFAULT NULL,
  `tempo` int(11) DEFAULT NULL,
  `meioTransporte` int(11) DEFAULT NULL,
  `transporteGratuito` char(1) COLLATE latin1_general_ci DEFAULT NULL,
  `necessidadesEspeciais` char(1) COLLATE latin1_general_ci DEFAULT NULL,
  `descricaoNecessidadesEspeciais` varchar(45) COLLATE latin1_general_ci DEFAULT NULL,
  `dataSenha` datetime DEFAULT NULL,
  `recuperaSenha` varchar(128) COLLATE latin1_general_ci DEFAULT NULL,
  `ano1g` char(4) COLLATE latin1_general_ci DEFAULT NULL,
  `escola1g` varchar(145) COLLATE latin1_general_ci DEFAULT NULL,
  `escolaPublica` char(1) COLLATE latin1_general_ci DEFAULT NULL,
  `lattes` varchar(200) COLLATE latin1_general_ci DEFAULT NULL,
  `bloqueioFoto` char(1) COLLATE latin1_general_ci DEFAULT NULL,
  `dataAlteracao` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `prontuario_UNIQUE` (`prontuario`),
  KEY `fk_Professores_Cidades1` (`cidade`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=2 ;

--
-- Extraindo dados da tabela `Pessoas`
--

INSERT INTO `Pessoas` (`codigo`, `nome`, `prontuario`, `senha`, `cpf`, `rg`, `naturalidade`, `nascimento`, `endereco`, `bairro`, `cidade`, `cep`, `telefone`, `celular`, `email`, `observacoes`, `foto`, `sexo`, `raca`, `estadoCivil`, `numeroPessoasNaResidencia`, `renda`, `situacaoTrabalho`, `tipoTrabalho`, `empresaTrabalha`, `cargoEmpresa`, `tempo`, `meioTransporte`, `transporteGratuito`, `necessidadesEspeciais`, `descricaoNecessidadesEspeciais`, `dataSenha`, `recuperaSenha`, `ano1g`, `escola1g`, `escolaPublica`, `lattes`, `bloqueioFoto`, `dataAlteracao`) VALUES
(1, 'Administrador', 'admin', 'admin', '', '', 0, '0000-00-00', '', '', 0, '', '', '', '', '', NULL, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, '', '', '', '2014-04-13 21:30:21', '', '', '', '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `PessoasTipos`
--

CREATE TABLE IF NOT EXISTS `PessoasTipos` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `pessoa` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `pessoa_2` (`pessoa`,`tipo`),
  KEY `tipo` (`tipo`),
  KEY `pessoa` (`pessoa`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Extraindo dados da tabela `PessoasTipos`
--

INSERT INTO `PessoasTipos` (`codigo`, `pessoa`, `tipo`) VALUES
(1, 1, 5);

-- --------------------------------------------------------

--
-- Estrutura da tabela `PlanosAula`
--

CREATE TABLE IF NOT EXISTS `PlanosAula` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `atribuicao` int(11) NOT NULL,
  `semana` int(2) NOT NULL,
  `conteudo` text NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `atribuicao` (`atribuicao`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `PlanosEnsino`
--

CREATE TABLE IF NOT EXISTS `PlanosEnsino` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `atribuicao` int(11) NOT NULL,
  `numeroAulaSemanal` int(2) DEFAULT NULL,
  `totalHoras` float DEFAULT NULL,
  `totalAulas` float DEFAULT NULL,
  `numeroProfessores` int(1) DEFAULT NULL,
  `ementa` text,
  `objetivo` text,
  `conteudoProgramatico` text,
  `metodologia` text,
  `recursoDidatico` text,
  `avaliacao` text,
  `recuperacaoParalela` text,
  `recuperacaoFinal` text,
  `bibliografiaBasica` text,
  `bibliografiaComplementar` text,
  `solicitante` int(11) DEFAULT NULL,
  `solicitacao` text,
  `valido` datetime DEFAULT NULL,
  `finalizado` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  KEY `codigo` (`atribuicao`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `PrazosAulas`
--

CREATE TABLE IF NOT EXISTS `PrazosAulas` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `atribuicao` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `motivo` varchar(200) NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `atribuicao` (`atribuicao`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `PrazosDiarios`
--

CREATE TABLE IF NOT EXISTS `PrazosDiarios` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `atribuicao` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `motivo` varchar(200) NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `atribuicao` (`atribuicao`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Professores`
--

CREATE TABLE IF NOT EXISTS `Professores` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `professor` int(11) NOT NULL,
  `atribuicao` int(11) NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `professor` (`professor`),
  KEY `atribuicao` (`atribuicao`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Racas`
--

CREATE TABLE IF NOT EXISTS `Racas` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(13) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Extraindo dados da tabela `Racas`
--

INSERT INTO `Racas` (`codigo`, `nome`) VALUES
(1, 'Branca'),
(2, 'Preta'),
(3, 'Parda'),
(4, 'Amarela'),
(5, 'Indí­gena');

-- --------------------------------------------------------

--
-- Estrutura da tabela `Rendas`
--

CREATE TABLE IF NOT EXISTS `Rendas` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Extraindo dados da tabela `Rendas`
--

INSERT INTO `Rendas` (`codigo`, `nome`) VALUES
(1, 'Menos de 1 salário'),
(2, '1 a 2 salários'),
(3, '2 a 3 salários'),
(4, '3 a 5 salários'),
(5, '5 a 10 salários'),
(6, '10 a 20 salários'),
(7, 'mais de 20 salários');

-- --------------------------------------------------------

--
-- Estrutura da tabela `Salas`
--

CREATE TABLE IF NOT EXISTS `Salas` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `localizacao` varchar(100) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `schema_migrations`
--

CREATE TABLE IF NOT EXISTS `schema_migrations` (
  `version` varchar(255) DEFAULT NULL,
  UNIQUE KEY `idx_schema_migrations_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `schema_migrations`
--

INSERT INTO `schema_migrations` (`version`) VALUES
('20140719174655');

-- --------------------------------------------------------

--
-- Estrutura da tabela `Sexos`
--

CREATE TABLE IF NOT EXISTS `Sexos` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(9) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Extraindo dados da tabela `Sexos`
--

INSERT INTO `Sexos` (`codigo`, `nome`) VALUES
(1, 'Masculino'),
(2, 'Feminino');

-- --------------------------------------------------------

--
-- Estrutura da tabela `Situacoes`
--

CREATE TABLE IF NOT EXISTS `Situacoes` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `sigla` varchar(2) DEFAULT NULL,
  `listar` int(1) DEFAULT NULL,
  `habilitar` int(1) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `SituacoesTrabalho`
--

CREATE TABLE IF NOT EXISTS `SituacoesTrabalho` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Extraindo dados da tabela `SituacoesTrabalho`
--

INSERT INTO `SituacoesTrabalho` (`codigo`, `nome`) VALUES
(1, 'Nunca trabalhou'),
(2, 'Proc. Prim. Emprego'),
(3, 'Desempregado');

-- --------------------------------------------------------

--
-- Estrutura da tabela `TemposPesquisa`
--

CREATE TABLE IF NOT EXISTS `TemposPesquisa` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Extraindo dados da tabela `TemposPesquisa`
--

INSERT INTO `TemposPesquisa` (`codigo`, `nome`) VALUES
(1, 'Menos de 1'),
(2, '1 a 2 anos'),
(3, '2 a 4 anos'),
(4, 'Mais de 4 ');

-- --------------------------------------------------------

--
-- Estrutura da tabela `Tipos`
--

CREATE TABLE IF NOT EXISTS `Tipos` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

--
-- Extraindo dados da tabela `Tipos`
--

INSERT INTO `Tipos` (`codigo`, `nome`) VALUES
(1, 'Aluno'),
(2, 'Professor'),
(3, 'Gerente Educacional'),
(4, 'Coordenadoria de Registros Escolares'),
(5, 'Administrador'),
(10, 'Coordenador'),
(11, 'Pedagogia'),
(12, 'Coordenadoria de Apoio ao Ensino');

-- --------------------------------------------------------

--
-- Estrutura da tabela `TiposAvaliacoes`
--

CREATE TABLE IF NOT EXISTS `TiposAvaliacoes` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `tipo` varchar(15) NOT NULL,
  `modalidade` int(1) NOT NULL,
  `calculo` varchar(15) DEFAULT NULL,
  `arredondar` int(1) DEFAULT NULL,
  `notaMaior` float DEFAULT NULL,
  `notaMenor` float DEFAULT NULL,
  `sigla` varchar(3) DEFAULT NULL,
  `final` int(1) DEFAULT NULL,
  `notaUltimBimestre` float DEFAULT NULL,
  `qdeMinima` int(1) DEFAULT NULL,
  `notaMaxima` float DEFAULT '10',
  PRIMARY KEY (`codigo`),
  KEY `modalidade` (`modalidade`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `TiposTrabalho`
--

CREATE TABLE IF NOT EXISTS `TiposTrabalho` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Extraindo dados da tabela `TiposTrabalho`
--

INSERT INTO `TiposTrabalho` (`codigo`, `nome`) VALUES
(1, 'Empresa Privada'),
(2, 'Empresa Pública'),
(3, 'Autônomo'),
(4, 'Trabalhador Rural'),
(5, 'Outros');

-- --------------------------------------------------------

--
-- Estrutura da tabela `Turmas`
--

CREATE TABLE IF NOT EXISTS `Turmas` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `ano` int(11) NOT NULL,
  `semestre` tinyint(4) NOT NULL,
  `sequencia` tinyint(4) DEFAULT NULL,
  `numero` varchar(10) NOT NULL,
  `serie` tinyint(4) DEFAULT NULL,
  `curso` int(11) NOT NULL,
  `turno` int(11) NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `turno` (`turno`),
  KEY `curso` (`curso`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Turnos`
--

CREATE TABLE IF NOT EXISTS `Turnos` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `sigla` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Restrições para as tabelas dumpadas
--

--
-- Restrições para a tabela `Atribuicoes`
--
ALTER TABLE `Atribuicoes`
  ADD CONSTRAINT `Atribuicoes_ibfk_1` FOREIGN KEY (`disciplina`) REFERENCES `Disciplinas` (`codigo`),
  ADD CONSTRAINT `Atribuicoes_ibfk_3` FOREIGN KEY (`turma`) REFERENCES `Turmas` (`codigo`);

--
-- Restrições para a tabela `Atualizacoes`
--
ALTER TABLE `Atualizacoes`
  ADD CONSTRAINT `Atualizacoes_ibfk_1` FOREIGN KEY (`pessoa`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `Aulas`
--
ALTER TABLE `Aulas`
  ADD CONSTRAINT `Aulas_ibfk_1` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `Avaliacoes`
--
ALTER TABLE `Avaliacoes`
  ADD CONSTRAINT `Avaliacoes_ibfk_2` FOREIGN KEY (`tipo`) REFERENCES `TiposAvaliacoes` (`codigo`),
  ADD CONSTRAINT `Avaliacoes_ibfk_3` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `Avisos`
--
ALTER TABLE `Avisos`
  ADD CONSTRAINT `Avisos_ibfk_1` FOREIGN KEY (`pessoa`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `Cidades`
--
ALTER TABLE `Cidades`
  ADD CONSTRAINT `Cidades_ibfk_1` FOREIGN KEY (`estado`) REFERENCES `Estados` (`codigo`);

--
-- Restrições para a tabela `Coordenadores`
--
ALTER TABLE `Coordenadores`
  ADD CONSTRAINT `Coordenadores_ibfk_3` FOREIGN KEY (`coordenador`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `Coordenadores_ibfk_4` FOREIGN KEY (`curso`) REFERENCES `Cursos` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `Cursos`
--
ALTER TABLE `Cursos`
  ADD CONSTRAINT `Cursos_ibfk_2` FOREIGN KEY (`modalidade`) REFERENCES `Modalidades` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `Disciplinas`
--
ALTER TABLE `Disciplinas`
  ADD CONSTRAINT `Disciplinas_ibfk_1` FOREIGN KEY (`curso`) REFERENCES `Cursos` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `Ensalamentos`
--
ALTER TABLE `Ensalamentos`
  ADD CONSTRAINT `Ensalamentos_ibfk_2` FOREIGN KEY (`sala`) REFERENCES `Salas` (`codigo`),
  ADD CONSTRAINT `Ensalamentos_ibfk_3` FOREIGN KEY (`horario`) REFERENCES `Horarios` (`codigo`),
  ADD CONSTRAINT `Ensalamentos_ibfk_5` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Ensalamentos_ibfk_6` FOREIGN KEY (`professor`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `Frequencias`
--
ALTER TABLE `Frequencias`
  ADD CONSTRAINT `Frequencias_ibfk_7` FOREIGN KEY (`matricula`) REFERENCES `Matriculas` (`codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `Frequencias_ibfk_8` FOREIGN KEY (`aula`) REFERENCES `Aulas` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `FrequenciasAbonos`
--
ALTER TABLE `FrequenciasAbonos`
  ADD CONSTRAINT `FrequenciasAbonos_ibfk_2` FOREIGN KEY (`aluno`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `FTDDados`
--
ALTER TABLE `FTDDados`
  ADD CONSTRAINT `FTDDados_ibfk_1` FOREIGN KEY (`professor`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `FTDHorarios`
--
ALTER TABLE `FTDHorarios`
  ADD CONSTRAINT `FTDHorarios_ibfk_1` FOREIGN KEY (`ftd`) REFERENCES `FTDDados` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `Matriculas`
--
ALTER TABLE `Matriculas`
  ADD CONSTRAINT `Matriculas_ibfk_3` FOREIGN KEY (`situacao`) REFERENCES `Situacoes` (`codigo`),
  ADD CONSTRAINT `Matriculas_ibfk_4` FOREIGN KEY (`aluno`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `Matriculas_ibfk_5` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `Notas`
--
ALTER TABLE `Notas`
  ADD CONSTRAINT `Notas_ibfk_3` FOREIGN KEY (`matricula`) REFERENCES `Matriculas` (`codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `Notas_ibfk_4` FOREIGN KEY (`avaliacao`) REFERENCES `Avaliacoes` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `NotasFinais`
--
ALTER TABLE `NotasFinais`
  ADD CONSTRAINT `NotasFinais_ibfk_5` FOREIGN KEY (`matricula`) REFERENCES `Matriculas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `NotasFinais_ibfk_4` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `Permissoes`
--
ALTER TABLE `Permissoes`
  ADD CONSTRAINT `Permissoes_ibfk_1` FOREIGN KEY (`tipo`) REFERENCES `Tipos` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `PessoasTipos`
--
ALTER TABLE `PessoasTipos`
  ADD CONSTRAINT `PessoasTipos_ibfk_2` FOREIGN KEY (`tipo`) REFERENCES `Tipos` (`codigo`),
  ADD CONSTRAINT `PessoasTipos_ibfk_5` FOREIGN KEY (`pessoa`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `PlanosAula`
--
ALTER TABLE `PlanosAula`
  ADD CONSTRAINT `PlanosAula_ibfk_2` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `PlanosEnsino`
--
ALTER TABLE `PlanosEnsino`
  ADD CONSTRAINT `PlanosEnsino_ibfk_2` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `PrazosAulas`
--
ALTER TABLE `PrazosAulas`
  ADD CONSTRAINT `PrazosAulas_ibfk_1` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para a tabela `PrazosDiarios`
--
ALTER TABLE `PrazosDiarios`
  ADD CONSTRAINT `Prazos_ibfk_1` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `Professores`
--
ALTER TABLE `Professores`
  ADD CONSTRAINT `Professores_ibfk_3` FOREIGN KEY (`professor`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `Professores_ibfk_4` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `TiposAvaliacoes`
--
ALTER TABLE `TiposAvaliacoes`
  ADD CONSTRAINT `TiposAvaliacoes_ibfk_2` FOREIGN KEY (`modalidade`) REFERENCES `Modalidades` (`codigo`) ON DELETE CASCADE;

--
-- Restrições para a tabela `Turmas`
--
ALTER TABLE `Turmas`
  ADD CONSTRAINT `Turmas_ibfk_2` FOREIGN KEY (`turno`) REFERENCES `Turnos` (`codigo`),
  ADD CONSTRAINT `Turmas_ibfk_3` FOREIGN KEY (`curso`) REFERENCES `Cursos` (`codigo`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
