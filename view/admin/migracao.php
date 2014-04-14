<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela onde é possível realizar a migração dos dados do antigo sistema BRT Acadêmico ao sistema atual Web Diário.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

include("inc/mysql.php");
include("inc/variaveis.inc.php");
include("inc/mensagens.inc.php");
include("inc/permissao.inc.php");

// FUNCAO QUE EXIBE AULAS E AVALIACOES DO WEBDIARIO ATUAL (OBS: NECESSARIO ESPECIFICAR CONFIGURAR mysqlMigracao.php
$bdMigracao = $bd;
$servidorMigracao = $servidor;
$usuarioMigracao = $usuario;
$senhaMigracao = $senha;
function conectBd2(){
    global $servidorMigracao, $usuarioMigracao, $senhaMigracao, $bdMigracao;
    
    # Conecta ao sgbd
    $conexao = mysql_connect($servidorMigracao, $usuarioMigracao, $senhaMigracao) or die (mysql_error());
    # Configura o charset 
    //mysql_set_charset('utf8');
    mysql_set_charset('latin1');
    # Seleciona o bd  
    mysql_select_db($bdMigracao);   

}
?>
<h2><?php print $TITLE; ?></h2>

<?php
// inicializando as variÃ¡veis do formulÃ¡rio
$codigo="";
$turma="";
$turma2="";
$atribuicao="";
$atribuicao2="";


$ordem="d.nome";

if (isset($_GET["turma"])){
    $turma=dcrip($_GET["turma"]);
    if (!empty($turma))
    $restricao.=" and t.codigo=$turma";
}
if (isset($_GET["atribuicao"])){
    $atribuicao=dcrip($_GET["atribuicao"]);
}
if (isset($_GET["turma2"])){
    $turma2=dcrip($_GET["turma2"]);
    if (!empty($turma2))
    $restricao2.=" and t.codigo=$turma2";
}
if (isset($_GET["atribuicao2"])){
    $atribuicao2=dcrip($_GET["atribuicao2"]);
}

if (isset($_GET["ordem"])){
    $ordem=$_GET["ordem"];
    if ($ordem=="d")
    $ordem="d.nome";
    else if ($ordem=="t")
    $ordem="t.numero";
}

