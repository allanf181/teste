<?php
//Esse arquivo é fixo para o professor.
//Permite a inserção de notas de avaliações pelo professor.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

require_once CONTROLLER . "/questionario.class.php";
$questionario = new Questionarios();

require_once CONTROLLER . "/questionarioResposta.class.php";
$resposta = new QuestionariosRespostas();

if ($_GET["opcao"] == 'importNotas') {
    $res = $resposta->listRespostasToJSON(dcrip($_GET["questionario"]));
    print json_encode($res);
    die;
}

require SESSAO;

require CONTROLLER . "/nota.class.php";
$nota = new Notas();

require CONTROLLER . "/avaliacao.class.php";
$aval = new Avaliacoes();

require CONTROLLER . "/atribuicao.class.php";
$att = new Atribuicoes();

require CONTROLLER . "/matricula.class.php";
$matricula = new Matriculas();

require CONTROLLER . "/matriculaAlteracao.class.php";
$ma = new MatriculasAlteracoes();
    
if ($_POST["opcao"] == 'InsertOrUpdate') {
    $_GET["avaliacao"] = $_POST["avaliacao"];
    $_GET["atribuicao"] = $_POST["atribuicao"];

    unset($_POST['opcao']);
    unset($_POST['atribuicao']);
    $ret = $nota->putNotas($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}
?>
<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<?php
$avaliacao = dcrip($_GET["avaliacao"]);
$atribuicao = dcrip($_GET["atribuicao"]);

// Cabeçalho
$resAval = $aval->getAvaliacao($avaliacao);
$travaFinal = $resAval['final'];
?>
<div id="etiqueta" align="center">
    <table width='80%'>
        <tr>
            <td>
                Curso: <?= $resAval['curso'] ?><br />
                Turma: <?= $resAval['turma'] ?><br />
                Semestre: <?= $resAval['semestre'] ?> / <?= $resAval['ano'] ?><br />
                Notas para: <?= $resAval['nome'] ?> de <?= $resAval['dataFormat'] ?>
                <?php if ($resAval['calculo'] == 'peso') { ?>
                    (peso: <?= $resAval['peso'] ?>)
                    <?php
                    $resAval['notaMaxima'] = '10';
                }
                ?>
                <br> Nota m&aacute;xima permitida: <?= $resAval['notaMaxima'] ?>
            </td>
            <td width = '33%' valign = 'top' align='right'>
                <a id="item-import" title='Importar resultado de um question&aacute;rio para Notas' data-content='Aten&ccedil;&atilde;o, as notas ser&atilde;o importadas apenas se o question&aacute;rio estiver desativado.' class = 'nav questionario_item' href = "#">
                    <img width = '48' src = "<?= IMAGES . '/questionarioDownload.png' ?>" title = 'Question&aacute;rios' class = 'menuQuestionario'/>
                </a>
            </td>
        </tr>
    </table>
</div>
<br><hr>

<?php
if ($_SESSION['dataExpirou'])
    $disabled = "disabled='disabled'";
?>
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
            <tr>
                <th align="center" width="80">Prontuário</th>
                <th align="center">Aluno</th>
                <th width="50" align='center'>Nota</th>
                <?php
                // SE FOR BIMESTRAL, ACHAR OS CODIGOS DAS OUTRAS ATRIBUICOES
                // PARA MOSTRAR AS NOTAS DOS BIMESTRES ANTERIORES
                if ($resAval['bimestre'] <> 0) {
                    foreach ($att->listAtribuicoesOfBimestre($atribuicao, $ANO) as $reg) {
                        if ($resAval['bimestre'] == $reg['bimestre'] && !$resAval['final'])
                            $color = 'blue';
                        else
                            $color = "";
                        ?>
                        <th width="35" align='center'>
                            <font color="<?= $color ?>">&nbsp;<?= $reg['bimestre'] ?>&ordm; BIM</font>
                        </th>
                        <?php
                        $AT_BIM[$reg['bimestre']] = $reg['codigo'];
                    }
                    if ($resAval['final'])
                        $color = 'blue';
                    else
                        $color = '';
                    ?>
                    <th width="50"><font color="<?= $color ?>">M&eacute;dia</font></th>
                    <?php
                } else {
                    ?>
                    <th width="50">M&eacute;dia</th>
                    <?php
                }
                ?>
                <th width="100"></th>
                <?php
                $i = 1;

                foreach ($aval->getNotasAlunosOfAvaliacao($atribuicao, $avaliacao) as $reg) {
                    $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                    ?>
                <tr <?= $cdif ?>>
                    <td align='center'><?= $reg['prontuario'] ?></td>
                    <td><a href='#' rel='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codAluno']) ?>' class='screenshot nav' title='<?= mostraTexto($reg['aluno']) ?>'>
                            <img style='width: 20px; height: 20px' src='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codAluno']) ?>' />
                        </a>
                        <a class='nav' title='Clique aqui para ver o boletim do aluno.' href="javascript:$('#professor').load('<?= VIEW ?>/professor/boletim.php?aluno=<?= crip($reg['codAluno']) ?>&turma=<?= crip($reg['turma']) ?>&bimestre=<?= crip($reg['bimestre']) ?>');void(0);"><?= mostraTexto($reg['aluno']) ?></a>
                    </td>
                    <td align='center'>
                        <?php
                        $matSituacao = $ma->getAlteracaoMatricula($reg['codAluno'], $atribuicao, $resAval['data']);
                        if ($matSituacao['listar'] && $matSituacao['habilitar']) {
                            ?>
                            <input type='hidden' name='codigo[<?= $reg['matricula'] ?>]' value='<?= $reg['codNota'] ?>'>
                            <input <?= $disabled ?> id='A<?= $reg['codAluno'] ?>' tabindex='<?= $i ?>' style='width: 30px' type='text' value='<?= $reg['nota'] ?>' size='4' maxlength='4' name='matricula[<?= $reg['matricula'] ?>]' onchange="validaItem(this)" />
                            <?php
                            $situacao = array();
                            if ($reg['bimestre'] > 0) { // Busca as Notas dos Bimestres
                                foreach ($AT_BIM as $nBim => $at) {
                                    $dados = $nota->resultado($matricula->getMatricula($reg['codAluno'], $at, $nBim), $at, 0);
                                    if ($reg['bimestre'] == $nBim && $res['final'] == 0) {
                                        $color = 'blue';
                                        $situacao[$reg['codAluno']] = abreviar($dados['situacao'], 14);
                                    } else {
                                        $color = null;
                                    }
                                    ?>
                                <td align='center'><font color='<?= $color ?>'><?= $dados['media'] ?></font></td>
                                <?php
                            }

                            if ($reg['bimestre'] == 4 && $nBim == 4 && $resAval['tipo'] == 'recuperacao' && !$situacao[$reg['codAluno']])
                                $resAval['final'] = 1;
                            $dados1 = $nota->resultadoBimestral($reg['codAluno'], $resAval['turmaCodigo'], $resAval['discNumero']);
                            if ($resAval['final'])
                                $color = 'blue';
                            else
                                $color = '';
                            ?>
                            <td align='center'><font color="<?= $color ?>"><?= $dados1['media'] ?></font></td>
                            <?php
                            if ($reg['bimestre'] == 4 && $nBim == 4 && $resAval['tipo'] == 'recuperacao' && !$situacao[$reg['codAluno']]) {
                                ?>
                                <td align='center'><?= abreviar($dados1['situacao'], 24) ?></td>
                                <?php
                                //TRAVANDO PARA REAVALIACAO FINAL
                                $trava = null;
                                if ($travaFinal && !$dados1['situacao'] && $resAval['tipo'] == 'recuperacao' && !$reg['nota']) {
                                    ?>
                                                    <!--<script> $('#<?= $i ?>').attr('disabled','disabled'); </script>-->
                                    <?php
                                }
                            }
                            ?>
                            <td align='center'><?= $situacao[$reg['codAluno']] ?></td>
                            <?php
                            //TRAVANDO PARA RECUPERACAO
                            if (!$travaFinal && !$situacao[$reg['codAluno']] && $resAval['tipo'] == 'recuperacao' && !$reg['nota']) {
                                ?>
                                                <!--<script> $('#<?= $i ?>').attr('disabled','disabled'); </script>-->
                                <?php
                            }
                        } else {
                            $dados = $nota->resultado($reg['matricula'], $atribuicao, $resAval['final']);
                            ?>
                            <td align='center'><?= $dados['media'] ?></td>
                            <td align='center'><?= $dados['situacao'] ?></td>
                            <?php
                            // TRAVANDO PARA ATRIBUICOES NAO BIMESTRAIS
                            if (!$dados['situacao'] && $resAval['tipo'] == 'recuperacao' && !$reg['nota']) {
                                ?>
                            <script> $('#<?= $i ?>').attr('disabled', 'disabled');</script>
                            <?php
                        }
                    }
                } else {
                    ?>
                    <td align='center' colspan='6'><?= $matSituacao['tipo'] ?></td>
                    <?php
                }
                ?>
                </tr>
                <?php
                $i++;
            }
            ?>
        </table>
        <table align="center">
            <tr><td></td><td>
                    <input type="hidden" value="<?= crip($avaliacao) ?>" name="avaliacao" />
                    <input type="hidden" value="<?= crip($atribuicao) ?>" name="atribuicao" />
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <?php if (!$disabled) { ?>
                        <input type="submit" value="Salvar" name="salvar" />
                    <?php } ?>
                </td></tr>
        </table>
    </form>
