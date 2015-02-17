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
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>
<div class='fundo_listagem'>
    <table width="100%" align="center" style="border: 0px solid black">
        <tr class="listagem_tr">
        <th align="center" width="30">#</th>
        <th align="center" width="200">Descri&ccedil;&atilde;o</th>
        <th align="left" width="255">Link</th>
        <th align="left" width="255">Arquivo</th>
        <th align="left" width="150">Data</th>
        </tr>
    <?php
    // efetuando a consulta para listagem
    $i = 1;
    foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            if ($reg['arquivo'])
                $path_parts = pathinfo($reg['arquivo']);
            ?>
            <tr <?= $cdif ?>><td align='left'><?= $i++ ?></td>
                <td><a href="#" title="<?=$reg['descricao']?>"><?= abreviar(nl2br(mostraTexto($reg['descricao'])),25) ?></a></td>
                <td>
                    <?php if ($reg['link']) {
                        ?>
                        <img class='botao' src='<?= ICONS . 'files/internet.png' ?>' />
                        <?php
                    }
                    ?>                    
                    <a title="<?=$reg['link']?>" href="<?= $reg['link'] ?>" target="_blank"><?= abreviar($reg['link'],25) ?></a>
                </td>
                <td>
                    <?php
                    if ($reg['arquivo']) {
                        if (strlen($reg['arquivo']) > 25)
                            $fileName = abreviar($reg['arquivo'],25).$path_parts['extension'];
                        else
                            $fileName = $reg['arquivo'];
                        ?>
                        <a title="Clique aqui para abrir o arquivo." href="<?= INC ?>/file.inc.php?type=arquivo&id=<?= crip($reg['codigo']) ?>" target="_blank">
                            <?php
                            if (is_file(PATH . ICONS . 'files/' . $path_parts['extension'] . '.png')) {
                                $fileIcon = 'files/' . $path_parts['extension'] . '.png';
                            } else {
                                $fileIcon = 'files/file.png';
                            }
                            ?>
                            <img class='botao' src='<?= ICONS . $fileIcon ?>' /></a>
                            <a href="<?=INC?>/file.inc.php?type=arquivo&id=<?=crip($reg['codigo'])?>&pessoa=<?=crip($_SESSION['loginCodigo'])?>" target="_blank"><?= $fileName ?></a>
                        <?php
                    }
                    ?>
                </td>
            <td><?= $reg['data'] ?></td>
        </tr>
        <?php
    }
    ?>
</table>
</div>
<br /><br /><br />
