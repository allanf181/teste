<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela de registro de abonos de faltas dos alunos referente ao regime de exercícios domiciliares, matrícula após início do ano letivo ou por outro motivo.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

require CONTROLLER . "/abono.class.php";
$abono = new FrequenciasAbonos();

if ($_POST["opcao"] == 'InsertOrUpdate') {
    extract(array_map("htmlspecialchars", $_POST), EXTR_OVERWRITE);
    unset($_POST['opcao']);

    $ret = $abono->insertOrUpdateAbono($_POST);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    if ($_POST['codigo'])
        $_GET["codigo"] = $_POST['codigo'];
    else
        $_GET["codigo"] = crip($ret['RESULTADO']);
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $abono->deleteAbono($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
}
?>
<h2><?php print $TITLE; ?></h2>

<?php
// inicializando as variáveis do formulário
$params['ano'] = $ano;
$sqlAdicional .= " AND date_format(f.data, '%Y') = :ano ";

if (dcrip($_GET["aluno"]) != "") {
    $params['aluno'] = dcrip($_GET["aluno"]);
    $sqlAdicional .= "AND aluno = :aluno";
    $aluno = dcrip($_GET["aluno"]);
}

$aula = dcrip($_GET["aula"]);
$atribuicao = dcrip($_GET["atribuicao"]);

$dataInicio = $_GET['dataInicio'];
$dataFim = $_GET['dataFim'];

if ($dataFim && $dataInicio) {
    $params['dataInicio'] = dataMysql($dataInicio);
    $params['dataFim'] = dataMysql($dataFim);
    $sqlAdicional .= " AND f.data >= :dataInicio AND f.data <= :dataFim ";
} else {
    if (!empty($dataInicio)) {
        $params['dataInicio'] = dataMysql($dataInicio);
        $sqlAdicional .= " AND f.data = :dataInicio ";
    }
    if (!empty($dataFim)) {
        $params['dataFim'] = dataMysql($dataFim);
        $sqlAdicional .= " AND f.data = :dataFim ";
    }
}

// LISTAGEM
if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $params = array('codigo' => dcrip($_GET["codigo"]));
    $sqlAdicional = ' AND f.codigo = :codigo';
    $res = $abono->listAbonos($params, $sqlAdicional);
    extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>

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
    <form id="form_padrao">

        <table align="center" width="100%" id="form">
            <input type="hidden" value="<?php echo crip($codigo); ?>" name="codigo" id="codigo" />
            <tr><td  style="width: 100px" align="right">Data: </td><td>
                    <input type="text" size="10" value="<?php echo $dataInicio; ?>" name="dataInicio" id="dataInicio" />
                    a <input type="text" size="10" value="<?php echo $dataFim; ?>" name="dataFim" id="dataFim" />
                </td>
            </tr>
            <tr><td align="right">Aluno: </td>
                <td><select name="aluno" id="aluno" style="width: 350px">
                        <?php
                        require CONTROLLER . '/pessoa.class.php';
                        $pessoa = new Pessoas();
                        $paramPessoa = array('tipo' => $ALUNO);
                        $res = $pessoa->listPessoas($paramPessoa, " AND pt.tipo = :tipo");
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $aluno)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>[" . $reg['prontuario'] . "]" . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </td></tr>
            <tr><td align="right">Disciplina: </td>
                <td><select name="atribuicao" id="atribuicao" style="width: 350px">
                        <option></option>
                        <?php
                        require CONTROLLER . '/atribuicao.class.php';
                        $att = new Atribuicoes();
                        $res = $att->listAtribuicoes($aluno, 'aluno');
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['atribuicao'] == $atribuicao)
                                $selected = "selected";
                            if ($reg['bimestre'] > 0)
                                $BIM = '[' . $reg['bimestre'] . 'ºBIM]';
                            echo "<option $selected value='" . crip($reg['atribuicao']) . "'>" . $reg['disciplina'] . " $BIM</option>";
                        }
                        ?>
                    </select> ou 
                </td></tr>
            <?php

            if ($atribuicao)
                $disabled = 'disabled';
            ?>
            <tr><td align="right">Aula: </td>
                <td><select name="aula" <?php print $disabled; ?> id="aula" style="width: 350px">
                        <option></option>
                        <?php
                        require CONTROLLER.'/turno.class.php';
                        $turno = new Turnos();
                        $res = $turno->listRegistros();
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['sigla'] == $aula)
                                $selected = "selected";
                            echo "<option $selected value='" . crip($reg['sigla']) . "'>".$reg['nome']."</option>";
                        }

                        require CONTROLLER.'/horario.class.php';
                        $horario = new Horarios();
                        $res = $horario->listHorarios();
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['nome'] == $aula)
                                $selected = "selected";
                            echo "<option $selected value='" . crip($reg['nome']) . "'>".$reg['nome']." [".$reg['inicio']." - ".$reg['fim']."]</option>";
                        }
                        ?>
                    </select>
                </td></tr>
            <tr><td align="right">Motivo:</td><td><input type="text" size="50" maxlength="255" name="motivo" id="motivo" value="<?php echo $motivo; ?>" />
                </td></tr>
            <tr><td align="right">Tipo: </td><td>
                    <input type="radio" id="tipo" <?php if ($sigla == 'A') print 'checked'; ?> name="tipo" value="A" />Abono &nbsp;
                    <input type="radio" id="tipo" <?php if ($sigla == 'D') print 'checked'; ?> name="tipo" value="D" />Dispensa &nbsp;
                    <input type="radio" id="tipo" <?php if ($sigla == 'R') print 'checked'; ?> name="tipo" value="R" />Regime de Exerc&iacute;cios Domiciliares &nbsp;
                    <input type="radio" id="tipo" <?php if ($sigla == 'M') print 'checked'; ?> name="tipo" value="M" />Matr&iacute;cula ap&oacute;s inicio letivo &nbsp;
                </td></tr>
            <tr><td></td><td>
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                            <td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Novo/Limpar</a></td>
                        </tr></table>
                </td></tr>
        </table>
    </form>
    <?php
