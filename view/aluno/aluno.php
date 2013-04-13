<?php 
//Esse arquivo é fixo para o aluno. Não entra em permissões
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;

if (!in_array($ALUNO, $_SESSION["loginTipo"])) {
    print "Você não tem permissão. :(";
    die;
}
?>
<table width="100%" align="center" border="0">
<?php
if ($_GET["atribuicao"]) {
    $atribuicao=dcrip($_GET["atribuicao"]);
    $aluno = $_SESSION["loginCodigo"];

    require CONTROLLER . "/atribuicao.class.php";
    $att = new Atribuicoes();
    $res = $att->getAtribuicao($atribuicao);
    
    $bimestre="SEMESTRAL";
    $fechamento=$res['fechamento'];
    $_SESSION['semestre']=$res['semestre'];
    $_SESSION['ano']=$res['ano'];

    foreach(getProfessor($atribuicao) as $key => $reg)
        $professores[] = "<a title='Curr&iacute;culo Lattes' target=\"_blank\" href=".$reg['lattes'].">".$reg['nome']."</a>";
    $professor = implode(" / ", $professores);

    if ($fechamento == 'a') $bimestre="ANUAL";

    $numeroBimestre=null;
    if ($res['bimestre']!="" && $res['bimestre']>0){
        $numeroBimestre=$res['bimestre'];
        $bimestre=abreviar($res['bimestre']."&ordm; BIMESTRE", 100);
    }

    echo "<h2>".abreviar($res['disciplina'].": ".$res['turma']."/".$res['curso'], 150)."</h2>";
    echo "<h2 id='titulo_disciplina_modalidade'>$bimestre<br></h2><br />";
    echo "<tr align='center'>";
    echo "<td><a class='nav professores_item' href=\"javascript:$('#aluno').load('".VIEW."/aluno/aula.php?atribuicao=".crip($atribuicao)."'); void(0);\"><img style='width: 100px' src='".IMAGES."/aulas.png' /><br />Aulas</a></td>";
    echo "<td><a class='nav professores_item' href=\"javascript:$('#aluno').load('".VIEW."/aluno/avaliacao.php?atribuicao=".crip($atribuicao)."'); void(0);\"><img style='width: 100px' src='".IMAGES."/avaliacoes.png' /><br />Avalia&ccedil;&otilde;es</a></td>";
    echo "<td><a class='nav professores_item' href=\"javascript:$('#aluno').load('".VIEW."/aluno/ensalamento?atribuicao=".crip($atribuicao)."'); void(0);\"><img style='width: 100px' src='".IMAGES."/horario.png' /><br />Hor&aacute;rio da Disciplina</a></td>";
    echo "<td><a class='nav professores_item' href=\"javascript:$('#aluno').load('".VIEW."/aluno/aviso?atribuicao=".crip($atribuicao)."'); void(0);\"><img style='width: 100px' src='".IMAGES."/aviso.png' /><br />Avisos</a></td>";
    echo "<td><a class='nav professores_item' href=\"javascript:$('#aluno').load('".VIEW."/aluno/boletim.php?turma=".crip($res['turmaCodigo'])."&aluno=".crip($aluno)."&bimestre=".crip($numeroBimestre)."'); void(0);\"><img style='width: 100px' src='".IMAGES."/boletim.png' /><br />Boletim Bimestral</a></td>";
    if ($bimestre=="SEMESTRAL" || $bimestre=="1&ordm; BIMESTRE")
    	echo "<td><a class='nav professores_item' href=\"javascript:$('#aluno').load('".VIEW."/aluno/planoEnsino.php?atribuicao=".crip($atribuicao)."'); void(0);\"><img style='width: 100px' src='".IMAGES."/planoEnsino.png' /><br />Plano de Ensino</a></td>";
    echo "</tr>";

    echo "<tr><td colspan=10 align='center'>\n";
    print "<hr>\n";
    print "PROFESSOR: <b>$professor</b>";
    print "<hr>\n";  
    print "</tr></tr>";
    print "</table>\n";
}

mysql_close($conexao);

?>
<div id="aluno"></div>
<script>
    $('#aluno').load('<?php print VIEW."/aluno/aviso?atribuicao=".crip($atribuicao); ?>');
</script>