// IMPORTACAO / ATUALIZACAO
if (isset($_GET["importar"])){
    // BUSCANDO AULAS
    include "inc/mysqlMigracao.php";
    $sql = "select * from Aulas where atribuicao=$atribuicao2";
//    //echo "<br>".$sql;
    $result = mysql_query($sql);
    while ($r = mysql_fetch_object($result)) {
        $aulas[] = $r;
    }

    // BUSCANDO TIPOS AVALIACOES
    include "inc/mysqlMigracao.php";
    $sql = "select * from TiposAvaliacoes";
//    //echo "<br>".$sql;
    $result = mysql_query($sql);
    while ($r = mysql_fetch_object($result)) {
        $tiposAvaliacoes[] = $r;
    }

    // BUSCANDO AVALIACOES
    include "inc/mysqlMigracao.php";
    $sql = "select * from Avaliacoes where atribuicao=$atribuicao2";
//    //echo "<br>".$sql;
    $result = mysql_query($sql);
    while ($r = mysql_fetch_object($result)) {
        $avaliacoes[] = $r;
    }

    // IMPORTANDO AULAS
    conectBd2();
    foreach ($aulas as $a) {
        $codigo = 0;
        $data = $a->data;
        $quantidade = $a->quantidade;
        $conteudo = addslashes($a->conteudo);
        $anotacao = '';
        $atividade = '';
//        $atribuicao = $a->atribuicao;

        mysql_set_charset('utf8');
        $sql = "insert into Aulas values($codigo, '$data', $quantidade, '$conteudo', '$anotacao', '$atividade', $atribuicao)";
//        echo "<br>".$sql;

        $result = mysql_query($sql) or die(mysql_error());    
    }

    // IMPORTANDO TIPOS AVALIACOES
    conectBd2();
    foreach ($tiposAvaliacoes as $a) {
        $codigo = 0;
        $nome = $a->nome;
        $tipo = getTipo($a->nome);
        $modalidade = getModalidade($atribuicao);
        $calculo = 'sub_media';
        $arredondar = 1;
        $notaMaior = 5.9;
        $notaMenor = 2;
        $sigla = getSigla($nome);
        $final = 0;
        $notaUltimBimestre = 4;
        $qdeMinima = 0;
        $notaMaxima = 10;
        
        $sql = "insert into TiposAvaliacoes values($codigo, '$nome', '$tipo', $modalidade, '$calculo', $arredondar, $notaMaior, $notaMenor, '$sigla', $final, $notaUltimBimestre, $qdeMinima, $notaMaxima)";
//        echo "<br>".$sql;
        
        $result = mysql_query($sql) or die(mysql_error());    
    }    
    
    // IMPORTANDO AVALIACOES
    conectBd2();
    foreach ($avaliacoes as $a) {
        $codigo = 0;
        $data = $a->data;
        $nome = $a->nome;
        $sigla =  getSigla($nome);
        $peso = $a->peso;
//        $atribuicao = $a->atribuicao;
        $tipo = $a->tipo;

        mysql_set_charset('utf8');
        $sql = "insert into Avaliacoes values($codigo, '$data', '$nome', '$sigla', '$peso', $atribuicao, $tipo)";
        //echo "<br>".$sql;

        $result = mysql_query($sql) or die(mysql_error());    
    }

    // BUSCANDO FREQUENCIAS ORIGEM
    include "inc/mysqlMigracao.php";
    $sql = "select p.prontuario as prontuario, a.data as aula, f.quantidade as quantidade "
            . "from Frequencias f, Aulas a, Pessoas p, Matriculas m "
            . "where a.codigo=f.aula "
            . "and m.aluno=p.codigo "
            . "and f.matricula=m.codigo "
            . "and a.atribuicao=$atribuicao2";
//        //echo "<br>".$sql;
    $result = mysql_query($sql) or die(mysql_error());  
    while ($r = mysql_fetch_object($result)) {
        $frequenciasOrigem[$r->prontuario][$r->aula]=$r->quantidade;            
    }            
    
    // BUSCANDO NOTAS ORIGEM
    include "inc/mysqlMigracao.php";
    $sql = "select p.prontuario as prontuario, a.data as data, n.nota as nota, a.nome as nome "
            . "from Notas n, Avaliacoes a, Pessoas p, Matriculas m "
            . "where a.codigo=n.avaliacao "
            . "and m.aluno=p.codigo "
            . "and n.matricula=m.codigo "
            . "and a.atribuicao=$atribuicao2";
        //echo "<br>".$sql;
    $result = mysql_query($sql) or die(mysql_error());  
    while ($r = mysql_fetch_object($result)) {
        $notasOrigem[$r->prontuario][$r->nome]=$r->nota;            
    }            
    
    // IMPORTANDO FREQUENCIAS
    conectBd2();   
    foreach ($frequenciasOrigem as $prontuario => $codAula) {
        $codigo = 0;
        $matricula = getMatricula($prontuario, $atribuicao);
        foreach ($aulas as $aula) {
            $codAula = getAula($aula->data, $atribuicao);
            $quantidade = $frequenciasOrigem[$prontuario][$aula->data];
            $quantidade2="";
            for ($i=0; $i<$quantidade; $i++) {
                $quantidade2 .= "F";
            }

            mysql_set_charset('utf8');
            if (!empty($codAula) && !empty($matricula)){
                $sql = "insert into Frequencias values($codigo, $matricula, $codAula, \"$quantidade2\")";
                //echo "<br>".$sql;
                $result = mysql_query($sql) or die(mysql_error());              
            }
            else{
//                echo "<br>    codAula=$codAula; matricula=$matricula";
            }
        }
    }
    // IMPORTANDO NOTAS
    conectBd2();   
    foreach ($notasOrigem as $prontuario => $nome) {
        $codigo = 0;
        $matricula = getMatricula($prontuario, $atribuicao);
        foreach ($avaliacoes as $avaliacao) {
            $codAvaliacao = getAvaliacao($avaliacao->nome, $atribuicao);
            $nota = $notasOrigem[$prontuario][$avaliacao->nome];

            mysql_set_charset('utf8');
            if (!empty($codAvaliacao) && !empty($matricula)){
                $sql = "insert into Notas values($codigo, $matricula, $codAvaliacao, '$nota')";
                //echo "<br>".$sql;
                $result = mysql_query($sql) or die(mysql_error());              
            }
            else{
//                print "<br>\n////=>codAvaliacao=$codAvaliacao; matricula=$matricula";
            }
        }
    }
    
    mensagem('OK', 'TRUE_INSERT');
}

