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
require SESSAO;

if (in_array($ALUNO, $_SESSION["loginTipo"])) {
    $atribuicao=dcrip($_GET["atribuicao"]);
    $aluno = $_SESSION["loginCodigo"];
    
    require CONTROLLER . "/avaliacao.class.php";
    $avaliacao = new Avaliacoes();
    $res = $avaliacao->listAvaliacoesAluno($aluno, $atribuicao);
    
    print "<h2>Avalia&ccedil;&otilde;es</h2>\n";
    print "<div class='professores_textarea'>\n";
    print "<table width=\"100%\" align=\"center\" style=\"border: 0px solid black\">\n";
    print "<tr class=\"listagem_tr\"><th align=\"center\" style=\"width: 100px\">Data</th><th align=\"center\">Avalia&ccedil;&atilde;o</th><th width=\"50\" align='center'>Nota</th></tr>\n";
    $i = 0;
    foreach($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        echo "<tr $cdif><td align='left'>".$reg['data']."</td>";
        echo "<td align='left'>".$reg['conteudo']."</td>";
        echo "<td align='left'>".$reg['falta']."</td></tr>";
        $i++;
    }
    print "</table>\n";
    print "</div>\n";
}
?>