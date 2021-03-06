<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Exibe o Formulário de Preferência de Atividades cadastradas e finalizadas pelos Professores.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require CONTROLLER . "/tdDado.class.php";
$dados = new TDDados();

require CONTROLLER . "/logSolicitacao.class.php";
$log = new LogSolicitacoes();

require CONTROLLER . "/logEmail.class.php";
$logEmail = new LogEmails();

require CONTROLLER . "/pessoa.class.php";
$pessoa = new Pessoas();

require CONTROLLER . "/caad.class.php";
$caad = new Caads();

if ($_GET["opcao"] == 'historico') {
    $_GET['tabela'] = $tabela;
    // COPIA DE:
    require PATH . VIEW . '/common/logSolicitacao.php';
    die;
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $dados->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

if ($_GET["opcao"] == 'change') {
    $_GET['nomeTabela'] = $tabela;
    $_GET['solicitante'] = $_SESSION['loginCodigo'];
    $_GET['dataSolicitacao'] = date('Y-m-d H:m:s');
    $_GET['codigoTabela'] = $_GET['codigo'];
    $_GET['solicitacao'] = 'Correção solicitada: ' . $_GET['solicitacao'];

    unset($_GET['codigo']);
    unset($_GET['opcao']);
    unset($_GET['_']);

    $ret = $log->insertOrUpdate($_GET);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

    $params["finalizado"] = null;
    $params['codigo'] = $_GET['codigoTabela'];
    $ret = $dados->insertOrUpdate($params);

    //ENVIANDO EMAIL
    if ($ret['STATUS'] == 'OK') {
        $resPessoa = $dados->listRegistros(array('codigo' => $_GET['codigoTabela']));
        if ($email = $pessoa->getEmailFromPessoa($resPessoa[0]['pessoa']))
            $logEmail->sendEmailLogger($_SESSION['loginNome'], "Foi solicitada uma corre&ccedil;&atilde;o em sua $tabela.", $email);
    }
}

if ($_GET["opcao"] == 'controle') {
    $_GET['solicitante'] = $_SESSION['loginCodigo'];
    unset($_GET['opcao']);
    unset($_GET['_']);

    if ($_GET["conferido"] != 'false' && !$_GET["solicitacao"]) {
        $_GET["valido"] = date('Y-m-d H:m:s');
        $params['solicitacao'] = "$tabela validada";
    } else {
        $params['solicitacao'] = "$tabela aberta para alteração.";
        $_GET["valido"] = null;
        $_GET["finalizado"] = null;
    }

    $params['nomeTabela'] = $tabela;
    $params['solicitante'] = $_SESSION['loginCodigo'];
    $params['dataSolicitacao'] = date('Y-m-d H:m:s');
    $params['dataConcessao'] = date('Y-m-d H:m:s');
    $params['codigoTabela'] = $_GET['codigo'];
    $ret = $log->insertOrUpdate($params);

    unset($_GET['solicitante']);
    unset($_GET['conferido']);
    
    $_GET['codigo']=crip($_GET['codigo']);

    $ret = $dados->insertOrUpdate($_GET);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}

$params = array();

if (dcrip($_GET["area"])) {
    $area = dcrip($_GET["area"]);
    $params['area'] = $area;
    $sqlAdicional .= ' AND f.area = :area ';
}

if (dcrip($_GET["professor"])) {
    $professor = dcrip($_GET["professor"]);
    $params['professor'] = $professor;
    $sqlAdicional .= ' AND f.codigo = :professor ';
}
else if (dcrip($_GET["codigo"])) {
    $codigo = dcrip($_GET["codigo"]);
    $params['codigo'] = $codigo;
    $sqlAdicional .= ' AND f.codigo = :codigo ';
}

//LISTA OS REGISTROS DA TD
$coordenador = false;
if (in_array($COORD, $_SESSION["loginTipo"])) {
    $params['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicionalCoord = 'AND (f.area IN (SELECT c.area FROM Coordenadores c WHERE c.coordenador = :coord)';
    $coordenador = true;
}
if ($areas = $caad->getAreas($_SESSION['loginTipo'])){ // VERIFICA SE É MEMBRO DE ALGUMA CAAD
    if ($coordenador)
        $sqlAdicionalCoord.=" OR  (";
    else
        $sqlAdicionalCoord.=" AND (";
    for ($i=0; $i<count($areas); $i++){
        if ($i>0)
            $sqlAdicionalCoord.=" OR ";
            
        $params['area'.$i] = $areas[$i];
        $sqlAdicionalCoord.=" f.area = :area$i ";
    }
    if ($coordenador)
        $sqlAdicionalCoord.="))";
    else
        $sqlAdicionalCoord.=")";
}
else{
    if ($coordenador)
        $sqlAdicionalCoord.=")";
}
$sqlAdicional .= " AND f.modelo = :modelo $sqlAdicionalCoord ORDER BY p.nome ";
$params['ano'] = $ANO;
$params['semestre'] = $SEMESTRE;
$params['modelo'] = $tabela;

$res = $dados->listTDs($params, $sqlAdicional, null, null);
$totalRegistros = count($res);

?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<table align="center" width="100%" id="form">
    <tr>
        <td align="right">Professor: </td>
        <td>
            <select name="professor" id="professor" value="<?= $professor ?>" style="width: 400px">
                <option></option>
                <?php
                foreach ($res as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $professor)
                        $selected = "selected";
                    print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td align="right">Área: </td>
        <td>
            <select name="area" id="area" value="<?= $area ?>" style="width: 400px">
                <option></option>
                <?php
                foreach (array_combine(array_column($res,'area'),array_column($res,'areaNome')) as $i => $reg) {
                    $selected = "";
                    if ($i == $area)
                        $selected = "selected";
                    print "<option $selected value='" . crip($i) . "'>" . $reg . "</option>";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <table width="100%">
                <tr>
                    <td><a href="javascript:$('#index').load('<?= $SITE ?>');void(0);">Limpar</a></td>
                </tr>
            </table>
        </td>
        <td>&nbsp;</td>
    </tr>    
</table>
<?php
// PAGINACAO
$itensPorPagina = 50;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];


$SITENAV = $SITE . '?';
require PATH . VIEW . '/system/paginacao.php';
?>
<table id="listagem" border="0" align="center" width="100%">
    <tr>
        <th align="center" width="40">#</th>
        <th width="300">Professor</th>
        <th width="100">Sem/Ano</th>
        <th width="150">Entregue</th>
        <?php
        if (isset($areas) || $in_array($COORD, $_SESSION["loginTipo"]) || in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
            ?>
            <th width="150" title='Solicitar Corre&ccedil;&atilde;o?'>Solicitar corre&ccedil;&atilde;o</th>
            <th width="70" title='Marcar como conferido?'>Validar?</th>
            <th width="70">Hist&oacute;rico</th>            
            <th width="10">&nbsp;</th>
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
                <a target='_blank' href='<?= VIEW ?>/secretaria/relatorios/inc/<?= strtolower($tabela) ?>.php?professor=<?= crip($reg['pessoa']) ?>'><?= mostraTexto($reg['nome']) ?></a>
            </td>
            <td><?= $reg['semestre'] . '/' . $reg['ano'] ?></td>
            <td><?= $reg['finalizado'] ?></td>
            <?php
            // VERIFICA SE JÀ FOI CORRIGIDO
            if ($reg['valido'] != "00/00/0000 00:00" && $reg['valido'] != "") {
                ?>
                <td>
                    Corrigido
                </td>
                <?php
                $checked = "checked='checked'";
            } else if ($reg['solicitacao']) {
                ?>
                <td colspan='2'>
                    Correção solicitada
                </td>
                <?php
                $correcao = 1;
            } else {
                if (isset($areas) || in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"]) || in_array($COORD, $_SESSION["loginTipo"])) {
                    ?>
                    <td align='center'>
                        <a href='#' title='Solicitar correção' onclick="return change('<?= $reg['codigo'] ?>', '<?= $reg['nome'] ?>')">
                            <img class='botao campoCorrecao' id='<?= crip($reg['codigo']) ?>' name='<?= $reg['codigo'] ?>' src='<?= ICONS ?>/cancel.png' />
                        </a>
                    </td>
                    <?php
                }
            }

            if (!$correcao) {
                if ($reg['valido'] != "00/00/0000 00:00" || $reg['valido'] != "") {
                    if (!isset($areas) && !in_array($ADM, $_SESSION["loginTipo"]) && !in_array($COORD, $_SESSION["loginTipo"]) && !in_array($GED, $_SESSION["loginTipo"]))
                        $bloqueado = "disabled='disabled' title='pendente'";

                    if (!isset($areas) && !in_array($ADM, $_SESSION["loginTipo"]) && !in_array($GED, $_SESSION["loginTipo"]) && $checked)
                        $bloqueado = "disabled='disabled' title='Somente GED'";
                    ?>
                    <td align='center'>
                        <input <?= $bloqueado ?> type='checkbox' <?= $checked ?> id='<?= $reg['codigo'] ?>' value='<?= $valor ?>' onclick="return conf(this.checked, '<?= $reg['nome'] ?>', '<?= $reg['codigo'] ?>')">
                    </td>
                    <?php
                } else {
                    ?>
                    <td>&nbsp;</td>
                    <?php
                }
            }
            ?>
            <td align='center'>
                <a href='#' title='Ver hist&oacute;rico de solicita&ccedil;&otilde;es'>
                    <img class='botao search' id='<?= crip($reg['codigo']) ?>' src='<?= ICONS ?>/search.png' />
                </a>
            </td>
            <td align='center'>
                <a href='#' title='Excluir' class="item-excluir" id='<?= crip($reg['codigo']) ?>'>
                    <img class='botao' src='<?= ICONS ?>/delete.png' />
                </a>
            </td>                    
        </tr>
        <?php
        $i++;
    }
    ?>
</table>
<script>
    function atualizar(getLink) {
        var area = $('#area').val();
        var professor = $('#professor').val();
        var URLS = '<?= $SITE ?>?area=' + area + '&professor=' + professor;
        if (!getLink)
            $('#index').load(URLS + '&item=<?= $item ?>');
        else
            return URLS;
    }

    $('#professor, #area').change(function () {
        atualizar();
    });

    $(".search").click(function () {
        var codigo = $(this).attr('id');
        new $.Zebra_Dialog('<strong>Hist&oacute;rico de Solicita&ccedil;&otilde;es</strong>', {
            source: {'iframe': {
                    'src': 'view/secretaria/atribuicao_docente/<?= strtolower($tabela) ?>.php?opcao=historico&codigo=' + codigo,
                    'height': 300
                }},
            width: 600,
            title: '<?= $tabela ?>'
        });
    });

    $(".item-excluir").click(function () {
        var codigo = $(this).attr('id');
        $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>', {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption) {
                if (caption == 'Sim') {
                    $('#index').load(atualizar(1) + '&opcao=delete&codigo=' + codigo + '&item=<?= $item ?>');
                }
            }
        });
    });

    function change(codigo, nome) {
        $.Zebra_Dialog('<strong>Confirma a solicitação de correção na <?= $tabela ?> de ' + nome + '? <br><br>Descrever a solicitação:</strong>', {
            'type': 'prompt',
            'promptInput': '<textarea rows="2" cols="30" name="Zebra_valor" maxlength="200" id="Zebra_valor"></textarea>',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption, valor) {
                if (caption == 'Sim') {
                    $('#index').load('<?= $SITE ?>?opcao=change&codigo=' + codigo + '&solicitacao=' + encodeURIComponent(valor));
                }
            }
        });
    }

    function conf(checked, nome, codigo) {
        if (!checked)
            modo = '<strong>Confirma abrir a <?= $tabela ?> de ' + nome + '?</strong>';
        else
            modo = '<strong>Confirma a conferência da <?= $tabela ?> de ' + nome + '?<br><br>Atenção: somente o GED poderá abrir novamente!</strong>';

        $.Zebra_Dialog(modo, {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption) {
                if (caption == 'Sim') {
                    $('#index').load('<?= $SITE ?>?opcao=controle&codigo=' + codigo + '&conferido=' + checked);
                } else {
                    document.getElementById(codigo).checked = !checked;
                }
            }
        });
    }
</script>