<?php

if (!$LOCATION_CRON) {
	require("../inc/mysql.php");
	require("../inc/db2.php");
	require("db2Funcoes.php");
	require("../inc/variaveis.inc.php");
}

mysql_set_charset('latin1');

$inicio = $ano."-01-01";
$fim = $ano."-12-31";

// NOVAS MODALIDADES 
modalidadesCursoNovo();

$i=0;
$j=0;

$db2 = "SELECT DISTINCT ED_DISC, D_NOME, CM_CDCURSO, CN_TPCURSO, CN_NOME, D_CARGAH, DM_MODULO, CN_PERIODICIDADE  
			from escola.v_evtdiscmod 
			left join escola.cursomod on (DM_modulo = cm_modulo) 
			left join escola.cursonv  on (cn_cdcurso = cm_cdcurso)
			left join escola.v_ed_versaoh on (nh_eventod = ed_eventod ) and (nh_disc = ed_disc) 
			WHERE (ED_EVENTOD < 1900000) 
				AND (ED_DTINICIO >= '$inicio') 
				AND (ED_DTFINAL <= '$fim')  
				AND ( CM_CDCURSO > 0)
        AND CN_HISTORICO = 'N'
        AND CM_CDCURSO IN (SELECT MC_CDCURSO FROM ESCOLA.MATCURSO WHERE MC_DTINGRES > '$dataInicio' )
			ORDER BY CM_CDCURSO";
$res = db2_exec($conn, $db2);

if (db2_stmt_error() == 42501) {
	$ERRO = "SEM ACESSO NA TABELA CURSOS/DISCIPLINAS (CURSOS NOVOS)";
	mysql_query("INSERT INTO Logs VALUES (0, '".addslashes($ERRO)."', now(), 'CRON_ERRO', 1)");
	print $ERRO;
}

while ($row = db2_fetch_object($res)) {
	$CODIGO = '';
	$CODIGO = '100' . $row->CN_TPCURSO; //CODIGO DA MODALIDADE
    
  if (strtolower($row->CN_PERIODICIDADE) == '') $P = 's';
  else $P = strtolower($row->CN_PERIODICIDADE);
    	
	// VERIFICA SE O CURSO EXISTE
  $sql = "select * from Cursos where codigo=$row->CM_CDCURSO";
  $result = mysql_query($sql);
  $curso = @mysql_fetch_object($result);
    
  if (!$curso) { 
  	$sql = "insert into Cursos (codigo, nome, modalidade, fechamento) 
    				values ($row->CM_CDCURSO,"
                    . "'".formatarTexto(addslashes((conv($row->CN_NOME))))."', "
                    . " $CODIGO, "
                    . "'$P')";
      	
  	if (!$result = mysql_query($sql)){
     	if ($DEBUG) echo "<br>Erro ao importar curso: $sql \n";
    	mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
   	} else {
    	$i++;
    	$REG = "CURSO NOVO: ". formatarTexto(addslashes((conv($row->CN_NOME))));
    	mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_CN', 1)");
     	if ($DEBUG) print "$REG <br>\n"; 
    }
  } else {
  	$sql = "UPDATE Cursos SET fechamento ='$P' WHERE codigo = $curso->codigo";
   	if (!$result = mysql_query($sql)){
	  	if ($DEBUG) echo "<br>Erro ao atualizar CURSO: $sql \n";
	    mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
		}
	}

	// VERIFICA SE A DISCIPLINA EXISTE
  $DISC = trim($row->ED_DISC);

  $sql = "select * from Disciplinas where numero='$DISC' and curso=$row->CM_CDCURSO";
  $result = mysql_query($sql);
  $disciplina = @mysql_fetch_object($result);
    
  if (!$disciplina) {
	  $sql = "insert into Disciplinas (codigo, numero, modulo, nome, ch, curso) values (0, "
	                . "'$DISC', "
	                . "'$row->DM_MODULO', "
	                . "'".formatarTexto(addslashes((conv($row->D_NOME))))."', "
	                . "'$row->D_CARGAH', "
	                . "$row->CM_CDCURSO) ";
    if (!$result = mysql_query($sql)){
  	 	if ($DEBUG) echo "<br>Erro ao importar disciplina: $sql \n";
     	mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
   	} else {
     	$j++;
     	$REG = "DISCIPLINA (CURSO NOVO): ". formatarTexto(addslashes((conv($row->D_NOME))));
     	mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_DN', 1)");
     	if ($DEBUG) print "$REG <br>\n"; 
    }
	} else {
	 	if ($row->D_CARGAH != $disciplina->ch) {
	  	$sql = "UPDATE Disciplinas SET ch = '$row->D_CARGAH' WHERE codigo = $disciplina->codigo";
  	  if (!$result = mysql_query($sql)){
	  	 	if ($DEBUG) echo "<br>Erro ao atualizar disciplina $sql \n";
      	mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
  		}
  	}
  }
}

// REGISTRA A ATUALIZACAO
$sql = "insert into Atualizacoes values(0,3,".$_SESSION['loginCodigo'].", now())";
mysql_query($sql);

if (!$LOCATION_CRON) {
	?>
	<script>
		$('#cursosnRetorno').text('<?php print $i; ?> cursos processados | <?php print $j; ?> disciplinas processadas');
	</script><?php
} else {
	$sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
	$resultAdmin = mysql_query($sqlAdmin);
	$admin = mysql_fetch_object($resultAdmin);
	
	$sql = "insert into Atualizacoes values(0,103,".$admin->codigo.", now())";
	mysql_query($sql);
		
	$URL = "CURSOS NOVOS: $i |DISCIPLINAS: $j";
	$sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
	mysql_query($sql);
}
?>