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

require CONTROLLER . "/matriculaAlteracao.class.php";
$matricualAlteracao = new MatriculasAlteracoes();

// INSERT SITUACAO
if ($_POST["opcao"] == 'InsertOrUpdate') {
    $_POST['data'] = dataMysql($_POST['data']);
    unset($_POST['opcao']);
    $_GET = $_POST;
    unset($_POST['atribuicao']);
    unset($_POST['turma']);
    unset($_POST['nome']);
    unset($_POST['prontuario']);
    
    $matriculas = explode(',' , $_POST['matriculas']);
    unset($_POST['matriculas']);
    foreach ($matriculas as $mat) {
        $_POST['matricula'] = $mat;
        $ret = $matricualAlteracao->insertOrUpdate($_POST);
    }
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}

// HISTORICO
if ($_GET["opcao"] == 'historico') {
    //REMOVENDO HISTORICO
    if (isset($_GET["remover"])) {
        $matricualAlteracao->delete($_GET["remover"]);
    }

    $params = array('matricula' => dcrip($_GET['codigo']));
    $sqlAdicional = ' AND ma.matricula = :matricula ORDER BY ma.data DESC ';
    $res = $matricualAlteracao->listAlteracaoMatricula($params, $sqlAdicional);
    ?>
    <table border="1" style="border-collapse:collapse; font-family: verdana; font-size: 10px;" align="center" width="100%">
        <tr style="background-color: #ccc">
            <th align="center" width="100">Situa&ccedil;&atilde;o</th>
            <th align="left">Data de Altera&ccedil;&atilde;o</th>
            <th align="left">&nbsp;</th>
        </tr>
        <?php
        // efetuando a consulta para listagem
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?= $cdif ?>>        
                <td align='left'><?= $reg['nome'] ?></td>
                <td><?= $reg['data'] ?></td>
                <td><a href="matricula.php?opcao=historico&codigo=<?= $_GET['codigo'] ?>&remover=<?= crip($reg['codigo']) ?>">remover</a></td>
            </tr>
            <?php
            $i++;
        }
        ?>
    </table>
    <?php
    die;
}

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
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<div id="html5form" class="main">
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
                <select name="atribuicao" id="atribuicao" value="<?= $atribuicao ?>">
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
                <input type="text" size="5" value="<?= $prontuario ?>" name="prontuario" id="prontuario" />
                <a href="#" title="Buscar" id="setProntuario"><img class='botao' style="width:15px;height:15px;" src='<?= ICONS ?>/search.png' /></a>
            </td>
        </tr>
        <tr>
            <td align="right">Nome: </td>
            <td>   
                <input type="text" size="25" value="<?= $nome ?>" name="nome" id="nome" />
                <a href="#" title="Buscar" id="setNome"><img class='botao' style="width:15px;height:15px;" src='<?= ICONS ?>/search.png' /></a>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="hidden" name="opcao" value="InsertOrUpdate" />
                <table width="100%">
                    <tr>
                        <td>
                            <a href="javascript:$('#index').load('<?= $SITE ?>');void(0);">Limpar</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<br>
<div id="dialog2" title="Assinatura para o atestado de matr&iacute;cula">
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
</div>

