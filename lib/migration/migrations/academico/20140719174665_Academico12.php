<?php

require "inc/config.inc.php";

class Academico12 extends Ruckusing_Migration_Base {

    public function up() {
        // ADICIONANDO CALENDÁRIO
        $result = $this->select_all("SELECT prof,aluno FROM Instituicoes");
        $aluno = $result[0]['aluno'];
        $prof = $result[0]['prof'];

        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes WHERE tipo = $prof");
        if ($result) {
            $calendario = 'view/professor/calendario.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if (!in_array($calendario, $P['permissao'])) { 
                    $P['permissao'][] = $calendario;
                    $P['menu'][] = $calendario;
                    $P['nome'][] = 'Calend&aacute;rio Escolar';
                }

                $P1 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }

        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes WHERE tipo = $aluno");
        if ($result) {
            $calendario = 'view/aluno/calendario.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if (!in_array($calendario, $P['permissao'])) { 
                    $P['permissao'][] = $calendario;
                    $P['menu'][] = $calendario;
                    $P['nome'][] = 'Calend&aacute;rio Escolar';
                }

                $P1 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='394', versaoAtual='394'");
        printf("<br>Patch Academico12: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
