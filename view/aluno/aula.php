<?php

//Esse arquivo é fixo para o aluno. Não entra em permissões
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;


if (in_array($ALUNO, $_SESSION["loginTipo"])) {
    $atribuicao=dcrip($_GET["atribuicao"]);
    $aluno = $_SESSION["loginCodigo"];
    
    require CONTROLLER . "/aula.class.php";
    $aula = new Aula();
    $res = $aula->listAulas($aluno, $atribuicao);
    print "<h2>Aulas</h2>\n";
    print "<div class='professores_textarea'>\n";
    print "<table width=\"100%\" align=\"center\" style=\"border: 0px solid black\">\n";
    print "<tr class=\"listagem_tr\"><th align=\"center\" style=\"width: 100px\">Data</th><th align=\"center\">Conte&uacute;do</th><th width=\"50\" align='center'>Falta</th></tr>\n";
    $i = 0;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        if (isset($reg['freqMat'])) $regFreq = explode(',', $reg['freqMat']);
        if (!$A = getFrequencia($reg[1], $atribuicao, $reg['data'])) {
            $falta = $regFreq[0];
            if ($falta) {
                $F = $falta;
            } else {
                $F = str_repeat('*', $reg['quantidade']);
            }
        } else {
            $F = $A['nome'];
        }

        echo "<tr $cdif><td align='left'>".$reg['dataFormatada']."</td>";
        echo "<td align='left'>".$reg['conteudo']."</td>";
        echo "<td align='center'>$F</td></tr>";
        $i++;
    }
    print "</table>\n";
    print "</div>\n";
}
?>