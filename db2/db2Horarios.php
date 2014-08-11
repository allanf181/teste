<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

mysql_set_charset('latin1');

$dataInicio = $ano . "-01-01"; // DATA INICIO DO PERIODO LETIVO

$db2 = "SELECT * FROM GERAL.FERIADOS WHERE F_DATA >= '$dataInicio'";
$res = db2_exec($conn, $db2);

if (db2_stmt_error() == 42501) {
    $ERRO = "SEM ACESSO NA TABELA FERIADOS";
    mysql_query("INSERT INTO Logs VALUES (0, '" . addslashes($ERRO) . "', now(), 'CRON_ERRO', 1)");
    print $ERRO;
}

$f = 0;
$h = 0;
$s = 0;

while ($row = db2_fetch_object($res)) {
    $sql = "SELECT * FROM Calendarios WHERE data = '" . $row->F_DATA . "'";
    $res2 = mysql_query($sql);
    if (!$calendario = mysql_fetch_object($res2)) {// DATA NÃO EXISTE, ENTÃO IMPORTA
        // IMPORTA A DATA
        $sql = "INSERT INTO Calendarios VALUES (NULL, '" . $row->F_DATA . "', 0, '" . formatarTexto(addslashes((conv($row->F_DESCR)))) . "')";
        if (!$result = mysql_query($sql)) {
            if ($DEBUG)
                echo "<br>Erro ao importar DATA: $sql \n";
            mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
        } else {
            $REG = "FERIADO: " . $row->F_DATA;
            mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_HORARIO', 1)");
            if ($DEBUG)
                print "$REG <br>\n";
            $f++;
        }
    }
}

$db2 = "SELECT * FROM ESCOLA.HORAAULA";
$res = db2_exec($conn, $db2);

if (db2_stmt_error() == 42501) {
    $ERRO = "SEM ACESSO NA TABELA HORA AULA";
    mysql_query("INSERT INTO Logs VALUES (0, '" . addslashes($ERRO) . "', now(), 'CRON_ERRO', 1)");
    print $ERRO;
}

while ($row = db2_fetch_object($res)) {
    $nome = 'AULA ' . $row->HA_AULA . ' [' . $row->HA_PERIODO . ']';
    $sql = "SELECT * FROM Horarios WHERE nome = '$nome'";
    $res2 = mysql_query($sql);
    if (!$horario = mysql_fetch_object($res2)) {// HORARIO NÃO EXISTE, ENTÃO IMPORTA
        // IMPORTA O HORARIO
        $sql = "INSERT INTO Horarios VALUES (NULL, '$nome', '" . $row->HA_INICIO . "', '" . $row->HA_FIM . "')";
        if (!$result = mysql_query($sql)) {
            mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
            if ($DEBUG)
                echo "<br>Erro ao importar HORARIO: $sql \n";
        } else {
            $h++;
            mysql_query("insert into Logs values(0, '$nome', now(), 'CRON_HORARIO', 1)");
            if ($DEBUG)
                print "HORARIO: $nome <br>\n";
        }
    } else {
        $inico = $horario->inicio.':00';
        $fim = $horario->HA_FIM.':00';
        if ($horario->nome != $nome || $horario->inicio != $inicio || $horario->fim != $fim) {
            print "ok";
            $sql = "UPDATE Horarios SET nome = '$nome', inicio = '" . $row->HA_INICIO . "', fim = '" . $row->HA_FIM . "' WHERE codigo = $horario->codigo";
            if (!$result = mysql_query($sql)) {
                mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
                if ($DEBUG)
                    echo "<br>Erro ao alterar HORARIO: $sql \n";
            } else {
                $h++;
                mysql_query("insert into Logs values(0, '$nome', now(), 'CRON_HORARIO', 1)");
                if ($DEBUG)
                    print "HORARIO: $nome <br>\n";
            }
        }
    }
}

$db2 = "SELECT * FROM ESCOLA.SALAS";
$res = db2_exec($conn, $db2);

if (db2_stmt_error() == 42501) {
    $ERRO = "SEM ACESSO NA TABELA SALAS";
    mysql_query("INSERT INTO Logs VALUES (0, '" . addslashes($ERRO) . "', now(), 'CRON_ERRO', 1)");
    print $ERRO;
}

while ($row = db2_fetch_object($res)) {
    $nome = 'SALA ' . $row->SL_SALA;
    $sql = "SELECT * FROM Salas WHERE nome = '$nome'";
    $res2 = mysql_query($sql);
    if (!$horario = mysql_fetch_object($res2)) {// SALA NÃO EXISTE, ENTÃO IMPORTA
        // IMPORTA A SALA
        $sql = "INSERT INTO Salas VALUES (NULL, '$nome', '')";
        if (!$result = mysql_query($sql)) {
            mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
            if ($DEBUG)
                echo "<br>Erro ao importar SALA: $sql \n";
        } else {
            mysql_query("insert into Logs values(0, '$nome', now(), 'CRON_HORARIO', 1)");
            if ($DEBUG)
                print "SALA: " . $nome . "<br>\n";
            $s++;
        }
    }
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
    $sql = "insert into Atualizacoes values(0,11," . $_SESSION['loginCodigo'] . ", now())";
    mysql_query($sql);
    ?>
    <script>
        $('#horariosRetorno').text('Feriados: <?php print $f; ?> |Horas: <?php print $h; ?> |Salas: <?php print $s; ?>');
    </script><?php
} else {
    $sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
    $resultAdmin = mysql_query($sqlAdmin);
    $admin = mysql_fetch_object($resultAdmin);

    $sql = "insert into Atualizacoes values(0,111," . $admin->codigo . ", now())";
    mysql_query($sql);

    $URL = "FERIADOS IMPORTADOS: $f |HORAS IMPORTADAS: $h |SALAS IMPORTADAS: $s ";
    if ($DEBUG)
        print "$URL \n";
    $sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
    mysql_query($sql);
}
?>