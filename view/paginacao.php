<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Navegação do Sistema
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0

if (!$DIV_SITE) $DIV_SITE = '#index';

if (!$itensPorPagina)
    $itensPorPagina = 50;

$primeiro = 1;
$anterior = $item - $itensPorPagina;
$proximo = $item + $itensPorPagina;
$ultimo = 1;

// validando a p�gina atual
if (!empty($item)) {
    $anterior = $item - $itensPorPagina;
    $proximo = $item + $itensPorPagina;
}

// validando a página anterior
if ($item - $itensPorPagina < 1)
    $anterior = 1;

$ultimo = $totalRegistros;

// validando o próximo item
if ($proximo > $ultimo) {
    $proximo = $item;
    $ultimo = $item;
}

// validando o último item
if ($ultimo % $itensPorPagina > 0)
    $ultimo = $ultimo - ($ultimo % $itensPorPagina) + 1;

?>

<table id="listagemPaginacaoTopo" align="center">
    <tr>
        <td colspan="4" id="rodapeTabela">
            <?php
            if ($item != $primeiro) {
                ?>
                <a class="nav" href="javascript:$('<?= $DIV_SITE ?>').load('<?= $SITENAV ?>&item=<?= $primeiro ?>&ordem=<?= $ordem ?>'); void(0);" title="primeiro"><img class="botao" src="<?= ICONS ?>/rewind.png" /></a>
                <?php
            } else {
                ?>
                <img class="botao" title="botao desativado" src="<?= ICONS ?>/rewind_dis.png" />
                <?php
            } // fecha o if
            if ($item != $anterior) {
                ?>
                <a class="nav" href="javascript:$('<?= $DIV_SITE ?>').load('<?= $SITENAV ?>&item=<?= $anterior ?>&ordem=<?= $ordem ?>'); void(0);" title="anterior"><img class="botao" src="<?= ICONS ?>/wleft.png" /></a>
                <?php
            } else {
                ?>
                <img class="botao" title="botao desativado" src="<?= ICONS ?>/wleft_dis.png" />
                <?php
            } // fecha o if
            // ADICIONANDO NUMEROS DAS PAGINAS
            $pagina = '';
            $pagAtual = intval($item / $itensPorPagina);
            if ($pagAtual >= 12)
                $pagina = $pagAtual - 11;
            $ultimoItem = (intval($ultimo / $itensPorPagina) >= 23) ? ($itensPorPagina * (23 + $pagina)) : $ultimo;
            for ($i = ($pagina * $itensPorPagina); $i < $ultimoItem; $i+=$itensPorPagina) {
                if ($ultimo / $itensPorPagina >= $pagina) {
                    $size = ($pagina == $pagAtual) ? 4 : 2;
                    $pagina++;
                    //$pagina = str_pad($pagina, strlen(intval($ultimo/$itensPorPagina)), 0, STR_PAD_LEFT);
                    print "<font size=\"$size\"><a href=\"javascript:$('$DIV_SITE').load('$SITENAV&item=" . ($i + 1) . "&ordem=$ordem'); void(0);\" title=\"Página $pagina\">$pagina</a></font>\n";
                }
            }
            // FIM DOS NUMEROS DE PAGINAS

            if ($item != $proximo) {
                ?>
                <a class="nav" href="javascript:$('<?= $DIV_SITE ?>').load('<?= $SITENAV ?>&item=<?= $proximo ?>&ordem=<?= $ordem ?>'); void(0);" title="próximo"><img class="botao" src="<?= ICONS ?>/wright.png" /></a>
                <?php
            } else {
                ?>
                <img class="botao" title="botao desativado" src="<?= ICONS ?>/wright_dis.png" />
                <?php
            } // fecha o if
            if ($item != $ultimo) {
                ?>
                <a class="nav" href="javascript:$('<?= $DIV_SITE ?>').load('<?= $SITENAV ?>&item=<?= $ultimo ?>&ordem=<?= $ordem ?>'); void(0);" title="último"><img class="botao" src="<?= ICONS ?>/fastforward.png" /></a>
                <?php
            } else {
                ?>
                <img class="botao" title="botao desativado" src="<?= ICONS ?>/fastforward_dis.png" />
                <?php
            } // fecha o if
            ?>
        </td>
    </tr>

</table>
