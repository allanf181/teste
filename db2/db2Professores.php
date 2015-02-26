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

for ($n = 1; $n <= 2; $n++) {
    $nS = str_pad($n, 2, "0", STR_PAD_LEFT);

    $db2 = "SELECT * 
			FROM ESCOLA.PROFESS 
			WHERE PR_PRONTP IN (SELECT DISTINCT NH_PROFESSOR 
						 FROM ESCOLA.NHORARIO 
					WHERE NH_VERSAOH=$ano$nS)
			ORDER BY PR_NOME";
    $res = db2_exec($conn, $db2);

    if (db2_stmt_error() == 42501) {
        $ERRO = "SEM ACESSO NA TABELA PROFESSORES/HORARIOS";
        mysql_query("insert into Logs values(0, '" . addslashes($ERRO) . "', now(), 'CRON_ERRO', 1)");
        print $ERRO;
    }

    while ($row = db2_fetch_object($res)) {
        $professor = null;
        
        $row->PR_PRONTP = trim(addslashes($row->PR_PRONTP));
         
        // VERIFICA SE O PROFESSOR EXISTE
        $sql = "SELECT * FROM Pessoas p WHERE p.prontuario = '$row->PR_PRONTP'";

        $result = mysql_query($sql);
        $professor = mysql_fetch_object($result);

        //FORMATANDO DADOS
        $sexo = trim($row->PR_SEXO);
        $nome = formatarTexto(addslashes((conv(rtrim($row->PR_NOME)))));

        if (empty($professor)) { // NÃO EXISTE, ENTÃO IMPORTA
            $sql = "insert into Pessoas (codigo, prontuario, nome, senha, sexo, cidade, naturalidade) values (0, "
                    . "'" . $row->PR_PRONTP . "', "
                    . "'$nome', "
                    . "PASSWORD('" . $row->PR_PRONTP . "'), "
                    . "'$sexo', "
                    . " 1, 1)";

            if (!mysql_query($sql)) {
                if ($DEBUG)
                    print "ERRO: $sql <br>\n";
                mysql_query("insert into Logs values(0, 'ERRO: $sql', now(), 'CRON_ERRO', 1)");
                print "insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)";
            } else {
                $i++;
                $COD = mysql_insert_id();
                mysql_query("INSERT INTO PessoasTipos VALUES (NULL, $COD, $PROFESSOR)");
                $REG = "PROFESSOR NOVO: $nome";
                mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_PROFESSOR', 1)");
                if ($DEBUG)
                    print "$REG <br>\n";
            }
        } else {
            if (strcmp($professor->nome, $nome) != 0 || $professor->sexo != $sexo) {
                $sql = "UPDATE Pessoas
		    			SET nome = '$nome',
		    			sexo = '$sexo' WHERE codigo = $professor->codigo";
                mysql_query($sql);
                $REG = "PROFESSOR ALTERACAO: $row->PR_PRONTP $nome / $professor->prontuario $professor->nome";
                mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_PROFESSOR', 1)");
                if ($DEBUG)
                    print "$REG <br>\n";
                $j++;
            }
        }
    }
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
    $sql = "insert into Atualizacoes values(0,2," . $_SESSION['loginCodigo'] . ", now())";
    mysql_query($sql);
    ?>
    <script>
        $('#db2ProfessoresRetorno').text('<?= $i ?> importados, <?= $j ?> atualizados');
    </script><?php
} else {
    $sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
    $resultAdmin = mysql_query($sqlAdmin);
    $admin = mysql_fetch_object($resultAdmin);

    $sql = "insert into Atualizacoes values(0,102," . $admin->codigo . ", now())";
    mysql_query($sql);

    $URL = "PROFESSORES IMPORTADOS: $i |ATUALIZADOS: $j";
    if ($DEBUG)
        print "$URL \n";
    $sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
    mysql_query($sql);
}
?>