// PAGINACAO
    $itensPorPagina = 20;
    $item = 1;
    $ordem = '';

    if (isset($_GET['item']))
        $item = $_GET["item"];

    $res = $abono->listAbonos($params, $sqlAdicional, $item, $itensPorPagina);
    $totalRegistros = count($abono->listAbonos($params, $sqlAdicional));

    $params['aluno'] = crip($params['aluno']);
    $params['atribuicao'] = crip($params['atribuicao']);
    $SITENAV = $SITE . "?" . mapURL($params);

    require PATH . VIEW . '/paginacao.php';
    ?>
    <table id="listagem" border="0" align="center">
        <tr><th width="80">Data</th><th width="80">Prontu&aacute;rio</th><th width="220">Aluno</th><th th width="200">Tipo</th><th>Aula/Disciplina</th><th width="50">&nbsp;&nbsp;<input type="checkbox" id="select-all" value=""><a href="#" class='item-excluir'><img class='botao' src='<?php print ICONS; ?>/delete.png' /></a></th></tr>
        <?php
        $i = $item;
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?php print $cdif; ?>><td><?php print $reg['dataInicio']; ?></td>
                <td><?php print $reg['prontuario']; ?></td>
                <td><?php print mostraTexto($reg['nome']); ?></td>
                <td><a href="#" title="<?php print mostraTexto($reg['tipo']); ?>"><?php print $reg['sigla']; ?></a></td>
                <td><?php print $reg['aula'] . ' / ' . $reg['disciplina']; ?></td>
                <td align='center'>
                    <input type='checkbox' id='deletar' name='deletar[]' value='<?php print crip($reg['codigo']); ?>' />
                    <a href='#' title='Alterar' class='item-alterar' id='<?php print crip($reg['codigo']); ?>'>
                        <img class='botao' src='<?php print ICONS; ?>/config.png' /></a>
                </td></tr>
            <?php
            $i++;
        }
        ?>
    </table>

    <script>
        function atualizar(getLink) {
            var dataInicio = $('#dataInicio').val();
            var dataFim = $('#dataFim').val();
            var aluno = $('#aluno').val();
            var atribuicao = $('#atribuicao').val();
            var URLS = '<?php print $SITE; ?>?dataInicio=' + dataInicio + '&dataFim=' + dataFim + '&aluno=' + aluno + '&atribuicao=' + atribuicao;
            if (!getLink)
                $('#index').load(URLS + '&item=<?php print $item; ?>');
            else
                return URLS;
        }

        function valida() {
            if ($('#dataInicio').val() != "" && $('#motivo').val() != ""
                    && ($('#atribuicao').val() != "" || $('#aula').val() != "")) {
                $('#salvar').enable();
            } else {
                $('#salvar').attr('disabled', 'disabled');
            }
        }

        $(document).ready(function() {
            valida();
            $('#dataInicio, #motivo, #atribuicao, #aula').change(function() {
                valida();
            });

            $("#dataInicio, #dataFim").datepicker({
                dateFormat: 'dd/mm/yy',
                defaultDate: '<?php print date("d/m/Y"); ?>',
                dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                nextText: 'Próximo',
                prevText: 'Anterior'
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

            $(".item-alterar").click(function() {
                var codigo = $(this).attr('id');
                $('#index').load(atualizar(1) + '&codigo=' + codigo);
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

<?php if (!$_GET["codigo"]) { ?>
                $('#dataInicio, #dataFim, #aluno, #atribuicao').change(function() {
                    atualizar();
                });
<?php } ?>
        });
    </script>