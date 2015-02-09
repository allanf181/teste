<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Exibe a Folha de Trabalho Docente cadastradas e finalizadas pelos Professores.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
require CONTROLLER . "/ftdDado.class.php";
$ftd = new FTDDados();

if ($_GET["opcao"] == 'controleFTD') {
    $_GET['codigo'] = crip($_GET['codigo']);
    $_GET['solicitante'] = $_SESSION['loginCodigo'];
    unset($_GET['opcao']);
    unset($_GET['_']);
    
    if ($_GET["conferido"] != 'false' && !$_GET["solicitacao"]) {
        $_GET["valido"] = date('Y-m-d H:m:s');
    } else {
        $_GET["valido"] = null;
        $_GET["finalizado"] = null;
    }
    unset($_GET['conferido']);

    $ret = $ftd->insertOrUpdate($_GET);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}

// PAGINACAO
$itensPorPagina = 20;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

$params['professor'] = $PROFESSOR;
$params['ano'] = $ANO;
$params['sem'] = $SEMESTRE;

$res = $ftd->listFTDs($params, $item, $itensPorPagina, $sqlAdicional);
$totalRegistros = count($ftd->listFTDs($params, null, null, $sqlAdicional));

$SITENAV = $SITE . '?';
require PATH . VIEW . '/paginacao.php';
?>
<table id="listagem" border="0" align="center" width="100%">
    <tr><th align="center" width="40">#</th><th>Professor</th><th>Entregue</th>
        <?php
        if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
            ?>
            <th align="center" style="width: 300px">Coordenador</th>
            <?php
        }
        if (in_array($COORD, $_SESSION["loginTipo"]) || in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
            ?>
            <th width="100" title='Solicitar Corre&ccedil;&atilde;o?'>Corre&ccedil;&atilde;o</th>
            <th width="30" title='Marcar como conferido?'>Conf?</th>
            <?php
        }
        ?>
    </tr>
    <?php
    $i = $item;
    foreach ($res as $reg) {
        $checked = null;
        $bloqueado = null;
        $correcao = null;

        if ($reg['finalizado'] == '' || $reg['finalizado'] == '00/00/0000 00:00')
            $reg['finalizado'] = 'NÃO';

        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        ?>
        <tr <?= $cdif ?>>
            <td><?= $i ?></td>
            <td>
                <a target='_blank' href='<?= VIEW ?>/secretaria/relatorios/inc/ftd.php?detalhada=1&professor=<?= crip($reg['pessoaCodigo']) ?>'><?= mostraTexto($reg['professor']) ?></a>
            </td>
            <td><?= $reg['finalizado'] ?></td>
            <?php
            if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
                if ($reg['valido'] != "00/00/0000 00:00" && $reg['valido'] != "") {
                    ?>
                    <td><?= $reg['solicitante'] ?></td>
                    <?php
                } else {
                    ?>
                    <td style='color:red; font-weight: bold'>pendente</td>
                    <?php
                }
            }

            // VERIFICA SE JÀ FOI CORRIGIDO
            if ($reg['valido'] != "00/00/0000 00:00" && $reg['valido'] != "") {
                ?>
                <td>Corrigido</td>
                <?php
                $checked = "checked='checked'";
            } else if ($reg['solicitacao']) {
                ?>
                <td align='center' colspan='3'>
                    <a href='#' title='Correção solicitada por <?= $reg['solicitante'] ?>'>Correção solicitada</a>
                </td>
                <?php
                $correcao = 1;
            } else {
                if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"]) || in_array($COORD, $_SESSION["loginTipo"])) {
                    ?>
                    <td align='center'>
                        <a href='#' title='Solicitar correção' onclick="return FTD('<?= $reg['codigo'] ?>', '<?= $reg['professor'] ?>')">
                            <img class='botao campoCorrecao' id='<?= crip($reg['codigo']) ?>' name='<?= $reg['codigo'] ?>' src='<?= ICONS ?>/cancel.png' />
                        </a>
                    </td>
                    <?php
                }
            }

            if (!$correcao) {
                if ($reg['valido'] != "00/00/0000 00:00" || $reg['valido'] != "") {
                    if (!in_array($ADM, $_SESSION["loginTipo"]) && !in_array($COORD, $_SESSION["loginTipo"]) && !in_array($GED, $_SESSION["loginTipo"]))
                        $bloqueado = "disabled='disabled' title='pendente'";

                    if (!in_array($ADM, $_SESSION["loginTipo"]) && !in_array($GED, $_SESSION["loginTipo"]) && $checked)
                        $bloqueado = "disabled='disabled' title='Somente GED'";
                    ?>
                    <td align='center'>
                        <input <?= $bloqueado ?> type='checkbox' <?= $checked ?> id='<?= $reg['codigo'] ?>' value='<?= $valor ?>' onclick="return confFTD(this.value, this.checked, '<?= $reg['professor'] ?>', '<?= $reg['codigo'] ?>')">
                    </td>
                    <?php
                } else {
                    ?>
                    <td>&nbsp;</td>
                    <?php
                }
            }
            ?>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>
<script>
    function FTD(codigo, nome) {
        $.Zebra_Dialog('<strong>Confirma a solicitação de correção na FTD de ' + nome + '? \n\n Motivo:</strong>', {
            'type': 'prompt',
            'title': '<?php print $TITLE; ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function(caption, valor) {
                if (caption == 'Sim') {
                    $('#index').load('<?php print $SITE; ?>?opcao=controleFTD&codigo=' + codigo + '&solicitacao=' + encodeURIComponent(valor));
                }
            }
        });
    };        
    function confFTD(value, checked, nome, codigo) {
        if (!checked)
            modo = '<strong>Confirma abrir a FTD de ' + nome + '?</strong>';
        else
            modo = '<strong>Confirma a conferência da FTD de ' + nome + '?<br><br>Atenção: somente o GED poderá abrir novamente!</strong>';

        $.Zebra_Dialog(modo, {
            'type': 'question',
            'title': '<?php print $TITLE; ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function(caption) {
                if (caption == 'Sim') {
                    $('#index').load('<?php print $SITE; ?>?opcao=controleFTD&codigo=' + codigo + '&conferido=' + checked);
                } else {
                    document.getElementById(codigo).checked = !checked;
                }
            }
        });
    }
</script>