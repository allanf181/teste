<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

require ('lib/digitaNotasWS.php');

if (isset($_GET["codigo"])) {
    $codigo = $_GET["codigo"];
    $sqlCodigo = "AND n.codigo = $codigo";
} else
    $sqlCodigo = "AND (n.sincronizado IS NULL OR n.sincronizado = '0000-00-00 00:00:00')";


$user = 'BT000001';
$pass = '4(HC&m3KbT';
$campus = $DIGITANOTAS;
$turma = '0';
$flagDigitacaoNota = '0';

$sql = "SELECT (SELECT p1.prontuario FROM Pessoas p1, Professores pr1, Atribuicoes a1 
                    WHERE p1.codigo = pr1.professor
                    AND pr1.atribuicao = a1.codigo
                    AND a1.codigo = a.codigo LIMIT 1),
        p.prontuario, d.numero, a.eventod, t.ano, t.semestre,
        n.bimestre, n.falta, n.sincronizado, n.mcc, n.rec, n.ncc, n.codigo, DATEDIFF(NOW(),a.dataFim) as data
	FROM NotasFinais n, Atribuicoes a, Pessoas p, Matriculas m, Disciplinas d, Turmas t
	WHERE n.atribuicao = a.codigo
	AND n.matricula = m.codigo
	AND m.aluno = p.codigo
	AND d.codigo = a.disciplina
	AND m.atribuicao = a.codigo
	AND t.codigo = a.turma
        AND flag <> 5
	$sqlCodigo";

$result = mysql_query($sql);
$n = 0;
$s = 0;
$notas = array();
$count=1;

while ($l = mysql_fetch_array($result)) {
    //if ($l[13] > 10)
    //    $flagDigitacaoNota = 5;
    $prontuario = $l[0];
    $prontuarioAluno = $l[1];
    $codigoDisciplina = $l[2];
    $eventod = $l[3];
    $bimestre = ($l[6] == 0) ? 'M' : $l[6];
    $ano = $l[4];
    $nota = $l[11];
    if ($l[11] < 10) $nota = number_format($l[11], 1, '.', ' ');

    $semestre = ($l[5]) ? $l[5] : $semestre;
    $semestre = str_pad($semestre, 2, "0", STR_PAD_LEFT);
    $faltas = $l[7];

    $j = ($bimestre == 'M') ? 2 : 1;

    for ($i = 0; $i < $j; $i++) {

        if ($i == 1) {
            if ($l[10]) {
                $bimestre = 'R';
                $faltas = '0';
                $nota = $l[10];
                if ($l[10] < 10) $nota = number_format($l[10], 1, '.', ' ');
            } else {
                continue;
            }
        }

        $aluno = array(
            "ano" => $ano,
            "turma" => $turma,
            "eventoTod" => $eventod,
            "bimestre" => $bimestre,
            "codigoDisciplina" => $codigoDisciplina,
            "prontuarioUsuario" => $prontuario,
            "prontuarioAluno" => $prontuarioAluno,
            "semestre" => $semestre,
            "flagDigitacaoNota" => $flagDigitacaoNota,
            "nota" => $nota,
            "falta" => $faltas,
            "campus" => $campus,
            "dataGravacao" => date('dmY'));

        array_push($notas, $aluno);

        if ($count == 10 || $codigo) {
            $count=0;

            try {
                $digitaNotaAlunoWS = new digitaNotasWS();

                $lista = array("notas" => $notas);

                $ret = $digitaNotaAlunoWS->digitarNotasAlunos($user, $pass, $campus, $lista);
                print $ret;
                print_r($notas);
                $URL = "DIGITANOTAS (PROF:$prontuario|AL:$prontuarioAluno|DISC:$codigoDisciplina|N:$nota|F:$faltas|FLAG:$flagDigitacaoNota): $ret \n";

                if ($ret) {
                    if ($DEBUG)
                        echo "$URL \n";
                    mysql_query("insert into Logs values(0, '$URL', now(), 'CRON_NT', 1)");
                    mysql_query("UPDATE NotasFinais SET sincronizado = NOW(), retorno='$ret' WHERE codigo = " . $l[12]);
                    if ($codigo)
                        print "Nota registrada.";
                    $s++;
                } else {
                    $URL = "ERRO $URL \n";
                    if ($DEBUG)
                        echo "$URL \n";
                    mysql_query("insert into Logs values(0, '" . addslashes($URL) . "', now(), 'CRON_ERRO', 1)");
                    mysql_query("UPDATE NotasFinais SET retorno='$ret' WHERE codigo = " . $l[12]);
                    if ($codigo)
                        print "Problema ao registrar nota.";
                    $n++;
                }
            } catch (Exception $ex) {
                if ($codigo)
                    print $ex;
                $erro = "Erro DigitaNotas: $ex";
                if ($DEBUG)
                    echo "$erro \n";
                mysql_query("insert into Logs values(0, '" . addslashes($erro) . "', now(), 'CRON_ERRO', 1)");
                mysql_query("UPDATE NotasFinais SET retorno='$ret' WHERE codigo = " . $l[12]);
                $n++;
            }
            
            $notas = array();
        }
        
        $count++;
    }
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
    $sql = "insert into Atualizacoes values(0,12," . $_SESSION['loginCodigo'] . ", now())";
    mysql_query($sql);
    ?>
    <script>
        $('#db2DigitaNotasRetorno').text('<?php print $s; ?> sincronizadas, <?php print $n; ?> nao sincronizados');
    </script><?php
} else {
    $sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
    $resultAdmin = mysql_query($sqlAdmin);
    $admin = mysql_fetch_object($resultAdmin);

    $sql = "insert into Atualizacoes values(0,112," . $admin->codigo . ", now())";
    mysql_query($sql);

    $URL = "DIGITA NOTAS: $s NOTAS SINCRONIZADAS";
    if ($DEBUG)
        print "$URL \n";
    $sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
    mysql_query($sql);
}
?>