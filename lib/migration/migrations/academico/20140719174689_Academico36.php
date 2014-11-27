<?php

require "inc/config.inc.php";

class Academico36 extends Ruckusing_Migration_Base {

    public function up() {
        // ALTER COLUMN ON Calendarios
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '" . MY_DB . "'
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'ldap_drop_left'");
        if (!$result)
            $this->execute("ALTER TABLE  `Instituicoes` ADD  `ldap_drop_left` VARCHAR( 2 ) NULL AFTER  `ldap_cache`; ");
                
        // ALTERANDO NOME DE MENU
        $result = $this->select_all("SELECT codigo,nome,menu,permissao FROM Permissoes");
        if ($result) {
            $arquivo = 'view/secretaria/relatorios/listagem.php';

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
                        $novo['nome'][] = 'Relatórios (em PDF)';
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
        $this->execute("UPDATE Instituicoes SET versao='419', versaoAtual='419'");
        printf("<br>Patch Academico36: OK");
    }

//up()

    public function down() {
        
    }

//down()
}