</div>

<br>
<div style='margin: auto'>
    <a href="javascript:$('#professor').load('<?= VIEW ?>/professor/avaliacao.php?atribuicao=<?= crip($atribuicao) ?>');void(0);" class='voltar' title='Voltar' >
        <img class='botao' src='<?= ICONS ?>/left.png'/>
    </a>
</div>
<?php
$_SESSION['VOLTAR'] = "professor";
$_SESSION['LINK'] = VIEW . "/professor/nota.php?atribuicao=" . crip($atribuicao) . "&avaliacao=" . crip($avaliacao);

$params['criador'] = $_SESSION['loginCodigo'];
$res = $questionario->listQuestionarios($params);
?>
<script>
    function validaItem(item) {
        item.value = item.value.replace(",", ".");
        if (item.value < 0 || item.value > <?= $resAval['notaMaxima'] ?>) {
            item.value = '';
        }
    }

    $("#item-import").click(function () {
        function preparaInput() {
            var resultado = '<br>Question&aacute;rio: ';
            resultado += '<select id="Zebra_valor" name="Zebra_valor" value="">';
            <?php
            foreach ($res as $reg) {
                ?>
                resultado += "<option value='<?= crip($reg['codigo']) ?>'><?= $reg['nome'] ?></option>\n";
                <?php
            }
            ?>
            resultado += "</select>";
            return resultado;
        }

        $.Zebra_Dialog('<strong>Selecione o question&aacute;rio para importar as notas:</strong>', {
            'type': 'prompt',
            'promptInput': preparaInput(),
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption, valor) {
                if (caption == 'Sim') {
                    importaNota(valor);
                }
            }
        });
        
    function importaNota(valor) {
        $.ajax({
            url: '<?= $SITE ?>',
            data: {'opcao': 'importNotas', 'questionario': valor},
            dataType: 'json',
            success: function (data)
            {
                for (var i in data) {
                    alert(data[i].total);
                    $('#A'+data[i].codAluno).val(data[i].total);
                }
            }
        });
    }
    });
</script>