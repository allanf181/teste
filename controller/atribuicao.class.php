<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Atribuicoes extends Generic {

    // USADO POR: PROFESSOR/AVALIACAO.PHP
    // Retorna dados da atribuicao (Disciplina, Turma, etc..)
    // Pode ser colocado com função no MySQL futuramente
    public function insertIfNotCalculo($codigo) {
        $bd = new database();

        $sql = "SELECT calculo FROM Atribuicoes a WHERE a.codigo = :cod";
        $params = array(':cod' => $codigo);

        $res = $bd->selectDB($sql, $params);
        if ($res[0]['calculo'] == null) {
            $params = array('codigo' => crip($codigo), 'calculo' => crip('peso'));
            $res = $this->insertOrUpdate($params);
            return true;
        } else {
            return false;
        }
    }
    
    // USADO POR: ALUNO/ALUNO.PHP, PROFESSOR/PROFESSOR.PHP, PROFESSOR/PLANO.PHP
    // Retorna dados da atribuicao (Disciplina, Turma, etc..)
    // Pode ser colocado com função no MySQL futuramente
    public function getAtribuicao($codigo, $LIMITE_AULA_PROF=0) {
        $bd = new database();
        
        $LIMITE_AULA_PROF1 = $LIMITE_AULA_PROF * 2;
        
        $sql = "SELECT d.nome as disciplina, t.numero as turma, c.nome as curso, a.status, a.prazo,
                c.nomeAlternativo as cursoAlt, a.bimestre as bimestre, c.fechamento as fechamento,
        	t.semestre as semestre, t.ano as ano, t.codigo as turmaCodigo, a.subturma as subturma,
                c.fechamento as fechamento, a.observacoes as observacoes, a.competencias as competencias,
                m.codigo as codModalidade, m.nome as modalidade, d.ch as CH, a.aulaPrevista as aulaPrevista,
                IF(a.prazo, date_format(a.prazo, '%H:%i de %d/%m/%Y'), '') as prazoFormat, 
                DATEDIFF(a.prazo, NOW()) as prazoDiff,
                date_format( DATE_ADD(a.dataFim, INTERVAL $LIMITE_AULA_PROF DAY), '%d/%m/%Y') as dataFimFormat,
                date_format( DATE_SUB(NOW(), INTERVAL $LIMITE_AULA_PROF1 DAY), '%d/%m/%Y') as dataInicioCal,
                date_format( a.prazo, '%d/%m/%Y') as dataFimCal,
                DATEDIFF( DATE_ADD(a.dataFim, INTERVAL $LIMITE_AULA_PROF DAY), NOW()) as dataFimDiff
                FROM Disciplinas d, Turmas t, Cursos c, Turnos tu, Modalidades m, Atribuicoes a
                WHERE a.disciplina=d.codigo
                AND t.curso=c.codigo
                AND a.turma=t.codigo
                AND t.turno=tu.codigo
                AND m.codigo = c.modalidade
                AND a.codigo=:cod";
        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            if ($res[0]['prazoFormat']) {
                $res[0]['inicioCalendar'] = $res[0]['dataInicioCal'];
                $res[0]['fimCalendar'] = $res[0]['dataFimCal'];
            } else {
                $res[0]['inicioCalendar'] = date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") - $LIMITE_AULA_PROF, date("Y")));
                $res[0]['fimCalendar'] = date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") + $LIMITE_AULA_PROF, date("Y")));
            }
            return $res[0];
        } else {
            return false;
        }
    }
    
    // LISTA AS ATRIBUICOES DO PROFESSOR/ALUNO
    // USADO POR: INDEX.PHP (PARA MONTAR O MENU DO PROFESSOR)
    public function listAtribuicoes($codigo, $papel, $menu=null) {
        $bd = new database();

        $ANO = $_SESSION["ano"];

        $professor = "SELECT t.ano as ano, t.semestre as semestre, a.bimestre as bimestre, a.codigo as atribuicao, d.nome as disciplina,
			d.numero as numero, c.nome as curso, t.numero as turma, a.subturma as subturma,
			a.eventod as evento, c.nomeAlternativo as cursoAlt, m.codigo as codigoModalidade, m.nome as modalidade
			FROM Turnos tt, Disciplinas d, Turmas t, Atribuicoes a, Professores pr, Cursos c, Modalidades m
			WHERE tt.codigo = t.turno 
			AND d.codigo = a.disciplina 
			AND t.codigo = a.turma
			AND c.codigo = t.curso 
			AND m.codigo = c.modalidade
			AND pr.atribuicao = a.codigo
			AND pr.professor=:cod 
                        AND (t.semestre=1 OR t.semestre=2 OR t.semestre=0)
                        AND t.ano = $ANO
			ORDER BY t.ano,t.semestre,a.bimestre,d.numero,a.grupo";

        $aluno = "SELECT t.ano as ano, t.semestre as semestre, at.bimestre as bimestre,
                                at.codigo as atribuicao, d.nome as disciplina,
                                d.numero as numero, tu.nome as turma, c.nome as curso,
                                c.nomeAlternativo as cursoAlt
				FROM Matriculas m, Turmas t, Pessoas p, Cursos c, Turnos tu, Atribuicoes at, Disciplinas d 
				WHERE m.aluno=p.codigo 
				AND at.turma=t.codigo 
				AND m.atribuicao=at.codigo
				AND t.curso=c.codigo
				AND t.turno=tu.codigo
				AND at.disciplina = d.codigo
				AND p.codigo=:cod
                                AND (t.semestre=1 OR t.semestre=2 OR t.semestre=0)
                                AND t.ano = $ANO
				ORDER BY d.numero, at.bimestre";        
        $params = array(':cod'=> $codigo);
        $res = $bd->selectDB($$papel, $params);
        if (!$menu) return $res;
        
        if ( $res )
        {
            foreach ($res as $reg) {
                $reg['disciplina'] = $reg['disciplina'];
                $reg['curso'] = $reg['curso'];
                if (isset($reg['evento']) && !$reg['subturma']) $reg['subturma'] = $reg['evento'];
                if (isset($reg['subturma'])) $reg['numero']=$reg['numero'].' ['.$reg['turma'].'-'.$reg['subturma'].']';
            					
                $curso=($reg['cursoAlt']) ? $reg['cursoAlt'] : $reg['curso'];
                if (isset($reg['codigoModalidade'])) {
                    if ( ($reg['codigoModalidade'] < 1000 || $reg['codigoModalidade'] >= 2000) && !$reg['cursoAlt']) 
                        $curso = $reg['curso'] .'-'. $reg['modalidade'];
                }
            
                // SE FOR ANUAL
                if ($reg['semestre'] == 0)
                    $disciplinas[$reg['ano']]['A']['A'][$reg['atribuicao']] = array($reg['disciplina'],$reg['atribuicao'],$reg['numero'],$reg['curso']);
                
                // SE FOR SEMESTRAL
                if ($reg['bimestre'] == 0)
                    $disciplinas[$reg['ano']][$reg['semestre']]['S'][$reg['atribuicao']] = array($reg['disciplina'],$reg['atribuicao'],$reg['numero'],$reg['curso']);

                // SE FOR BIMESTRAL
                if ($reg['bimestre'] != 0) 
                    $disciplinas[$reg['ano']][$reg['semestre']]['B'][$reg['bimestre']][$reg['atribuicao']] = array($reg['disciplina'],$reg['atribuicao'],$reg['numero'],$reg['curso']);
            }
            return $disciplinas;
        }
        else
        {
            return false;
        }
    }
}

?>