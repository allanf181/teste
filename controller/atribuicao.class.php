<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Atribuicoes extends Generic {

    // USADO POR: ALUNO/ALUNO.PHP
    // Retorna dados da atribuicao (Disciplina, Turma, etc..)
    // Pode ser colocado com função no MySQL futuramente
    public function getAtribuicao($codigo) {
        $bd = new database();
        $sql = "select d.nome as disciplina, t.numero as turma, c.nome as curso,
                c.nomeAlternativo as cursoAlt, a.bimestre as bimestre, c.fechamento as fechamento,
        	t.semestre as semestre, t.ano as ano, t.codigo as turmaCodigo
                from Disciplinas d, Turmas t, Cursos c, Turnos tu, Modalidades m, Atribuicoes a
                where a.disciplina=d.codigo
                and d.curso=c.codigo
                and a.turma=t.codigo
                and t.turno=tu.codigo
                and m.codigo = c.modalidade
                and a.codigo=:cod";
        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res[0];
        } else {
            return false;
        }
    }
    
    // LISTA AS ATRIBUICOES DO PROFESSOR/ALUNO
    // USADO POR: INDEX.PHP (PARA MONTAR O MENU DO PROFESSOR)
    public function listAtribuicoes($codigo, $papel) {
        $bd = new database();
        
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
				ORDER BY d.numero, at.bimestre";        
        $params = array(':cod'=> $codigo);
        $res = $bd->selectDB($$papel, $params);
        if ( $res )
        {
            foreach ($res as $reg) {
                $reg['disciplina'] = $reg['disciplina'];
                $reg['curso'] = $reg['curso'];
                if (isset($reg['evento']) && !$reg['subturma']) $reg['subturma'] = $reg['evento'];
                if (isset($reg['subturma'])) $reg['numero']=$reg['numero'].' ['.$reg['subturma'].']';
            					
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
                    $disciplinas[$reg['ano']][$reg['semestre']][$reg['bimestre']][$reg['atribuicao']] = array($reg['disciplina'],$reg['atribuicao'],$reg['numero'],$reg['curso']);
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