function getModalidade($atribuicao){
    $sql = "select m.codigo as modalidade from Turmas t, Cursos c, Modalidades m, Atribuicoes a "
            . "where t.curso=c.codigo and c.modalidade=m.codigo and a.turma=t.codigo "
            . "and a.codigo=$atribuicao";
    $result = mysql_query($sql) or die(mysql_error());  
    return mysql_fetch_object($result)->modalidade;
}

function getTipo($avaliacao){
    if (substr($avaliacao, 0, 1)=="A")
        return "Avaliação";
    else
        return "Recuperação";
}

function getSigla($texto){
    $words = explode(" ", $texto);
    $acronym = "";

    foreach ($words as $w) {
      $acronym .= $w[0];
    }
    
    return $acronym;
}

function getAvaliacao($nome, $atribuicao){
    $sql = "select a.codigo as avaliacao from Avaliacoes a "
            . "where a.nome='$nome' and a.atribuicao=$atribuicao ";
//    echo "<br>getAvaliacao: ".$sql;
    $result = mysql_query($sql);
    return mysql_fetch_object($result)->avaliacao;
}

function getAula($data, $atribuicao){
    $sql = "select a.codigo as aula from Aulas a "
            . "where a.data='$data' and a.atribuicao=$atribuicao ";
//    echo "<br>getAula: ".$sql;
    $result = mysql_query($sql);
    return mysql_fetch_object($result)->aula;
}

