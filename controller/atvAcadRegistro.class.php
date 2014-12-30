<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class AtvAcadRegistros extends Generic {

    public function __construct() {
        //
    }

    // SOBREPOSIÇÃO DO MÉTODO, NECESSÁRIO PARA VERIFICAR
    // PENDÊNCIAS ANTES DE INSERIR.
    // USADO POR: CURSOS/ATIVIDADES_ACADEMICAS/REGISTROS.PHP
    public function insertOrUpdateReg($params) {
        $bd = new database();

        $paramsReg = array('atvAcademica' => dcrip($params['atvAcademica']),
            'aluno' => dcrip($params['aluno']),
            'ano' => dcrip($params['ano']),
            'semestre' => dcrip($params['semestre']));

        $sql = "SELECT aa.CHTotal, aa.CHminSem, aa.CHmaxSem,
                        (SELECT SUM(ra2.CH) FROM AtvAcadRegistros ra2, AtvAcadItens ai2
                            WHERE ra2.atvAcadItem = ai2.codigo
                            AND ra2.aluno = :aluno
                            AND ra2.ano = :ano
                            AND ra2.semestre = :semestre
                            AND ai2.atvAcademica = :atvAcademica
                            GROUP BY ai2.atvAcademica, ra2.aluno
                        ) as CHSem,
                        (SELECT SUM(ra1.CH) FROM AtvAcadRegistros ra1, AtvAcadItens ai1
                            WHERE ra1.atvAcadItem = ai1.codigo
                            AND ra1.aluno = :aluno
                             AND ai1.atvAcademica = :atvAcademica
                            GROUP BY ai1.atvAcademica, ra1.aluno
                        ) as CHCurso
                FROM AtvAcademicas aa
                WHERE aa.codigo = :atvAcademica";

        $res = $bd->selectDB($sql, $paramsReg);

        if ($params['codigo']) {
            $res[0]['CHSem'] = $res[0]['CHSem'] - dcrip($params['CHAnt']);
            $res[0]['CHCurso'] = $res[0]['CHCurso'] - dcrip($params['CHAnt']);
        }

        if ($res && ($res[0]['CHSem'] + $params['CH']) > $res[0]['CHmaxSem']) {
            $rs['TIPO'] = 'CHSEM';
            $rs['RESULTADO'] = '';
            $rs['STATUS'] = 'ERRO';
            return $rs;
        }

        if ($res && ($res[0]['CHCurso'] + $params['CH']) > $res[0]['CHTotal']) {
            $rs['TIPO'] = 'CHCURSO';
            $rs['RESULTADO'] = '';
            $rs['STATUS'] = 'ERRO';
            return $rs;
        }

        unset($params['atvAcademica']);
        unset($params['CHAnt']);

        $res = $this->insertOrUpdate($params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // LISTA OS REGISTROS DAS ATIVIDADES ACADÊMICAS CADASTRADAS
    // USADO POR: CURSOS/ATIVIDADES_ACADEMICAS/REGISTROS.PHP
    public function listRegistros($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT ra.codigo, aa.nome as atividade, ra.aluno, ra.ano, ra.semestre, ra.CH, ai.tipo,"
                . "aa.codigo as atvAcademica, atvAcadItem, ai.atividade as item, p.nome as aNome, ai.CHLimite  "
                . "FROM AtvAcadItens ai, AtvAcademicas aa, AtvAcadRegistros ra, Pessoas p "
                . "WHERE aa.codigo = ai.atvAcademica "
                . "AND ra.atvAcadItem = ai.codigo "
                . "AND ra.aluno = p.codigo ";

        $sql .= " $sqlAdicional ";

        $sql .= " ORDER BY p.nome, aa.nome, ai.atividade, ano, semestre ";

        $sql .= "$nav";
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // LISTA A SITUAÇÃO DO ALUNO EM RELAÇÃO ÀS ATIVIDADES ACADÊMICAS
    // USADO POR: CURSOS/ATIVIDADES_ACADEMICAS/REGISTROS.PHP
    public function listSituacao($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT p.nome, aa.nome as atividade, CONCAT(ra.semestre, '/', ra.ano) 
                        as semAno, aa.CHTotal, aa.CHminSem, aa.CHmaxSem, SUM(ra.CH) CHSem,
                        aa.CHminCientifica, aa.CHminCultural, aa.CHminAcademica,aa.codigo,
                        p.codigo as aluno,
                        (SELECT SUM(ra1.CH) 
                            FROM AtvAcadRegistros ra1, AtvAcadItens ai1
                            WHERE ra1.atvAcadItem = ai1.codigo
                            AND ra1.aluno = ra.aluno
                            AND ai1.atvAcademica = ai.atvAcademica
                            GROUP BY ai1.atvAcademica, ra1.aluno
                        ) as CHCurso,
                        (SELECT SUM(ra1.CH) 
                            FROM AtvAcadRegistros ra1, AtvAcadItens ai1
                            WHERE ra1.atvAcadItem = ai1.codigo
                            AND ra1.aluno = ra.aluno
                            AND ai1.atvAcademica = ai.atvAcademica
                            AND ai1.tipo = 'Acadêmica'
                            GROUP BY ai1.atvAcademica, ra1.aluno, ai1.tipo
                        ) as CHAcademica,
                        (SELECT SUM(ra1.CH) 
                            FROM AtvAcadRegistros ra1, AtvAcadItens ai1
                            WHERE ra1.atvAcadItem = ai1.codigo
                            AND ra1.aluno = ra.aluno
                            AND ai1.atvAcademica = ai.atvAcademica
                            AND ai1.tipo = 'Cultural'
                            GROUP BY ai1.atvAcademica, ra1.aluno, ai1.tipo
                        ) as CHCultural,
                        (SELECT SUM(ra1.CH) 
                            FROM AtvAcadRegistros ra1, AtvAcadItens ai1
                            WHERE ra1.atvAcadItem = ai1.codigo
                            AND ra1.aluno = ra.aluno
                            AND ai1.atvAcademica = ai.atvAcademica
                            AND ai1.tipo = 'Científica'
                            GROUP BY ai1.atvAcademica, ra1.aluno, ai1.tipo
                        ) as CHCientifica
                FROM AtvAcadRegistros ra, AtvAcadItens ai, AtvAcademicas aa, Pessoas p
                WHERE aa.codigo = ai.atvAcademica
                AND ra.atvAcadItem = ai.codigo
                AND ra.aluno = p.codigo
                $sqlAdicional
                GROUP BY aa.codigo, ra.aluno, ra.ano, ra.semestre";

        $sql .= " ORDER BY p.nome, ano, semestre ";

        $sql .= "$nav";
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    // RETORNA O STATUS DO ALUNO EM RELAÇÃO AS ATIVIDADES ACADEMICAS
    public function status($reg) {
        if ($reg['CHSem'] < $reg['CHminSem'])
            $ret = 'Carga hor&aacute;ria do semestre ainda n&atilde;o foi completada.';

        if ($reg['CHCientifica'] < $reg['CHminCientifica']) {
            if ($ret) $ret .= '<br>';
            $ret .= 'Carga hor&aacute;ria cient&iacute;fica do curso ainda n&atilde;o foi completada.';
        }

        if ($reg['CHCultural'] < $reg['CHminCultural']) {
            if ($ret) $ret .= '<br>';
            $ret .= 'Carga hor&aacute;ria cultural do curso ainda n&atilde;o foi completada.';
        }

        if ($reg['CHAcademica'] < $reg['CHminAcademica']) {
            if ($ret) $ret .= '<br>';
            $ret .= 'Carga hor&aacute;ria acad&ecirc;mica do curso ainda n&atilde;o foi completada.';
        }
    
        if ($reg['CHCurso'] < $reg['CHTotal']) {
            if ($ret) $ret .= '<br>';
            $ret .= 'Carga hor&aacute;ria total do curso ainda n&atilde;o foi completada.';
        }
        
        return $ret;
    }

}

?>