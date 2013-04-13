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

if (in_array($ALUNO, $_SESSION["loginTipo"])) {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $aluno = $_SESSION["loginCodigo"];

    require CONTROLLER . "/aviso.class.php";
    $aviso = new Avisos();
    $res = $aviso->listAvisos($aluno, $atribuicao);
    
    print "<h2>Avisos</h2>\n";
    print "<div class='professores_textarea'>";
    print "<table width=\"100%\" align=\"center\" style=\"border: 0px solid black\">\n";
    print "<tr class=\"listagem_tr\"><th align=\"center\" style=\"width: 150px\">Data</th><th align=\"center\">Conte&uacute;do</th></tr>\n";

    $i=0;
    if ($res) {
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            $data = $linha[0];
            $conteudo = $linha[1];
            echo "<tr $cdif><td align='left'>".$res['data']."</td>";
            echo "<td align='left'>" . nl2br($res['conteudo']) . "</td>";
            $i++;
        }
    }
    print "</table>\n";
    print "</div>\n";
}
?>