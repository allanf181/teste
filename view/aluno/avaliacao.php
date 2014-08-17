<?php
//Esse arquivo é fixo para o aluno.
//Tela em que o aluno visualiza as avaliações e notas.
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

require CONTROLLER . "/avaliacao.class.php";
$avaliacao = new Avaliacoes();
$res = $avaliacao->listAvaliacoesAluno($aluno, $atribuicao);
?>
<h2><?= $TITLE ?></h2>
<div class='professores_textarea'>
    <table width="100%" align="center" style="border: 0px solid black">
        <tr class="listagem_tr">
            <th align="center" style="width: 100px">Data</th>
            <th align="center">Avalia&ccedil;&atilde;o</th><th width="50" align='center'>Nota</th>
        </tr>
        <?php
        $i = 0;
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?= $cdif ?>><td align='left'><?= $reg['data'] ?></td>
                <td align='left'><?= $reg['conteudo'] ?></td>
                <td align='left'><?= $reg['falta'] ?></td></tr>
            <?php
            $i++;
        }
        ?>
    </table>
</div>