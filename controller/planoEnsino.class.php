<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class PlanosEnsino extends Generic {
    
    public function __construct(){
        //
    }
    
    // USADO POR: INC/FILE.PHP
    // PARA IMPRESSAO DO PLANO EM PDF
    public function getPlano($codigo) {
        $bd = new database();
        $sql = "SELECT file, d.nome as disciplina "
                . "FROM Planos p, Atribuicoes a, Disciplinas d	"
                . "WHERE p.atribuicao = a.codigo "
                . "AND a.disciplina = d.codigo "
                . "AND atribuicao = :cod";
        $params = array(':cod'=> $codigo);
        $res = $bd->selectDB($sql, $params);
        if ( $res[0] )
        {
            return $res[0];
        }
        else
        {
            return false;
        }
    }
    
    // USADO POR: HOME.PHP
    // Verifica se o usuário tem correções para Plano
    // Pode ser colocado com função no MySQL futuramente
    public function hasChangePlano($codigo, $atribuicao=null) {
        $bd = new database();
        
        if ($atribuicao)
            $sqlAtt = " AND a.codigo = :atr";
            
        $sql = "SELECT (SELECT nome FROM Pessoas "
                . "WHERE codigo = pe.solicitante) as PlanoSolicitante,"
                . " pe.solicitacao as PlanoSolicitacao, d.nome as Disc, "
                . "a.codigo as CodAtribuicao "
                . "FROM PlanosEnsino pe, Atribuicoes a, Pessoas p, "
                . "Professores pr, Disciplinas d "
                . "WHERE pe.atribuicao = a.codigo "
                . "AND pr.atribuicao = a.codigo "
                . "AND pr.professor = p.codigo "
                . "AND d.codigo = a.disciplina "
                . "AND pe.valido = '0000-00-00 00:00:00' "
                . "AND (pe.solicitacao IS NOT NULL AND pe.solicitacao <> \"\") "
                . "AND p.codigo = :cod "
                . " $sqlAtt";

        $params = array(':cod'=> $codigo);
        if ($atribuicao) $params = array(':cod'=> $codigo, ':atr' => $atribuicao);
        $res = $bd->selectDB($sql, $params);

        if ( $res )
        {
            return $res;
        }
        else
        {
            return false;
        }
    }
    
    // USADO POR: ALUNO/PLANOENSINO.PHP
    // LISTA O PLANO DE ENSINO E PLANO DE AULA
    public function listPlanoEnsino($codigo, $validado=null) {
        $bd = new database();
        
        if ($validado) $validado = "AND (pe.valido <> '' AND pe.valido <> '0000-00-00 00:00:00')";
	$sql = "SELECT pe.numeroAulaSemanal as numeroAulaSemanal,
                pe.totalHoras as totalHoras, pe.totalAulas as totalAulas,
                pe.numeroProfessores as numeroProfessores,
		pe.ementa as ementa, pe.objetivo as objetivo, 
                pe.conteudoProgramatico as conteudoProgramatico,
                pe.metodologia as metodologia, d.numero as numero,
                pe.recursoDidatico as recursoDidatico, pe.avaliacao as avaliacao,
                pe.recuperacaoParalela as recuperacaoParalela,
                pe.recuperacaoFinal as recuperacaoFinal,
		pe.bibliografiaBasica as bibliografiaBasica,
                pe.bibliografiaComplementar as bibliografiaComplementar,
		d.nome as disciplina, d.ch as ch, d.numero as numero,
                IF(LENGTH(c.nomeAlternativo) > 0,c.nomeAlternativo, c.nome) as curso,
                m.nome as modalidade
		FROM PlanosEnsino pe, Atribuicoes a, Disciplinas d,
		Cursos c, Modalidades m, Turmas t
		WHERE pe.atribuicao = a.codigo 
		AND d.codigo = a.disciplina
		AND a.turma = t.codigo
		AND t.curso = c.codigo
		AND c.modalidade = m.codigo
		$validado
		AND a.codigo = :cod";
        $params = array(':cod'=> $codigo);
        $res = $bd->selectDB($sql, $params);

        if ( $res[0] )
        {
            return $res[0];
        }
        else
        {
            return false;
        }
    }    
}

?>