<?php

require "inc/config.inc.php";

class Academico23 extends Ruckusing_Migration_Base {

    public function up() {
        // DROP TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'PrazosAulas'");
        if ($result)
            $this->execute("DROP TABLE `PrazosAulas`;");

        // ADD COLUMN ON PrazosDiarios
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'PrazosDiarios' 
                                    AND COLUMN_NAME = 'dataConcessao'");
        if (!$result)
            $this->execute("ALTER TABLE  `PrazosDiarios` ADD  `dataConcessao` DATETIME NULL AFTER  `motivo`;");

        // AJUSTE PARA NOVA TABELA
        $this->execute("UPDATE `PrazosDiarios` SET dataConcessao = NOW();");
        
        // ATUALIZACAO DE MENU
        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes");
        if ($result) {
            $remove_menu = 'view/secretaria/prazos/aula.php';
            $novo_menu = 'view/professor/diario.php';

            $P = array();

            foreach ($result as $P) {
                $P1 = array();
                $P2 = array();
                $M1 = array();
                $N1 = array();

                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if (!in_array($novo_menu, $P['permissao'])) {
                    $P['permissao'][] = $novo_menu;
                    $P['menu'][] = '';
                    $P['nome'][] = 'Di&aacute;rios';
                }

                $i = 0;
                foreach ($P['permissao'] as $menu) {
                    //ALTERANDO NOME MENU
                    if ($menu == 'view/secretaria/prazos/diario.php')
                        $P['nome'][$i] = 'Di&aacute;rios';

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

            // ATUALIZAR VERSAO ATUAL
            $this->execute("UPDATE Instituicoes SET versao='406', versaoAtual='406'");
            printf("<br>Patch Academico23: OK");
        }

//up()

        public function down() {
            
        }

//down()
    }
    