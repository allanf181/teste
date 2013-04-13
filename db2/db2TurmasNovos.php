<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON"."db2Mysql.php");
    require("$LOCATION_CRON"."../inc/db2.php");
    require("$LOCATION_CRON"."db2Funcoes.php");
    require("$LOCATION_CRON"."db2Variaveis.inc.php");
}

mysql_set_charset('latin1');

$dataInicio=$ano."-01-01";// DATA INICIO DO PERIODO LETIVO
$dataFim=$ano."-12-31";// DATA FINAL DO PERIODO LETIVO

turnos(); // CRIA OS TURNOS

$db2 = "SELECT DISTINCT EM_MODULO, EM_EVENTOM, EM_DTINICIO, CM_MODUSEQ,
			EM_DTFINAL, EM_PERIODO, EM_SITUACAO, M_NOME, CN_CDCURSO, EM_DTINICIO, EM_DTFINAL
			from escola.cursonv    
			left join escola.cursomod on (cn_cdcurso = cm_cdcurso) 
			left join escola.evtmodu  on (EM_MODULO = Cm_MODULO)
			left join escola.modulo  on (EM_MODULO = m_MODULO)  
			WHERE (cn_tpcurso in ('3', '5')) AND (em_modulo is not null ) 
			AND (EM_DTINICIO >= '$dataInicio')
			AND CN_HISTORICO = 'N'
			AND CM_CDCURSO IN (SELECT MC_CDCURSO FROM ESCOLA.MATCURSO WHERE MC_DTINGRES > '$dataInicio' )			
			ORDER BY EM_MODULO, EM_DTINICIO , em_periodo, em_eventom";

$res = db2_exec($conn, $db2);

if (db2_stmt_error() == 42501) {
	$ERRO = "SEM ACESSO NA TABELA TURMAS (CURSOS NOVOS)";
	mysql_query("insert into Logs values(0, '".addslashes($ERRO)."', now(), 'CRON_ERRO', 1)");
	print $ERRO;
}

$i=0;
while ($row = db2_fetch_object($res)) {
    $turno = getTurno($row->EM_PERIODO);

	if (abs(date('m', strtotime("$row->EM_DTFINAL")) - date('m', strtotime("$row->EM_DTINICIO"))) > 6)
    	$semestre = 0;
    else
    	$semestre = date('m', strtotime("$row->EM_DTFINAL"))>10?2:1;

		$numero = 'N'.$row->EM_MODULO.$row->CM_MODUSEQ;
    $sql = "SELECT * FROM Turmas 
    		WHERE numero='$numero' 
    		AND curso=$row->CN_CDCURSO 
    		AND (semestre=$semestre OR semestre=0)";
    $res2 = mysql_query($sql);
    if (!$turma = mysql_fetch_object($res2)){// TURMA NÃO EXISTE, ENTÃO IMPORTA
        // IMPORTA A TURMA
        $ano = date('Y', strtotime($row->EM_DTINICIO));
        $sql = "insert into Turmas (codigo, ano, semestre, numero, curso, turno, sequencia) values (0, "
                . $ano.", "
                . $semestre.", "
                . "'$numero', "
                . "$row->CN_CDCURSO, "
                . $turno.", "
                . "$row->EM_EVENTOM "
                . ")";

       	if (!$result = mysql_query($sql)){
        	if ($DEBUG) echo "<br>Erro ao importar turma: $sql \n";
       		mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
   	    } else {
       		$i++;
       		$REG = "TURMA NOVA (CURSO NOVO): $numero";
       		mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_TN', 1)");
        	if ($DEBUG) print "$REG <br>\n"; 
       	}
    }
}

$db2 = "SELECT DISTINCT CM_MODULO, ED_EVENTOD, CM_MODUSEQ, CM_CDCURSO, ED_DTINICIO, ED_DTFINAL, ED_PERIODO
			from escola.v_evtdiscmod 
			left join escola.cursomod on (DM_modulo = cm_modulo) 
			left join escola.cursonv  on (cn_cdcurso = cm_cdcurso)
			left join escola.v_ed_versaoh on (nh_eventod = ed_eventod ) and (nh_disc = ed_disc) 
			WHERE (ED_EVENTOD < 1900000) 
				AND (ED_DTINICIO >= '$dataInicio') 
				AND (ED_DTFINAL <= '$dataFim')  
				AND ( CM_CDCURSO > 0)
				AND CN_HISTORICO = 'N'
        AND CN_TPCURSO not in ('3', '5')
        AND CM_CDCURSO IN (SELECT MC_CDCURSO FROM ESCOLA.MATCURSO WHERE MC_DTINGRES > '$dataInicio' )";

$res = db2_exec($conn, $db2);
$i=0;
while ($row = db2_fetch_object($res)) {
    $turno = getTurno($row->ED_PERIODO);
	
	if (abs(date('m', strtotime("$row->ED_DTFINAL")) - date('m', strtotime("$row->ED_DTINICIO"))) > 6)
    	$semestre = 0;
    else
    	$semestre = date('m', strtotime("$row->ED_DTFINAL"))>10?2:1;
    
    $numero = 'N'.$row->CM_MODULO.$row->CM_MODUSEQ;
    
    $sql = "SELECT * FROM Turmas 
    		WHERE numero='$numero' 
    		AND curso=$row->CM_CDCURSO 
    		AND (semestre=$semestre OR semestre=0)";
    $res2 = mysql_query($sql);
    if (!$turma = mysql_fetch_object($res2)){// TURMA NÃO EXISTE, ENTÃO IMPORTA
        // IMPORTA A TURMA
        $ano = date('Y', strtotime($row->ED_DTFINAL));
        $sql = "insert into Turmas (codigo, ano, semestre, numero, curso, turno, sequencia) values (0, "
                . $ano.", "
                . $semestre.", "
                . "'$numero', "
                . "$row->CM_CDCURSO, "
                . $turno.", "
                . "$row->ED_EVENTOD "
                . ")";

       	if (!$result = mysql_query($sql)){
        	if ($DEBUG) echo "<br>Erro ao importar turma: $sql \n";
       		mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
   	    } else {
       		$i++;
       		$REG = "TURMA NOVA (CURSO NOVO): $numero";
       		mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_TN', 1)");
        	if ($DEBUG) print "$REG <br>\n"; 
       	}
    }
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
	$sql = "insert into Atualizacoes values(0,5,".$_SESSION['loginCodigo'].", now())";
	mysql_query($sql);
	?>
	<script>
 		$('#turmasnRetorno').text('<?php print $i; ?> registros processados...');
	</script><?php
} else {
	$sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
	$resultAdmin = mysql_query($sqlAdmin);
	$admin = mysql_fetch_object($resultAdmin);
	
	$sql = "insert into Atualizacoes values(0,105,".$admin->codigo.", now())";
	mysql_query($sql);
		
	$URL = "TURMAS CURSOS NOVOS IMPORTADAS: $i";
	$sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
	mysql_query($sql);
}
?>