<?php

require "inc/config.inc.php";

class Academico17 extends Ruckusing_Migration_Base {

    public function up() {
        // ADICIONANDO TABELA NOVA
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Arquivos'");
        if (!$result) {
            $this->execute("CREATE TABLE IF NOT EXISTS `Arquivos` (
                    `codigo` int(11) NOT NULL AUTO_INCREMENT,
                    `descricao` varchar(200) NOT NULL,
                    `link` varchar(255) DEFAULT NULL,
                    `arquivo` varchar(255) DEFAULT NULL,
                    `pessoa` int(11) NOT NULL,
                    `atribuicao` int(11) NOT NULL,
                    `data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`codigo`),
                        KEY `atribuicao` (`atribuicao`),
                        KEY `pessoa` (`pessoa`)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                    ALTER TABLE `Arquivos`
                        ADD CONSTRAINT `Arquivos_ibfk_2` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
                        ADD CONSTRAINT `Arquivos_ibfk_1` FOREIGN KEY (`pessoa`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;");
        }

        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'ArquivosLogs'");
        if (!$result) {
            $this->execute("CREATE TABLE IF NOT EXISTS `ArquivosLogs` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `arquivo` int(11) NOT NULL,
                            `pessoa` int(11) NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `pessoa` (`pessoa`),
                            KEY `arquivo` (`arquivo`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                        ALTER TABLE `ArquivosLogs`
                          ADD CONSTRAINT `ArquivosLogs_ibfk_2` FOREIGN KEY (`pessoa`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
                          ADD CONSTRAINT `ArquivosLogs_ibfk_1` FOREIGN KEY (`arquivo`) REFERENCES `Arquivos` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;");
        }
        
        // ALTERANDO VALOR DA TABELA TIPOSAVALIACOES
        $this->execute("ALTER TABLE  `TiposAvaliacoes` CHANGE  `modalidade`  `modalidade` INT( 11 ) NOT NULL");

        // ADICIONANDO ARQUIVO EM ALUNOS E PROFESSORES
        $result = $this->select_all("SELECT prof,aluno FROM Instituicoes");
        $aluno = $result[0]['aluno'];
        $prof = $result[0]['prof'];

        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes WHERE tipo = $prof");
        if ($result) {
            $calendario = 'view/professor/arquivo.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if (!in_array($calendario, $P['permissao'])) {
                    $P['permissao'][] = $calendario;
                    $P['menu'][] = $calendario;
                    $P['nome'][] = 'Material de Aula';
                }

                $P1 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }

        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes WHERE tipo = $aluno");
        if ($result) {
            $calendario = 'view/aluno/arquivo.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if (!in_array($calendario, $P['permissao'])) {
                    $P['permissao'][] = $calendario;
                    $P['menu'][] = $calendario;
                    $P['nome'][] = 'Material de Aula';
                }

                $P1 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='400', versaoAtual='400'");
        printf("<br>Patch Academico17: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
