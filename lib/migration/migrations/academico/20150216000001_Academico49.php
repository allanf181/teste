<?php

require "inc/config.inc.php";

class Academico49 extends Ruckusing_Migration_Base {

    public function up() {
        //REMOVENDO TDs CADASTRADAS COM ERRO
        $this->execute("DELETE FROM TDDados WHERE duracaoAula = ''");
            
        // CREATE TABLE
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.TABLES 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Chat'");
        if (!$result)
            $this->execute("CREATE TABLE IF NOT EXISTS `Chat` (
                            `codigo` int(11) NOT NULL AUTO_INCREMENT,
                            `atribuicao` int(11) NOT NULL,
                            `prontuario` varchar(45) COLLATE latin1_general_ci NOT NULL,
                            `para` varchar(45) COLLATE latin1_general_ci NOT NULL,
                            `mensagem` varchar(1000) COLLATE latin1_general_ci NOT NULL,
                            `visualizado` char(1) COLLATE latin1_general_ci NOT NULL,
                            `data` datetime NOT NULL,
                            PRIMARY KEY (`codigo`),
                            KEY `atribuicao` (`atribuicao`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

                          ALTER TABLE `Chat`
                            ADD CONSTRAINT `Chat_ibfk_1` FOREIGN KEY (`atribuicao`) REFERENCES `Atribuicoes` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE; ");

        //ADD CHAT PARA ALUNO E PROFESSOR
        $result = $this->select_all("SELECT prof,aluno FROM Instituicoes");
        $prof = $result[0]['prof'];
        $aluno = $result[0]['aluno'];

        // ALTERANDO A FTD,CIDADES E ESTADOS DE MENU.
        $result = $this->select_all("SELECT codigo,tipo,nome,menu,permissao FROM Permissoes WHERE tipo IN ($aluno,$prof)");

        if ($result) {
            $new_aluno = 'view/aluno/chat.php';
            $new_prof = 'view/professor/chat.php';

            foreach ($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if ($P['tipo'] == $prof) {
                    if (!in_array($new_prof, $P['permissao'])) {
                        $P['permissao'][] = $new_prof;
                        $P['menu'][] = '';
                        $P['nome'][] = 'Chat (Atendimento)';
                    }
                }
                
                if ($P['tipo'] == $aluno) {
                    if (!in_array($new_aluno, $P['permissao'])) {
                        $P['permissao'][] = $new_aluno;
                        $P['menu'][] = '';
                        $P['nome'][] = 'Chat (Atendimento)';
                    }
                }                

                $P1 = implode(",", $P['permissao']);
                $M1 = implode(",", $P['menu']);
                $N1 = implode(",", $P['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='433', versaoAtual='433'");
        printf("<br>Patch Academico49: OK");
    }

    //up()
    public function down() {
        
    }

    //down()
}
