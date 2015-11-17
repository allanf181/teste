<?php

set_time_limit(2*60); // LIMITE DE TEMPO

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
} 

if (isset($_GET["atribuicao"])) {
    $atribuicao = $_GET["atribuicao"];
    $sqlCodigo = "AND a.codigo = $atribuicao";
}
//else
//    $sqlCodigo = "AND (n.sincronizado IS NULL OR n.sincronizado = '0000-00-00 00:00:00')";

// FECHAR DIARIO *****************************************************
// COMMAND LINE CLIENT
if (isset($argv)){
    $atribuicao = $argv[1];
    $sqlCodigo = "AND a.codigo = $atribuicao";
    
    require CONTROLLER . "/notaFinal.class.php"; 
    $notaFinal = new NotasFinais();
    echo $notaFinal->fecharDiario($atribuicao);
}
// FECHAR DIARIO *****************************************************

$user = 'BA000022'; 
$pass = '4(HC&m3KbT';
//$pass = '1234';
$campus = strtoupper($DIGITANOTAS);
$turma = '0';
$flagDigitacaoNota = '5';

$sql = "SELECT (SELECT p1.prontuario FROM Pessoas p1, Professores pr1, Atribuicoes a1 
                    WHERE p1.codigo = pr1.professor
                    AND pr1.atribuicao = a1.codigo
                    AND a1.codigo = a.codigo LIMIT 1),
        p.prontuario, d.numero, a.eventod, t.ano, t.semestre,
        n.bimestre, n.falta, n.sincronizado, n.mcc, n.rec, n.ncc, n.codigo, 
        DATEDIFF(NOW(),a.dataFim) as data, t.numero, n.recuperacao, n.reavaliacao 
	FROM NotasFinais n, Atribuicoes a, Pessoas p, Matriculas m, Disciplinas d, Turmas t
	WHERE n.atribuicao = a.codigo
	AND n.matricula = m.codigo
	AND m.aluno = p.codigo
	AND d.codigo = a.disciplina
	AND m.atribuicao = a.codigo
	AND t.codigo = a.turma
        AND flag <> 5
	$sqlCodigo
        ORDER BY n.bimestre, t.numero";
//echo $sql;
$result = mysql_query($sql);
$n = 0;
$s = 0;
$c = 0;
$notas = array();
$codigos = array();
$logs = array();
$count = 0;
$conexao = 0;

$total = mysql_num_rows($result);

