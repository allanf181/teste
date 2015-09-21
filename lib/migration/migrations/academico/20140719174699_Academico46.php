<?php

require "inc/config.inc.php";

class Academico46 extends Ruckusing_Migration_Base {

    public function up() {
        // CREATE TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'AtvAcademicas'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `AtvAcademicas` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `nome` varchar(200) NOT NULL,
                            `curso` int(11) NOT NULL,
                            `CHminCientifica` varchar(3) NOT NULL,
                            `CHminCultural` varchar(3) NOT NULL,
                            `CHminAcademica` varchar(3) NOT NULL,
                            `CHTotal` varchar(3) NOT NULL,
                            `CHminSem` varchar(3) NOT NULL,
                            `CHmaxSem` varchar(3) NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `curso` (`curso`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
                          
                        ALTER TABLE `AtvAcademicas`
                          ADD CONSTRAINT `AtvAcademicas_ibfk_1` FOREIGN KEY (`curso`) REFERENCES `Cursos` (`codigo`);");

        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'AtvAcadItens'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `AtvAcadItens` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `atvAcademica` int(11) NOT NULL,
                            `tipo` varchar(20) NOT NULL,
                            `atividade` varchar(300) NOT NULL,
                            `comprovacao` varchar(300) NOT NULL,
                            `CH` varchar(300) NOT NULL,
                            `CHLimite` varchar(3) NOT NULL,
                            PRIMARY KEY (`codigo`,`atvAcademica`),
                            KEY `atvAcademica` (`atvAcademica`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
                          
                        ALTER TABLE `AtvAcadItens`
                          ADD CONSTRAINT `AtvAcadItens_ibfk_1` FOREIGN KEY (`AtvAcademica`) REFERENCES `AtvAcademicas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
                          ADD CONSTRAINT `AtvAcadItens_ibfk_2` FOREIGN KEY (`atvAcademica`) REFERENCES `AtvAcademicas` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'AtvAcadRegistros'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `AtvAcadRegistros` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `atvAcadItem` int(11) NOT NULL,
                            `aluno` int(11) NOT NULL,
                            `ano` varchar(4) NOT NULL,
                            `semestre` varchar(2) NOT NULL,
                            `CH` varchar(3) NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `AtvAcadItem` (`atvAcadItem`,`aluno`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          
            ALTER TABLE `AtvAcadRegistros`
              ADD CONSTRAINT `AtvAcadRegistros_ibfk_2` FOREIGN KEY (`atvAcadItem`) REFERENCES `AtvAcadItens` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `AtvAcadRegistros_ibfk_1` FOREIGN KEY (`AtvAcadItem`) REFERENCES `AtvAcadItens` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;");


        // ADICIONANDO ATIVIDADES EM COORDENADORES/ADM/SEC/GED
        // REMOVER AREA COORDENADORES
        $result = $this->select_all("SELECT aluno,coord,adm,sec,ged FROM Instituicoes");
        $coord = $result[0]['coord'];
        $adm = $result[0]['adm'];
        $sec = $result[0]['sec'];
        $ged = $result[0]['ged'];
        $aluno = $result[0]['aluno'];

        // ATUALIZACAO DE MENU
        $result = $this->select_all("SELECT codigo,tipo,nome,menu,permissao FROM Permissoes WHERE tipo IN ($aluno,$coord,$adm,$sec,$ged) ");
        if ($result) {
            $new_arquivo1 = 'view/secretaria/cursos/atividade_academica/atvAcadEmica.php';
            $new_arquivo2 = 'view/secretaria/cursos/atividade_academica/atvAcadItem.php';
            $new_arquivo3 = 'view/secretaria/cursos/atividade_academica/atvAcadRegistro.php';
            $new_arquivo4 = 'view/aluno/atvAcadEmica.php';
            $new_arquivo5 = 'view/secretaria/relatorios/inc/atvAcadEmica.php';
            $remove_menu = 'view/secretaria/cursos/area.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if ($aluno != $P['tipo']) {
                    if (!in_array($new_arquivo1, $P['permissao'])) {
                        $P['permissao'][] = $new_arquivo1;
                        $P['menu'][] = $new_arquivo1;
                        $P['nome'][] = 'Cadastro de Atividades';
                    }

                    if (!in_array($new_arquivo2, $P['permissao'])) {
                        $P['permissao'][] = $new_arquivo2;
                        $P['menu'][] = $new_arquivo2;
                        $P['nome'][] = 'Itens de Atividades';
                    }

                    if (!in_array($new_arquivo3, $P['permissao'])) {
                        $P['permissao'][] = $new_arquivo3;
                        $P['menu'][] = $new_arquivo3;
                        $P['nome'][] = 'Registro de Alunos';
                    }
                    
                    if (!in_array($new_arquivo5, $P['permissao'])) {
                        $P['permissao'][] = $new_arquivo5;
                        $P['menu'][] = '';
                        $P['nome'][] = 'Atividades Acadêmicas';
                    }                    
                }
                
                if ($aluno == $P['tipo']) {
                    if (!in_array($new_arquivo4, $P['permissao'])) {
                        $P['permissao'][] = $new_arquivo4;
                        $P['menu'][] = '';
                        $P['nome'][] = 'Atividades Acad&ecirc;micas';
                    }
                }

                $P2 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);
                
                if ($coord == $P['tipo']) {
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
                }



                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P2',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='429', versaoAtual='429'");
        printf("<br>Patch Academico46: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
