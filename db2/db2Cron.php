<?php

//SESSAO
error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!isset($_SESSION)) {
    session_start();
}

$DEBUG = 0;
$LOCATION_CRON = dirname(__FILE__) . '/';

require $LOCATION_CRON . '../inc/config.inc.php';

if (isset($argv[1]))
    $DEBUG = 1;

require("$LOCATION_CRON" . "db2Mysql.php");
require("$LOCATION_CRON" . "db2.php");
require("$LOCATION_CRON" . "db2Funcoes.php");
require("$LOCATION_CRON" . "db2Variaveis.inc.php");
require("$LOCATION_CRON" . "../inc/funcoes.inc.php");

include ("$LOCATION_CRON" . "db2Alunos.php");
include ("$LOCATION_CRON" . "db2Professores.php");
include ("$LOCATION_CRON" . "db2Horarios.php");

include ("$LOCATION_CRON" . "db2CursosDisciplinasNovos.php");
include ("$LOCATION_CRON" . "db2TurmasNovos.php");
include ("$LOCATION_CRON" . "db2AtribuicoesNovos.php");
include ("$LOCATION_CRON" . "db2MatriculasNovos.php");

include ("$LOCATION_CRON" . "db2CursosDisciplinas.php");
include ("$LOCATION_CRON" . "db2Turmas.php");
include ("$LOCATION_CRON" . "db2Atribuicoes.php");
include ("$LOCATION_CRON" . "db2Matriculas.php");

include ("$LOCATION_CRON" . "db2DigitaNotas.php");

include ("$LOCATION_CRON" . "db2Notas.php");

include ("$LOCATION_CRON" . "db2Dispensas.php");

// PEGA A CIDADE PRINCIPAL
$sql = "SELECT c.nome as city, COUNT(c.nome) registros 
            FROM Pessoas p, Cidades c 
            WHERE c.codigo = p.cidade 
            GROUP BY c.nome 
            ORDER by registros DESC LIMIT 1";
$result = @mysql_query($sql);
$cidPr = @mysql_fetch_object($result);

//VERIFICA O USO DO SISTEMA
$sql = "SELECT p.nome,
                SUM((SELECT COUNT(*) FROM Aulas au WHERE au.atribuicao = a.codigo)) as aula,
                SUM((SELECT COUNT(*) FROM Frequencias f, Aulas au WHERE au.codigo = f.aula AND au.atribuicao = a.codigo)) as frequencia,
                SUM((SELECT COUNT(*) FROM Avaliacoes av WHERE av.atribuicao = a.codigo)) as avaliacao,
                SUM((SELECT COUNT(*) FROM Avaliacoes av, Notas n WHERE av.codigo = n.avaliacao AND av.atribuicao = a.codigo)) as nota
                FROM Atribuicoes a, Disciplinas d, Turmas t, Professores pr, Pessoas p
                WHERE a.disciplina = d.codigo
                AND t.codigo = a.turma
                AND pr.atribuicao = a.codigo
                AND p.codigo = pr.professor        
                AND (t.semestre=$semestre OR t.semestre=0)
                AND t.ano = $ano
                GROUP BY pr.professor ORDER BY aula DESC, frequencia DESC, avaliacao DESC, nota DESC";
$result = @mysql_query($sql);
while ($uso = mysql_fetch_object($result)) {
    if ($uso->aula || $uso->frequencia || $uso->avaliacao || $uso->nota)
        $usoSistema++;
    $count++;
}
$usoSistema = round(($usoSistema * 100) / $count);

// INSERINDO INFOS VIA WS
require $LOCATION_CRON . '../lib/nusoap/lib/nusoap.php';
$client = new nusoap_client("https://200.133.218.2:80/wsWD/server.wsdl", true);
$client->setCredentials("WebDiarioWDWS", "W3bD1ari0_WS_WD_##!!", "basic");

if ($client) {
    $result = $client->call("setversion", array("nome" => "$SITE_TITLE",
        "cidade" => "$SITE_CIDADE",
        "digitaNotas" => "$DIGITANOTAS",
        "versao" => "$VERSAO",
        "cidadePredominante" => $cidPr->city,
        "uname" => php_uname(),
        "hostname" => gethostname(),
        "usoSistema" => $usoSistema));
}

if ($result)
    mysql_query("UPDATE Instituicoes SET versaoAtual = '$result'");

// DELETANDO LOGS ANTIGOS
mysql_query("DELETE FROM Logs WHERE datediff(now(), data) > 30");
?>