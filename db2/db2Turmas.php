<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON"."db2Mysql.php");
    require("$LOCATION_CRON"."db2.php");
    require("$LOCATION_CRON"."db2Funcoes.php");
    require("$LOCATION_CRON"."db2Variaveis.inc.php");
    require("$LOCATION_CRON"."../inc/funcoes.inc.php");
}

mysql_set_charset('latin1');

$db2 = "SELECT T_TURMA, T_CURSO, T_MODAL, T_ANO, T_SERIE, T_PERIODO FROM ESCOLA.V_TURMAS WHERE T_ANO = $ano";
$res = db2_exec($conn, $db2);

if (db2_stmt_error() == 42501) {
	$ERRO = "SEM ACESSO NA TABELA TURMAS (CURSOS ANTIGOS)";
	mysql_query("insert into Logs values(0, '".addslashes($ERRO)."', now(), 'CRON_ERRO', 1)");
	print $ERRO;
}

turnos(); // CRIA OS TURNOS

$i=0;
while ($row = db2_fetch_object($res)) {
	$turno = getTurno($row->T_PERIODO);
	$cCodigo = $row->T_CURSO.ord($row->T_MODAL);
	$numero = "A".$row->T_TURMA;
	
	for ($n=1; $n <= 2; $n++) {
	    $sql = "select * from Turmas where numero='$numero' and curso=$cCodigo AND ano=$ano AND semestre = $n";
	    $res2 = mysql_query($sql);
	    if (!$turma = mysql_fetch_object($res2)){// TURMA NAO EXISTE, ENTAO IMPORTA

	        // IMPORTA A TURMA
	        $sql = "insert into Turmas (codigo, ano, semestre, sequencia, numero, serie, curso, turno) values (0, "
	                . "$row->T_ANO, "
	                . "$n, "
	                . "'$row->T_MODAL', "
	                . "'$numero', "
	                . "$row->T_SERIE, "
	                . "$cCodigo, "
	                . $turno." "
	                . ")";
	
	       	if (!$result = mysql_query($sql)){
	        	if ($DEBUG) echo "<br>Erro ao importar turma: $sql \n";
	       		mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
	   	    } else {
	       		$i++;
	       		$REG = "TURMA NOVA (CURSO ANTIGO): ". $row->T_TURMA;
	       		mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_TA', 1)");
	        	if ($DEBUG) print "$REG <br>\n"; 
	       	}
	  	}
    }
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
	$sql = "insert into Atualizacoes values(0,6,".$_SESSION['loginCodigo'].", now())";
	mysql_query($sql);
	?>
	<script>
  		$('#db2TurmasRetorno').text('<?php print $i; ?> registros processados...');
	</script><?php
} else {
	$sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
	$resultAdmin = mysql_query($sqlAdmin);
	$admin = mysql_fetch_object($resultAdmin);
	
	$sql = "insert into Atualizacoes values(0,106,".$admin->codigo.", now())";
	mysql_query($sql);
		
	$URL = "TURMAS CURSOS ANTIGOS IMPORTADAS: $i";
        if ($DEBUG) print "$URL \n";
	$sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
	mysql_query($sql);
}
?>