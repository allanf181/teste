<?php

require "inc/config.inc.php";

class Academico43 extends Ruckusing_Migration_Base {

    public function up() {
        // ADICIONANDO BOLETIM.PHP EM PROFESSORES
        // REMOVER FTD PROFESSORES
        // ADICIONAR FPA, PIT, RIT COORDENADORES, GED, SEC e ADMIN
        $result = $this->select_all("SELECT prof, aluno FROM Instituicoes");
        $prof = $result[0]['prof'];
        $aluno = $result[0]['aluno'];

        // ATUALIZACAO DE MENU
        $result = $this->select_all("SELECT codigo,tipo,nome,menu,permissao FROM Permissoes WHERE tipo <> $aluno ");
        if ($result) {
            $P = array();
            foreach ($result as $P) {
                $P1 = array();
                $P2 = array();
                $M1 = array();
                $N1 = array();

                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if ($prof != $P['tipo']) {
                    if (in_array('view/secretaria/relatorios/boletimTurma.php', $P['permissao']) && !in_array('view/secretaria/relatorios/boletim.php', $P['permissao'])) {
                        $P['permissao'][] = 'view/secretaria/relatorios/boletim.php';
                        $P['menu'][] = 'view/secretaria/relatorios/boletim.php';
                        $P['nome'][] = 'Boletim do Aluno';
                    }
                } else {
                    if (!in_array('view/professor/boletim.php', $P['permissao'])) {
                        $P['permissao'][] = 'view/professor/boletim.php';
                        $P['menu'][] = '';
                        $P['nome'][] = 'Boletim do Aluno';
                    }
                }

                $i = 0;
                foreach ($P['permissao'] as $menu) {
                    // AJUSTANDO MENUS
                    if ($menu != 'view/aluno/boletim.php') {
                        $P1['permissao'][] = $menu;
                        $P1['menu'][] = $P['menu'][$i];
                        $P1['nome'][] = $P['nome'][$i];
                    } else {
                        if (!in_array('view/secretaria/relatorios/boletim.php', $P['permissao'])) {
                            $P['permissao'][] = 'view/secretaria/relatorios/boletim.php';
                            $P['menu'][] = '';
                            $P['nome'][] = 'Boletim do Aluno';
                        }
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
        $this->execute("UPDATE Instituicoes SET versao='426', versaoAtual='426'");
        printf("<br>Patch Academico43: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
