<?php

require "inc/config.inc.php";

class Academico29 extends Ruckusing_Migration_Base {

    public function up() {
        // ALTER COLUMN ON Atribuicoes
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Atribuicoes' 
                                    AND COLUMN_NAME = 'grupo'");
        if ($result)
            $this->execute("ALTER TABLE  `Atribuicoes` CHANGE  `grupo`  `periodo` INT( 11 ) NULL DEFAULT NULL ;");
        
        // ADICIONANDO ARQUIVO EM PROFESSORES
        $result = $this->select_all("SELECT prof FROM Instituicoes");
        $prof = $result[0]['prof'];

        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes WHERE tipo = $prof");
        if ($result) {
            $new_arquivo = 'view/professor/chamada.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if (!in_array($new_arquivo, $P['permissao'])) {
                    $P['permissao'][] = $new_arquivo;
                    $P['menu'][] = '';
                    $P['nome'][] = 'Lista de Chamada';
                }

                $P1 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='412', versaoAtual='412'");
        printf("<br>Patch Academico29: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
