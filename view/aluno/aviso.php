<?php
//Esse arquivo é fixo para o aluno.
//Tela em que o aluno visualiza os avisos enviados por professores.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;
require PERMISSAO;

    $atribuicao = dcrip($_GET["atribuicao"]);
    $aluno = $_SESSION["loginCodigo"];

    require CONTROLLER . "/aviso.class.php";
    $aviso = new Avisos();
    $res = $aviso->getAvisoAtribuicao($aluno, $atribuicao);
    ?>
    <h2><?=$TITLE?></h2>
    <div class='professores_textarea'>
    <table width="100%" align="center" style="border: 0px solid black">
    <tr class="listagem_tr"><th align="center" style="width: 150px">Data</th>
        <th align="center">Conte&uacute;do</th></tr>
    <?php
    $i=0;
    if ($res) {
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?php print $cdif; ?>><td align='left'><?php print $reg['Data']; ?></td>
            <td align='left'><?php print nl2br($reg['Conteudo']); ?></td>
            <?php
            $i++;
        }
    }
    ?>
    </table>
    </div>