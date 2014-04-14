<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita a tela que exibe um quadro estatístico de acessos e ações realizadas no sistema pelos docentes dos Campus.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

?>
<style>
#principal{
  width:300px;
  height:60px;
  margin-left:10px;
  font-family:Arial;
  font-size: 16px;
  text-align:center;
}
#barras{
  width:328px;
  height:20px;
  float:left;
  margin: 2px 0;
}
.barra1, .barra2 { 	
  color:#FFF;
  padding-left:0px;
  height:20px;
  line-height:20px;
}
.barra1{ background-color: #0000FF; }
.barra2{ background-color: #FF0000; }
</style>

<?php
$resultado = mysql_query("SELECT
        SUM((SELECT COUNT(*) FROM Aulas au WHERE au.atribuicao = a.codigo)) as aula,
        SUM((SELECT COUNT(*) FROM Frequencias f, Aulas au WHERE au.codigo = f.aula AND au.atribuicao = a.codigo)) as frequencia,
        SUM((SELECT COUNT(*) FROM Avaliacoes av WHERE av.atribuicao = a.codigo)) as avaliacao,
        SUM((SELECT COUNT(*) FROM Avaliacoes av, Notas n WHERE av.codigo = n.avaliacao AND av.atribuicao = a.codigo)) as nota
        FROM Atribuicoes a, Disciplinas d, Turmas t, Professores pr, Pessoas p
        WHERE a.disciplina = d.codigo
        AND t.codigo = a.turma
        AND pr.atribuicao = a.codigo
        AND p.codigo = pr.professor
		AND (t.semestre=$semestre OR t.semestre=0)
		AND t.ano = $ano
		GROUP BY pr.professor ORDER BY aula DESC, frequencia DESC, avaliacao DESC, nota DESC");
$uso=0;
$count=0;
while ($linha = mysql_fetch_array($resultado)) { 
	if ($linha[0] || $linha[1] || $linha[2] || $linha[3])
		$uso++;
	$count++;
}
$uso = round (($uso*100)/$count);
$width1 = "$uso%";
$width2 = (100-$uso).'%';
$total  = 2;
print "<div id=\"principal\">\n";
print "<p><b>Porcentagem de Uso do Sistema</b></p>\n";
for($i=1;$i <= $total;$i++){
	$width = ${'width' . $i};
	print "<div id=\"barras\">\n";
    print "<div class=\"barra$i\" style=\"width:$width\">$width</div>\n";
	print "</div>\n";
}
print "</div>\n";

// inicializando as vari?veis
$item = 1;
$itensPorPagina = 50;
$primeiro = 1;
$anterior = $item - $itensPorPagina;
$proximo = $item + $itensPorPagina;
$ultimo = 1;

// validando a p?gina atual
if (!empty($_GET["item"])){
   $item = $_GET["item"];
   $anterior = $item - $itensPorPagina;
   $proximo = $item + $itensPorPagina;
}

// validando a p?gina anterior
if ($item - $itensPorPagina < 1)
   $anterior = 1;

// descobrindo a quantidade total de registros
$resultado = mysql_query("SELECT COUNT(*)
		FROM Atribuicoes a, Disciplinas d, Turmas t, Professores pr, Pessoas p
        WHERE a.disciplina = d.codigo
        AND t.codigo = a.turma
        AND pr.atribuicao = a.codigo
        AND p.codigo = pr.professor        
	AND (t.semestre=$semestre OR t.semestre=0)
	AND t.ano = $ano
	GROUP BY pr.professor ORDER BY aula DESC, frequencia DESC, avaliacao DESC, nota DESC, p.nome ASC");
$linha = mysql_fetch_row($resultado);
$ultimo = $linha[0];

// validando o pr?ximo item
if ($proximo > $ultimo){
   $proximo = $item;
   $ultimo = $item;
}

// validando o ?ltimo item
if ($ultimo % $itensPorPagina > 0)
   $ultimo=$ultimo-($ultimo % $itensPorPagina)+1;    

$SITENAV = $SITE."?";
require(PATH.VIEW.'/navegacao.php'); ?>

<table id="listagem" border="0" align="center">
<tr><th width='220'>Nome</th><th align='center' width='80'>Aulas Lan&ccedil;adas</th><th align='center' width='90'>Frequ&ecirc;ncias Cadastradas</th><th align='center' width='80'>Avalia&ccedil;&otilde;es</th><th align='center' width='30'>Notas Lan&ccedil;adas</th><th align='center' width='70'>&Uacute;ltimo Registro de Aula</th></tr>
<?php
// efetuando a consulta para listagem
$resultado = mysql_query("SELECT p.nome,
        SUM((SELECT COUNT(*) FROM Aulas au WHERE au.atribuicao = a.codigo)) as aula,
        SUM((SELECT COUNT(*) FROM Frequencias f, Aulas au WHERE au.codigo = f.aula AND au.atribuicao = a.codigo)) as frequencia,
        SUM((SELECT COUNT(*) FROM Avaliacoes av WHERE av.atribuicao = a.codigo)) as avaliacao,
        SUM((SELECT COUNT(*) FROM Avaliacoes av, Notas n WHERE av.codigo = n.avaliacao AND av.atribuicao = a.codigo)) as nota,
        (SELECT date_format(data, '%d/%m/%Y') FROM Aulas ad WHERE ad.atribuicao = a.codigo ORDER BY data DESC LIMIT 1) as ultAula
        FROM Atribuicoes a, Disciplinas d, Turmas t, Professores pr, Pessoas p
        WHERE a.disciplina = d.codigo
        AND t.codigo = a.turma
        AND pr.atribuicao = a.codigo
        AND p.codigo = pr.professor        
	AND (t.semestre=$semestre OR t.semestre=0)
	AND t.ano = $ano
	GROUP BY pr.professor ORDER BY aula DESC, frequencia DESC, avaliacao DESC, nota DESC, p.nome ASC limit ". ($item - 1) . ",$itensPorPagina");
$i = $item;
while ($linha = mysql_fetch_array($resultado)) {
   $i%2==0 ? $cdif="class='cdif'" : $cdif="";
echo "<tr $cdif><td>$linha[0]</td><td>$linha[1]</td><td align='center'>$linha[2]</td><td align='center'>$linha[3]</td><td align='center'>$linha[4]</td><td align='center'>$linha[5]</td><td align='center'>$linha[6]</td></tr>";
   $i++;
}
?>

<?php 	require(PATH.VIEW.'/navegacao.php');

mysql_close($conexao);