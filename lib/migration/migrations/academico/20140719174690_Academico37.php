<?php

require "inc/config.inc.php";

class Academico37 extends Ruckusing_Migration_Base {

    public function up() {
        // ADICIONANDO TABELA NOVA
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'TDDados'");
        if (!$result) {
            $this->execute("CREATE TABLE IF NOT EXISTS `TDDados` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `pessoa` int(11) NOT NULL,
                            `ano` varchar(4) NOT NULL,
                            `semestre` varchar(2) NOT NULL,
                            `apelido` varchar(100) DEFAULT NULL,
                            `area` varchar(100) NOT NULL,
                            `regime` varchar(15) NOT NULL,
                            `duracaoAula` varchar(5) NOT NULL,
                            `dedicarEnsino` tinyint(1) NOT NULL,
                            `subHorario` tinyint(1) NOT NULL,
                            `horario1` varchar(20) DEFAULT NULL,
                            `horario2` varchar(20) DEFAULT NULL,
                            `horario3` varchar(20) DEFAULT NULL,
                            `horario` text,
                            PRIMARY KEY (`codigo`),
                            KEY `pessoa` (`pessoa`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
                          
                        ALTER TABLE `TDDados`
                            ADD CONSTRAINT `TDDados_ibfk_1` FOREIGN KEY (`pessoa`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;");
        }
        // ADICIONANDO TABELA NOVA
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'TDFPAAtvECmt'");
        if (!$result) {
            $this->execute("CREATE TABLE IF NOT EXISTS `TDFPAAtvECmt` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `TD` int(11) NOT NULL,
                            `descricao` varchar(200) NOT NULL,
                            `aulas` int(2) NOT NULL,
                            `tipo` varchar(3) NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `TD` (`TD`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
                          
                         ALTER TABLE `TDFPAAtvECmt`
                            ADD CONSTRAINT `TDFPAAtvECmt_ibfk_1` FOREIGN KEY (`TD`) REFERENCES `TDDados` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;");
        }

        // ADICIONANDO TABELA NOVA
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'TDFPAComponente'");
        if (!$result) {
            $this->execute("CREATE TABLE IF NOT EXISTS `TDFPAComponente` (
                        `codigo` int(11) NOT NULL AUTO_INCREMENT,
                        `TD` int(11) NOT NULL,
                        `sigla` varchar(45) NOT NULL,
                        `nome` varchar(45) NOT NULL,
                        `curso` varchar(145) NOT NULL,
                        `periodo` char(1) NOT NULL,
                        `aulas` int(2) NOT NULL,
                        PRIMARY KEY (`codigo`),
                        KEY `TD` (`TD`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                          
                         ALTER TABLE `TDFPAComponente`
                            ADD CONSTRAINT `TDFPAComponente_ibfk_1` FOREIGN KEY (`TD`) REFERENCES `TDDados` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;");
        }

        //LIMPANDO DADOS DO DIGITA NOTAS PARA AMBIENTE DE PRODUÇÃO
        $this->execute("DELETE FROM Logs WHERE origem = 'CRON_NT' OR origem = 'CRON_NTDIV' OR origem = 'CRON_NTERR'");
        $this->execute("UPDATE NotasFinais SET sincronizado = NULL, retorno = NULL WHERE flag <> 5");
            
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='420', versaoAtual='420'");
        printf("<br>Patch Academico37: OK");
    }
    //up()
    public function down() {
    }
    //down()
}
