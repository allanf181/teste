<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita a visualização dos dados cadastrais de docentes e discentes do Campus e desbloqueio de fotos enviadas pelos alunos.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/pessoaTipo.class.php";
$pessoaTipo = new PessoasTipos();

require CONTROLLER . "/pessoa.class.php";
$pessoa = new Pessoas();

if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);
    unset($_POST['estado']);
    unset($_POST['estadoNaturalidade']);

    if (!$_POST['senha'])
        unset($_POST['senha']);

    $tipo = $_POST['tipo'];
    unset($_POST['tipo']);

    $ret = $pessoa->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    if (dcrip($_POST['codigo']))
        $_GET["codigo"] = $_POST['codigo'];
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);

    $ret = $pessoaTipo->insertOrUpdateTipo(dcrip($_GET["codigo"]), $tipo);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $pessoa->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET = null;
}

if ($_GET["opcao"] == 'removeFoto') {
    unset($_GET['opcao']);
    unset($_GET['_']);
    $_GET['foto'] = '';
    $ret = $pessoa->insertOrUpdate($_GET);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}
?>
<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
if ($_GET["opcao"] == 'validacao') {
    if ($_GET["insert"])
        $ret = $pessoa->desloqueioFoto($_GET['insert']);
    if ($_GET["delete"])
        $ret = $pessoa->removeFoto($_GET['delete']);

    if ($ret)
        mensagem('OK', 'TRUE_UPDATE');
    ?>
    <table align="center" id="form" width="100%">
        <tr>
            <td><a href="javascript:$('#index').load('<?= $SITE ?>');void(0);">Voltar</a></td>
            <td align="right"><input type="submit" name="liberar" id="liberar" value="Liberar"></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td align="right"><input type="submit" name="remover" id="remover" value="Remover"></td>
        </tr>
    </table>

    <form id="form_padrao">
        <table id="form" border="0" align="center" width="100%">
            <tr><th align="center" width="40">#</th><th align="left">Aluno</th><th align="left">Foto</th>
                <th width="40" align="center"><input type='checkbox' checked id='select-all' name='select-all' class='campoTodos' value='' /></th></tr>
            <?php
            $res = $pessoa->countBloqPic();
            $i = 1;
            if ($res[0]['total']) {
                foreach ($res as $reg) {
                    $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                    ?>
                    <tr <?= $cdif ?>>
                        <td align='center'><?= $i ?></td>
                        <td align=left><?= $reg['nome'] ?></td>
                        <td align=left>
                            <img alt="foto" width="100" src="<?= INC ?>/file.inc.php?type=pic&force=<?= crip('1') ?>&id=<?= crip($reg['codigo']) ?>" />
                        </td>
                        <?php
                        if ($reg['bloqueioFoto']) {
                            $bloqueado = 'checked';
                        }
                        ?>
                        <td align='center'>
                            <input <?= $bloqueado ?> type='checkbox' id='bloqueioFoto' name='bloqueioFoto[]' value='<?= $reg['codigo'] ?>' />
                        </td>
                    </tr>
                    <?php
                    $i++;
                }
            }
            ?>
        </table>
    </form>

    <script>
        $('#select-all').click(function (event) {
            if (this.checked) {
                // Iterate each checkbox
                $(':checkbox').each(function () {
                    this.checked = true;
                });
            } else {
                $(':checkbox').each(function () {
                    this.checked = false;
                });
            }
        });

        $(document).ready(function () {
            $('#remover').click(function (event) {
                $.Zebra_Dialog('<strong>Deseja remover as fotos selecionadas?</strong>', {
                    'type': 'question',
                    'title': '<?= $TITLE ?>',
                    'buttons': ['Sim', 'Não'],
                    'onClose': function (caption) {
                        if (caption == 'Sim') {
                            var selected = [];
                            $('input:checkbox:checked').each(function () {
                                selected.push($(this).val());
                            });

                            $('#index').load('<?= $SITE ?>?opcao=validacao&delete=' + selected);
                        }
                    }
                });
            });

            $('#liberar').click(function (event) {
                $.Zebra_Dialog('<strong>Deseja prosseguir com o desbloqueio?</strong>', {
                    'type': 'question',
                    'title': '<?= $TITLE ?>',
                    'buttons': ['Sim', 'Não'],
                    'onClose': function (caption) {
                        if (caption == 'Sim') {
                            var selected = [];
                            $('input:checkbox:checked').each(function () {
                                selected.push($(this).val());
                            });

                            $('#index').load('<?= $SITE ?>?opcao=validacao&insert=' + selected);
                        }
                    }
                });
            });
        });
    </script>
    <?php
    die;
}

