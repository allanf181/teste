<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Atualizacoes extends Generic {

    // USADO POR: ADMIN/LOGS.PHP
    public function listAtualizacoes() {
        $bd = new database();

        $res = array();
        
        $res[0] = array('title' => 'Alunos', 'file' => 'db2Alunos', 'tipo' => '1');
        $res[1] = array('title' => 'Professores', 'file' => 'db2Professores', 'tipo' => '2');
        $res[2] = array('title' => 'Hor&aacute;rios e Feriados', 'file' => 'db2Horarios', 'tipo' => '11');
        $res[3] = array('title' => 'Cursos Novos', 'file' => 'db2CursosDisciplinasNovos', 'tipo' => '3');
        $res[4] = array('title' => 'Turmas (Cursos Novos)', 'file' => 'db2TurmasNovos', 'tipo' => '5');
        $res[5] = array('title' => 'Atribui&ccedil;&otilde;es (Cursos Novos)', 'file' => 'db2AtribuicoesNovos', 'tipo' => '7');
        $res[6] = array('title' => 'Matr&iacute;culas (Cursos Novos)', 'file' => 'db2MatriculasNovos', 'tipo' => '9');
        $res[7] = array('title' => 'Cursos Antigos', 'file' => 'db2CursosDisciplinas', 'tipo' => '4');
        $res[8] = array('title' => 'Turmas (Cursos Antigos)', 'file' => 'db2Turmas', 'tipo' => '6');
        $res[9] = array('title' => 'Atribui&ccedil;&otilde;es (Cursos Antigos)', 'file' => 'db2Atribuicoes', 'tipo' => '8');
        $res[10] = array('title' => 'Matr&iacute;culas (Cursos Antigos)', 'file' => 'db2Matriculas', 'tipo' => '10');
        $res[11] = array('title' => 'Dispensas', 'file' => 'db2Dispensas', 'tipo' => '13');
        $res[12] = array('title' => 'Digita Notas', 'file' => 'db2DigitaNotas', 'tipo' => '12');
        $res[13] = array('title' => 'Consulta Roda', 'file' => 'db2ConsultaDisciplinas', 'tipo' => '14');
 
        for ($i = 0; $i < count($res); $i++) {
            $sql = "SELECT p.nome, a.tipo, date_format(a.data, '%d/%m/%Y') as data "
                    . "FROM Atualizacoes a, Pessoas p "
                    . "WHERE (a.tipo=:tipo1 OR a.tipo=:tipo2) "
                    . "AND a.pessoa=p.codigo  "
                    . "ORDER BY a.codigo DESC LIMIT 1";

            $params = array('tipo1'=> $res[$i]['tipo'], 'tipo2' => ($res[$i]['tipo']+100));
            $res1 = $bd->selectDB($sql, $params);
            $res[$i]['rotulo'] = "Importar";
            $res[$i]['situacao'] = "Dados nunca importados manualmente.";
            if ($res1[0]) {
                if ($res1[0]['tipo'] >= 100)
                    $res1[0]['nome'] = 'CRON';
                $ultimaAtualizacao = $res1[0]['data'] . " por " . $res1[0]['nome'];
                $res[$i]['rotulo'] = "Atualizar";
                $res[$i]['situacao'] = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
            }
        }

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

}

?>