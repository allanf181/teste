<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

mysql_set_charset('latin1');

// IMPORTA AS MODALIDADES
modalidadesCursoAntigo();

$i = 0;
$j = 0;

// GRADES DAS DISCIPLINAS
$db2_2 = "SELECT GRD_CURSO,GRD_MODAL,GRD_DISC,GR_SEMANA,GRD_CHS 
        	FROM ESCOLA.GRADEDIS, ESCOLA.GRADE 
		WHERE GRD_CURSO = GR_CURSO
		AND GRD_MODAL = GR_MODAL
		AND GRD_SERIE = GR_SERIE
		AND GRD_ANO = $ano";
$res_2 = db2_exec($conn, $db2_2);

if (db2_stmt_error() == 42501) {
    $ERRO = "SEM ACESSO NA TABELA GRADES (CURSOS ANTIGOS)";
    mysql_query("INSERT INTO Logs VALUES (0, '" . addslashes($ERRO) . "', now(), 'CRON_ERRO', 1)");
    print $ERRO;
}

while ($row_2 = db2_fetch_object($res_2)) {
    $GRD[$row_2->GRD_CURSO][$row_2->GRD_MODAL][trim($row_2->GRD_DISC)] = $row_2->GR_SEMANA * $row_2->GRD_CHS;
}

$db2 = "SELECT C_CURSO, C_NOME, T_MODAL, T_TURMA FROM ESCOLA.V_TURMAS WHERE T_ANO = $ano";
$res = db2_exec($conn, $db2);

if (db2_stmt_error() == 42501) {
    $ERRO = "SEM ACESSO NA TABELA CURSOS/DISCIPLINAS (CURSOS ANTIGOS)";
    mysql_query("INSERT INTO Logs VALUES (0, '" . addslashes($ERRO) . "', now(), 'CRON_ERRO', 1)");
    print $ERRO;
}

while ($row = db2_fetch_object($res)) {
    $cCodigo = $row->C_CURSO . ord($row->T_MODAL);

    // VERIFICA SE O CURSO EXISTE
    $sql = "select * from Cursos where codigo=$cCodigo";
    $result = mysql_query($sql);
    $curso = @mysql_fetch_object($result);

    if (!$curso) {
        if (!is_numeric($row->T_MODAL))
            $row->T_MODAL += (ord($row->T_MODAL) + 2000); // PARA BASE DE SP...
        $sql = "insert into Cursos 
			(codigo, nome, modalidade, fechamento)
			values ($cCodigo, '" . formatarTexto(addslashes((conv($row->C_NOME)))) . "', " . $row->T_MODAL . ", 'b')";
        if (!$result = mysql_query($sql)) {
            if ($DEBUG)
                echo "<br>Erro ao importar curso: $sql \n";
            mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
        } else {
            $i++;
            $REG = "CURSO ANTIGO: " . formatarTexto(addslashes((conv($row->C_NOME))));
            mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_CA', 1)");
            if ($DEBUG)
                print "$REG <br>\n";
        }
    }

    $db2_1 = "SELECT DISTINCT TD_DISC, D_NOME FROM ESCOLA.V_DISC_TURMA WHERE TD_ANO = $ano AND TD_TURMA = $row->T_TURMA";
    $res_1 = db2_exec($conn, $db2_1);
    while ($row_1 = db2_fetch_object($res_1)) {
        $DISC = trim($row_1->TD_DISC);
        $G = @$GRD[$row->C_CURSO][$row->T_MODAL][$DISC];
        if (!$G)
            $G = null;
        else
            $G = round ( ((($G * 5) / 6) / 4), 2);

        $sql = "select * from Disciplinas where numero='$DISC' and curso=$cCodigo";
        $result = mysql_query($sql);
        if (!$r = mysql_fetch_object($result)) {
            // IMPORTA A DISCIPLINA
            $sql = "insert into Disciplinas (codigo, numero, modulo, nome, ch, curso) values (0, "
                    . "'$DISC', "
                    . "'', "
                    . "'" . rtrim(formatarTexto(addslashes((conv($row_1->D_NOME))))) . "', "
                    . "'$G', "// CALCULO DA CARGA HORARIA DA DISCIPLINA
                    . "$cCodigo) ";
            if (!$result = mysql_query($sql)) {
                if ($DEBUG)
                    echo "<br>Erro ao importar disciplina $sql \n";
                mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
            } else {
                $j++;
                $REG = "DISCIPLINA (CURSO ANTIGO): " . rtrim(formatarTexto(addslashes((conv($row_1->D_NOME)))));
                mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_DA', 1)");
                if ($DEBUG)
                    print "$REG <br>\n";
            }
        } else {
            if ($G != $r->ch) {
                $sql = "UPDATE Disciplinas SET ch = '$G' WHERE codigo = $r->codigo";
                if (!$result = mysql_query($sql)) {
                    if ($DEBUG)
                        echo "<br>Erro ao atualizar disciplina $sql \n";
                    mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
                }
            }
        }
    }
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
    $sql = "insert into Atualizacoes values(0,4," . $_SESSION['loginCodigo'] . ", now())";
    mysql_query($sql);
    ?>
    <script>
        $('#db2CursosDisciplinasRetorno').text('<?= $i ?> cursos processados | <?= $j ?> disciplinas processadas');
    </script><?php
} else {
    $sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
    $resultAdmin = mysql_query($sqlAdmin);
    $admin = mysql_fetch_object($resultAdmin);

    $sql = "insert into Atualizacoes values(0,104," . $admin->codigo . ", now())";
    mysql_query($sql);

    $URL = "CURSOS ANTIGOS: $i |DISCIPLINAS: $j";
    if ($DEBUG)
        print "$URL \n";
    $sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
    mysql_query($sql);
}
?>