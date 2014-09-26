<?php

require "inc/config.inc.php";

class Academico24 extends Ruckusing_Migration_Base {

    public function up() {
        // DROP COLUMN ON PrazosDiarios
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'limiteInsAulaProf'");
        if ($result)
            $this->execute("ALTER TABLE `Instituicoes` DROP `limiteInsAulaProf`;");

        // ADICIONANDO DIARIO PROFESSORES
        $result = $this->select_all("SELECT sec, prof, adm FROM Instituicoes");
        $prof = $result[0]['prof'];
        $sec = $result[0]['sec'];
        $adm = $result[0]['adm'];

        // ATUALIZACAO DE MENU
        $result = $this->select_all("SELECT codigo,tipo,nome,menu,permissao FROM Permissoes");
        if ($result) {
            $menu1 = 'view/secretaria/cursos/notasFinais.php';
            $menu2 = 'view/secretaria/relatorios/atendimento.php';
            $menu3 = 'view/aluno/boletim.php';
            $menu4 = 'view/secretaria/atendimento.php';
            $menu5 = 'view/professor/diario.php';

            $P = array();

            foreach ($result as $P) {
                $P1 = array();
                $P3 = array();
                $P2 = array();
                $M1 = array();
                $N1 = array();

                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);

                if ($prof == $P['tipo']) {
                    if (!in_array($menu3, $P['permissao'])) {
                        $P['permissao'][] = $menu3;
                        $P['menu'][] = '';
                        $P['nome'][] = 'Boletim';
                    }
                }
                if ($sec == $P['tipo'] || $adm == $P['tipo']) {
                    if (!in_array($menu1, $P['permissao'])) {
                        $P['permissao'][] = $menu1;
                        $P['menu'][] = $menu1;
                        $P['nome'][] = 'Notas Finais';
                    }
                    if (!in_array($menu2, $P['permissao'])) {
                        $P['permissao'][] = $menu2;
                        $P['menu'][] = $menu2;
                        $P['nome'][] = 'Atendimento do Professor';
                    }
                }
                
                $i=0;
                foreach ($P['permissao'] as $menu) {
                    // REMOVENDO MENU
                    if ($menu != $menu4 && $menu != $menu5) {
                        $P1[$menu]['permissao'] = $menu;
                        $P1[$menu]['menu'] = $P['menu'][$i];
                        $P1[$menu]['nome'] = $P['nome'][$i];
                    } else if ($menu == $menu5 && $prof == $P['tipo']) {
                        $P1[$menu]['permissao'] = $menu;
                        $P1[$menu]['menu'] = $P['menu'][$i];
                        $P1[$menu]['nome'] = $P['nome'][$i];
                    }                    
                    $i++;
                }
                
                $i=0;
                foreach ($P1 as $k=>$v) {
                    // REMOVENDO MENU
                    $P3['permissao'][] = $k;
                    $P3['menu'][] = $v['menu'];
                    $P3['nome'][] = $v['nome'];
                    $i++;
                }
 
                $P2 = implode(",", $P3['permissao']);
                $M1 = implode(",", $P3['menu']);
                $N1 = implode(",", $P3['nome']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P2',nome='$N1' WHERE codigo = " . $P['codigo']);
            }
        }

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='407', versaoAtual='407'");
        printf("<br>Patch Academico24: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