function getMatricula($prontuario, $atribuicao){
    $sql = "select m.codigo as matricula from Matriculas m, Pessoas p "
            . "where m.aluno=p.codigo "
            . "and m.atribuicao=$atribuicao "
            . "and p.prontuario='$prontuario'";
//    echo "<br>getMatricula: ".$sql;
    $result = mysql_query($sql);
    return mysql_fetch_object($result)->matricula;
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
<table align="center" align="left" id="form" width="100%" border="0">
	<input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
        <tr><td align="center" style="width: 100px" colspan="2">Destino</td></tr>
	<tr><td align="right" style="width: 100px">Turma: </td><td>
		<select name="campoTurma" id="campoTurma" value="<?php echo $turma; ?>" style="width: 650px">
			<option></option>
			<?php

			$resultado = mysql_query("select distinct t.codigo, t.numero, c.nome, m.nome, m.codigo 
                        							from Cursos c, Turmas t, Modalidades m
                        							where t.curso=c.codigo 
                        							and m.codigo = c.modalidade
													and t.ano=$ano and (t.semestre=$semestre OR t.semestre=0) 
													order by c.nome, t.numero");
			$selected=""; // controla a alteraÃ§Ã£o no campo select
			while ($linha = mysql_fetch_array($resultado)){
				if ($linha[0]==$turma)
				$selected="selected";
                if ($linha[4] < 1000 || $linha[4] >= 2000) $linha[2] = "$linha[2] [$linha[3]]";				
				echo "<option $selected value='".crip($linha[0])."'>[$linha[1]] $linha[2]</option>";
				$selected="";
			}

			?>
		</select>
	</td></tr><?php
                                $sql = "select a.codigo, d.nome, p.nome, a.bimestre, a.subturma "
                                . "from Disciplinas d, Atribuicoes a, Pessoas p, Professores pr "
                                . "where a.disciplina=d.codigo and pr.professor=p.codigo and pr.atribuicao=a.codigo and a.turma=$turma"
                                        . " order by d.nome, a.bimestre, a.subturma";
//                        echo $sql;

        ?>
	<tr><td align="right" style="width: 100px">Atribuição: </td><td>
		<select name="campoAtribuicao" id="campoAtribuicao" value="<?php echo $atribuicao; ?>" style="width: 650px">
			<option></option>
			<?php

			$resultado = mysql_query($sql);
			$selected=""; // controla a alteraÃ§Ã£o no campo select
			while ($linha = mysql_fetch_array($resultado)){
				if ($linha[0]==$atribuicao)
				$selected="selected";
                                if ($linha[3]!="0") $bim="[$linha[3]ºBim]";
                                if ($linha[4]!="0") $grupo=" $linha[4]";
                                if ($linha[4] < 1000 || $linha[4] >= 2000) $linha[2] = "$linha[2] $grupo [$linha[3]ºBim]";	
				echo "<option $selected value='".crip($linha[0])."'> $linha[1] $linha[2]</option>";
				$selected="";
			}

			?>
		</select>
	</td></tr>
        <tr><td align="center" style="width: 100px" colspan="2">Origem</td></tr>
	<tr><td align="right" style="width: 100px">Turma: </td><td>
		<select name="campoTurma2" id="campoTurma2" value="<?php echo $turma2; ?>" style="width: 650px">
			<option></option>
			<?php

                        include "inc/mysqlMigracao.php";
			$resultado = mysql_query("select distinct t.codigo, t.numero, c.nome, m.nome, m.codigo 
                        							from Cursos c, Turmas t, Modalidades m
                        							where t.curso=c.codigo 
                        							and m.codigo = c.modalidade
													and t.ano=$ano and (t.semestre=$semestre OR t.semestre=0) 
													order by c.nome, t.numero");
			$selected=""; // controla a alteraÃ§Ã£o no campo select
			while ($linha = mysql_fetch_array($resultado)){
				if ($linha[0]==$turma2)
				$selected="selected";
                if ($linha[4] < 1000 || $linha[4] >= 2000) $linha[2] = "$linha[2] [$linha[3]]";				
				echo "<option $selected value='".crip($linha[0])."'>[$linha[1]] $linha[2]</option>";
				$selected="";
			}

			?>
		</select>
	</td></tr><?php
                                $sql = "select a.codigo, d.nome, p.nome, a.bimestre, a.grupo "
                                . "from Disciplinas d, Atribuicoes a, Pessoas p "
                                . "where a.disciplina=d.codigo and a.professor=p.codigo and a.turma=$turma2";
//                        echo $sql;

        ?>
	<tr><td align="right" style="width: 100px">Atribuição: </td><td>
		<select name="campoAtribuicao2" id="campoAtribuicao2" value="<?php echo $atribuicao2; ?>" style="width: 650px">
			<option></option>
			<?php

                        include "inc/mysqlMigracao.php";
			$resultado = mysql_query($sql);
			$selected=""; // controla a alteraÃ§Ã£o no campo select
			while ($linha = mysql_fetch_array($resultado)){
				if ($linha[0]==$atribuicao2)
				$selected="selected";
                                if ($linha[3]!="0") $bim="[$linha[3]ºBim]";
                                if ($linha[4]!="0") $grupo=" $linha[4]";
                                if ($linha[4] < 1000 || $linha[4] >= 2000) $linha[2] = "$linha[2] $grupo $bim";				
				echo "<option $selected value='".crip($linha[0])."'>[$linha[1]] $linha[2]</option>";
				$selected="";
			}

			?>
		</select>
	</td></tr>
<tr><td>&nbsp;</td><td><input type="button" value="Importar" id="importar" /><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">&nbsp;Limpar</a></td></tr>

</table>

</form>

<?php
// inicializando as variÃ¡veis
$item = 1;
$itensPorPagina = 25;
$primeiro = 1;
$anterior = $item - $itensPorPagina;
$proximo = $item + $itensPorPagina;
$ultimo = 1;

// validando a pÃ¡gina atual
if (!empty($_GET["item"])){
	$item = $_GET["item"];
	$anterior = $item - $itensPorPagina;
	$proximo = $item + $itensPorPagina;
}

// validando a pÃ¡gina anterior
if ($item - $itensPorPagina < 1)
$anterior = 1;

// descobrindo a quantidade total de registros
$sql = "SELECT count(*) FROM Aulas a
                WHERE a.codigo=$atribuicao";//print $sql;
$resultado = mysql_query($sql);
$linha = mysql_fetch_row($resultado);
$ultimo = $linha[0];

// validando o prÃ³ximo item
if ($proximo > $ultimo){
	$proximo = $item;
	$ultimo = $item;
}

// validando o Ãºltimo item
if ($ultimo % $itensPorPagina > 0)
$ultimo=$ultimo-($ultimo % $itensPorPagina)+1;

$SITENAV = $SITE."?turma=".crip($turma)."&disciplina=".crip($disciplina)."&professor=".crip($professor);

require('inc/navegacao.php'); ?>

<table id="listagem" border="0" align="center">
	<tr><th align="left" width="30">Aula</th><th align="left" width="100">Data</th><th>Qtd</th><th>Conteúdo</th></tr>
	<?php
        
         conectBd2();
	// efetuando a consulta para listagem
	$sql = "SELECT * FROM Aulas a
                WHERE atribuicao=$atribuicao";
//	echo $sql;
	$resultado = mysql_query($sql);
	$i = $item;
	if ($resultado){
            if (mysql_num_rows($resultado)>0){
		while ($linha = mysql_fetch_array($resultado)) {
			$i%2==0 ? $cdif="class='cdif'" : $cdif="class='cdif2'";
			$codigo = crip($linha[0]);
			echo "<tr $cdif><td align='left'>$linha[0]</td><td>$linha[1]</td><td align='left'>$linha[2]</td><td align=left>$linha[3] </td></tr>";
			$i++;
		}
            }
            else{
                echo "<tr><td colspan=10>Não há aulas cadastradas!</td></tr>";
            }
	}
	mysql_close($conexao);
	?>

	<?php require('inc/navegacao.php'); ?>
	
<script>
function atualizar(getLink){
    var turma = $('#campoTurma').val();
    var turma2 = $('#campoTurma2').val();
    var atribuicao = $('#campoAtribuicao').val();
    var atribuicao2 = $('#campoAtribuicao2').val();
	var URLS = '<?php print $SITE; ?>?turma=' + turma+"&atribuicao="+atribuicao+"&turma2="+turma2+"&atribuicao2="+atribuicao2;
//        alert(URLS);
	if (!getLink)
		$('#index').load(URLS + '&item=<?php print $item; ?>');
	else
		return URLS;
}

$(document).ready(function(){
	$("#importar").click(function(){ 
		$('#index').load(atualizar(1) +'&importar=1');
	});

	$(".ordenacao").click(function(){ 
		$('#index').load(atualizar(1) +'&ordem='+ $(this).attr('id'));
	});

	$(".item-excluir").click(function(){
		var codigo = $(this).attr('id');
		jConfirm('Aten&ccedil;&atilde;o, ser&atilde;o exclu&iacute;das as avalia&ccedil;&otilde;es, notas e ensalamentos gerados para essa atribui&ccedil;&atilde;o. Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
			if ( r )	
				$('#index').load(atualizar(1) + '&opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
		});
	});
	
   	$('#campoTurma').change(function(){
    	atualizar();
	});
   	$('#campoAtribuicao').change(function(){
    	atualizar();
	});
   	$('#campoTurma2').change(function(){
    	atualizar();
	});
   	$('#campoAtribuicao2').change(function(){
    	atualizar();
	});
});    
</script>