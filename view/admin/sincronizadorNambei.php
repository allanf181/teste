<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Realiza a sincronização da base de dados utilizada pelo WebDiário com relação à base de dados do Nambei.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>

<script>
$(document).ready(function(){  
    $("#alunos").click(function(){
        $('#alunosRetorno').load('db2/db2Alunos.php');
    });

    $("#professores").click(function(){
        $('#professoresRetorno').load('db2/db2Professores.php');
    });

    $("#horarios").click(function(){
        $('#horariosRetorno').load('db2/db2Horarios.php');
    });
    
    $("#cursosn").click(function(){
        $('#cursosnRetorno').load('db2/db2CursosDisciplinasNovos.php');
    });

    $("#cursosa").click(function(){
        $('#cursosaRetorno').load('db2/db2CursosDisciplinas.php');
    });
    
    $("#turmasn").click(function(){
        $('#turmasnRetorno').load('db2/db2TurmasNovos.php');
    });
    
    $("#turmasa").click(function(){
        $('#turmasaRetorno').load('db2/db2Turmas.php');
    });
    
    $("#atribuicoesn").click(function(){
        $('#atribuicoesnRetorno').load('db2/db2AtribuicoesNovos.php');
    });
    
    $("#atribuicoesa").click(function(){
        $('#atribuicoesaRetorno').load('db2/db2Atribuicoes.php');
    });
    
    $("#matriculasn").click(function(){
        $('#matriculasnRetorno').load('db2/db2MatriculasNovos.php');
    });
    
    $("#matriculasa").click(function(){
        $('#matriculasaRetorno').load('db2/db2Matriculas.php');
    });

    $("#digitaNotas").click(function(){
        $('#digitaNotasRetorno').load('db2/db2DigitaNotas.php');
    });
    
  $("#dispensa").click(function(){
        $('#dispensaRetorno').load('db2/db2Dispensas.php');
    });    
});

</script>

