<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

mysql_set_charset('latin1');

$i = 0;
$j = 0;

// INSERINDO OS TIPOS DE SITUACOES
situacaoNovo();

$db2 = "SELECT DISTINCT AL_PRONT, AL_NOME, MD_EVENTOD, MD_DISC, MD_SITUACAO, MC_CDCURSO 
    FROM ESCOLA.NHORARIO, ESCOLA.MATCURSO, ESCOLA.MATDIS, ESCOLA.ALUNOS
    WHERE NH_EVENTOD = MD_EVENTOD
    AND NH_DISC = MD_DISC
    AND MD_PRONT = MC_ALPRONT
    AND MC_ALPRONT = AL_PRONT
    AND (NH_VERSAOH = " . $ano . "01 OR NH_VERSAOH = " . $ano . "02)";
$result = db2_exec($conn, $db2);

if (db2_stmt_error() == 42501) {
    $ERRO = "SEM ACESSO NA TABELA MATRICULAS (CURSOS NOVOS)";
    mysql_query("INSERT INTO Logs VALUES (0, '" . addslashes($ERRO) . "', now(), 'CRON_ERRO', 1)");
    print $ERRO;
}

while ($row = db2_fetch_object($result)) {
    // BUSCA O CODIGO DO ALUNO
    $sql = "SELECT p.codigo FROM Pessoas p, PessoasTipos pt 
   				WHERE p.codigo = pt.pessoa 
   				AND pt.tipo=$ALUNO 
   				AND p.prontuario='$row->AL_PRONT'";
    $res = mysql_query($sql);
    if ($aluno = mysql_fetch_object($res)) {
        $DISC = trim($row->MD_DISC);
        $sql = "SELECT a.codigo 
	   			FROM Atribuicoes a, Disciplinas d, Turmas t
	   			WHERE a.disciplina = d.codigo
	   			AND a.turma = t.codigo
	   			AND t.curso = $row->MC_CDCURSO
	   			AND a.eventod = $row->MD_EVENTOD
	   			AND d.numero = '$DISC'";
        $atribuicao = mysql_query($sql);
        while ($l = mysql_fetch_array($atribuicao)) {
            // VERIFICA SE A MATRICULA EXISTE
            $sql = "select * from Matriculas where aluno='$aluno->codigo' and atribuicao=" . $l[0];
            $res = mysql_query($sql);
            $matricula = mysql_fetch_object($res);

            if ($row->MD_SITUACAO == 'I' || $row->MD_SITUACAO == 'i')
                $row->MD_SITUACAO = 10;
            $situacao = $row->MD_SITUACAO;

            if (empty($matricula)) { // NÃO EXISTE, ENTÃO IMPORTA
                $sql = "insert into Matriculas (codigo, aluno, atribuicao) values (0, $aluno->codigo, " . $l[0] . ")";
                if (!$result1 = mysql_query($sql)) {
                    if ($DEBUG)
                        echo "<br>Erro ao importar MATRICULA: $sql \n";
                    mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
                } else {
                    $COD = mysql_insert_id();
                    mysql_query("insert into MatriculasAlteracoes (codigo, matricula, situacao, data) values (0, $COD, $situacao, now())");

                    $i++;
                    $REG = "MATRICULA NOVA (CURSO NOVO): " . $aluno->codigo;
                    mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_MN', 1)");
                    if ($DEBUG)
                        print "$REG <br>\n";
                }
            } else {
                $sql = "select * from MatriculasAlteracoes where matricula='$matricula->codigo' ORDER BY codigo DESC LIMIT 1";
                $res = mysql_query($sql);
                $ma = mysql_fetch_object($res);
                if ($ma->situacao != $situacao) {
                    mysql_query("insert into MatriculasAlteracoes (codigo, matricula, situacao, data) values (0, $matricula->codigo, $situacao, now())");
                    if ($DEBUG)
                        print "MATRICULA NOVA - ALTERACAO DE SITUACAO (CURSO NOVO): " . $aluno->codigo . "<br>\n";
                    $j++;
                }
            }
        }
    }
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
    $sql = "insert into Atualizacoes values(0,9," . $_SESSION['loginCodigo'] . ", now())";
    mysql_query($sql);
    ?>
    <script>
        $('#db2MatriculasNovosRetorno').text('<?php print $i; ?> importadas, <?php print $j; ?> atualizadas');
    </script><?php
} else {
    $sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
    $resultAdmin = mysql_query($sqlAdmin);
    $admin = mysql_fetch_object($resultAdmin);

    $sql = "insert into Atualizacoes values(0,109," . $admin->codigo . ", now())";
    mysql_query($sql);

    $URL = "MATRICULAS CURSOS NOVOS IMPORTADOS: $i |ATUALIZADAS $j ";
    if ($DEBUG)
        print "$URL \n";
    $sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
    mysql_query($sql);
}
?>