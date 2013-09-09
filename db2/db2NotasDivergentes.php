<?php

if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

$sql = "SELECT a.eventod, d.numero, a.codigo, a.bimestre
                   FROM Atribuicoes a, Turmas t, Disciplinas d, Matriculas m, Pessoas p
                   WHERE a.turma = t.codigo
                   AND a.disciplina = d.codigo
                   AND m.atribuicao = a.codigo
                   AND m.aluno = p.codigo
                   AND t.ano = 2013
                   AND a.status <> 0
                   GROUP BY a.codigo";
//print $sql;
$res2 = mysql_query($sql);
while ($row2 = mysql_fetch_object($res2)) {
    $db2 = "SELECT NTA_DISC,NTA_PRONT,NTA_NOTA,NTA_FALTA,NTA_BIM,NTA_EVENTOD,NTA_ANO "
            . "FROM ESCOLA.NOTASAL "
            . "WHERE NTA_DISC = '$row2->numero' "
            . "AND NTA_EVENTOD = $row2->eventod ";
    //print $db2;
    $res = db2_exec($conn, $db2);
    $registros = array();
    while ($row = db2_fetch_object($res)) {
        $row->NTA_NOTA = str_replace(',', '.', $row->NTA_NOTA);

        if ($row->NTA_BIM == 1 && $row->NTA_ANO == 0)
            $row->NTA_BIM = 0;

        if ($row->NTA_BIM == 'R') {
            $registros[$row->NTA_PRONT]['M']['rec'] = $row->NTA_NOTA;
        } else {
            $registros[$row->NTA_PRONT][$row->NTA_BIM]['nota'] = $row->NTA_NOTA;
            $registros[$row->NTA_PRONT][$row->NTA_BIM]['falta'] = $row->NTA_FALTA;
        }
    }

    foreach ($registros as $pront => $reg) {
        foreach ($reg as $bim => $reg2) {
            $sql = "SELECT mcc,ncc,falta,rec,n.bimestre,a.codigo,t.numero
                   FROM Atribuicoes a, Turmas t, Disciplinas d, Matriculas m, Pessoas p, NotasFinais n
                   WHERE a.turma = t.codigo
                   AND a.disciplina = d.codigo
                   AND m.atribuicao = a.codigo
                   AND m.aluno = p.codigo
                   AND n.matricula = m.codigo
                   AND n.atribuicao = a.codigo
                   AND n.bimestre = a.bimestre
                   AND a.bimestre = $bim
                   AND a.codigo = $row2->codigo
                   AND p.prontuario = '$pront'";
            //print $sql;
            $res3 = mysql_query($sql);
            while ($row3 = mysql_fetch_object($res3)) {
                if ($row3->rec <> $reg2['rec'] && $row3->bimestre == 'M') {
                    $REG = "TURMA: $row3->numero |PRONT: $pront |DISC: $row2->numero |BIM: $bim  |NOTA WD: $row3->rec | DN: " . $reg2['rec'] . " \n";
		    mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_NTDIV', 1)");
		    if ($DEBUG) print "$REG <br>\n";
                }

                if ($row3->mcc <> $reg2['nota']) {
                    $REG = "TURMA: $row3->numero |PRONT: $pront |DISC: $row2->numero |BIM: $bim  |NOTA WD: $row3->mcc | DN: " . $reg2['nota'] . " \n";
		    mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_NTDIV', 1)");
		    if ($DEBUG) print "$REG <br>\n";
                }
                
                if ($row3->falta <> $reg2['falta']) {
                    $REG = "TURMA: $row3->numero |PRONT: $pront |DISC: $row2->numero |BIM: $bim  |FALTA WD: $row3->falta | DN: " . $reg2['falta'] . " \n";
		    mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_NTDIV', 1)");
		    if ($DEBUG) print "$REG <br>\n";
                }
            }
        }
    }
}
?>