<form id="form" method="post" >
    <table align="center" border="0" class="sincronizador" width="100%">
        <!--<input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />-->
        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=1 OR a.tipo=101) and a.pessoa=p.codigo  order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca importados manualmente.";            
            if (!empty($dados->data)){
                $dataAtualizacao = $dados->data;
                if ($dados->tipo >= 100) $dados->nome = 'CRON';
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }

        ?>
        <tr><td align="right">Alunos: </td><td><? if ($enabled) { ?><input type="button" id="alunos" value="<?=$rotulo?>" /><? } ?></td><td><div id='alunosRetorno'><?=$situacao?></div></td></tr>
        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=2 OR a.tipo=102) and a.pessoa=p.codigo  order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca importados manualmente.";      
            
            if (!empty($dados->data)){
                $dataAtualizacao = $dados->data;
                if ($dados->tipo >= 100) $dados->nome = 'CRON';
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }
        ?>
        <tr><td align="right">Professores: </td><td><? if ($enabled) { ?><input type="button" id="professores" value="<?=$rotulo?>" /><? } ?></td><td><div id='professoresRetorno'><?=$situacao?></div></td></tr>
      
        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=11 OR a.tipo=111) and a.pessoa=p.codigo  order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca importados manualmente.";  
            if ($dados->tipo >= 100) $dados->nome = 'CRON';
            if (!empty($dados->data)){
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }

        ?>
        <tr><td align="right">Hor&aacute;rios e Feriados: </td><td><? if ($enabled) { ?><input type="button" id="horarios" value="<?=$rotulo?>" /><? } ?></td><td><div id='horariosRetorno'><?=$situacao?></div></td></tr>
	         
        <tr><td colspan="3"><hr></td></tr>
        
        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=3 OR a.tipo=103) and a.pessoa=p.codigo  order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca importados manualmente."; 
            if ($dados->tipo >= 100) $dados->nome = 'CRON';
            if (!empty($dados->data)){
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }

        ?>
        <tr><td align="right">Cursos Novos: </td><td><? if ($enabled) { ?><input type="button" id="cursosn" value="<?=$rotulo?>" /><? } ?></td><td><div id='cursosnRetorno'><?=$situacao?></div></td></tr>
	    

        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=5 OR a.tipo=105) and a.pessoa=p.codigo  order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca importados manualmente."; 
            if ($dados->tipo >= 100) $dados->nome = 'CRON';
            if (!empty($dados->codigo)){ //VERIFICA SE HÁ ALGUMA TURMA CADASTRADA
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }

        ?>
        <tr><td align="right">Turmas (Cursos Novos): </td><td><? if ($enabled) { ?><input type="button" id="turmasn" value="<?=$rotulo?>" /><? } ?></td><td><div id='turmasnRetorno'><?=$situacao?></div></td></tr>
    
        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=7 OR a.tipo=107) and a.pessoa=p.codigo  order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca importados manualmente.";
            if ($dados->tipo >= 100) $dados->nome = 'CRON';
            if (!empty($dados->codigo)){ //VERIFICA SE HÁ ALGUMA CADASTRADA
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }

        ?>
        <tr><td align="right">Atribui&ccedil;&otilde;es (Cursos Novos): </td><td><? if ($enabled) { ?><input type="button" id="atribuicoesn" value="<?=$rotulo?>" /><? } ?></td><td><div id='atribuicoesnRetorno'><?=$situacao?></div></td></tr>
	    

        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=9 OR a.tipo=109) and a.pessoa=p.codigo  order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca importados manualmente.";
            if ($dados->tipo >= 100) $dados->nome = 'CRON';  
            if (!empty($dados->codigo)){ //VERIFICA SE HÁ ALGUMA CADASTRADA
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }

        ?>
        <tr><td align="right">Matr&iacute;culas (Cursos Novos): </td><td><? if ($enabled) { ?><input type="button" id="matriculasn" value="<?=$rotulo?>" /><? } ?></td><td><div id='matriculasnRetorno'><?=$situacao?></div></td></tr>
        <tr><td colspan="3"><hr></td></tr>

        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=4 OR a.tipo=104) and a.pessoa=p.codigo  order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca importados manualmente.";
            if ($dados->tipo >= 100) $dados->nome = 'CRON';
            if (!empty($dados->data)){
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }

        ?>
        <tr><td align="right">Cursos Antigos: </td><td><? if ($enabled) { ?><input type="button" id="cursosa" value="<?=$rotulo?>" /><? } ?></td><td><div id='cursosaRetorno'><?=$situacao?></div></td></tr>

        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=6 OR a.tipo=106) and a.pessoa=p.codigo  order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca importados manualmente.";
            if ($dados->tipo >= 100) $dados->nome = 'CRON';
            if (!empty($dados->codigo)){ //VERIFICA SE HÁ ALGUMA TURMA CADASTRADA
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }
        ?>
        <tr><td align="right">Turmas (Cursos Antigos): </td><td><? if ($enabled) { ?><input type="button" id="turmasa" value="<?=$rotulo?>" /><? } ?></td><td><div id='turmasaRetorno'><?=$situacao?></div></td></tr>

        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=8 OR a.tipo=108) and a.pessoa=p.codigo  order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca importados manualmente.";
            if ($dados->tipo >= 100) $dados->nome = 'CRON';            
            if (!empty($dados->codigo)){ //VERIFICA SE HÁ ALGUMA CADASTRADA
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }

        ?>
        <tr><td align="right">Atribui&ccedil;&otilde;es (Cursos Antigos): </td><td><? if ($enabled) { ?><input type="button" id="atribuicoesa" value="<?=$rotulo?>" /><? } ?></td><td><div id='atribuicoesaRetorno'><?=$situacao?></div></td></tr>
		    
        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=10 OR a.tipo=110) and a.pessoa=p.codigo order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca importados manualmente.";
            if ($dados->tipo >= 100) $dados->nome = 'CRON';            
            if (!empty($dados->codigo)){ //VERIFICA SE HÁ ALGUMA CADASTRADA
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }

        ?>
        <tr><td align="right">Matr&iacute;culas (Cursos Antigos): </td><td><? if ($enabled) { ?><input type="button" id="matriculasa" value="<?=$rotulo?>" /><? } ?></td><td><div id='matriculasaRetorno'><?=$situacao?></div></td></tr>
				</td></tr>
         <tr><td colspan="3"><hr></td></tr>

        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=13 OR a.tipo=113) and a.pessoa=p.codigo  order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca importados manualmente.";
            if ($dados->tipo >= 113) $dados->nome = 'CRON';
            if (!empty($dados->data)){
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }

        ?>
        <tr><td align="right">Dispensas: </td><td><? if ($enabled) { ?><input type="button" id="dispensa" value="Sincronizar" /><? } ?></td><td><div id='dispensaRetorno'><?=$situacao?></div></td></tr>

    <tr><td colspan="3"><hr></td></tr>

        <?php
            $sql = "select * from Atualizacoes a, Pessoas p where (a.tipo=12 OR a.tipo=112) and a.pessoa=p.codigo  order by a.codigo desc limit 1";
            $result = mysql_query($sql);
            $dados = mysql_fetch_object($result);
            $enabled=true;
            $rotulo = "Importar";
            $situacao = "Dados nunca exportados manualmente.";
            if ($dados->tipo >= 112) $dados->nome = 'CRON';
            if (!empty($dados->data)){
                $ultimaAtualizacao = (date('d/m/Y H:i:s', strtotime($dados->data)))." por ". $dados->nome;
                $rotulo="Atualizar";
                $situacao = "<p class='info'>Última atualização: $ultimaAtualizacao</p>";
                $enabled=true;
            }

        ?>
        <tr><td align="right">Digita Notas: </td><td><? if ($enabled) { ?><input type="button" id="digitaNotas" value="Sincronizar" /><? } ?></td><td><div id='digitaNotasRetorno'><?=$situacao?></div></td></tr>

    		<tr><td colspan="3"><hr></td></tr>
    		<tr><td colspan="3" align="center"><a id="atualizar" href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);" title="Atualizar Resumos"><img class="botao" src="<?php print ICONS; ?>/sync.png" /></td></tr>
   
    <tr><td colspan="3"><b>RESUMO</b></td></tr>
	<tr><td colspan="3">
	<?php
		$sql = "SELECT DATE_FORMAT(data, '%d/%m/%Y %H:%i'), url 
				FROM Logs WHERE origem = 'CRON' 
				ORDER BY codigo DESC, data DESC, url ASC LIMIT 13";
    	$resultado = mysql_query($sql);
    	while ($l = mysql_fetch_array($resultado)) {
     		print "$l[0] - $l[1]<br>"; 
    	} 	
    ?>
	</td></tr>
    <tr><td colspan="3"><hr></td></tr>
    <tr><td colspan="3"><b>ERROS OCORRIDOS</b></td></tr>
	<tr><td colspan="3">
	<?php
		$sql = "SELECT DATE_FORMAT(data, '%d/%m/%Y %H:%i'), url, origem
				FROM Logs WHERE origem LIKE 'CRON_ERRO'
				ORDER BY origem ASC, url ASC, codigo DESC, data DESC";
    	$resultado = mysql_query($sql);
    	while ($l = mysql_fetch_array($resultado)) {
     		print "$l[0] - $l[1] [$l[2]]<br>"; 
    	} 	
    ?>
	</td></tr>
    <tr><td colspan="3"><hr></td></tr>
    <tr><td colspan="3"><b>IMPORTA&Ccedil;&Otilde;ES REALIZADAS</b></td></tr>
	<tr><td colspan="3">
