<?php

require "inc/config.inc.php";

class Academico21 extends Ruckusing_Migration_Base {

    public function up() {
        // ADICIONANDO ARQUIVO EM ALUNOS
        $result = $this->select_all("SELECT aluno FROM Instituicoes");
        $aluno = $result[0]['aluno'];

        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes WHERE tipo = $aluno");
        if ($result) {
            $novo_menu = 'view/aluno/atendimento.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if (!in_array($novo_menu, $P['permissao'])) {
                    $P['permissao'][] = $novo_menu;
                    $P['menu'][] = $novo_menu;
                    $P['nome'][] = 'Atendimento do Professor';
                }

                $P1 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='404', versaoAtual='404'");
        printf("<br>Patch Academico21: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
