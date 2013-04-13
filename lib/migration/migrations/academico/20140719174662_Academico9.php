<?php

require "inc/config.inc.php";

class Academico9 extends Ruckusing_Migration_Base {

    public function up() {
        // ADICIONANDO NOVO RELATÓRIO
        $result = $this->select_all("SELECT prof,aluno FROM Instituicoes");
        $aluno = $result[0]['aluno'];
        $prof = $result[0]['prof'];

        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes WHERE tipo NOT IN ($prof,$aluno)");
        if ($result) {
            $ausencias = 'view/secretaria/relatorios/ausencias.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if (!in_array($ausencias, $P['permissao'])) { 
                    $P['permissao'][] = $ausencias;
                    $P['menu'][] = $ausencias;
                    $P['nome'][] = 'Aus&ecirc;ncias';
                }

                $P1 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='391', versaoAtual='391'");
        printf("<br>Patch Academico9: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
