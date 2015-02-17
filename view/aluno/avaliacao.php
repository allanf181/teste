<?php
//Esse arquivo é fixo para o aluno.
//Visualização das avaliações e notas aplicadas.
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

$params = array(':aluno' => $aluno, ':atribuicao' => $atribuicao);
$sqlAdicional = ' ORDER BY al.nome ';
$res = $avaliacao->listAvaliacoesAluno($params, $sqlAdicional);
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>
<div class='fundo_listagem'>
    <table width="100%" align="center" style="border: 0px solid black">
        <tr class="listagem_tr">
            <th align="center" style="width: 100px">Data</th>
            <th align="center">Avalia&ccedil;&atilde;o</th>
            <th align="center" style="width: 50px">Sigla</th>
            <th align="center" style="width: 200px">Tipo</th>
            <th align="center" style="width: 100px">C&aacute;lculo</th>
            <th align="center" style="width: 40px">Peso</th>
            <th width="50" align='center'>Nota</th>
        </tr>
        <?php
        $i = 0;
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?= $cdif ?>><td align='left'><?= $reg['data'] ?></td>
                <td align='left'><?= $reg['nome'] ?></td>
                <td align='left'><?= $reg['sigla'] ?></td>
                <td align='left'><?= $reg['tipoAval'] ?></td>
                <td align='left'><?= $$reg['calculo'] ?></td>
                <td align='left'><?= $reg['peso'] ?></td>
                <td align='left'><?= $reg['nota'] ?></td>
            </tr>
            <?php
            $i++;
        }
        ?>
    </table>
</div>