<div id="add" title="Altera&ccedil;&atilde;o de hist&oacute;rico de matr&iacute;cula">
    <br>
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
    <form id="form_padrao">
        <table align="center" width="100%" id="form" border="0">
            <tr>
                <td align="right" style="width: 100px">Situação: </td>
                <td>
                    <select name="situacao" id="situacao" value="<?= $situacao ?>">
                        <?php
                        require CONTROLLER . '/situacao.class.php';
                        $sit = new Situacoes();
                        foreach ($sit->listRegistros() as $reg) {
                            $selected = "";
                            if ($reg['situacao'] == $situacao)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Data: </td>
                <td>
                    <input type="text" class="data" readonly size="10" id="data" name="data" value="<?= $data ?>" />
                </td>
            </tr>
            <tr><td></td><td>
                    <input type="hidden" id="matriculas" name="matriculas" value="" />
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <input type="hidden" name="atribuicao" value="<?= $_GET['atribuicao'] ?>" />
                    <input type="hidden" name="turma" value="<?= $_GET['turma'] ?>" />
                    <input type="hidden" name="nome" value="<?= $_GET['nome'] ?>" />
                    <input type="hidden" name="prontuario" value="<?= $_GET['prontuario'] ?>" />
                    <input type="submit" value="Salvar" id="salvar" />
                </td></tr>                
        </table>
    </form>
</div>
<br />

<?php
if ($params['turma'] || $params['atribuicao'] || $params['nome'] || $params['prontuario']) {
    // PAGINACAO
    $itensPorPagina = 50;
    $item = 1;

    if ($_GET['item'])
        $item = $_GET["item"];

    $sqlAdicional .= " GROUP BY m.codigo ORDER BY a.bimestre, p.nome ";
    $res = $matricula->getMatriculas($params, $sqlAdicional, $item, $itensPorPagina);
    $totalRegistros = count($matricula->getMatriculas($params, $sqlAdicional, null, null));

    $params['prontuario'] = crip($params['prontuario']);
    $params['turma'] = crip($params['turma']);
    $params['atribuicao'] = crip($params['atribuicao']);
    $params['nome'] = $_GET['nome'];
    $SITENAV = $SITE . "?" . mapURL($params);

    require PATH . VIEW . '/system/paginacao.php';
    ?>
    <table id="listagem" border="0" align="center">
        <tr>
            <th align="center" width="100">Prontu&aacute;rio</th>
            <th align="left">Aluno</th>
            <th>Disciplina</th>
            <th>Situa&ccedil;&atilde;o atual</th>
            <th width="70">Hist&oacute;rico</th>
            <th width="61">
                <a href="#" class='item-excluir' title='Deletar uma matr&iacute;cula'>
                    <img class='botao' src='<?= ICONS ?>/delete.png' />
                </a>
                <input type="checkbox" id="select-all" value="">
                <a href="#" class='item-add' title='Adicionar uma data de altera&ccedil;&atilde;o de matr&iacute;cula'>
                    <img class='botao' src='<?= ICONS ?>/add.png' />
                </a>
            </th>
            <th width="40">&nbsp;</th>            
        </tr>
        <?php
        // efetuando a consulta para listagem
        $i = $item;
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            if ($reg['dataFim'] && $reg['dataFim'] != '00/00/0000')
                $reg['dataInicio'] = $reg['dataInicio'] . ' a ' . $reg['dataFim'];
            ?>
            <tr <?= $cdif ?>>        
                <td align='left'><?= $reg['prontuario'] ?></td>
                <td><?= mostraTexto($reg['pessoa']) ?></td>
                <td align='left'>
                    <a target='_blank' href='<?= VIEW ?>/secretaria/relatorios/inc/diario.php?atribuicao=<?= crip($reg['atribuicao']) ?>' title='<?= $professor->getProfessor($reg['atribuicao'], 1, '', 0, 0) ?>'>
                        <?= ' [' . $reg['turma'] . '] ' . $reg['subturma'] . $reg['bimestreFormat'] . ' [' . $reg['numero'] . '] ' . $reg['disciplina'] ?>
                    </a>
                </td>
                <td align='left'><?= mostraTexto($reg['situacao']) ?></td>
                <td align='center'>
                    <a href='#' title='Ver hist&oacute;rico de altera&ccedil;&otilde;es de matr&iacute;cula'>
                        <img class='botao search' id='<?= crip($reg['matricula']) ?>' src='<?= ICONS ?>/search.png' />
                    </a>
                </td>            
                <td align='center'>
                    <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['matricula']) ?>' />
                </td>
                <td align='center'>
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
    <?php
}
?>

<script>
    $("#add").hide();

    function atualizarMatricula(getLink) {
        var turma = $('#turma').val();
        var atribuicao = $('#atribuicao').val();
        var nome = encodeURIComponent($('#nome').val());
        var prontuario = encodeURIComponent($('#prontuario').val());
        var URLS = '<?= $SITE ?>?pesquisa=1&turma=' + turma + '&atribuicao=' + atribuicao + '&nome=' + nome + '&prontuario=' + prontuario;
        if (!getLink)
            $('#index').load(URLS + '&item=<?= $item ?>');
        else
            return URLS;
    }

    $(document).ready(function () {
        $(".item-atestado").click(function () {
            var codigo = $(this).attr('id');
            window.open('<?= VIEW ?>/secretaria/relatorios/inc/atestadoMatricula.php?codigo=' + codigo + '&assinatura1=' + $('#campoAssinatura1').val() + '&assinatura2=' + $('#campoAssinatura2').val());
        });

        $(".search").click(function () {
            var codigo = $(this).attr('id');
            new $.Zebra_Dialog('', {
                source: {'iframe': {
                        'src': 'view/secretaria/cursos/matricula.php?opcao=historico&codigo=' + codigo,
                        'height': 300
                    }},
                width: 600,
                title: 'Hist&oacute;rico de Matr&iacute;culas',
                onClose: function () {
                    atualizarMatricula();
                }
            });
        });

        $(".item-add").click(function () {
            var selected = [];
            $('input:checkbox:checked').each(function () {
                selected.push($(this).val());
                $('#matriculas').val(selected);
            });
            $("#add").show();
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
                });
            }
        });
        
        $("#data").datepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior',
            minDate: '<?= $res['inicioCalendar'] ?>',
            maxDate: '<?= $res['fimCalendar'] ?>'
        });        
    });
</script>