<?php
// inicializando as vari�veis do formul�rio
$data=date("d/m/Y");
$filtro="";
$filtragem = "";
if ($_GET["data"] != '')
    $data = $_GET["data"];
if (($_GET["filtro"]) != ''){
    $filtro = $_GET["filtro"];

    $filtragem = "and (url like '%$filtro%')";
}

print "<script>\n";
print "    $('#form_padrao').html5form({ \n";
print "        method : 'POST', \n";
print "        action : '$SITE', \n";
print "        responseDiv : '#index', \n";
print "        colorOn: '#000', \n";
print "        colorOff: '#999', \n";
print "        messages: 'br' \n";
print "    }) \n";
print "</script>\n";

print "<div id=\"html5form\" class=\"main\">\n";
print "<form action=\"$SITE\" method=\"post\" id=\"form_padrao\">\n";

?>
    <table align="center" width="100%" id="form">
        <tr><td align="right" style="width: 100px">Data: </td><td><input value="<?php echo $data; ?>" type="text" name="data" id="data" onChange="$('#index').load('<?php print $SITE; ?>?filtro=<?php echo $filtro; ?>&data=' + this.value);"></td></tr>
        <tr><td align="right">Filtro: </td><td><input value="<?php echo $filtro; ?>" type="text" value="<?php echo $filtro; ?>" name=filtro" id=filtro" onblur="$('#index').load('<?php print $SITE; ?>?data=<?php echo $data; ?>&filtro=' + encodeURIComponent(this.value));" />
        <a href="#" title="Buscar"><img class="botao" style="width:15px;height:15px;" src='<?php print ICONS; ?>/sync.png' id="atualizaData" /></a>
        &nbsp;&nbsp;<a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Limpar</a></td>
	</tr>
    </table>
