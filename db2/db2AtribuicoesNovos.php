<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON"."db2Mysql.php");
    require("$LOCATION_CRON"."db2.php");
    require("$LOCATION_CRON"."db2Funcoes.php");
    require("$LOCATION_CRON"."db2Variaveis.inc.php");
}

mysql_set_charset('latin1');

$sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
$resultAdmin = mysql_query($sqlAdmin);
$admin = mysql_fetch_object($resultAdmin);

//PEGANDO UMA SALA PADRAO
$db2 = "SELECT * FROM ESCOLA.SALAS ORDER BY SL_SALA FETCH FIRST 1 ROWS ONLY";
$res1 = db2_exec($conn, $db2);
while ($r = db2_fetch_object($res1)){
	$SALA_PADRAO = $r->SL_SALA;
}

for ($n=1; $n <= 2; $n++) {
	$nS = str_pad($n,2,"0",STR_PAD_LEFT);
	
	$db2 = "SELECT DISTINCT CM_CDCURSO,ED_PERIODO,CM_MODULO,CM_MODUSEQ,D_DISC,ED_DTINICIO,ED_DTFINAL,ED_AUPREV,ED_EVENTOD,
                NH_AULA,NH_PERIODO,NH_SALA,NH_DIASEM,NH_PROFESSOR
                FROM ESCOLA.NHORARIO, ESCOLA.EVTDISC, ESCOLA.CURSOMOD, ESCOLA.V_DISCMOD, ESCOLA.MATCURSO
                WHERE ED_EVENTOD = NH_EVENTOD
                AND ED_DISC = NH_DISC
                AND CM_MODULO = DM_MODULO
          	    AND DO_DISC = NH_DISC
            	  AND NH_VERSAOH = $ano$nS
              	AND MC_CDCURSO = CM_CDCURSO
                AND mc_alpront IN
                (select md_pront from escola.matdis where md_disc= ED_DISC and md_eventod = ED_EVENTOD)";
	$res = db2_exec($conn, $db2);

	if (db2_stmt_error() == 42501) {
		$ERRO = "SEM ACESSO NA TABELA ATRIBUICOES (CURSOS NOVOS)";
		mysql_query("INSERT INTO Logs VALUES (0, '".addslashes($ERRO)."', now(), 'CRON_ERRO', 1)");
		print $ERRO;
	}

	$i=0;
	while ($row = db2_fetch_object($res)) {
		// BUSCA O CODIGO DA DISCIPLINA
  	$DISC = trim($row->D_DISC);

    $sql = "select * from Disciplinas d where d.curso='".$row->CM_CDCURSO."' and d.numero='$DISC'";
    $result = mysql_query($sql);
    $disciplina = mysql_fetch_object($result);
		if ($disciplina) {
			// BUSCA O CODIGO DO TURNO
			$turno = getTurno($row->ED_PERIODO);
		
			$numero = 'N'.$row->CM_MODULO.$row->CM_MODUSEQ;
		
			// VERIFICA SE H� TURMA
	    $sql = "SELECT * FROM Turmas t 
	    		WHERE numero='$numero' 
	    		AND curso='".$row->CM_CDCURSO."'
	    		AND semestre = $n";

      $result = mysql_query($sql);
      if ($turma = mysql_fetch_object($result)) {
	   		// BUSCAR O PROFESSOR DA DISCIPLINA
       	$sqlProf = "SELECT * FROM Pessoas WHERE prontuario='$row->NH_PROFESSOR'";
   			$resultProf = mysql_query($sqlProf);
   			if (!$professor = mysql_fetch_object($resultProf))
				$professor->codigo = $admin->codigo;
					
		   	$sql = "SELECT * FROM Atribuicoes 
	     				WHERE disciplina = $disciplina->codigo 
	     				AND turma = $turma->codigo
	     				AND bimestre = 0
	     				AND eventod = $row->ED_EVENTOD";
				$att = mysql_query($sql);
		   	if (mysql_num_rows($att) == '') {
	      	// IMPORTA A ATRIBUICAO
	        $sql = "insert into Atribuicoes (codigo, disciplina, turma, bimestre, dataInicio, dataFim, aulaPrevista, status, grupo, eventod) "
	                      . " values (0, "
	                      . "$disciplina->codigo, "
	                       . "$turma->codigo, "
	                       . "0, "// BIMESTRE
		    	           . "'".$row->ED_DTINICIO."',"
			               . "'".$row->ED_DTFINAL."',"
	                       . "$row->ED_AUPREV, "
	                       . "0,0, $row->ED_EVENTOD)";// STATUS
		      if (!$result = mysql_query($sql)){
		      	if ($DEBUG) echo "<br>Erro ao importar ATRIBUICAO: $sql \n";
		       		mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
		   	  } else {
		      	$i++;
	   				$COD = mysql_insert_id();
		       	$REG = "ATRIBUICAO (CURSO NOVO): $COD";
		       	mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_AN', 1)");
		        if ($DEBUG) print "$REG <br>\n"; 
		        $sql = "INSERT INTO Professores VALUES (NULL, $professor->codigo, $COD)";
			      if (!$result = mysql_query($sql)){
			      	if ($DEBUG) echo "<br>Erro ao importar ATRIBUICAO/PROFESSOR: $sql \n";
			       		mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
		   		  }
		    	}
	    	} else {
	    		$COD = mysql_result($att, 0, "codigo");
        	$sql = "UPDATE Atribuicoes SET dataInicio='".$row->ED_DTINICIO."', dataFim='".$row->ED_DTFINAL."' WHERE codigo = $COD";
        	if (!$result = mysql_query($sql)){
		       	if ($DEBUG) echo "<br>Erro ao atualizar ATRIBUICAO: $sql \n";
		    	 		mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
		      }
		       	
		      $sql = "SELECT * FROM Professores 
	     				WHERE professor = $professor->codigo 
	     				AND atribuicao = $COD";
					$prof2 = mysql_query($sql);
		   		if (mysql_num_rows($prof2) == '') {
		       	$sql = "INSERT INTO Professores VALUES (NULL, $professor->codigo, $COD)";
			     	if (!$result = mysql_query($sql)){
			    	 	if ($DEBUG) echo "<br>Erro ao importar ATRIBUICAO/PROFESSOR: $sql \n";
			      		mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
		   		  }
		   		}
	    	}
	    	
	    	if ($COD) {
				$HOR = 'AULA '.$row->NH_AULA. ' ['.$row->NH_PERIODO.']';
				$sqlHOR = "SELECT * FROM Horarios WHERE nome='$HOR'";
				$resultHOR = mysql_query($sqlHOR);
				$HORARIO = @mysql_fetch_object($resultHOR);
				// PEGANDO A SALA
	    	if ($row->NH_SALA == 0) $row->NH_SALA=$SALA_PADRAO;
				$sqlSA = "SELECT * FROM Salas WHERE nome='SALA $row->NH_SALA'";
				$resultSA = mysql_query($sqlSA);
				$SALA = @mysql_fetch_object($resultSA);
	
		   	if ($SALA && $HORARIO) {
		     	$sqlENS = "SELECT * FROM Ensalamentos WHERE atribuicao=$COD AND sala=$SALA->codigo AND diaSemana=$row->NH_DIASEM AND horario=$HORARIO->codigo AND professor=$professor->codigo";
		    	$resultENS = mysql_query($sqlENS);
		    	$ensalamento = mysql_fetch_object($resultENS);
		    	if (!$ensalamento) {	   			
						$sql = "INSERT INTO Ensalamentos VALUES (NULL, $COD, $professor->codigo, $SALA->codigo, $row->NH_DIASEM, $HORARIO->codigo)";
		     		if (!$result = mysql_query($sql)){
				     	if ($DEBUG) echo "<br>Erro ao importar ENSALAMENTO: $sql \n";
				     		mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
				 	    } else {
				     		$j++;
				     		$REG = "ENSALAMENTO (CURSO NOVO): $COD";
				     		mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_EN', 1)");
				       	if ($DEBUG) print "$REG <br>\n"; 
				   		}
	     			}
	     		}
	     	}
			}
   	}
	}
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
	$sql = "insert into Atualizacoes values(0,7,".$_SESSION['loginCodigo'].", now())";
	mysql_query($sql);
	?>
	<script>
  		$('#atribuicoesnRetorno').text('<?php print $i; ?> registros processados...');
	</script><?php
} else {
	$sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
	$resultAdmin = mysql_query($sqlAdmin);
	$admin = mysql_fetch_object($resultAdmin);
	
	$sql = "insert into Atualizacoes values(0,107,".$admin->codigo.", now())";
	mysql_query($sql);
		
	$URL = "ATRIBUI&Ccedil;&Otilde;ES CURSOS NOVOS: $i";
	$sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
	mysql_query($sql);
}
?>