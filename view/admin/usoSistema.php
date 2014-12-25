<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Exibe um quadro estatístico de acessos e ações realizadas no sistema pelos docentes dos Campus.
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
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<style>
    progress::-webkit-progress-bar { /* Estilizando barra de progresso */ } progress::-webkit-progress-value { /* Estilizando apenas valor do progresso */ }
    progress { /* Estilizando barra de progresso */ } progress::-webkit-progress-value { /* Estilizando apenas valor do progresso */ }
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
$uso = 0;
$count = 0;
while ($linha = mysql_fetch_array($resultado)) {
    if ($linha[0] || $linha[1] || $linha[2] || $linha[3])
        $uso++;
    $count++;
}
$uso = round(($uso * 100) / $count);
$width1 = "$uso";
$width2 = (100 - $uso);
?>
<div class='fundo_listagem'>
    <table class="listagem" align="center">
        <tr>
            <td>Utilizando: </td>
            <td><progress max="100" value="<?= $width1 ?>"></progress><?= $width1 ?>%</td>
        </tr>
        <tr>
            <td>N&atilde;o utilizado: </td><td><progress max="100" value="<?= $width2 ?>"></progress><?= $width2 ?>%</td>
        </tr>
    </table>
</div>
<?php
// inicializando as vari?veis
$item = 1;
$itensPorPagina = 50;
$primeiro = 1;
$anterior = $item - $itensPorPagina;
$proximo = $item + $itensPorPagina;
$ultimo = 1;

// validando a p?gina atual
if (!empty($_GET["item"])) {
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
if ($proximo > $ultimo) {
    $proximo = $item;
    $ultimo = $item;
}

// validando o ?ltimo item
if ($ultimo % $itensPorPagina > 0)
    $ultimo = $ultimo - ($ultimo % $itensPorPagina) + 1;

$SITENAV = $SITE . "?";
require(PATH . VIEW . '/navegacao.php');
?>

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
	GROUP BY pr.professor ORDER BY aula DESC, frequencia DESC, avaliacao DESC, nota DESC, p.nome ASC limit " . ($item - 1) . ",$itensPorPagina");
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        echo "<tr $cdif><td>$linha[0]</td><td>$linha[1]</td><td align='center'>$linha[2]</td><td align='center'>$linha[3]</td><td align='center'>$linha[4]</td><td align='center'>$linha[5]</td><td align='center'>$linha[6]</td></tr>";
        $i++;
    }
    ?>

    <?php
    require(PATH . VIEW . '/navegacao.php');

    mysql_close($conexao);
    