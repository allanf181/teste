<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita visualizar as atribuições dos professores/disciplinas de todos os cursos.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/atribuicao.class.php";
$atribuicao = new Atribuicoes();

require CONTROLLER . "/professor.class.php";
$prof = new Professores();

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $atribuicao->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}
?>
<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND t.codigo = :turma ';
}

if (dcrip($_GET["professor"])) {
    $professor = dcrip($_GET["professor"]);
    $params['professor'] = $professor;
    $sqlAdicional .= ' AND p.professor = :professor ';
}

?>
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
        <table align="center" align="left" id="form" width="100%" >
            <input type="hidden" name="campoCodigo" value="<?= crip($codigo) ?>" />
            <tr>
                <td align="right" style="width: 100px">Turma: </td>
                <td>
                    <select name="turma" id="turma" value="<?= crip($turma) ?>" style="width: 650px">
                        <option></option>
                        <?php
                        require CONTROLLER . '/turma.class.php';
                        $turmas = new Turmas();
                        $paramsTurma = array(':ano' => $ANO, ':semestre' => $SEMESTRE);
                        foreach ($turmas->listTurmas($paramsTurma, $sqlAdicionaTurma) as $reg) {
                            $selected = "";
                            if ($reg['codTurma'] == $turma)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codTurma']) . "'>" . $reg['numero'] . " [" . $reg['curso'] . "]</option>";
                        }
                        ?>
                    </select>
                </td></tr>
            <tr>
                <td align="right">Professor: </td>
                <td>
                    <select name="professor" id="professor" style="width: 350px">
                        <option></option>
                        <?php
                        $sqlAdicionalProf = "AND pr.atribuicao "
                                . "IN (SELECT a1.codigo "
                                . "FROM Atribuicoes a1 "
                                . "WHERE a1.turma = :turmaP)";
                        $paramsProf['tipo'] = $PROFESSOR;
                        $paramsProf['turmaP'] = $turma;

                        foreach ($prof->listProfessores($paramsProf, $sqlAdicionalProf) as $reg) {
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
                <td>
                    <table width="100%">
                        <tr>
                            <td>&nbsp;</td>
                            <td align="right">
                                <a href="javascript:$('#index').load('<?= $SITE ?>');void(0);">Limpar</a>
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

$params['ano'] = $ANO;
$params['semestre'] = $SEMESTRE;

$res = $atribuicao->getAllAtribuicoes($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($atribuicao->getAllAtribuicoes($params, $sqlAdicional, null, null));

$params['turma'] = $_GET['turma'];
$params['professor'] = $_GET['professor'];
$params['ordem'] = $_GET['ordem'];
$SITENAV = $SITE . "?" . mapURL($params);

require PATH . VIEW . '/system/paginacao.php';
?>

<table id="listagem" border="0" align="center">
    <tr>
        <th align="left" width="60">N&uacute;mero</th>
        <th align="left">Disciplina</th>
        <th>Professor</th>
        <th width="70px">Turma</th>
        <th>Per&iacute;odo</th>
        <th align="center" width="50">&nbsp;&nbsp;
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
        $disc = $reg['bimestre'] . '['. $reg['turno'] .']';
        ?>
        <tr <?= $cdif ?>>
            <td><?= $reg['numero'] ?></td>
            <td>
                <a data-placement="right" data-content='Clique aqui para abrir o Di&aacute;rio desta disciplina.' title="<?= $reg['disciplina'] .' '. $disc ?>" target="_blank" href='<?= VIEW ?>/secretaria/relatorios/inc/diario.php?atribuicao=<?= crip($reg['atribuicao']) ?>'>
                <?= abreviar($reg['disciplina'], 30).$disc ?>
                </a>
            </td>
            <td align='left'><?= $prof->getProfessor($reg['atribuicao'], 1, '<br>', 1, 1) ?></td>
            <td align=left><?= $reg['turma'] ?></td>
            <td align=left><?= $reg['periodo'] ?></td>            
            <td align='center'>
                <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['atribuicao']) ?>' />
            </td>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>

<script>
    function atualizar(getLink) {
        var turma = $('#turma').val();
        var professor = $('#professor').val();
        var URLS = '<?= $SITE ?>?turma=' + turma + '&professor=' + professor;
        if (!getLink)
            $('#index').load(URLS + '&item=<?= $item ?>');
        else
            return URLS;
    }

    $(document).ready(function() {
        $(".item-excluir").click(function() {
            $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>', {
                'type': 'question',
                'title': '<?= $TITLE ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function(caption) {
                    if (caption == 'Sim') {
                        var selected = [];
                        $('input:checkbox:checked').each(function() {
                            selected.push($(this).val());
                        });
                        $('#index').load(atualizar(1) + '&opcao=delete&codigo=' + selected + '&item=<?= $item ?>');
                    }
                }
            });
        });
        
        $('#turma, #professor').change(function() {
            atualizar();
        });
        
        $('#select-all').click(function(event) {
            if (this.checked) {
                // Iterate each checkbox
                $(':checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $(':checkbox').each(function() {
                    this.checked = false;
                });
            }
        });        
    });
</script>