require CONTROLLER . "/estado.class.php";
$estados = new Estados();

require CONTROLLER . "/cidade.class.php";
$cidades = new Cidades();

if (!empty($_GET["codigo"])) {
// LISTAGEM
    $params = array('codigo' => dcrip($_GET["codigo"]));
    $res = $pessoa->listRegistros($params);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);

    $senha = null;
    $params1['cidade'] = $cidade;
    $estado = $cidades->listCidades($params1, null, null, ' AND c.codigo = :cidade ');
    $estado = $estado[0]['codEstado'];

    $params1['cidade'] = $naturalidade;
    $estadoNaturalidade = $cidades->listCidades($params1, null, null, ' AND c.codigo = :cidade ');
    $estadoNaturalidade = $estadoNaturalidade[0]['codEstado'];

    $tipo = $pessoaTipo->getTipoPessoa($codigo);
    if (in_array($ALUNO, $tipo) || in_array($PROFESSOR, $tipo))
        $NOT_PERM = 1;
}

if ($_GET["pesquisa"] == 1) {
    $_GET["prontuario"] = crip($_GET["prontuario"]);
    $_GET["nome"] = crip($_GET["nome"]);
    $_GET["tipo"] = crip($_GET["tipo"]);
}

if (dcrip($_GET["prontuario"])) {
    $params['prontuario'] = '%' . dcrip($_GET["prontuario"]) . '%';
    $prontuario = dcrip($_GET["prontuario"]);
    $sqlAdicional .= ' AND prontuario like :prontuario ';
}

if (dcrip($_GET["nome"])) {
    $params['nome'] = '%' . dcrip($_GET["nome"]) . '%';
    $nome = dcrip($_GET["nome"]);
    $sqlAdicional .= ' AND nome like :nome ';
}

if (dcrip($_GET["tipo"])) {
    $params['tipo'] = dcrip($_GET["tipo"]);
    $tipo = dcrip($_GET["tipo"]);
    $sqlAdicional = ' AND codigo IN (SELECT pessoa FROM PessoasTipos t WHERE tipo = :tipo) ';
}
?>
<link rel="stylesheet" type="text/css" href="<?= VIEW ?>/css/aba.css" media="screen" />
<script src="<?= VIEW ?>/js/aba.js"></script>

<script>

    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?= $SITE ?>',
        responseDiv: '#index',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })
</script>

