<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON"."db2Mysql.php");
    require("$LOCATION_CRON"."db2.php");
    require("$LOCATION_CRON"."db2Funcoes.php");
    require("$LOCATION_CRON"."db2Variaveis.inc.php");
    require("$LOCATION_CRON"."../inc/funcoes.inc.php");
}

mysql_set_charset('latin1');

$inicio = $ano."-01-01";
$fim = $ano."-12-31";

$i=0;
$j=0;

situacaoAntigo();

$db2 = "SELECT * 
				FROM ESCOLA.ALTURMAS 
    		WHERE AT_ANO = $ano";
$result = db2_exec($conn, $db2);

if (db2_stmt_error() == 42501) {
	$ERRO = "SEM ACESSO NA TABELA MATRICULAS (CURSOS ANTIGOS)";
	mysql_query("insert into Logs values(0, '".addslashes($ERRO)."', now(), 'CRON_ERRO', 1)");
	print $ERRO;
}

while ($row = db2_fetch_object($result)) {
	$turma = 'A'.$row->AT_TURMA;
	$subturma = trim($row->AT_SUBTURMA);

	// BUSCA A LISTA DE ATRIBUICOES
    $sql = "SELECT a.codigo FROM Atribuicoes a, Turmas t 
    		WHERE a.turma=t.codigo AND t.numero='$turma' AND subturma LIKE '%$subturma%'";
    $result1 = mysql_query($sql);
    while ($atribuicao = mysql_fetch_object($result1)) {
	   // BUSCA O CODIGO DO ALUNO
	   $sql = "SELECT p.codigo FROM Pessoas p, PessoasTipos pt 
   				WHERE p.codigo = pt.pessoa 
   				AND pt.tipo=$ALUNO 
   				AND p.prontuario='$row->AT_PRONT'";
	   $res = mysql_query($sql);
	   if ($aluno = mysql_fetch_object($res)) {
	
	       // VERIFICA SE A MATRICULA EXISTE
	       $sql = "select * from Matriculas where aluno='$aluno->codigo' and atribuicao='$atribuicao->codigo' and data>='$inicio' and data<='$fim'";
	
	       $res = mysql_query($sql);
	       $matricula = mysql_fetch_object($res);            
	       $situacao='10'.$row->AT_STATUS;
		
	       if (empty($matricula)) { // NÃO EXISTE, ENTÃO IMPORTA
	           $sql = "insert into Matriculas (codigo, aluno, atribuicao, situacao, data, dataAlteracao) values (0,
	               $aluno->codigo,
	               $atribuicao->codigo,
	               $situacao,
	               now(), now())";
	
		       	if (!$result2 = mysql_query($sql)){
		        	if ($DEBUG) echo "<br>Erro ao importar MATRICULA: $sql \n";
		       		mysql_query("insert into Logs values(0, '".addslashes($sql)."', now(), 'CRON_ERRO', 1)");
		   	    } else {
		       		$i++;
		       		$REG = "MATRICULA NOVA (CURSO ANTIGO): ". $aluno->codigo;
		       		mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_MA', 1)");
		        	if ($DEBUG) print "$REG <br>\n"; 
		       	}
	       	} else {
	       		if ($matricula->situacao != $situacao) {
	       			mysql_query("UPDATE Matriculas SET situacao = $situacao, dataAlteracao=now() WHERE codigo = $matricula->codigo");
	  		  	    if ($DEBUG) print "MATRICULA NOVA - ALTERACAO DE SITUACAO (CURSO ANTIGO): ". $aluno->codigo . "<br>\n";
	       			$j++;
	       		}
	    	}
	   }
	}
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
	$sql = "insert into Atualizacoes values(0,10,".$_SESSION['loginCodigo'].", now())";
	mysql_query($sql);
	?>
	<script>
  		$('#matriculasaRetorno').text('<?php print $i; ?> importadas, <?php print $j; ?> atualizadas');
	</script><?php
} else {
	$sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
	$resultAdmin = mysql_query($sqlAdmin);
	$admin = mysql_fetch_object($resultAdmin);
	
	$sql = "insert into Atualizacoes values(0,110,".$admin->codigo.", now())";
	mysql_query($sql);
		
	$URL = "MATR&Iacute;CULAS (CURSOS ANTIGOS) IMPORTADAS: $i |ATUALIZADAS $j ";
	$sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
	mysql_query($sql);
}
?>