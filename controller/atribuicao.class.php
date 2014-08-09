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

        $sql = "SELECT d.nome as disciplina, t.numero as turma, c.nome as curso, a.status,
                c.nomeAlternativo as cursoAlt, a.bimestre as bimestre, c.fechamento as fechamento,
        	t.semestre as semestre, t.ano as ano, t.codigo as turmaCodigo, a.subturma as subturma,
                c.fechamento as fechamento, a.observacoes as observacoes, a.competencias as competencias,
                m.codigo as codModalidade, m.nome as modalidade, d.ch as CH, a.aulaPrevista as aulaPrevista,
                date_format(DATE_ADD(a.prazo, INTERVAL $LIMITE_AULA_PROF DAY), '%H:%i de %d/%m/%Y') as prazoFormat, 
                DATEDIFF(DATE_ADD(a.prazo, INTERVAL $LIMITE_AULA_PROF DAY), NOW()) as prazoDiff,
                date_format( DATE_ADD(a.dataFim, INTERVAL $LIMITE_AULA_PROF DAY), '%d/%m/%Y') as dataFimFormat,
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
    
    // USADO POR: HOME.PHP
    // INFOMRAR AO COORDENADOR PROFESSORES QUE NÃO CADASTRAM 
    // DISCIPLINAS DE ACORDO COM O LIMITE IMPOSTO EM INSTITUIÇÕES
    // --> Enviar essa query para o Banco no futuro.
    public function listProfOutOfLimitAddAula($codigo, $ano, $semestre) {
        $bd = new database();
        $sql = "SELECT p.nome as Professor, date_format(data, '%d/%m/%Y') as Data 
			FROM Pessoas p, Atribuicoes a, Professores pr, Aulas au, Turmas t, Cursos c
			WHERE p.codigo = pr.professor
			AND a.codigo = pr.atribuicao
			AND au.atribuicao = a.codigo
			AND t.codigo = a.turma
			AND t.curso = c.codigo
			AND t.semestre = :sem
			AND t.ano = :ano
			AND DATEDIFF(NOW(), au.data) > 7
			AND c.codigo IN (SELECT curso 
                        FROM Coordenadores co 
                        WHERE co.coordenador=:cod)
			GROUP BY p.codigo
			ORDER BY data ASC";
        $params = array(':cod' => $codigo, ':sem' => $semestre, ':ano' => $ano);
        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }    
}

?>