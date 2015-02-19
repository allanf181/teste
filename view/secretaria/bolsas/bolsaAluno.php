<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Cadastra um ou mais alunos para uma bolsa de ensino.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/bolsa.class.php";
$bolsas = new Bolsas();

require CONTROLLER . "/bolsaAluno.class.php";
$bolsaAluno = new BolsasAlunos();

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);

    $ret = $bolsaAluno->insertOrUpdate($_POST);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

    if (dcrip($_POST['codigo']))
        $_GET["codigo"] = $_POST['codigo'];
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);
    
    $_GET["bolsa"] = $_POST['bolsa'];
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $bolsaAluno->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}


if (dcrip($_GET["bolsa"]) != "") {
    $params['bolsa'] = dcrip($_GET["bolsa"]);
    $sqlAdicional .= "AND b.codigo = :bolsa";
    $bolsa = dcrip($_GET["bolsa"]);
}

// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params1 = array('codigo' => dcrip($_GET["codigo"]));
    $res = $bolsaAluno->listRegistros($params1);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
}
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
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
    <?php include("base.php"); ?>
    <form id="form_padrao">
        <table align="center" width="100%" id="form">
            <input type="hidden" name="codigo" value="<?= crip($codigo) ?>" />        
            <tr>
                <td align="right">Bolsa: </td>
                <td>
                    <select name="bolsa" id="bolsa" value="<?= $bolsa ?>">
                        <option></option>
                        <?php
                        foreach ($bolsas->listBolsas() as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $bolsa)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['titulo'] . " [" . $reg['professor'] . "]</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Aluno: </td>
                <td>
                    <select name="aluno" id="aluno" style="width: 350px">
                        <option></option>
                        <?php
                        require CONTROLLER . '/pessoa.class.php';
                        $pessoa = new Pessoas();
                        $paramPessoa = array('tipo' => $ALUNO, 'ano' => $ANO, 'semestre' => $SEMESTRE);
                        $sqlAdicionalTipo = " AND pt.tipo = :tipo AND p.codigo IN "
                                . "(SELECT m.aluno FROM Atribuicoes a, Matriculas m, Turmas t "
                                . "WHERE a.codigo = m.atribuicao AND a.turma = t.codigo "
                                . "AND t.ano = :ano AND (t.semestre = :semestre OR t.semestre = 0)) ";
                        $res = $pessoa->listPessoasTipos($paramPessoa, $sqlAdicionalTipo);
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $aluno)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>[" . $reg['prontuario'] . "] " . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />    
                    <table width="100%">
                        <tr>
                            <td>
                                <input type="submit" value="Salvar" id="salvar" />
                            </td>
                            <td>
                                <a href="javascript:$('#index').load('<?= $SITE ?>');void(0);">Novo/Limpar</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>
<?php
// PAGINACAO
$itensPorPagina = 20;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

$sqlAdicional .= ' ORDER BY b.dataInicio DESC, b.titulo ASC, p.nome ASC ';

$res = $bolsaAluno->listAlunos($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($bolsaAluno->listAlunos($params, $sqlAdicional, null, null));

$params['bolsa'] = crip($params['bolsa']);
$SITENAV = $SITE . "?" . mapURL($params);
require PATH . VIEW . '/system/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="60">C&oacute;digo</th>
        <th align="left">Bolsa</th>
        <th align="left">Aluno</th>
        <th align="center" width="45">&nbsp;
            <input type="checkbox" id="select-all" value="">
            <a href="#" class='item-excluir'>
                <img class='botao' src='<?= ICONS ?>/delete.png' />
            </a>
        </th>
    </tr>
    <?php
    // efetuando a consulta para listagem
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        ?>
        <tr <?= $cdif ?>><td align='center'><?= $i ?></td>
            <td><a href="#" title="<?= $reg['titulo'] ?>"><?= abreviar(mostraTexto($reg['titulo']), 40) ?></a></td>
            <td><a href="#" title="<?= $reg['aluno'] ?>"><?= abreviar(mostraTexto($reg['aluno']), 40) ?></a></td>
            <td align='left'>
                &nbsp;<input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
                <a href='#' title='Alterar' class='item-alterar' id='<?= crip($reg['codigo']) ?>'>
                    <img class='botao' src='<?= ICONS ?>/config.png' />
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
        var bolsa = $('#bolsa').val();
        var URLS = '<?= $SITE ?>?bolsa=' + bolsa;
        if (!getLink)
            $('#index').load(URLS + '&item=<?= $item ?>');
        else
            return URLS;
    }

    $('#bolsa').change(function () {
        atualizar();
    });

    function valida() {
        if ($('#bolsa').val() == "" || $('#aluno').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').removeAttr('disabled');
        }
    }

    $(document).ready(function () {
        valida();
        $('#bolsa,#aluno').change(function () {
            valida();
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
                        $('#index').load('<?= $SITE ?>?opcao=delete&codigo=' + selected + '&item=<?= $item ?>');
                    }
                }
            });
        });

        $('#select-all').click(function () {
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
            $('#index').load('<?= $SITE ?>?codigo=' + codigo);
        });
    });
</script>