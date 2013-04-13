<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON"."db2Mysql.php");
    require("$LOCATION_CRON"."db2.php");
    require("$LOCATION_CRON"."db2Funcoes.php");
    require("$LOCATION_CRON"."db2Variaveis.inc.php");
    require("$LOCATION_CRON"."../inc/funcoes.inc.php");
}

// FAZ UMA BUSCA POR TODOS OS ALUNOS
// E BUSCA ALUNO POR ALUNO NA TABELA DO NAMBEI
// PARA IMPORTAR AS NOTAS.
// NÃO FOI FEITO UM SELECT GENERICO NO NAMBEI, POIS
// A TABELA É MUITO GRANDE.
// SOMENTE 1 e 2 BIMESTRE DOS CURSOS ANTIGOS

if ($semestre == 2) {
    $db2 = "SELECT NTA_DISC, NTA_PRONT, NTA_NOTA, NTA_FALTA, NTA_BIM, NTA_EVENTOD "
            . "FROM ESCOLA.NOTASAL "
            . "WHERE NTA_ANO = $ano "
            . "AND (NTA_BIM = '1' OR NTA_BIM = '2')";
    $res = db2_exec($conn, $db2);
    while ($row = db2_fetch_object($res)) {
        $row->NTA_DISC = trim($row->NTA_DISC);
        $sql = "SELECT p.prontuario as prontuario, a.codigo as atribuicao,
                        m.codigo as matricula, 
                        (SELECT codigo FROM NotasFinais n 
                            WHERE n.atribuicao = a.codigo 
                            AND n.matricula = m.codigo 
                            AND n.bimestre = a.bimestre) as notas
                   FROM Atribuicoes a, Turmas t, Disciplinas d, Matriculas m, Pessoas p
                   WHERE a.turma = t.codigo
                   AND a.disciplina = d.codigo
                   AND m.atribuicao = a.codigo
                   AND m.aluno = p.codigo
                   AND t.ano = $ano
                   AND a.bimestre = $row->NTA_BIM
                   AND p.prontuario = '$row->NTA_PRONT'
                   AND a.eventod = '$row->NTA_EVENTOD'
                   AND d.numero = '$row->NTA_DISC'";
        //print $sql;
        $res2 = mysql_query($sql);
        while ($row2 = mysql_fetch_object($res2)) {
            if (!$row2->notas) {
                // IMPORTA A NOTA
                $sql = "INSERT INTO NotasFinais 
                    (codigo, atribuicao, matricula, bimestre, mcc, rec, ncc, falta, sincronizado, flag, retorno) 
                    VALUES (
                    NULL, $row2->atribuicao, "
                        . "$row2->matricula, "
                        . "'$row->NTA_BIM', "
                        . "'$row->NTA_NOTA', '', '$row->NTA_NOTA', "
                        . "$row->NTA_FALTA, NOW(), '5', "
                        . "'FROM IMPORT NOTAS')";
                mysql_query($sql);
                
                // ATUALIZACAO A ATRIBUICAO PARA FECHADA, 
                // ASSIM A FUNCAO RESULTADO BUSCA A NOTA NA TABELA NOTASFINAIS
                $sql = "UPDATE Atribuicoes SET status = 4 WHERE codigo = $row2->atribuicao";
                mysql_query($sql);                
            }
        }
    }
    if ($DEBUG) print "NOTAS \n";
}
?>