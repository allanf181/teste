<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita registrar a atividade acadêmica de um aluno.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/atvAcademica.class.php";
$atv = new AtvAcademicas();

require CONTROLLER . "/atvAcadItem.class.php";
$atvItem = new AtvAcadItens();

require CONTROLLER . "/atvAcadRegistro.class.php";
$atvRegistro = new AtvAcadRegistros();

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);
    $_POST['semestre'] = $_POST['psemestre'];
    $_POST['ano'] = $_POST['pano'];
    unset($_POST['psemestre']);
    unset($_POST['pano']);

    $ret = $atvRegistro->insertOrUpdateReg($_POST);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

    if (dcrip($_POST['codigo']))
        $_GET["codigo"] = $_POST['codigo'];
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);

    $_GET["curso"] = $_POST['curso'];
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $atvRegistro->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

if (dcrip($_GET["atvAcademica"])) {
    $atvAcademica = dcrip($_GET["atvAcademica"]);
}

if (dcrip($_GET["aluno"])) {
    $aluno = dcrip($_GET["aluno"]);
}

// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params1 = array('codigo' => dcrip($_GET["codigo"]));
    $res = $atvRegistro->listRegistros($params1, ' AND ra.codigo = :codigo ');
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
    $pano = $ano;
    $psemestre = $semestre;
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $paramsCurso['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicionalCurso = " curso IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord)";
    $sqlAdicionalCurso2 = ' WHERE ' . $sqlAdicionalCurso;    
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
    <form id="form_padrao">
        <table align="center" width="100%" id="form">
            <input type="hidden" name="codigo" value="<?= crip($codigo) ?>" />
            <input type="hidden" name="CHAnt" value="<?= crip($CH) ?>" />
            <tr>
                <td align="right">Atividade do Curso: </td>
                <td>
                    <select name="atvAcademica" id="atvAcademica" value="<?= $atvAcademica ?>" style="width: 350px">
                        <option></option>
                        <?php
                        foreach ($atv->listRegistros($paramsCurso, $sqlAdicionalCurso2) as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $atvAcademica)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Aluno: </td>
                <td><select name="aluno" id="aluno" style="width: 350px">
                        <option></option>
                        <?php
                        require CONTROLLER . '/pessoa.class.php';
                        $pessoa = new Pessoas();
                        $paramPessoa = array('tipo' => $ALUNO, 'codigo' => $atvAcademica);
                        $sqlAdicionalPessoa = " AND pt.tipo = :tipo AND p.codigo IN ("
                                . "SELECT m.aluno FROM Matriculas m, Cursos c, Atribuicoes a, Turmas t, AtvAcademicas aa "
                                . "WHERE m.atribuicao = a.codigo "
                                . "AND a.turma = t.codigo "
                                . "AND t.curso = c.codigo "
                                . "AND aa.curso = c.codigo "
                                . "AND aa.codigo = :codigo) ";
                        $res = $pessoa->listPessoasTipos($paramPessoa, $sqlAdicionalPessoa);
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
                <td align="right">Item: </td>
                <td>
                    <select name="atvAcadItem" id="atvAcadItem" value="<?= $atvAcadItem ?>" style="width: 350px">
                        <option></option>
                        <?php
                        $paramsItem = array('codigo' => $atvAcademica);
                        $sqlAdicionalItem = " AND aa.codigo = :codigo ";
                        foreach ($atvItem->listItens($paramsItem, $sqlAdicionalItem) as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $atvAcadItem)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>[" . $reg['tipo'] . "] " . abreviar($reg['atividade'], 94) . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Semestre/Ano: </td>
                <td>
                    <select name="psemestre" id="psemestre" value="<?= $psemestre ?>" style="width: 50pt">
                        <option></option>
                        <option <?php if ($psemestre == '1') print 'selected'; ?> value='<?= crip('1') ?>'>1</option>
                        <option <?php if ($psemestre == '2') print 'selected'; ?> value='<?= crip('2') ?>'>2</option>
                    </select> /
                    <select name="pano" id="pano" value="<?= $pano ?>" style="width: 50pt">
                        <option></option>
                        <?php
                        for ($i = ($ANO - 2); $i <= ($ANO + 2); $i++) {
                            $selected = "";
                            if ($pano == $i)
                                $selected = "selected";
                            print "<option $selected value='" . crip($i) . "'>" . $i . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Carga Hor&aacute;ria: </td>
                <td>
                    <input type="text" maxlength="3" size="10" name="CH" id="CH" value="<?= $CH ?>"/>
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
if (in_array($COORD, $_SESSION["loginTipo"])) {
    $params = $paramsCurso;
    $sqlAdicional .= ' AND ' . $sqlAdicionalCurso;
}

if ($atvAcademica) {
    $params['atvAcademica'] = $atvAcademica;
    $sqlAdicional .= ' AND ai.atvAcademica = :atvAcademica ';
}

if ($aluno) {
    $params['aluno'] = $aluno;
    $sqlAdicional .= ' AND p.codigo = :aluno ';
}

$sqlAdicional .= ' AND ra.aluno IS NOT NULL ';

$res = $atvRegistro->listSituacao($params, $sqlAdicional);
$totalRegistros = count($atvRegistro->listSituacao($params, $sqlAdicional, null, null));
?>
<h3>Situa&ccedil;&atilde;o do Aluno</h3>
<br>
<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="40px">&nbsp;</th>
        <th align="left">Atividade</th>
        <th align="left">Aluno</th>
        <th align="left" width="120px">Semestre/Ano</th>
        <th align="left" width="100px">CH no semestre</th>
        <th align="left" width="100px">CH total no curso</th>
        <th align="left" width="250px">CH total<br> [Cient&iacute;fica] [Cultural] [Acad&ecirc;mica]</th>
        <th align="left" width="40px">&nbsp;</th>        
    </tr>
    <?php
    // efetuando a consulta para listagem
    $i = 0;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        $color = null;
        if ($sit = $atvRegistro->status($reg))
            $color = 'yellow';
        ?>
        <tr <?= $cdif ?> style="background-color: <?= $color ?>">
            <td align="center">
                <a href="#" title="<?= $sit ?>">
                    <img class="botao" src='<?= ICONS ?>/info.png' />
                </a>
            </td>            
            <td><a href="#" data-placement="top" data-content='<?= $reg['atividade'] ?>' title="Atividade"><?= abreviar($reg['atividade'], 20) ?></a></td>
            <td><?= mostraTexto($reg['nome']) ?></td>
            <td><?= $reg['semAno'] ?></td>
            <td><?= $reg['CHSem'] . 'h/[' . $reg['CHminSem'] . '-' . $reg['CHmaxSem'] ?>]h</td>
            <td><?= $reg['CHCurso'] . 'h/' . $reg['CHTotal'] ?>h</td>
            <td>[<?= $reg['CHCientifica'] . 'h/' . $reg['CHminCientifica'] . 'h] [' . $reg['CHCultural'] . 'h/' . $reg['CHminCultural'] . 'h] [' . $reg['CHAcademica'] . 'h/' . $reg['CHminAcademica'] ?>h]</td>
            <td align="center">
                <a href="#" title="Imprimir" class='item-print' id='<?= crip($reg['aluno']) ?>'>
                    <img class="botao" src='<?= ICONS ?>/icon-printer.gif' />
                </a>
            </td>             
        </tr>
        <?php
        $i++;
    }
    ?>
