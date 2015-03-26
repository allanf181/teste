<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

mysql_set_charset('utf8');

//SYNC LDAP
if ($LDAPSYNC) {
    require CONTROLLER . "/ldapSync.class.php";
    $ldapSync = new ldapSync();
    $res = $ldapSync->syncLDAP();
    if ($DEBUG)
        print $res;
}

//METRICAS DO SISTEMA
// PEGA A CIDADE PRINCIPAL
$sql = "SELECT c.nome as city, COUNT(c.nome) registros 
            FROM Pessoas p, Cidades c 
            WHERE c.codigo = p.cidade 
            GROUP BY c.nome 
            ORDER by registros DESC LIMIT 1";
$result = @mysql_query($sql);
$cidPr = @mysql_fetch_object($result);

//VERIFICA O USO DO SISTEMA
$res = metricas();

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
        "usoSistema" => $res['uso'],
        "dadosSistema" => $res['dados']));

    if ($result) {
        mysql_query("UPDATE Instituicoes SET versaoAtual = '$result'");
        if ($DEBUG)
            print "Registrando a versao $result \n";
    }
}

// DELETANDO LOGS ANTIGOS
mysql_query("DELETE FROM Logs WHERE origem LIKE 'CRON%' AND datediff(now(), data) > 30");

//ENVIA OS BOLETINS DIÁRIOS
require CONTROLLER . "/logEmail.class.php";
$logEmail = new LogEmails();
$logEmail->send();

function metricas() {
    global $ANO, $SEMESTRE;

    $sql = "SELECT p.nome,
                SUM((SELECT COUNT(*) FROM Aulas au WHERE au.atribuicao = a.codigo)) as aula,
                SUM((SELECT COUNT(*) FROM Frequencias f, Aulas au WHERE au.codigo = f.aula AND au.atribuicao = a.codigo)) as frequencia,
                SUM((SELECT COUNT(*) FROM Avaliacoes av WHERE av.atribuicao = a.codigo)) as avaliacao,
                SUM((SELECT COUNT(*) FROM Avaliacoes av, Notas n WHERE av.codigo = n.avaliacao AND av.atribuicao = a.codigo)) as nota,
                (SELECT date_format(data, '%d/%m/%Y') FROM Aulas ad WHERE ad.atribuicao = a.codigo ORDER BY data DESC LIMIT 1) as ultAula
                FROM Atribuicoes a, Disciplinas d, Turmas t, Professores pr, Pessoas p
                WHERE a.disciplina = d.codigo
                AND t.codigo = a.turma
                AND pr.atribuicao = a.codigo
                AND p.codigo = pr.professor        
                AND (t.semestre=$SEMESTRE OR t.semestre=0)
                AND t.ano = $ANO
                GROUP BY pr.professor ORDER BY aula DESC, frequencia DESC, avaliacao DESC, nota DESC ";

    $res = mysql_query($sql);
    while ($row = mysql_fetch_object($res)) {
        if ($row->aula || $row->frequencia || $row->avaliacao || $row->nota)
            $uso++;
        $count++;
        $newRes .= '<tr><td>' . $row->nome . '</td><td>' . $row->aula . '</td><td>' . $row->frequencia . '</td><td>' . $row->avaliacao . '</td><td>' . $row->nota . '</td><td>' . $row->ultAula . '</td></tr>';
    }
    $uso = round(($uso * 100) / $count);
    $rs['uso'] = $uso;
    $rs['dados'] = $newRes;
    return $rs;
}

// POR FAVOR, NAO ADICIONE NENHUM SCRIPT EXTERNO
// ISSO IMPEDE A ATUALIZACAO DO GIT
?>