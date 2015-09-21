<?php

require "inc/config.inc.php";

class Academico48 extends Ruckusing_Migration_Base {

    public function up() {
        // CREATE TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Ocorrencias'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `Ocorrencias` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `aluno` int(11) NOT NULL,
                            `data` datetime NOT NULL,
                            `descricao` varchar(500) NOT NULL,
                            `registroPor` int(11) NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `aluno` (`aluno`,`registroPor`),
                            KEY `registroPor` (`registroPor`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                          ALTER TABLE `Ocorrencias`
                            ADD CONSTRAINT `Ocorrencias_ibfk_1` FOREIGN KEY (`aluno`) REFERENCES `Pessoas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
                            ADD CONSTRAINT `Ocorrencias_ibfk_2` FOREIGN KEY (`registroPor`) REFERENCES `Pessoas` (`codigo`) ON DELETE NO ACTION ON UPDATE NO ACTION;
                          ");

        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'OcorrenciasInteracoes'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `OcorrenciasInteracoes` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `data` datetime NOT NULL,
                            `ocorrencia` int(11) NOT NULL,
                            `descricao` varchar(500) NOT NULL,
                            `registroPor` int(11) NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `ocorrencia` (`ocorrencia`,`registroPor`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                          ALTER TABLE `OcorrenciasInteracoes`
                            ADD CONSTRAINT `OcorrenciasInteracoes_ibfk_1` FOREIGN KEY (`ocorrencia`) REFERENCES `Ocorrencias` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;
                          ");


        $result = $this->select_all("SELECT prof,adm,ged,sec,coord FROM Instituicoes");
        $prof = $result[0]['prof'];
        $adm = $result[0]['adm'];
        $ged = $result[0]['ged'];
        $sec = $result[0]['sec'];
        $coord = $result[0]['coord'];

        // ALTERANDO A FTD,CIDADES E ESTADOS DE MENU.
        $result = $this->select_all("SELECT codigo,tipo,nome,menu,permissao FROM Permissoes");

        if ($result) {
            $then_menu_ftd = 'view/secretaria/ftd.php';
            $now_menu_ftd = 'view/secretaria/atribuicao_docente/ftd.php';
            $then_menu_cidade = 'view/secretaria/cidade.php';
            $now_menu_cidade = 'view/admin/cidade.php';
            $then_menu_estado = 'view/secretaria/estado.php';
            $now_menu_estado = 'view/admin/estado.php';

            $new_menu = 'view/secretaria/ocorrencia.php';

            foreach ($result as $P) {
                $has_ftd = 0;
                $has_cidade = 0;
                $has_estado = 0;

                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if (in_array($then_menu_ftd, $P['permissao'])) {
                    $P['permissao'][] = $now_menu_ftd;
                    $P['menu'][] = $now_menu_ftd;
                    $P['nome'][] = 'FTDs (descontinuado)';
                    $has_ftd = 1;
                }

                if (in_array($then_menu_cidade, $P['permissao'])) {
                    $P['permissao'][] = $now_menu_cidade;
                    $P['menu'][] = $now_menu_cidade;
                    $P['nome'][] = 'Cidades';
                    $has_cidade = 1;
                }

                if (in_array($then_menu_estado, $P['permissao'])) {
                    $P['permissao'][] = $now_menu_estado;
                    $P['menu'][] = $now_menu_estado;
                    $P['nome'][] = 'Estados';
                    $has_estado = 1;
                }

                if ($P['tipo'] == $prof || $P['tipo'] == $coord || $P['tipo'] == $sec || $P['tipo'] == $ged || $P['tipo'] == $adm) {
                    if (!in_array($new_menu, $P['permissao'])) {
                        $P['permissao'][] = $new_menu;
                        $P['menu'][] = $new_menu;
                        $P['nome'][] = 'Ocorr&ecirc;ncias';
                    }
                }

                $P2 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                if ($has_ftd || $has_cidade || $has_estado) {
                    $i = 0;
                    foreach ($P['permissao'] as $menu) {
                        // REMOVENDO MENU
                        if ($menu != $then_menu_ftd && $menu != $then_menu_cidade && $menu != $then_menu_estado) {
                            $P1['permissao'][] = $menu;
                            $P1['menu'][] = $P['menu'][$i];
                            $P1['nome'][] = $P['nome'][$i];
                        }
                        $i++;
                    }
                    $P2 = implode(",", $P1['permissao']);
                    $M1 = implode(",", $P1['menu']);
                    $N1 = implode(",", $P1['nome']);
                }

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P2',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='431', versaoAtual='431'");
        printf("<br>Patch Academico48: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
