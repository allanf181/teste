<?php
//Esse arquivo é fixo para o professor.
//Permite o registro de frequências no WebDiário.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/frequencia.class.php";
$freq = new Frequencias();

require CONTROLLER . "/aula.class.php";
$aulaFreq = new Aulas();

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $_GET["aula"] = $_POST["aula"];
    $_GET["atribuicao"] = $_POST["atribuicao"];

    unset($_POST['opcao']);
    unset($_POST['atribuicao']);
    $ret = $freq->putFrequencias($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}
?>

<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>

<?php
$aula = dcrip($_GET["aula"]);
$atribuicao = dcrip($_GET["atribuicao"]);

if ($_SESSION['dataExpirou'])
    $disabled = "disabled='disabled'";

// Cabeçalho
$dadosAula = $aulaFreq->getAula($aula);
?>
<div id="etiqueta" align="center">
    <span class='rotulo_professor'>Curso: </span><?= $dadosAula['curso'] ?><br />
    <span class='rotulo_professor'>Turma: </span><?= $dadosAula['turma'] ?><br />
    <span class='rotulo_professor'>Semestre: </span><?= $dadosAula['semestre'] ?>/<?= $dadosAula['ano'] ?><br />
    <span class='rotulo_professor'>Chamada para: </span><?= $dadosAula['dataFormatada'] ?> - <?= $dadosAula['quantidade'] ?> aulas<br />
</div>
<hr><br>

<script>
    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?= $SITE ?>',
        responseDiv: '#professor',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })
</script>
<div id="html5form" class="main">
    <form id="form_padrao">
        <table id="listagem" border="0" align="center">
            <tr class="listagem_tr">
                <th align="center" style="width: 100px">Prontuário</th>
                <th align="center">Aluno</th>
                <th width="120" align='center'>Faltas<br />
                    <input type="checkbox" id="select-all" value="">
                </th>
                <th width="50" align='center'>Total</th>
                <th width="85" align='center'>Frequ&ecirc;ncia na Disciplina</th>
            </tr>
            <?php
            $i = 1;
            foreach ($aulaFreq->listAlunosByAula($atribuicao, $aula) as $reg) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "class='cdif2'";
                ?>
                <tr <?= $cdif ?> ><td align='center'><?= $reg['prontuario'] ?></td>
                    <td>
                        <a href='#' rel='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codAluno']) ?>' class='screenshot'>
                            <img class='foto_lista' src='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codAluno']) ?>' /></a>
                        <a class='nav' title='Clique aqui para ver o boletim do aluno.' href="javascript:$('#professor').load('<?= VIEW ?>/aluno/boletim.php?aluno=<?= crip($reg['codAluno']) ?>&turma=<?= crip($reg['turma']) ?>&bimestre=<?= crip($reg['bimestre']) ?>'); void(0);"><?= mostraTexto($reg['aluno']) ?></a>
                    </td>
                    <?php
                    $frequencia = $freq->getFrequencia($reg['matricula'], $atribuicao);

                    if ($reg['listar']) {
                        if ($reg['habilitar']) {
                            ?>
                            <td align='left'>
                                <?php
                                if (!$A = $freq->getFrequenciaAbono($reg['codAluno'], $atribuicao, $dadosAula['data'])) {
                                    ?>
                                    <input type='hidden' name='codigo[<?= $reg['matricula'] ?>]' value='<?= $reg['freqCodigo'] ?>'>
                                    <input <?= $disabled ?> type='hidden' checked name='matricula[<?= $reg['matricula'] ?>][<?= $reg['matricula'] ?>]' />
                                    <?php
                                    for ($n = 0; $n < $reg['aulaQde']; $n++) {
                                        if (substr($reg['frequencia'], $n, 1) == 'F')
                                            $F = 'checked';
                                        else
                                            $F = '';
                                        ?>
                                        <input id='<?= $reg['matricula'] ?>' class='<?= $reg['matricula'] ?>' <?= $disabled ?> tabindex='<?= $i ?>' type='checkbox' <?= $F ?> name='matricula[<?= $reg['matricula'] ?>][<?= $n ?>]' />
                                        <?php
                                    }
                                } else {
                                    print $A['tipo'];
                                }
                                ?>
                            </td>
                            <td align='center'><?= $frequencia['faltas'] ?></td>
                            <td align='center'><?= arredondar($frequencia['frequencia']) ?>%</td>
                            <?php
                        } else {
                            ?>
                            <td align='center' colspan='3'><?= $reg['situacao'] ?></td>
                            <?php
                        }
                    }
                    $i++;
                    ?>
                </tr>
                <?php
            }
            ?>
        </table>
        <table align="center" style="width: 100%; margin-top: 10px;">
            <tr><td></td><td align="center">
                    <input type="hidden" value="<?= $reg['aulaQde']; ?>" name="quantidade" />
                    <input type="hidden" value="<?= crip($aula); ?>" name="aula" />
                    <input type="hidden" value="<?= crip($atribuicao); ?>" name="atribuicao" />
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <input id="professores_botao" <?= $disabled ?> type="submit" value="Salvar" />
                </td></tr>
        </table>
    </form>
</div>
<br>
<div style='margin: auto'>
    <a href="javascript:$('#professor').load('<?= VIEW ?>/professor/aula.php?atribuicao=<?= crip($atribuicao) ?>'); void(0);" class='voltar' title='Voltar' >
        <img class='botao' src='<?= ICONS ?>/left.png'/>
    </a>
</div>

<?php
$_SESSION['VOLTAR'] = "professor";
$_SESSION['LINK'] = VIEW . "/professor/frequencia.php?atribuicao=" . crip($atribuicao) . "&aula=" . crip($aula);
?>

<script>
    $(document).ready(function() {
        $("input:checkbox").click(function() {
            var codigo = $(this).attr('id');
            if ($(this).prop('checked') == true) {
                $('.' + codigo).prop('checked', true);
            }
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