</table>
<hr>
<br>
<h3>Atividades Entregues</h3>

<?php
// PAGINACAO
$itensPorPagina = 50;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

$res = $atvRegistro->listRegistros($params, $sqlAdicional);
$totalRegistros = count($atvRegistro->listRegistros($params, $sqlAdicional, null, null));

$params['atvAcademica'] = crip($atvAcademica);
$params['aluno'] = crip($aluno);
$SITENAV = $SITE . "?" . mapURL($params);
require PATH . VIEW . '/system/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="60">C&oacute;digo</th>
        <th align="left">Aluno</th>
        <th align="left">Atividade</th>
        <th align="left">Item</th>
        <th align="left">Semestre/Ano</th>
        <th align="left">Carga horária</th>
        <th align="center" width="45">&nbsp;
            <input type="checkbox" id="select-all" value="">
            <a href="#" class='item-excluir'>
                <img class='botao' src='<?= ICONS ?>/delete.png' />
            </a>
        </th>
        <th align="center" width="20"></th>
    </tr>
    <?php
    // efetuando a consulta para listagem
    $i = $item;
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        ?>
        <tr <?= $cdif ?>><td align='center'><?= $i ?></td>
            <td><a href="#" data-placement="top" data-content='<?= $reg['aNome'] ?>' title="Aluno"><?= abreviar($reg['aNome'], 20) ?></a></td>
            <td><a href="#" data-placement="top" data-content='<?= $reg['atividade'] ?>' title="Atividade"><?= abreviar($reg['atividade'], 20) ?></a></td>
            <td><a href="#" data-placement="top" data-content='<?= $reg['item'] ?>' title="Item"><?= abreviar('[' . $reg['tipo'] . '] ' . $reg['item'], 30) ?></a></td>
            <td><?= $reg['semestre'] . '/' . $reg['ano'] ?></td>
            <td><?= $reg['CH'] ?></td>
            <td align='left'>
                &nbsp;<input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
            </td>
            <td>
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
    function valida() {
        if ($('#atvAcademica').val() == "" || $('#atvAcadItem').val() == ""
                || $('#aluno').val() == "" || $('#ano').val() == ""
                || $('#semestre').val() == "" || $('#CH').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').removeAttr('disabled');
        }
    }

    function atualiza() {
        atvAcademica = $('#atvAcademica').val();
        aluno = $('#aluno').val();
        $('#index').load('<?= $SITE ?>?atvAcademica=' + atvAcademica + '&aluno=' + aluno);
    }

    $('#atvAcademica, #aluno').change(function () {
        atualiza();
    });

    $(document).ready(function () {
        valida();
        $('#atvAcademica, #atvAcadItem, #aluno, #ano, #semestre').change(function () {
            valida();
        });
        $('#CH').keyup(function () {
            valida();
        });

        $(".item-print").click(function () {
            var codigo = $(this).attr('id');
            window.open('<?= VIEW ?>/secretaria/relatorios/inc/atvAcadEmica.php?aluno=' + codigo);
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