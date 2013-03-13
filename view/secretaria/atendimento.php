<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela, em que é possível visualizar os horários de disponibilidade de atendimento aos discentes de cada docente do Campus.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

if (isset($_GET["professor"])) {
	$professor = dcrip($_GET["professor"]);
	if ($professor != 'Todos') $restricao = " AND p.codigo = $professor";
}
?>
<h2><?php print $TITLE; ?></h2>

<table border="0" width="100%" id="form" width="100%">
	<tr><td>Professor: </td><td><select name="campoProfessor" id="campoProfessor" style="width: 350px">
    <?php
    $sql = "SELECT p.codigo, p.nome FROM Pessoas p, PessoasTipos pt
    							WHERE p.codigo = pt.pessoa
    							AND pt.tipo = $PROFESSOR
    							ORDER BY p.nome";
    $resultado = mysql_query($sql);
    $selected = "";
    if (mysql_num_rows($resultado) > 0) {
    	echo "<option value='".crip("Todos")."'>Todos</option>";
      while ($linha = mysql_fetch_array($resultado)) {
      	if ($linha[0] == $professor)
        	$selected = "selected";
        echo "<option $selected value='".crip($linha[0])."'>$linha[1]</option>";
        $selected = "";
    	}
    }
    else {
    	echo "<option>Não há professores cadastrados.</option>";
    }
    ?>
		</select>
	</td></tr>
</table>

<?php
    // inicializando as variï¿½veis
    $item = 1;
    $itensPorPagina = 20;
    $primeiro = 1;
    $anterior = $item - $itensPorPagina;
    $proximo = $item + $itensPorPagina;
    $ultimo = 1;

    // validando a pï¿½gina atual
    if (!empty($_GET["item"])){
        $item = $_GET["item"];
        $anterior = $item - $itensPorPagina;
        $proximo = $item + $itensPorPagina;
    }

    // validando a pï¿½gina anterior
    if ($item - $itensPorPagina < 1)
        $anterior = 1;

    // descobrindo a quantidade total de registros
    $resultado = mysql_query("SELECT COUNT(*)
    												FROM Pessoas p, PessoasTipos pt
						    						WHERE p.codigo = pt.pessoa
							 							AND pt.tipo = $PROFESSOR
							 							$restricao
							 							ORDER BY p.nome $restricao");
    $linha = mysql_fetch_row($resultado);
    $ultimo = $linha[0];
    
    // validando o prï¿½ximo item
    if ($proximo > $ultimo){
        $proximo = $item;
        $ultimo = $item;
    }
    
    // validando o ï¿½ltimo item
    if ($ultimo % $itensPorPagina > 0)
        $ultimo=$ultimo-($ultimo % $itensPorPagina)+1;    

	if (!isset($_GET["codigo"])){
		$SITENAV = $SITE."?curso=".crip($curso)."&numeroDisciplina=".crip($numero)."&nomeDisciplina=".crip($nome);
	} else $SITENAV = $SITE."?";

$dias = diasDaSemana();
$sql = "SELECT p.codigo, p.nome, p.lattes 
							FROM Pessoas p, PessoasTipos pt
 							WHERE p.codigo = pt.pessoa
 							AND pt.tipo = $PROFESSOR
 							$restricao
 							ORDER BY p.nome limit ". ($item - 1) . ",$itensPorPagina";
$resultado = mysql_query($sql);
if (mysql_num_rows($resultado) > 0) {
	print "<table border=\"0\" id=\"form\" width=\"100%\">\n";
	print "<tr><td colspan=\"3\">\n";
		require PATH . VIEW . '/paginacao.php';

	print "</td></tr>\n";
	while ($l = mysql_fetch_array($resultado)) {
		$url='';
		if ($l[2] != '') {
			if (strpos($l[2],'http://') === FALSE) 
				$url = "<b>Lattes</b><br><a target=\"_blank\" href=\"".'http://'.$l[2]."\">".$l[2]."</a>";
			else
				$url = "<b>Lattes</b><br><a target=\"_blank\" href=\"".$l[2]."\">".$l[2]."</a>";
		}
		print "<tr><td colspan=\"3\"><h2>$l[1]</h2></td></tr>\n";
		print "<tr><td width=\"100\"><img alt=\"foto\" style=\"width: 100px; height: 90px\" src=\"".INC."/file.inc.php?type=pic&id=".crip($l[0])."\" /></td>\n";
		print "<td>$url</a></td>\n";
		print "<td width=\"200\">\n";
		foreach(getAtendimentoAluno($l[0]) as $dia => $h) {
			$diaSemana = $dias[$dia+1];
			$ES = $h[1].' &agrave;s '.$h[2];
			print "$diaSemana das $ES<br>";
		}
		print "</td>\n";
		print "</tr>\n";
	}
	print "</table>\n";
}
mysql_close($conexao);
?>
    
<script>
$(document).ready(function(){
	$('#campoProfessor').change(function(){
			$('#index').load('<?php print $SITE; ?>?professor='+ $('#campoProfessor').val());
  });
});