while ($l = mysql_fetch_array($result)) {
    
    $count++;

    $prontuario = $l[0];
    $prontuarioAluno = $l[1];
    $codigoDisciplina = $l[2];
    $eventod = $l[3];
    $bimestre = $l[6];
    $ano = $l[4];
    $nota = $l[11];
    $turmaD = $l[14];

    if ($l[11] < 10)
        $nota = number_format($l[11], 1, '.', ' ');

    $semestre = ($l[5]) ? $l[5] : $semestre;
    $semestre = str_pad($semestre, 2, "0", STR_PAD_LEFT);
    $faltas = $l[7];

    //recuperacao
    if ($l[15]) {
        $bimestre = 'R';
        $faltas = '0';
        $nota = $l[10];
        if ($l[10] < 10)
            $nota = number_format($l[10], 1, '.', ' ');
    }

    //REAVALIACAO
    if (!is_null($l[16])) {
        $bimestre = 'R';
        $faltas = '0';
        $nota = $l[16];
        if ($l[16] < 10)
            $nota = number_format($l[16], 1, '.', ' ');
    }

    $aluno = array(
        "ano" => $ano,
        "bimestre" => $bimestre,
        "campus" => $campus,
        "codigoDisciplina" => $codigoDisciplina,
        "dataGravacao" => date('dmY'),
        "eventoTod" => $eventod,
        "falta" => $faltas,
        "flagDigitacaoNota" => $flagDigitacaoNota,
        "nota" => $nota,
        "prontuarioAluno" => $prontuarioAluno,
        "prontuarioUsuario" => $prontuario,
        "semestre" => $semestre,
        "turma" => $turma
    );

    $logs[] = "<br>COD: $l[12] |TURMA: $turmaD [$bimestre BIM] |PRONT: $prontuario |DISC: $codigoDisciplina |NOTA: $nota \n";
    $codigos[] = $l[12];
    $dAtual = $codigoDisciplina;
    
    
//    echo "<br>ANT:".$dAnterior." ATUAL:".$dAtual;
    
//    if ($dAnterior==$dAtual || $dAnterior==""){// INCLUI O ALUNO NA LISTA SE FOR O PRIMEIRO OU SE FOR DA DISCIPLINA ATUAL
//        ECHO "<BR>=====>INCLUIU ".$dAtual;
        array_push($notas, $aluno);
        $c++;
//    }
//    echo "<br>]]]]]]]]]]]]]]]]]]]]]]]]";
//    echo "<br>ano?".($aluno['ano']);
//    echo "<br>semestre?".($aluno['semestre']);
//    echo "<br>diferente?".($dAnterior!=$dAtual);
//    echo "<br>diferente?".($dAnterior!="");
//    echo "<br>ant:".$dAnterior;
//    echo "<br>atual:".$dAtual;
//    echo "<br>count:".$count;
//    echo "<br>total:".$total;
//    if ($count >= 10 || isset($codigo) || $count >= $total) {
    if (($dAnterior!=$dAtual && $dAnterior!="")|| $count >= $total) {
//        echo "<br>==>$dAtual";
        $total -= $count;

        $count = 0;

        $cod = implode(',', $codigos);
        $log = implode('##', $logs);

        try {
            $digitaNotaAlunoWS = new digitaNotasWS();

            $lista = array("notas" => $notas);
//echo "<br>=====================<br>Lista:";
//            var_dump($lista);

            $ret = $digitaNotaAlunoWS->digitarNotasAlunos($user, $pass, $campus, $lista);
            
//            echo "<br>====="; var_dump($ret);
//            die;
            
            $conexao++;

            if ($ret) {
//                echo "<br>sucesso? ".$ret->sucesso;
//                echo "<br>motivo? ".$ret->motivo;
//                echo "<hr>";
                
                if ($ret->sucesso == 1) {
                    $URL = 'Nota registrada com sucesso';
                } else {
                    $URL = $ret->motivo;
                }

                if ($ret->sucesso == 1) {
                    if ($DEBUG)
                        echo "$URL \n";
                    mysql_query("insert into Logs values(0, '$URL:## $log', now(), 'CRON_NT', 1)");
                    mysql_query("UPDATE NotasFinais SET sincronizado = NOW(), retorno='$URL', flag='$flagDigitacaoNota' WHERE codigo IN ($cod)");
                    if ($codigo) {
                        print "Nota registrada.";
                    }
                    if ($atribuicao) {
                        $totalMsg += count($lista['notas']);
                    }
                    $s += $c;
                    $c = 0;
                } else {
                    $URL = "$URL:## $log \n";
                    if ($DEBUG)
                        echo "$URL \n";
                    mysql_query("insert into Logs values(0, '" . addslashes($URL) . "', now(), 'CRON_NTERR', 1)");
                    mysql_query("UPDATE NotasFinais SET retorno='" . $ret->motivo . "' WHERE codigo IN ($cod)");
                    if ($codigo)
                        print "Problema ao registrar nota.";
                    $n++;
                }
            }
        } catch (Exception $ex) {
            if ($codigo)
                print $ex;
            $erro = "Erro DigitaNotas: $ex";
            if ($DEBUG)
                echo "erro: $erro \n";
            mysql_query("insert into Logs values(0, '" . addslashes($erro) . "', now(), 'CRON_NTERR', 1)");
            mysql_query("UPDATE NotasFinais SET retorno='$erro' WHERE codigo IN ($cod)");
            $n++;
        }

        $codigos = array();
        $notas = array();
        $logs = array();

        if ($conexao == 6) {
            //AGUARDANDO 10 SEGUNDOS DA CONEXÃO COM O DIGITA NOTAS
            for ($m = 1; $m <= 10; $m++) {
                sleep(1);
                if (!$codigo)
                    print "Esperando WS Block... $m segundos... \n";
            }
            $conexao = 0;
        }
    }
    $dAnterior=$dAtual;
}

if ($atribuicao) {
    print "$totalMsg nota(s) registrada(s) com sucesso.";
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
    $sql = "insert into Atualizacoes values(0,12," . $_SESSION['loginCodigo'] . ", now())";
    mysql_query($sql);
    ?>
    <script>
        $('#db2DigitaNotasRetorno').text('<?= $s ?> sincronizadas, <?= $n ?> nao sincronizados');
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