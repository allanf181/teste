<?php

require "inc/config.inc.php";

class Academico34 extends Ruckusing_Migration_Base {

    public function up() {
// ADICIONANDO TABELA NOVA
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'AulasTrocas'");
        if (!$result) {

            $this->execute("CREATE TABLE IF NOT EXISTS `AulasTrocas` (
                        `codigo` int(11) NOT NULL AUTO_INCREMENT,
                        `tipo` varchar(10) NOT NULL,
                        `professor` int(11) NOT NULL,
                        `dataPedido` datetime NOT NULL,
                        `motivo` varchar(500) NOT NULL,
                        `atribuicao` int(11) NOT NULL,
                        `aula` varchar(200) NOT NULL,
                        `dataTroca` date NOT NULL,
                        `professorSub` int(11) NOT NULL,
                        `professorSubData` datetime DEFAULT NULL,
                        `professorSubParecer` varchar(200) DEFAULT NULL,
                        `professorSubAceite` char(1) DEFAULT '0',
                        `coordenador` int(11) NOT NULL,
                        `coordenadorData` datetime DEFAULT NULL,                        
                        `coordenadorParecer` varchar(200) DEFAULT NULL,
                        `coordenadorAceite` char(1) DEFAULT '0',
                        PRIMARY KEY (`codigo`),
                        KEY `atribuicao` (`atribuicao`),
                        KEY `professor` (`professor`),
                        KEY `professorSub` (`professorSub`)
                      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

            //RELACIONAMENTO
            $this->execute("ALTER TABLE  `AulasTrocas` ADD FOREIGN KEY (  `professor` ) REFERENCES  `academico`.`Pessoas` (
                        `codigo`
                        ) ON DELETE CASCADE ON UPDATE CASCADE ;

                        ALTER TABLE  `AulasTrocas` ADD FOREIGN KEY (  `atribuicao` ) REFERENCES  `academico`.`Atribuicoes` (
                        `codigo`
                        ) ON DELETE CASCADE ON UPDATE CASCADE ;

                        ALTER TABLE  `AulasTrocas` ADD FOREIGN KEY (  `professorSub` ) REFERENCES  `academico`.`Pessoas` (
                        `codigo`
                        ) ON DELETE CASCADE ON UPDATE CASCADE ;");
        }

        //ALTERACAO DA TABELA PlanosEnsino
        $this->execute("ALTER TABLE  `PlanosEnsino` CHANGE  `objetivo`  `objetivoGeral` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;");

        // ALTER COLUMN ON PlanosEnsino
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'PlanosEnsino' 
                                    AND COLUMN_NAME = 'objetivoEspecifico'");
        if (!$result)
            $this->execute("ALTER TABLE  `PlanosEnsino` ADD  `objetivoEspecifico` TEXT NOT NULL AFTER  `objetivoGeral` ;");
        
        $this->execute("ALTER TABLE  `PlanosAula` CHANGE  `conteudo`  `conteudo` VARCHAR( 400 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;");

        
        // ADICIONANDO TROCA DE AULA/REPOSIÇÃO EM PROFESSORES
        $result = $this->select_all("SELECT prof FROM Instituicoes");
        $prof = $result[0]['prof'];

        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes WHERE tipo = $prof");
        if ($result) {
            $new_arquivo = 'view/professor/aulaTroca.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if (!in_array($new_arquivo, $P['permissao'])) {
                    $P['permissao'][] = $new_arquivo;
                    $P['menu'][] = $new_arquivo;
                    $P['nome'][] = 'Troca/Reposição (Aula)';
                }

                $P1 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }
        
        // ADICIONANDO NOVO MENU EM SECRETARIA, ADMINISTRADOR
        $result = $this->select_all("SELECT sec,adm,ged FROM Instituicoes");
        $sec = $result[0]['sec'];
        $adm = $result[0]['adm'];
        $ged = $result[0]['ged'];

        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes WHERE tipo IN ($sec,$adm,$ged)");
        if ($result) {
            $new_arquivo = 'view/secretaria/relatorios/inconsistenciasNambei.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if (!in_array($new_arquivo, $P['permissao'])) {
                    $P['permissao'][] = $new_arquivo;
                    $P['menu'][] = $new_arquivo;
                    $P['nome'][] = 'Inconsistências Nambei';
                }

                $P1 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='417', versaoAtual='417'");
        printf("<br>Patch Academico34: OK");
    }

    //up()
    public function down() {
        
    }

//down()
}
