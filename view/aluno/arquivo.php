<?php
//Esse arquivo é fixo para o aluno.
//Possibilita o download de material de aula.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;
require PERMISSAO;


require CONTROLLER . "/arquivo.class.php";
$arq = new Arquivos();

$params = array('codigo' => $_GET["atribuicao"]);
$res = $arq->listArquivos($params, $item, $itensPorPagina);
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>
<div class='fundo_listagem'>
    <table width="100%" align="center" style="border: 0px solid black">
        <tr class="listagem_tr">
        <th align="center" width="30">#</th><th align="center" width="300">Descri&ccedil;&atilde;o</th><th align="left" width="255">Link</th><th align="left">Arquivo</th><th align="left" width="160">Data</th></tr>
    <?php
    // efetuando a consulta para listagem
    $i = 1;
    foreach ($res as $reg) {
        $reg['data'] = dataPTBR($reg['data']);
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        if ($reg['arquivo'])
            $path_parts = pathinfo($reg['arquivo']);
        ?>
        <tr <?= $cdif ?>><td align='left'><?= $i++ ?></td>
            <td><?= nl2br(mostraTexto($reg['descricao'])) ?></td>
            <td>
                <?php if ($reg['link']) {
                    ?>
                    <img class='botao' src='<?= ICONS . 'files/internet.png' ?>' />
                    <?php
                }
                ?>                    
                <a href="<?= $reg['link'] ?>" target="_blank"><?= $reg['link'] ?></a>
            </td>
            <td>
                <?php
                if ($reg['arquivo']) {
                    if (is_file(PATH . ICONS . 'files/' . $path_parts['extension'] . '.png')) {
                        $fileIcon = 'files/' . $path_parts['extension'] . '.png';
                    } else {
                        $fileIcon = 'files/file.png';
                    }
                    ?>
                    <img class='botao' src='<?= ICONS . $fileIcon ?>' />
                    <?php
                }
                ?>
                <a href="<?=INC?>/file.inc.php?type=arquivo&id=<?=crip($reg['codigo'])?>&pessoa=<?=crip($_SESSION['loginCodigo'])?>" target="_blank"><?= $reg['arquivo'] ?></a>
            </td>
            <td><?= $reg['data'] ?></td>
        </tr>
        <?php
    }
    ?>
</table>
</div>