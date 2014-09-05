<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Visualização da alocação das salas dos respectivos professores e das disciplinas dadas nesta sala em determinado horário.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/ensalamento.class.php";
$ensalamento = new Ensalamentos();

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $ensalamento->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>

<?php
if (dcrip($_GET["turma"])) {
    $params['turma'] = dcrip($_GET["turma"]);
    $sqlAdicional = " and t.codigo=:turma";
    $turma = dcrip($_GET["turma"]);
}
?>
<script>
    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?php print $SITE; ?>',
        responseDiv: '#index',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })
</script>

<div id="html5form" class="main">
    <form id="form_padrao"></form>
    <table align="center" width="100%" id="form">
        <input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
        <tr><td align="right" style="width: 100px">Turma: </td><td>
                <select name="campoTurma" id="campoTurma" value="<?php echo $turma; ?>">
                    <option></option>
                    <?php
                    require CONTROLLER . '/turma.class.php';
                    $turmas = new Turmas();
                    $paramsTurma = array(':ano' => $ANO,':semestre' => $SEMESTRE);
                    $res = $turmas->listTurmas($paramsTurma);
                    foreach ($res as $reg) {
                        $selected = "";
                        if ($reg['codTurma'] == $turma)
                            $selected = "selected";
                        print "<option $selected value='" . crip($reg['codTurma']) . "'>" . $reg['numero'] . " [".$reg['curso']."]</option>";
                    }
                    ?>
                </select>
            </td>
            <?php if ($turma) { ?>
                <td><a href="#" class='ensalamento'><img src="<?= VIEW ?>/css/images/horario.png" width="50%"></a></td>
            <?php } ?>
        </tr>
        <tr><td></td><td>   
                <input type="hidden" name="opcao" value="InsertOrUpdate" />
                <table width="100%"><tr>
                        <td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Limpar</a></td>
                    </tr></table>
            </td><td></td></tr>
    </table>
</form>
<?php
// PAGINACAO
$item = 1;
$itensPorPagina = 50;

if (isset($_GET['item']))
    $item = $_GET["item"];

$params['ano'] = $ANO;
$params['semestre'] = $SEMESTRE;

$res = $ensalamento->listEnsalamentos($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($ensalamento->listEnsalamentos($params, $sqlAdicional));

$params['turma'] = crip($curso);

$SITENAV = $SITE . "?" . mapURL($params);

require(PATH . VIEW . '/paginacao.php');
?>	
<table id="listagem" border="0" align="center">
    <tr><th align="center" width="40">#</th><th>Atribui&ccedil;&atilde;o</th><th>Sala</th><th width="180">Hor&aacute;rio</th><th align="center" width="40">
            <input type='checkbox' id="select-all" value="" />
            <a href="#" class='item-excluir'><img class='botao' src='<?php print ICONS; ?>/delete.png' /></a></th></tr>
    <?php
    $i = $item;
    $dias = diasDaSemana();
    foreach ($res as $reg) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        $codigo = crip($reg['codigo']);
        $reg['diaSemana'] = $dias[$reg['diaSemana']];
        ?>
        <tr <?= $cdif ?>><td align='left'><?= $i ?></td>
            <td><?= $reg['professor'] . ' [' . $reg['discNumero'] . '][' . $reg['turma'] . ']' ?></td>
            <td><?= $reg['sala'] ?></td>
            <td><?= $reg['horario'] . ' [' . $reg['inicio'] . ' - ' . $reg['fim'] . ']' ?></td>
            <td align='center'>
                <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
            </td>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>

<style>
    .ontop {
        z-index: 999;
        width: 100%;
        height: 1000px;
        top: 0;
        left: 0;
        display: none;
        position: absolute;				
        background-color: #666;
        color: #aaaaaa;
        opacity: .95;
    }
    #popup {
        width: 800px;
        min-height: 200px;
        position: absolute;
        color: #000000;
        background-color: #fff;
        top: 40%;
        left: 30%;
        margin-top: -100px;
        margin-left: -150px;
    }