<div id="html5form" class="main">
    <form id="form_padrao">
        <input type="hidden" name="codigo" value="<?= crip($codigo) ?>" />
        <input type="hidden" name="opcao" value="InsertOrUpdate" />
        <div class="tab_container" id="form">
            <ul class="tabs">
                <li><a href="#Dados">Cadastro</a></li>
                <li><a href="#Dados2">Contato</a></li>
                <li><a href="#Dados4">Tipos</a></li>
                <li><a href="#Dados5">Foto</a></li>
                <li><a href="#Dados6">Desbloqueio de Fotos</a></li>
            </ul>
            <div class="cont_tab" id="Dados">
                <table border="0">
                    <tr>
                        <td align="right">Nome: </td>
                        <td><input type="text" <?php if ($NOT_PERM) print "readonly"; ?> style="width: 250pt" id="nome" name="nome" maxlength="45" value="<?= $nome ?>"/>
                            <a href="#" id="setNome" title="Buscar"><img class='botao' style="width:15px;height:15px;" src='<?= ICONS ?>/search.png' /></a>
                    </tr>
                    <tr>
                        <td align="right">Prontuario: </td>
                        <td><input type="text" <?php if ($codigo) print "readonly"; ?> id="prontuario" autocomplete="off" name="prontuario" maxlength="45" value="<?= $prontuario ?>"/>
                            <a href="#" id="setProntuario" title="Buscar"><img class='botao' style="width:15px;height:15px;" src='<?= ICONS ?>/search.png' /></a>
                    </tr>
                    <tr>
                        <td align="right">Senha: </td>
                        <td><input type="password" name="senha" id="senha" autocomplete="off" maxlength="20" value="<?= $senha ?>"/></td>
                    </tr>
                    <tr>
                        <td align="right">Estado: </td>
                        <td>
                            <select <?php if ($NOT_PERM) print "disabled"; ?> name="estado" id="estado" value="<?= $estado ?>">
                                <option></option>
                                <?php
                                foreach ($estados->listRegistros() as $reg) {
                                    $selected = "";
                                    if ($reg['codigo'] == $estado)
                                        $selected = "selected";
                                    echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                                }
                                ?>
                            </select>
                            Cidade: <select <?php if ($NOT_PERM) print "disabled"; ?> name="cidade" id="cidade" value="<?= $cidade; ?>">
                                <?php
                                $sqlCidade = ' AND c.codigo = :codigo ';
                                $paramsCidade['codigo'] = $cidade;
                                foreach ($cidades->listCidades($paramsCidade, null, null, $sqlCidade) as $reg) {
                                    $selected = "";
                                    if ($reg['codigo'] == $cidade)
                                        $selected = "selected";
                                    echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['cidade'] . "</option>";
                                }
                                ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Email: </td>
                        <td><input type="text" <?php if ($NOT_PERM) print "readonly"; ?> style="width: 300pt" name="email" maxlength="100" value="<?= $email ?>"/></td>
                    </tr>
                    <tr><td></td><td>
                            <input type="hidden" name="opcao" value="InsertOrUpdate" />
                            <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                                    <td><a href="javascript:$('#index').load('<?= $SITE ?>');void(0);">Novo/Limpar</a></td>
                                </tr></table>
                        </td></tr>
                </table>
            </div>

            <div class="cont_tab" id="Dados2">
                <table width="492" border="0">
                    <tr>
                        <td width="70" align="right">CPF: </td>
                        <td width="406"><input id="cpf" <?php if ($NOT_PERM) print "readonly"; ?> type="text" name="cpf" maxlength="14" value="<?= $cpf ?>"/></td>
                        <td align="right">RG: </td>
                        <td><input type="text" size="20" <?php if ($NOT_PERM) print "readonly"; ?> name="rg" maxlength="14" value="<?= $rg ?>"/></td>
                    </tr>
                    <tr>
                        <td align="right">Naturalidade: </td>
                        <td><select <?php if ($NOT_PERM) print "disabled"; ?> name="estadoNaturalidade" id="estadoNaturalidade" value="<?= $estadoNaturalidade ?>">
                                <option></option>
                                <?php
                                foreach ($estados->listRegistros() as $reg) {
                                    $selected = "";
                                    if ($reg['codigo'] == $estadoNaturalidade)
                                        $selected = "selected";
                                    echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                                }
                                ?>
                            </select>
                            <select <?php if ($NOT_PERM) print "disabled"; ?> name="naturalidade" id="naturalidade" value="<?= $naturalidade ?>">
                                <?php
                                $sqlNaturalidade = ' AND c.codigo = :codigo ';
                                $paramsNaturalidade['codigo'] = $naturalidade;
                                foreach ($cidades->listCidades($paramsNaturalidade, null, null, $sqlNaturalidade) as $reg) {
                                    $selected = "";
                                    if ($reg['codigo'] == $cidade)
                                        $selected = "selected";
                                    echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['cidade'] . "</option>";
                                }
                                ?>
                            </select></td>
                        <td align="right">Nascimento: </td>
                        <td><input id="nascimento" <?php if ($NOT_PERM) print "readonly"; ?> type="text" style="width: 80pt" name="nascimento" maxlength="12" value="<?= $nascimento ?>"/></td>
                    </tr>
                    <tr>
                        <td align="right">Endere&ccedil;o: </td>
                        <td><input type="text" style="width: 260pt" <?php if ($NOT_PERM) print "readonly"; ?> name="endereco" maxlength="45" value="<?= $endereco ?>"/></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="right">Bairro: </td>
                        <td><input type="text" style="width: 200pt" <?php if ($NOT_PERM) print "readonly"; ?> name="bairro" maxlength="45" value="<?= $bairro ?>"/></td>
                        <td align="right">CEP: </td>
                        <td><input id="cep" type="text" <?php if ($NOT_PERM) print "readonly"; ?> style="width: 80pt" name="cep" maxlength="10" value="<?= $cep ?>"/></td>
                    </tr>
                    <tr>
                        <td align="right">Telefone: </td>
                        <td><input id="telefone" type="text" <?php if ($NOT_PERM) print "readonly"; ?> style="width: 80pt" name="telefone" maxlength="12" value="<?= $telefone ?>"/></td>
                        <td align="right">Celular: </td>
                        <td><input id="celular" type="text" <?php if ($NOT_PERM) print "readonly"; ?> style="width: 80pt" name="celular" maxlength="12" value="<?= $celular ?>"/></td>
                    </tr>
                    <tr>
                        <td align="right">Observa&ccedil;&otilde;es: </td>
                        <td><textarea name="observacoes" <?php if ($NOT_PERM) print "readonly"; ?> style="width: 300pt" rows="5"><?= $observacoes ?></textarea></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr><td></td><td>
                            <input type="hidden" name="opcao" value="InsertOrUpdate" />
                            <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                                    <td><a href="javascript:$('#index').load('<?= $SITE ?>');void(0);">Novo/Limpar</a></td>
                                </tr></table>
                        </td></tr>
                </table>
            </div>

            <div class="cont_tab" id="Dados4">
                <table width="100%">
                    <tr><td>Aten&ccedil;&atilde;o: n&atilde;o remova o tipo de um aluno ou professor, pois o sistema n&atilde;o permite incluir esses tipos.</td></tr>
                </table>
                <table width="60%" border="0">
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td rowspan="2" valign="top">
                            <a href="#" data-placement="top" id="setTipo" title="Buscar por Tipo" data-content="Clique para buscar um tipo de pessoa.">
                                <img class='botao' style="width:15px;height:15px;" src='<?= ICONS ?>/search.png' />
                            </a>
                        </td>
                    </tr>
                    <tr><td>Tipos Inseridos<br>
                            <select id="tipo" size="6" multiple name="tipo[]" style="width: 300px;">
                                <?php
                                if (!in_array($ADM, $_SESSION["loginTipo"]))
                                    $restricaoADM = 'WHERE codigo <> (SELECT adm FROM Instituicoes)';

                                require CONTROLLER . "/tipo.class.php";
                                $t = new Tipos();

                                foreach ($t->listRegistros(null, $restricaoADM . ' ORDER BY nome ', null, null) as $reg) {
                                    $TP[$reg['codigo']] = $reg['nome'];
                                    if (in_array($reg['codigo'], $tipo)) {
                                        echo "<option value='" . $reg['codigo'] . "'>" . $reg['nome'] . "</option>";
                                    } else {
                                        if ($reg['codigo'] != $ALUNO && $reg['codigo'] != $PROFESSOR)
                                            $TPS[$reg['codigo']] = $reg['nome'];
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" id="btnLeft" readonly <?php if (in_array($ALUNO, $tipo)) print "disabled"; ?> style="width: 20px; cursor: pointer;" value="&lt;&lt;" /><br>
                            <input type="text" id="btnRight" readonly <?php if (in_array($ALUNO, $tipo)) print "disabled"; ?> style="width: 20px; cursor: pointer;" value="&gt;&gt;" />
                        </td>
                        <td>Todos os Tipos<br>
                            <select id="rightValues" size="6" multiple style="width: 300px;">
                                <?php
                                foreach ($TPS as $TP_COD => $TP_NM)
                                    echo "<option value='$TP_COD'>$TP_NM</option>";
                                ?>
                            </select>
                        </td>
                    </tr>

                </table>
                <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                        <td><a href="javascript:$('#index').load('<?= $SITE ?>');void(0);">Novo/Limpar</a></td>
                    </tr></table>
            </div>
    </form>

    <div class="cont_tab" id="Dados5">
        <div id="retorno"></div>

        <?php
        $max_file = ini_get('upload_max_filesize') * 1024 * 1024;

        if (!$codigo) {
            ?>
            <font size="2">Aten&ccedil;&atilde;o: as fotos podem ser inseridas de 3 formas: </font><br>
            <font size="1">1 - Selecionando um usu&aacute;rio de cada vez e carregando sua foto.</font><br>
            <font size="1">2 - Carregar uma foto sem selecionar o usu&aacute;rio, mas o nome da foto precisa seguir o padr&atilde;o: XXXXXXX.JGP (XXXXX - Prontu&aacute;rio, s&atilde;o aceitas as extens&otilde;es: JPG, GIF e PNG)</font><br>
            <font size="1">3 - Com um arquivo ZIP contendo todas as fotos com o padr&atilde;o: XXXXXXX.JGP</font><br>
            <br>
            <?php
        }
        ?>
        Tamanho m&aacute;ximo do arquivo: <?= ini_get('upload_max_filesize') ?><br>

        <?php
        if ($codigo) {
            ?>
            <img id="divFoto" style="width: 200px; height: 200px" src='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($codigo) ?>&timestamp=<?= time() ?>' />
            <?php
        }
        ?>
        <div align="center">
            <form action="<?= INC ?>/processupload.inc.php" method="post" enctype="multipart/form-data" id="MyUploadForm" style="text-align: left">
                <input type="hidden" name="codigo" value="<?= $codigo ?>"/>
                <input name="ImageFile" id="imageInput" type="file" accept="application/zip,image/*" />

                <br><br><a href="javascript:$('#index').load('<?= $SITE ?>?opcao=removeFoto&codigo=<?= crip($codigo) ?>');void(0);">Remover Foto</a>
            </form>
        </div>

        <table width="100%"><tr><td>&nbsp;</td>
                <td align="right"><a href="javascript:$('#index').load('<?= $SITE ?>');void(0);">Novo/Limpar
                    </a></td>
            </tr></table>
    </div>

    <div class="cont_tab" id="Dados6">
        <table width="100%"><tr><td>&nbsp;</td>
                <?php
                $totalBloq = $pessoa->countBloqPic();
                ?>
                <td>Fotos bloqueadas: <?= $totalBloq[0]['total'] ?> 
                    <br><br>
                    <a href="javascript:$('#index').load('<?= $SITE ?>?opcao=validacao');void(0);">Visualizar Fotos</a>
                </td>
            </tr></table>
    </div>

</div>
</div>
<?php
// PAGINACAO
$itensPorPagina = 40;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

if (!$params['codigo'])
    $sqlAdicional = ' WHERE 1 ' . $sqlAdicional . ' ORDER BY nome';

$res = $pessoa->listRegistros($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($pessoa->listRegistros($params, $sqlAdicional, null, null));

$params['prontuario'] = crip($prontuario);
$params['nome'] = crip($nome);
$params['tipo'] = crip($tipo);
$SITENAV = $SITE . '?' . mapURL($params);
require PATH . VIEW . '/system/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="80">Prontu&aacute;rio</th>
        <th align="left">Nome</th>
        <th align="left">E-mail</th>
        <th align="left" width="170">Tipo</th>
        <th align="center" width="40">
            <input type="checkbox" id="select-all" value="">
            <a href="#" class='item-excluir'>
                <img class='botao' src='<?= ICONS ?>/delete.png' />
            </a>
        </th>
    </tr>
    <?php
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        $tp = "|";
        foreach ($pessoaTipo->getTipoPessoa($reg['codigo']) as $tc => $tn)
            $tp .= $TP[$tn] . "|";

        if (strlen($tp) > 20)
            $tp = "<a href='#' title='$tp'>" . abreviar($tp, 20) . "</a>";

        if ($codigo)
            $output = "id='output'";
        ?>
        <tr <?= $cdif ?>>
            <td align='left'><?= $reg['prontuario'] ?></td>
            <td>
                <div <?= $output ?> style='float: left; margin-right: 5px'>
                    <a href='#' rel='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codigo']) ?>&timestamp=<?= time() ?>' class='screenshot' title='<?= mostraTexto($reg['nome']) ?>'>
                        <img style='width: 25px; height: 25px' src='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codigo']) ?>&timestamp=<?= time() ?>' />
                    </a>
                </div><?= mostraTexto($reg['nome']) ?>
            </td>
            <td><?= $reg['email'] ?></td>
            <td><?= $tp ?></td>
            <?php
            if ((in_array($ADM, $_SESSION["loginTipo"])) || (!in_array($ADM, $_SESSION["loginTipo"]) && !in_array($ADM, $tipo) )) {
                ?>
                <td align='center'>
                    <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
                    <a href='#' title='Alterar' class='item-alterar' id='<?= crip($reg['codigo']) ?>'>
                        <img class='botao' src='<?= ICONS; ?>/config.png' />
                    </a>
                </td>
                <?php
            } else {
                ?>
                <td align="center">&nbsp;</td>
                <?php
            }
            $i++;
        }
        ?>