</form>

<?php
    // inicializando as vari�veis
    $item = 1;
    $itensPorPagina = 100;
    $primeiro = 1;
    $anterior = $item - $itensPorPagina;
    $proximo = $item + $itensPorPagina;
    $ultimo = 1;

    // validando a p�gina atual
    if (!empty($_GET["item"])){
        $item = $_GET["item"];
        $anterior = $item - $itensPorPagina;
        $proximo = $item + $itensPorPagina;
    }

    // validando a p�gina anterior
    if ($item - $itensPorPagina < 1)
        $anterior = 1;

	$SITENAV = $SITE."?data=$data&filtro=$filtro";

	$data = dataMysql($data);
    // descobrindo a quantidade total de registros
    $sql = "SELECT COUNT( * ) 
				FROM Logs WHERE origem LIKE 'CRON_%'
				AND STR_TO_DATE( data, '%Y-%m-%d' ) = '$data' $filtragem
				ORDER BY origem ASC, url ASC, codigo DESC, data DESC";
    $resultado = mysql_query($sql);
    $linha = mysql_fetch_row($resultado);
    $ultimo = $linha[0];
    
    // validando o pr�ximo item
    if ($proximo > $ultimo){
        $proximo = $item;
        $ultimo = $item;
    }
    
    // validando o �ltimo item
    if ($ultimo % $itensPorPagina > 0)
        $ultimo=$ultimo-($ultimo % $itensPorPagina)+1;    
 
	require('../../view/navegacao.php'); ?>

    <?php
    // efetuando a consulta para listagem
    $sql = "SELECT DATE_FORMAT(data, '%d/%m/%Y %H:%i'), url
				FROM Logs WHERE origem LIKE 'CRON_%'
				AND STR_TO_DATE( data, '%Y-%m-%d' ) = '$data' $filtragem
				ORDER BY origem ASC, url ASC, codigo DESC, data DESC limit ". ($item - 1) . ",$itensPorPagina";
    //echo $sql;
    $resultado = mysql_query($sql);
    while ($l = mysql_fetch_array($resultado))
    		print "$l[0] - $l[1]<br>"; 

    mysql_close($conexao);
    ?>

<?php require('../../view/navegacao.php'); ?>
 
<script>
$(document).ready(function(){
	$("#data").datepicker({
	    dateFormat: 'dd/mm/yy',
	    dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
	    dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
	    dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
	    monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
	    monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
	    nextText: 'Próximo',
	    prevText: 'Anterior'
	});
});
</script>