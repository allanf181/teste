<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class TiposAvaliacoes extends Generic {

    public function __construct() {
        
    }

    // USADO POR: PROFESSOR/AVALIACAO.PHP
    public function listTiposAvaliacoes($atribuicao, $calculo, $PONTO, $pontos, $tipo) {
        $bd = new database();

        if ($tipo == 'recuperacao' || $tipo == 'avaliacao' || !$tipo)
            $sqlAdicional = " AND ( t.tipo = 'avaliacao' OR t.tipo = 'recuperacao' ) ";
        else
            $sqlAdicional = " AND t.tipo = '$tipo'";

        $sql = "SELECT t.codigo as codigo, t.nome as nome, t.tipo as tipo, t.final, a.bimestre
		FROM TiposAvaliacoes t, Modalidades m, Cursos c, Atribuicoes a, Turmas tu 
		WHERE t.modalidade = m.codigo 
		AND m.codigo = c.modalidade 
		AND a.turma = tu.codigo 
		AND tu.curso = c.codigo 
		AND a.codigo = :cod
		AND ( (t.final = 0 OR t.final IS NULL AND a.bimestre < 4) OR (a.bimestre = 4))
		AND t.tipo NOT IN (SELECT t1.tipo FROM Avaliacoes a1, TiposAvaliacoes t1 
		WHERE a1.tipo= t1.codigo 
		AND a1.tipo = t.codigo 
		AND a1.atribuicao = :cod
		AND t1.tipo = 'recuperacao' 
		AND t1.final = 0)
                $sqlAdicional
		ORDER BY t.nome";
        $params = array(':cod' => $atribuicao);
        $res = $bd->selectDB($sql, $params);
        
        if ($res) {
            foreach ($res as $reg) {
                if ($reg['bimestre'] == 4 && $tipo == 'avaliacao' && $reg['final'])
                    continue;
                
                if ($reg['bimestre'] == 4 && $tipo == 'recuperacao' && !$reg['final'])
                    continue;

                // MOSTRA AS AVALIACOES CASO AINDA NAO ATINGIU OS PONTOS NO CASO
                // DE PESO E MEDIA
                if ( ($calculo == 'peso' || $calculo == 'soma') 
                        && $pontos < $PONTO && $reg['tipo'] != 'recuperacao') {
                    $new[$reg['codigo']]['codigo'] = $reg['codigo'];
                    $new[$reg['codigo']]['nome'] = $reg['nome'];
                    $new[$reg['codigo']]['tipo'] = $reg['tipo'];
                } 
                // MOSTRAS AS RECUPERACOES SE OS PONTOS FORAM ATINGIDOS
                if (($calculo == 'peso' || $calculo == 'soma')
                        && $pontos >= $PONTO && $reg['tipo'] == 'recuperacao') {
                    $new[$reg['codigo']]['codigo'] = $reg['codigo'];
                    $new[$reg['codigo']]['nome'] = $reg['nome'];
                    $new[$reg['codigo']]['tipo'] = $reg['tipo'];
                }

                // CASO NAO TENHA NENHUMA AVALIACAO, OBRIGA A CADASTRAR
                // A PRIMEIRA ANTES DA RECUPERACAO
                if (($calculo == 'media' || $calculo == 'formula')
                        && !$tipo && $reg['tipo'] != 'recuperacao') {
                    $new[$reg['codigo']]['codigo'] = $reg['codigo'];
                    $new[$reg['codigo']]['nome'] = $reg['nome'];
                    $new[$reg['codigo']]['tipo'] = $reg['tipo'];
                }
                if (($calculo == 'media' || $calculo == 'formula')
                        && $tipo) {
                    $new[$reg['codigo']]['codigo'] = $reg['codigo'];
                    $new[$reg['codigo']]['nome'] = $reg['nome'];
                    $new[$reg['codigo']]['tipo'] = $reg['tipo'];
                }                
            }

            return $new;
        } else {
            return false;
        }
    }

}

?>