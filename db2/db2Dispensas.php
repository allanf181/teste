<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

mysql_set_charset('latin1');

$dataFim = $ano . "-12-31";

$db2 = "SELECT DI_PRONT, DI_DISC, DI_DATA, DI_MOTIV FROM ESCOLA.DISPENSA WHERE DI_ANO = $ano";
$res = db2_exec($conn, $db2);
if (db2_stmt_error() == 42501) {
    $ERRO = "SEM ACESSO NA TABELA DISPENSA";
    mysql_query("INSERT INTO Logs VALUES (0, '" . addslashes($ERRO) . "', now(), 'CRON_ERRO', 1)");
    print $ERRO;
}

$f = 0;

while ($row = db2_fetch_object($res)) {
    $sql = "SELECT a.codigo as atribuicao, m.aluno as codigo "
            . "FROM Atribuicoes a, Matriculas m, Disciplinas d, Pessoas p "
            . "WHERE a.disciplina = d.codigo "
            . "AND p.codigo = m.aluno "
            . "AND a.codigo = m.atribuicao "
            . "AND d.numero = '" . trim($row->DI_DISC) . "' "
            . "AND p.prontuario = '" . trim($row->DI_PRONT) . "'";
    $res2 = mysql_query($sql);
    $aluno = mysql_fetch_object($res2);

    if ($aluno) {
        $sql = "SELECT f.codigo "
                . "FROM FrequenciasAbonos f "
                . "WHERE f.aluno = $aluno->codigo "
                . "AND f.dataInicio = '" . $row->DI_DATA . "'"
                . "AND f.atribuicao = $aluno->atribuicao";
        $res3 = mysql_query($sql);
        if (!$abono = mysql_fetch_object($res3)) {// NÃO EXISTE, ENTÃO IMPORTA
            // IMPORTA A DATA
            $sql = "INSERT INTO FrequenciasAbonos VALUES (NULL, $aluno->codigo, '" . $row->DI_DATA . "', '$dataFim', '', $aluno->atribuicao, '" . $row->DI_MOTIV . "', 'D')";
            if (!$result = mysql_query($sql)) {
                if ($DEBUG)
                    echo "<br>Erro ao importar DISPENSA: $sql \n";
                mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
            } else {
                $REG = "DISPENSA: " . $row->DI_PRONT . '-' . $row->DI_DATA;
                mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_DISPENSA', 1)");
                if ($DEBUG)
                    print "$REG <br>\n";
                $f++;
            }
        }
    }
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
    $sql = "insert into Atualizacoes values(0,13," . $_SESSION['loginCodigo'] . ", now())";
    mysql_query($sql);
    ?>
    <script>
        $('#db2DispensasRetorno').text('Dispensas: <?php print $f; ?>');
    </script><?php
} else {
    $sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
    $resultAdmin = mysql_query($sqlAdmin);
    $admin = mysql_fetch_object($resultAdmin);

    $sql = "insert into Atualizacoes values(0,113," . $admin->codigo . ", now())";
    mysql_query($sql);

    $URL = "DISPENSAS IMPORTADOS: $f ";
    if ($DEBUG)
        print "$URL \n";
    $sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
    mysql_query($sql);
}
?>