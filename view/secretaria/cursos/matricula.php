<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Lista a situação do aluno com relação as disciplinas cursadas por ele.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/matricula.class.php";
$matricula = new Matriculas();

require CONTROLLER . "/professor.class.php";
$professor = new Professores();

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $matricula->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND t.codigo = :turma ';

    if ($_SESSION['regAnterior'] && $turma != $_SESSION['regAnterior']) {
        unset($_GET["turma"]);
        unset($_GET["professor"]);
        unset($_GET["atribuicao"]);
        unset($_GET["prontuario"]);
        unset($_GET["nome"]);
    }
    $_SESSION['regAnterior'] = $turma;
}

if (dcrip($_GET["atribuicao"])) {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $params['atribuicao'] = $atribuicao;
    $sqlAdicional .= ' AND a.codigo = :atribuicao ';
}

if ($_GET["pesquisa"] == 1) {
    $_GET["nome"] = crip($_GET["nome"]);
    $_GET["prontuario"] = crip($_GET["prontuario"]);
}

if (dcrip($_GET["prontuario"])) {
    $prontuario = dcrip($_GET["prontuario"]);
    $params['prontuario'] = $prontuario;
    $sqlAdicional .= ' AND p.prontuario = :prontuario ';
}

if (dcrip($_GET["nome"])) {
    $nome = dcrip($_GET["nome"]);
    $params['nome'] = '%' . $nome . '%';
    $sqlAdicional .= ' AND p.nome LIKE :nome ';
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
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
        <table align="center" width="100%" id="form" border="0">
            <tr>
                <td align="right" style="width: 100px">Turma: </td>
                <td>
                    <select name="turma" id="turma" value="<?= $turma ?>">
                        <option></option>
                        <?php
                        require CONTROLLER . '/turma.class.php';
                        $turmas = new Turmas();
                        $paramsTurma = array(':ano' => $ANO, ':semestre' => $SEMESTRE);
                        foreach ($turmas->listTurmas($paramsTurma) as $reg) {
                            $selected = "";
                            if ($reg['codTurma'] == $turma)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codTurma']) . "'>" . $reg['numero'] . " [" . $reg['curso'] . "]</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Disciplina: </td>
                <td>
                    <select name="atribuicao" id="atribuicao" value="<?php echo $atribuicao; ?>">
                        <option></option>
                        <?php
                        require CONTROLLER . '/atribuicao.class.php';
                        $att = new Atribuicoes();
                        $paramsAtt = array(':ano' => $ANO, ':semestre' => $SEMESTRE, ':turma' => $turma);
                        $sqlAdicionalAtt = ' AND a.turma=:turma ';
                        foreach ($att->getAllAtribuicoes($paramsAtt, $sqlAdicionalAtt) as $reg) {
                            $selected = "";
                            if ($reg['atribuicao'] == $atribuicao)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['atribuicao']) . "'>" . $reg['bimestreFormat'] . ' ' . $reg['disciplina'] . $reg['subturma'] . " [" . $reg['turno'] . "]</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Prontu&aacute;rio: </td>
                <td>
                    <input type="text" size="5" value="<?php echo $prontuario; ?>" name="prontuario" id="prontuario" />
                    <a href="#" title="Buscar" id="setProntuario"><img class='botao' style="width:15px;height:15px;" src='<?php print ICONS; ?>/search.png' /></a>
                </td>
            </tr>
            <tr>
                <td align="right">Nome: </td>
                <td>   
                    <input type="text" size="25" value="<?php echo $nome; ?>" name="nome" id="nome" />
                    <a href="#" title="Buscar" id="setNome"><img class='botao' style="width:15px;height:15px;" src='<?php print ICONS; ?>/search.png' /></a>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <table width="100%">
                        <tr>
                            <td>
                                <a href="javascript:$('#index').load('<?= $SITE ?>'); void(0);">Limpar</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>
<br>
<div id="dialog2" title="Assinatura para o atestado de matr&iacute;cula">
    <form>
        <table align="center" width="100%" id="form" border="0">
            <tr>
                <td align="left" width="100">Assinatura 1: </td>
                <td align="left">
                    <select name="campoAssinatura1" id="campoAssinatura1" >
                        <option></option>
                        <?php
                        require CONTROLLER . '/pessoa.class.php';
                        $pessoa = new Pessoas();
                        $sqlAdicionalCoord = ' AND pt.tipo IN (:coord, :sec, :ged) ';
                        $paramsCoord = array('coord' => $COORD, 'sec' => $SEC, 'ged' => $GEG);
                        $resCoord = $pessoa->listPessoasTipos($paramsCoord, $sqlAdicionalCoord, null, null);
                        foreach ($resCoord as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $coordenador)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="left" width="100">Assinatura 2: </td>
                <td align="left">
                    <select name="campoAssinatura2" id="campoAssinatura2" >
                        <option></option>
                        <?php
                        foreach ($resCoord as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $coordenador)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
    </form>
</div>
<br />

<?php
// PAGINACAO
$itensPorPagina = 20;
$item = 1;

if (isset($_GET['item']))
    $item = $_GET["item"];

$sqlAdicional .= ' ORDER BY p.nome ';
$res = $matricula->getMatriculas($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($matricula->getMatriculas($params, $sqlAdicional, null, null));

$params['prontuario'] = crip($params['prontuario']);
$params['turma'] = crip($params['turma']);
$params['atribuicao'] = crip($params['atribuicao']);
$params['nome'] = $_GET['nome'];
$SITENAV = $SITE . "?" . mapURL($params);

require PATH . VIEW . '/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="center" width="100">Prontu&aacute;rio</th>
        <th align="left">Aluno</th>
        <th>Disciplina</th>
        <th>Situa&ccedil;&atilde;o</th>
        <th>Data</th>
        <th width="50">&nbsp;&nbsp;<input type="checkbox" id="select-all" value="">
            <a href="#" class='item-excluir'><img class='botao' src='<?php print ICONS; ?>/delete.png' /></a>
        </th>    </tr>
    <?php
    // efetuando a consulta para listagem
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        if ($reg['dataFim'] && $reg['dataFim'] != '00/00/0000')
            $reg['dataInicio'] = $reg['dataInicio'] . ' a ' . $reg['dataFim'];
        ?>
        <tr <?php print $cdif; ?>>        
            <td align='left'><?= $reg['prontuario'] ?></td>
            <td><?= mostraTexto($reg['pessoa']) ?></td>
            <td align='left'>
                <a target='_blank' href='<?= VIEW ?>/secretaria/relatorios/inc/diario.php?atribuicao=<?= crip($reg['atribuicao']) ?>' title='<?= $professor->getProfessor($reg['atribuicao'], '', 0, 0) ?>'><?= $reg['bimestreFormat'] . ' ' . $reg['disciplina'] . ' [' . $reg['numero'] . ']' ?></a>
            </td>
            <td align='left'><?= mostraTexto($reg['situacao']) ?></td>
            <td align='left'><?= $reg['data'] ?></td>
            <td align='center'>
                <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['matricula']) ?>' />
                <a href='#' class='item-atestado' id='<?= crip($reg['matricula']) ?>' title='Atestado'>
                    <img class='botao' src='<?= ICONS ?>/icon-printer.gif' />
                </a>
            </td>
        </tr>
        <?php
        $i++;
    }
    ?>

</table>

<script>
    function atualizarMatricula(getLink) {
        var turma = $('#turma').val();
        var atribuicao = $('#atribuicao').val();
        var nome = encodeURIComponent($('#nome').val());
        var prontuario = encodeURIComponent($('#prontuario').val());
        var URLS = '<?php print $SITE; ?>?pesquisa=1&turma=' + turma + '&atribuicao=' + atribuicao + '&nome=' + nome + '&prontuario=' + prontuario;         if (!getLink)
                    $('#index').load(URLS + '&item=<?= $item ?>');
        else
                            return URLS;
                                }

                        $(document).ready(function () {
                            $(".item-atestado").click(function () {
                                var codigo = $(this).attr('id');             window.open('<?= VIEW ?>/secretaria/relatorios/inc/atestadoMatricula.php?codigo=' + codigo + '&assinatura1=' + $('#campoAssinatura1').val() + '&assinatura2=' + $('#campoAssinatura2').val());
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

                                        $('#index').load(atualizarMatricula(1) + '&opcao=delete&codigo=' + selected + '&item=<?= $item ?>');
                    }
                                                            }
                                                        });
        });

                                                $('#setNome, #setProntuario').click(function () {
                                                    $('#index').load(atualizarMatricula(1) + '&pesquisa=1');
        });
                                                $('#atribuicao, #turma').change(function () {
                                                    atualizarMatricula();
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
                                                        });             }
        });
                                                    });
</script>