</table>

<script>

    function valida() {
        if ($('#nome').val() == "" ||
<?php if (!$codigo) print "$('#senha').val() == \"\" || "; ?>
        $('#prontuario').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').removeAttr('disabled');
        }
    }

    function atualizar(getLink) {
        var nome = encodeURIComponent($('#nome').val());
        var prontuario = encodeURIComponent($('#prontuario').val());
        var URLS = '<?= $SITE ?>?nome=' + nome + '&prontuario=' + prontuario;
        if (!getLink)
            $('#index').load(URLS + '&pesquisa=1&item=<?= $item ?>');
        else
            return URLS;
    }
    $(document).ready(function () {
        valida();
        $('#nome, #prontuario, #senha').keyup(function () {
            valida();
        });

        $("#cpf").mask("999.999.999-99");
        $("#phone").mask("(99) 9999-9999");
        $("#celular").mask("(99) 99999-9999");
        $("#cep").mask("99.999-999");

        $("#nascimento").datepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo', 'Segunda', 'TerÃ§a', 'Quarta', 'Quinta', 'Sexta', 'SÃ¡bado'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'SÃ¡b', 'Dom'],
            monthNames: ['Janeiro', 'Fevereiro', 'MarÃ§o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'PrÃ³ximo',
            prevText: 'Anterior'
        });

        $("#setTipo").click(function () {
            function preparaInput() {
                var resultado = '<br>Tipos: ';
                resultado += '<select id="Zebra_valor" name="Zebra_valor" value="<?= $mes ?>">';
                <?php
                foreach ($t->listRegistros(null, ' ORDER BY nome ') as $reg) {
                    ?>
                    resultado += "<option value='<?= ($reg['codigo']) ?>'><?= $reg['nome'] ?></option>\n";
                    <?php
                }
                ?>
                resultado += "</select>";
                return resultado;
            }
            
            $.Zebra_Dialog('<strong>Selecione o Tipo da pessoa para fazer a busca:</strong>', {
                'type': 'prompt',
                'promptInput': preparaInput(),
                'title': '<?= $TITLE ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function (caption, valor) {
                    if (caption == 'Sim') {
                        $('#index').load('<?= VIEW ?>/secretaria/pessoa.php?pesquisa=1&tipo=' + valor);
                    }
                }
            });
        });
        
        $(".item-excluir").click(function () {
            $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>', {
                'type': 'question',
                'title': '<?= $TITLE ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function (caption) {
                    if (caption == 'Sim') {
                        var selected = [];
                        $('input:checkbox:checked').each(function () {
                            selected.push($(this).val());
                        });

                        $('#index').load(atualizar(1) + '&pesquisa=1&opcao=delete&codigo=' + selected + '&item=<?= $item ?>');
                    }
                }
            });
        });

        $('#select-all').click(function (event) {
            if (this.checked) {
                // Iterate each checkbox
                $(':checkbox').each(function () {
                    this.checked = true;
                });
            } else {
                $(':checkbox').each(function () {
                    this.checked = false;
                });
            }
        });

        $(".item-alterar").click(function () {
            var codigo = $(this).attr('id');
            $('#nome').val('');
            $('#prontuario').val('');
            $('#index').load(atualizar(1) + '&pesquisa=1&codigo=' + codigo + '&item=<?= $item ?>');
        });

        $('#setNome, #setProntuario').click(function () {
            atualizar();
        });


        $('#tipo option').prop('selected', true);

        $("#btnLeft").click(function () {
            var selectedItem = $("#rightValues option:selected");
            $("#tipo").append(selectedItem);
            $('#tipo option').prop('selected', true);
        });

        $("#btnRight").click(function () {
            var selectedItem = $("#tipo option:selected");
            $("#rightValues").append(selectedItem);
            $('#tipo option').prop('selected', true);
        });

        $("#rightValues").change(function () {
            var selectedItem = $("#rightValues option:selected");
            $("#txtRight").val(selectedItem.text());
        });

        $(function () {
            $('#estadoNaturalidade').change(function () {
                if ($(this).val()) {
                    $('#naturalidade').hide();
                    $('.carregando').show();
                    $.getJSON('<?= VIEW ?>/admin/cidade.php?search=', {codigo: $(this).val(), ajax: 'true', ajaxCidade: 1}, function (j) {
                        var options = '<option value=""></option>';
                        for (var i = 0; i < j.length; i++) {
                            options += '<option value="' + j[i].codigo + '">' + j[i].nome + '</option>';
                        }
                        $('#naturalidade').html(options).show();
                        $('.carregando').hide();
                    });
                } else {
                    $('#naturalidade').html('<option value="">-- Escolha um estado --</option>');
                }
            });
        });

        $(function () {
            $('#estado').change(function () {
                if ($(this).val()) {
                    $('#cidade').hide();
                    $('.carregando').show();
                    $.getJSON('<?= VIEW ?>/admin/cidade.php?search=', {codigo: $(this).val(), ajax: 'true', ajaxCidade: 1}, function (j) {
                        var options = '<option value=""></option>';
                        for (var i = 0; i < j.length; i++) {
                            options += '<option value="' + j[i].codigo + '">' + j[i].nome + '</option>';
                        }
                        $('#cidade').html(options).show();
                        $('.carregando').hide();
                    });
                } else {
                    $('#cidade').html('<option value="">-- Escolha um estado --</option>');
                }
            });
        });

        $('#imageInput').change(function () {
            $('#MyUploadForm').submit();
        });

        var options = {
            target: '#output <?php if (!$codigo) print ",#retorno"; ?>', // target element(s) to be updated with server response 
            beforeSubmit: beforeSubmit, // pre-submit callback 
            success: afterSuccess, // post-submit callback 
            resetForm: true        // reset the form after successful submit 
        };

        $('#MyUploadForm').submit(function () {
            $(this).ajaxSubmit(options);
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });

        function afterSuccess()
        {
            $('#submit-btn').show(); //hide submit button
            $('#loading-img').hide(); //hide submit button
            $("#divFoto").attr("src", "<?= INC ?>/file.inc.php?type=pic&id=<?= crip($codigo) ?>&timestamp=" + new Date().getTime());

        }

        //function to check file size before uploading.
        function beforeSubmit() {
            //check whether browser fully supports all File API
            if (window.File && window.FileReader && window.FileList && window.Blob)
            {
                if (!$('#imageInput').val()) //check empty input filed
                {
                    $("#retorno").html("Foto não selecionada!");
                    return false
                }

                var fsize = $('#imageInput')[0].files[0].size; //get file size
                var ftype = $('#imageInput')[0].files[0].type; // get file type

                //Allowed file size is less than 1 MB (1048576)
                if (fsize ><?= $max_file ?>)
                {
                    $("#retorno").html("<b>" + bytesToSize(fsize) + "</b> Imagem muito grande, utilize um editor para diminuir o tamanho da foto!");
                    return false
                }

                $('#submit-btn').hide(); //hide submit button
                $('#loading-img').show(); //hide submit button
                $("#retorno").html("");
            }
            else
            {
                //Output error to older unsupported browsers that doesn't support HTML5 File API
                $("#retorno").html("Por favor, atualize seu browser para suportar essa função!");
                return false;
            }
        }

        //function to format bites bit.ly/19yoIPO
        function bytesToSize(bytes) {
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            if (bytes == 0)
                return '0 Bytes';
            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        }

    });
</script>