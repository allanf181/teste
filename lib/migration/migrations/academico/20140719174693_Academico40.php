<?php

require "inc/config.inc.php";

class Academico40 extends Ruckusing_Migration_Base {

    public function up() {
        // CREATE TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Atendimento'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `Atendimento` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `pessoa` int(11) NOT NULL,
                            `ano` varchar(4) NOT NULL,
                            `semestre` varchar(2) NOT NULL,
                            `horario` text NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `pessoa` (`pessoa`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

                          ALTER TABLE  `Atendimento` ADD FOREIGN KEY (  `pessoa` ) REFERENCES  `academico`.`Pessoas` (
                          `codigo`
                          ) ON DELETE CASCADE ON UPDATE CASCADE ;");


        // ADICIONANDO ATENDIMENTO PARA PROFESSOR
        $result = $this->select_all("SELECT prof FROM Instituicoes");
        $prof = $result[0]['prof'];

        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes WHERE tipo = $prof");
        if ($result) {
            $new_arquivo = 'view/professor/atribuicao/atendimento.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if (!in_array($new_arquivo, $P['permissao'])) {
                    $P['permissao'][] = $new_arquivo;
                    $P['menu'][] = $new_arquivo;
                    $P['nome'][] = 'Atendimento ao Aluno';
                }

                $P1 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }

        // ALTERANDO NOME DE MENU
        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes");
        if ($result) {
            $arquivo = 'view/professor/aulaTroca.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                $i = 0;
                $novo['permissao'] = array();
                $novo['menu'] = array();
                $novo['nome'] = array();
                foreach ($P['permissao'] as $perm) {
                    if ($perm == $arquivo) {
                        $novo['permissao'][] = $arquivo;
                        $novo['menu'][] = $arquivo;
                        $novo['nome'][] = 'Aulas (Trocas/Reposi&ccedil;&otilde;es)';
                    } else {
                        $novo['permissao'][] = $P['permissao'][$i];
                        $novo['menu'][] = $P['menu'][$i];
                        $novo['nome'][] = $P['nome'][$i];
                    }
                    $i++;
                }

                $P1 = implode(",", $novo['permissao']);
                $M1 = implode(",", $novo['menu']);
                $N1 = implode(",", $novo['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }
        
        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='423', versaoAtual='423'");
        printf("<br>Patch Academico40: OK");        
    }
    //up()

    public function down() {
        
    }
    //down()
}