</style>

<div id="popDiv" class="ontop">
    <?php
    // MOSTRA ENSALAMENTO
    if ($turma) {

        $codigo = dcrip($_GET['turma']);
        $tipo = 'turma';

        $res = $ensalamento->getEnsalamento($codigo, $tipo, $ANO, $SEMESTRE, $subturma);

        foreach ($res as $reg) {
            $reg['horario'] = str_ireplace("[$match[1]]", "", $reg['horario']);
            $link = $reg['disciplina'] . ' (' . $reg['professor'] . '): ' . $reg['sala'] . ' - ' . $reg['localizacao'];
            $horas[$reg['diaSemana']][] = "<a href='#' title='$link'>" . $reg['inicio'] . ' - ' . $reg['fim'] . '<br>' . $reg['discNumero'] . ' - ' . $reg['horario'] . "</a>";
            $turmaNome = $reg['turma'];
        }

        require CONTROLLER . "/turno.class.php";
        $t = new Turnos();
        $res = $t->listRegistros();
        foreach ($res as $reg)
            $turnos[$reg['sigla']] = $reg['nome'];

        $MOSTRA = "Turma $turmaNome";

        if ($atribuicao)
            $MOSTRA = $discNome[$atribuicao];
        ?>
        <h2><font color="white"><?= $MOSTRA ?></h2>

        <center><table width="80%" border="0" summary="Calendário" id="tabela_boletim">
                <thead>
                <tr><td colspan="7" align="right"><a href="#" onClick="hide('popDiv');">Fechar</a></td></tr>
                    <tr>
                        <?php
                        foreach (diasDaSemana() as $dCodigo => $dNome) {
                            ?>
                            <th abbr="Domingo" title="<?= $dNome ?>"><span style='font-weight: bold; color: white'><?= $dNome ?></span></th>
                            <?php
                        }
                        ?>
                    </tr>
                </thead>
                <tr align="center">
                    <?php
                    for ($i = 1; $i <= 7; $i++) {
                        $TA = '';
                        ?>
                        <td style='width: 10%;' valign="top">
                            <?php
                            if (isset($horas[$i]))
                                foreach ($horas[$i] as $disc) {
                                    preg_match('#\[(.*?)\]#', $disc, $match);
                                    $T = $match[1];
                                    if ($T != $TA) {
                                        print strtoupper($turnos[$T]) . "<hr>\n";
                                        $TA = $T;
                                    }
                                    print str_ireplace("[$match[1]]", "", $disc);
                                    print '<br>-----------------<br>';
                                }
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                </tr>
            </table>
        </center>
        <?php
    }
    ?>
</div>

<script>
function atualizar(getLink) {
    var turma = $('#campoTurma').val();
    var URLS = '<?php print $SITE; ?>?turma=' + turma;
    if (!getLink)
        $('#index').load(URLS + '&item=<?php print $item; ?>');
    else
        return URLS;
}

function pop(div) {
    document.getElementById(div).style.display = 'block';
}
function hide(div) {
    document.getElementById(div).style.display = 'none';
}

document.onkeydown = function(evt) {
    evt = evt || window.event;
    if (evt.keyCode == 27) {
        hide('popDiv');
    }
};

$(document).ready(function() {
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

    $('#campoTurma').change(function() {
        atualizar();
    });

    $(".ensalamento").click(function() {
        pop('popDiv');
    });

    $(".item-excluir").click(function() {
        $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?', {
            'type': 'question',
            'title': '<?php print $TITLE; ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function(caption) {
                if (caption == 'Sim') {
                    var selected = [];
                    $('input:checkbox:checked').each(function() {
                        selected.push($(this).val());
                    });
                    $('#index').load('<?php print $SITE; ?>?opcao=delete&codigo=' + selected + '&item=<?php print $item; ?>');
                }
            }
        });
    });
});
</script>