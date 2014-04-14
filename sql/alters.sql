========================= 12/07/2014 - Necessário para Revision 404 ============================

ALTER TABLE  `NotasFinais` ADD  `sincronizado` DATETIME NULL AFTER  `falta`;
ALTER TABLE  `NotasFinais` ADD  `retorno` VARCHAR( 100 ) NULL AFTER  `sincronizado`;
ALTER TABLE  `Instituicoes` ADD  `campiDigitaNotas` VARCHAR( 5 ) NULL AFTER  `bloqueioFoto`;

========================= 03/07/2014 - Necessário para Revision 396 ============================

ALTER TABLE  `Atribuicoes` ADD  `formula` VARCHAR( 50 ) NULL AFTER  `calculo`;


========================= 08/06/2014 - Necessário para Revision 379 ============================

ALTER TABLE  `NotasFinais` ADD  `sincronizado` DATETIME NULL AFTER  `falta
ALTER TABLE  `FrequenciasAbonos` ADD  `atribuicao` INT( 11 ) NULL AFTER  `aula`
ALTER TABLE  `FrequenciasAbonos` CHANGE  `aula`  `aula` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL

========================= 15/05/2014 - Necessário para Revision 348 ============================
DROP TABLE  `Prazos`;
ALTER TABLE  `Instituicoes` ADD  `limiteInsAulaProf` INT( 3 ) NULL AFTER  `limiteAltDiarioProf`;
ALTER TABLE  `Notas` CHANGE  `nota`  `nota` VARCHAR( 5 ) NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `PrazosDiarios` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `atribuicao` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `motivo` varchar(200) NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `atribuicao` (`atribuicao`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `PrazosDiarios`
  ADD CONSTRAINT `Prazos_ibfk_1` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS `PrazosAulas` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `atribuicao` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `motivo` varchar(200) NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `atribuicao` (`atribuicao`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE  `PrazosAulas` ADD FOREIGN KEY (  `atribuicao` ) REFERENCES  `academico`.`Atribuicoes` (
`codigo`
) ON DELETE CASCADE ON UPDATE CASCADE ;

UPDATE `academico`.`Permissoes` SET `permissao` = 'abono.php,atribuicao.php,atualizacaoSistema.php,aviso.php,boletim.php,boletimTurma.php,calendario.php,cidade.php,coordenador.php,curso.php,diario.php,disciplina.php,ensalamento.php,estado.php,frequenciaLista.php,ftd.php,horario.php,instituicao.php,logs.php,matricula.php,migracao.php,modalidade.php,permissao.php,pessoa.php,plano.php,prazoAula.php,prazoDiario.php,professorAtribuicao.php,relatorio.php,sala.php,sincronizadorNambei.php,situacao.php,socioEconomico.php,tipo.php,tipoAvaliacao.php,turma.php,turno.php,usoSistema.php', `nome` = 'Abono de Faltas,Atribui&ccedil;&otilde;es,Atualiza&ccedil;&atilde;o do Sistema,Avisos,Boletim,Boletim por Turma,Calend&aacute;rio Acad&ecirc;mico,Cidades,Coordenadores,Cursos,Di&aacute;rios,Disciplinas,Ensalamento,Estados,Frequ&ecirc;ncias,FTDs,Hor&aacute;rios,Institui&ccedil;&atilde;o,Logs,Matr&iacute;&shy;culas,Migra&ccedil;&atilde;o BRT,Modalidades,Permiss&otilde;es,Pessoas,Planos de Ensino,Prazos Aulas,Prazos Di&aacute;rios,Professores,Relat&oacute;rios,Salas,Sincronizar,Situa&ccedil;&otilde;es,Socioecon&ocirc;mico,Tipos,Tipos de Avalia&ccedil;&otilde;es,Turmas,Turnos,Uso do Sistema', `menu` = 'abono.php,atribuicao.php,atualizacaoSistema.php,aviso.php,,boletimTurma.php,calendario.php,cidade.php,coordenador.php,curso.php,diario.php,disciplina.php,ensalamento.php,estado.php,frequenciaLista.php,ftd.php,horario.php,instituicao.php,logs.php,matricula.php,migracao.php,modalidade.php,permissao.php,pessoa.php,plano.php,prazoAula.php,prazoDiario.php,professorAtribuicao.php,relatorio.php,sala.php,sincronizadorNambei.php,situacao.php,socioEconomico.php,tipo.php,tipoAvaliacao.php,turma.php,turno.php,usoSistema.php' WHERE `Permissoes`.`codigo` = 1; UPDATE `academico`.`Permissoes` SET `permissao` = 'aviso.php,boletim.php,boletimTurma.php,calendario.php,cidade.php,curso.php,diario.php,disciplina.php,ensalamento.php,estado.php,frequenciaLista.php,ftd.php,horario.php,matricula.php,modalidade.php,pessoa.php,plano.php,prazoAula.php,prazoDiario.php,relatorio.php,sala.php,situacao.php,socioEconomico.php,tipo.php,tipoAvaliacao.php,turma.php,turno.php,usoSistema.php', `nome` = 'Avisos,Boletim,Boletim - Turmas,Calend&aacute;rio Acad&ecirc;mico,Cidades,Cursos,Di&aacute;rios de Classe,Disciplinas,Ensalamento,Estados,Frequ&ecirc;ncias,FTDs,Hor&aacute;rios,Matr&iacute;culas,Modalidades,Cadastro de Pessoas,Planos de Ensino,Prazos Aulas,Prazos Di&aacute;rios,Relat&oacute;rios,Salas,Situa&ccedil;&otilde;es,SocioEcon&ocirc;mico,Tipos de Pessoas,Tipos de Avalia&ccedil;&otilde;es,Turmas,Turnos,Uso do Sistema', `menu` = 'aviso.php,,boletimTurma.php,calendario.php,cidade.php,curso.php,diario.php,disciplina.php,ensalamento.php,estado.php,frequenciaLista.php,ftd.php,horario.php,matricula.php,modalidade.php,pessoa.php,plano.php,prazoAula.php,prazoDiario.php,relatorio.php,sala.php,situacao.php,socioEconomico.php,tipo.php,tipoAvaliacao.php,turma.php,turno.php,usoSistema.php' WHERE `Permissoes`.`codigo` = 5; UPDATE `academico`.`Permissoes` SET `permissao` = 'aula.php,avaliacao.php,aviso.php,boletim.php,boletimTurma.php,diario.php,frequencia.php,frequenciaLista.php,ftd.php,nota.php,plano.php,prazoAula.php,prazoDiario.php,professor.php,relatorio.php', `nome` = 'Aulas,Avalia&ccedil;&otilde;es,Avisos,Boletim do Aluno,Boletim da Turma,Di&aacute;rios,Frequ&ecirc;ncia,Listas de Frequ&ecirc;ncias,FTDs,Notas,Planos de Ensino,Prazos Aulas,Prazos Di&aacute;rios,Disciplinas,Relat&oacute;rios', `menu` = ',,aviso.php,,boletimTurma.php,diario.php,,frequenciaLista.php,ftd.php,,plano.php,prazoAula.php,prazoDiario.php,professor.php,relatorio.php' WHERE `Permissoes`.`codigo` = 7; UPDATE `academico`.`Permissoes` SET `permissao` = 'abono.php,atribuicao.php,aula.php,aviso.php,boletim.php,boletimTurma.php,calendario.php,cidade.php,coordenador.php,curso.php,diario.php,disciplina.php,ensalamento.php,estado.php,frequencia.php,frequenciaLista.php,horario.php,matricula.php,migracao.php,modalidade.php,nota.php,pessoa.php,plano.php,prazo.php,relatorio.php,sala.php,situacao.php,socioEconomico.php,tipoAvaliacao.php,turma.php,turno.php,usoSistema.php', `nome` = 'Abono de Faltas,Atribui&ccedil;&atilde;o,Aulas,Avisos,Boletim,Boletim - Turmas,Calend&aacute;rio Acad&ecirc;mico,Cidades,Coordenadores,Cursos,Di&aacute;rios de Classe,Disciplinas,Ensalamento,Estados,Frequ&ecirc;ncias,Frequ&ecirc;ncias,Hor&aacute;rios,Matr&iacute;culas,Migra&ccedil;&atilde;o BRT,Modalidades,Notas,Cadastro de Pessoas,Planos de Ensino,Prazos dos Di&aacute;rios,Relat&oacute;rios,Salas,Situa&ccedil;&otilde;es,SocioEcon&ocirc;mico,Tipos de Avalia&ccedil;&otilde;es,Turmas,Turnos,Uso do Sistema', `menu` = 'abono.php,atribuicao.php,,aviso.php,,boletimTurma.php,calendario.php,cidade.php,coordenador.php,curso.php,diario.php,disciplina.php,ensalamento.php,estado.php,,frequenciaLista.php,horario.php,matricula.php,migracao.php,modalidade.php,,pessoa.php,plano.php,prazo.php,relatorio.php,sala.php,situacao.php,socioEconomico.php,tipoAvaliacao.php,turma.php,turno.php,usoSistema.php' WHERE `Permissoes`.`codigo` = 8;

ALTER TABLE  `Atualizacoes` ADD FOREIGN KEY (  `pessoa` ) REFERENCES  `academico`.`Pessoas` (
`codigo`
) ON DELETE CASCADE ON UPDATE CASCADE ;


========================= 09/05/2013 - Necessário para Revision 328 ============================

ALTER TABLE  `academico`.`Ensalamentos` DROP INDEX  `CHAVE` ,
ADD UNIQUE  `CHAVE` (  `atribuicao` ,  `sala` ,  `diaSemana` ,  `horario` ,  `professor` )

========================= 06/05/2013 - Necessário para Revision 328 ============================
ALTER TABLE  `FrequenciasAbonos` ADD  `tipo` CHAR( 1 ) NOT NULL AFTER  `motivo`;

ALTER TABLE  `PlanosEnsino` CHANGE  `totalHoras`  `totalHoras` FLOAT NOT NULL ,
CHANGE  `totalAulas`  `totalAulas` FLOAT NOT NULL;

ALTER TABLE `PlanosEnsino` CHANGE `numeroAulaSemanal` `numeroAulaSemanal` INT(2) NULL, CHANGE `totalHoras` `totalHoras` FLOAT NULL, CHANGE `totalAulas` `totalAulas` FLOAT NULL, CHANGE `numeroProfessores` `numeroProfessores` INT(1) NULL, CHANGE `ementa` `ementa` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL, CHANGE `objetivo` `objetivo` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL, CHANGE `conteudoProgramatico` `conteudoProgramatico` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL, CHANGE `metodologia` `metodologia` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL, CHANGE `recursoDidatico` `recursoDidatico` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL, CHANGE `avaliacao` `avaliacao` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL, CHANGE `recuperacaoParalela` `recuperacaoParalela` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL, CHANGE `recuperacaoFinal` `recuperacaoFinal` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL, CHANGE `bibliografiaBasica` `bibliografiaBasica` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL, CHANGE `bibliografiaComplementar` `bibliografiaComplementar` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL, CHANGE `solicitante` `solicitante` INT(11) NULL DEFAULT NULL, CHANGE `solicitacao` `solicitacao` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `valido` `valido` DATETIME NULL DEFAULT NULL, CHANGE `finalizado` `finalizado` DATETIME NULL DEFAULT NULL;

ALTER TABLE  `Instituicoes` ADD  `versao` VARCHAR( 20 ) NULL AFTER  `bloqueioFoto`;
UPDATE  `academico`.`Instituicoes` SET  `versao` =  '328';

========================= 03/05/2013 ============================
ALTER TABLE `Avisos` ADD `pessoa` INT( 11 ) NOT NULL AFTER `codigo`;
ALTER TABLE `Avisos` ADD INDEX ( `pessoa` ) ;
ALTER TABLE `Avisos` ADD `destinatario` INT( 11 ) NULL AFTER `atribuicao`;
ALTER TABLE `Avisos` DROP FOREIGN KEY `Avisos_ibfk_2` ;
ALTER TABLE `Avisos` CHANGE `atribuicao` `atribuicao` INT( 11 ) NULL DEFAULT NULL ;
ALTER TABLE `Avisos` ADD `turma` INT( 11 ) NULL AFTER `atribuicao` ,
ADD `curso` INT( 11 ) NULL AFTER `turma` 

ALTER TABLE `Avisos` ADD FOREIGN KEY ( `pessoa` ) REFERENCES `academico`.`Pessoas` (
`codigo`
) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE `Instituicoes` ADD `bloqueioFoto` VARCHAR( 1 ) NULL AFTER `senhaServidorAtualizacao` 

ALTER TABLE `Pessoas` ADD `lattes` VARCHAR( 200 ) NULL AFTER `escolaPublica` ,
ADD `bloqueioFoto` CHAR( 1 ) NULL AFTER `lattes` 

ALTER TABLE  `PlanosEnsino` ADD  `finalizado` DATETIME NULL AFTER  `valido`

ALTER TABLE `Pessoas` CHANGE `email` `email` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL 


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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


UPDATE `academico`.`Permissoes` SET `permissao` = 'abono.php,atribuicao.php,atualizacaoSistema.php,aviso.php,boletim.php,boletimTurma.php,calendario.php,cidade.php,coordenador.php,curso.php,diario.php,disciplina.php,ensalamento.php,estado.php,frequenciaLista.php,ftd.php,horario.php,instituicao.php,logs.php,matricula.php,migracao.php,modalidade.php,permissao.php,pessoa.php,plano.php,prazo.php,professorAtribuicao.php,relatorio.php,sala.php,sincronizadorNambei.php,situacao.php,socioEconomico.php,tipo.php,tipoAvaliacao.php,turma.php,turno.php,usoSistema.php', `nome` = 'Abono de Faltas,Atribui&ccedil;&otilde;es,Atualiza&ccedil;&atilde;o do Sistema,Avisos,Boletim,Boletim por Turma,Calend&aacute;rio Acad&ecirc;mico,Cidades,Coordenadores,Cursos,Di&aacute;rios,Disciplinas,Ensalamento,Estados,Frequ&ecirc;ncias,FTDs,Hor&aacute;rios,Institui&ccedil;&atilde;o,Logs,Matr&iacute;&shy;culas,Migra&ccedil;&atilde;o BRT,Modalidades,Permiss&otilde;es,Pessoas,Planos de Ensino,Prazos,Professores,Relat&oacute;rios,Salas,Sincronizar,Situa&ccedil;&otilde;es,Socioecon&ocirc;mico,Tipos,Tipos de Avalia&ccedil;&otilde;es,Turmas,Turnos,Uso do Sistema', `menu` = 'abono.php,atribuicao.php,atualizacaoSistema.php,aviso.php,,boletimTurma.php,calendario.php,cidade.php,coordenador.php,curso.php,diario.php,disciplina.php,ensalamento.php,estado.php,frequenciaLista.php,ftd.php,horario.php,instituicao.php,logs.php,matricula.php,migracao.php,modalidade.php,permissao.php,pessoa.php,plano.php,prazo.php,professorAtribuicao.php,relatorio.php,sala.php,sincronizadorNambei.php,situacao.php,socioEconomico.php,tipo.php,tipoAvaliacao.php,turma.php,turno.php,usoSistema.php' WHERE `Permissoes`.`codigo` = 1; 
UPDATE `academico`.`Permissoes` SET `permissao` = 'aula.php,avaliacao.php,aviso.php,boletim.php,calendario.php,ensalamento.php,frequencia.php,ftdProfessor.php,nota.php,planoEnsino.php,professor.php', `nome` = 'Aulas,Avalia&ccedil;&otilde;es,Avisos para Turma,Boletim,Calend&aacute;rio Acad&ecirc;mico,Hor&aacute;rio do Professor,Frequ&ecirc;ncias,FTD,Notas,Plano de Ensino,Disciplinas', `menu` = ',,,,calendario.php,ensalamento.php,,ftdProfessor.php,,,professor.php' WHERE `Permissoes`.`codigo` = 4;
UPDATE `academico`.`Permissoes` SET `permissao` = 'aviso.php,boletim.php,boletimTurma.php,calendario.php,cidade.php,curso.php,diario.php,disciplina.php,ensalamento.php,estado.php,frequenciaLista.php,ftd.php,horario.php,matricula.php,modalidade.php,pessoa.php,plano.php,prazo.php,relatorio.php,sala.php,situacao.php,socioEconomico.php,tipo.php,tipoAvaliacao.php,turma.php,turno.php,usoSistema.php', `nome` = 'Avisos,Boletim,Boletim - Turmas,Calend&aacute;rio Acad&ecirc;mico,Cidades,Cursos,Di&aacute;rios de Classe,Disciplinas,Ensalamento,Estados,Frequ&ecirc;ncias,FTDs,Hor&aacute;rios,Matr&iacute;culas,Modalidades,Cadastro de Pessoas,Planos de Ensino,Prazos dos Di&aacute;rios,Relat&oacute;rios,Salas,Situa&ccedil;&otilde;es,SocioEcon&ocirc;mico,Tipos de Pessoas,Tipos de Avalia&ccedil;&otilde;es,Turmas,Turnos,Uso do Sistema', `menu` = 'aviso.php,,boletimTurma.php,calendario.php,cidade.php,curso.php,diario.php,disciplina.php,ensalamento.php,estado.php,frequenciaLista.php,ftd.php,horario.php,matricula.php,modalidade.php,pessoa.php,plano.php,prazo.php,relatorio.php,sala.php,situacao.php,socioEconomico.php,tipo.php,tipoAvaliacao.php,turma.php,turno.php,usoSistema.php' WHERE `Permissoes`.`codigo` = 5; 
UPDATE `academico`.`Permissoes` SET `permissao` = 'aula.php,avaliacao.php,aviso.php,boletim.php,boletimTurma.php,diario.php,frequencia.php,frequenciaLista.php,ftd.php,nota.php,plano.php,prazo.php,professor.php,relatorio.php', `nome` = 'Aulas,Avalia&ccedil;&otilde;es,Avisos,Boletim do Aluno,Boletim da Turma,Di&aacute;rios,Frequ&ecirc;ncia,Listas de Frequ&ecirc;ncias,FTDs,Notas,Planos de Ensino,Prazos Di&aacute;rios,Disciplinas,Relat&oacute;rios', `menu` = ',,aviso.php,,boletimTurma.php,diario.php,,frequenciaLista.php,ftd.php,,plano.php,prazo.php,professor.php,relatorio.php' WHERE `Permissoes`.`codigo` = 7; 
UPDATE `academico`.`Permissoes` SET `permissao` = 'abono.php,atribuicao.php,aula.php,aviso.php,boletim.php,boletimTurma.php,calendario.php,cidade.php,coordenador.php,curso.php,diario.php,disciplina.php,ensalamento.php,estado.php,frequencia.php,frequenciaLista.php,horario.php,matricula.php,migracao.php,modalidade.php,nota.php,pessoa.php,plano.php,prazo.php,relatorio.php,sala.php,situacao.php,socioEconomico.php,tipoAvaliacao.php,turma.php,turno.php,usoSistema.php', `nome` = 'Abono de Faltas,Atribui&ccedil;&atilde;o,Aulas,Avisos,Boletim,Boletim - Turmas,Calend&aacute;rio Acad&ecirc;mico,Cidades,Coordenadores,Cursos,Di&aacute;rios de Classe,Disciplinas,Ensalamento,Estados,Frequ&ecirc;ncias,Frequ&ecirc;ncias,Hor&aacute;rios,Matr&iacute;culas,Migra&ccedil;&atilde;o BRT,Modalidades,Notas,Cadastro de Pessoas,Planos de Ensino,Prazos dos Di&aacute;rios,Relat&oacute;rios,Salas,Situa&ccedil;&otilde;es,SocioEcon&ocirc;mico,Tipos de Avalia&ccedil;&otilde;es,Turmas,Turnos,Uso do Sistema', `menu` = 'abono.php,atribuicao.php,,aviso.php,,boletimTurma.php,calendario.php,cidade.php,coordenador.php,curso.php,diario.php,disciplina.php,ensalamento.php,estado.php,,frequenciaLista.php,horario.php,matricula.php,migracao.php,modalidade.php,,pessoa.php,plano.php,prazo.php,relatorio.php,sala.php,situacao.php,socioEconomico.php,tipoAvaliacao.php,turma.php,turno.php,usoSistema.php' WHERE `Permissoes`.`codigo` = 8;
UPDATE `academico`.`Permissoes` SET `permissao` = 'aluno.php,atendimento.php,boletim.php,calendario.php,ensalamento.php,planoEnsino.php,socioEconomico.php', `nome` = 'Boletim,Atendimento do Professor,Boletim,Calend&aacute;rio Acad&ecirc;mico,Hor&aacute;rio do Aluno,Plano de Ensino,Socioecon&ocirc;mico', `menu` = 'aluno.php,atendimento.php,,calendario.php,ensalamento.php,,socioEconomico.php' WHERE `Permissoes`.`codigo` = 9;