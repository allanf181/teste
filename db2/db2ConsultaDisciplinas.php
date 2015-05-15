<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

require ('lib/consultaDisciplinasWS.php');

if (isset($_GET["codigo"])) {
    $codigo = $_GET["codigo"];
    $sqlCodigo = "AND n.codigo = $codigo";
}
//else
//    $sqlCodigo = "AND (n.sincronizado IS NULL OR n.sincronizado = '0000-00-00 00:00:00')";


$user = 'BA000022';
$pass = '4(HC&m3KbT';
$campus = strtoupper($DIGITANOTAS);

$sql = "SELECT p.prontuario
	FROM NotasFinais n, Atribuicoes a, Pessoas p, Professores pr, Disciplinas d, Turmas t
	WHERE n.atribuicao = a.codigo
	AND pr.professor = p.codigo
        AND pr.atribuicao = a.codigo
	AND d.codigo = a.disciplina
	AND t.codigo = a.turma
        AND flag = 5
	GROUP BY p.codigo
        ORDER BY n.bimestre";

$result = mysql_query($sql);

$total = mysql_num_rows($result);

while ($l = mysql_fetch_array($result)) {   
    //$prontuario = $l[0];
    $prontuario = '137029';
    $campus = 'BI';
    
    
        $consultaDisciplinasWS = new ConsultaDisciplinasWS();

        $ret = $consultaDisciplinasWS->consultaDisciplinas($user, $pass, $campus, $prontuario);
        print_r($ret);

        if ($ret) {
            print_r($ret);
            die;
        }


    $codigos = array();
    $notas = array();
    $logs = array();

    if ($conexao == 6) {
        //AGUARDANDO 10 SEGUNDOS DA CONEXÃO COM O DIGITA NOTAS
        for ($m = 1; $m <= 10; $m++) {
            sleep(1);
            print "Esperando WS Block... $m segundos... \n";
        }
        $conexao = 0;
    }
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