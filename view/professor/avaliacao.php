<?php
//Esse arquivo é fixo para o professor.
//Permite a inserção de avaliações no WebDiário.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/avaliacao.class.php";
$avaliacao = new Avaliacoes();

require CONTROLLER . "/atribuicao.class.php";
$att = new Atribuicoes();

require CONTROLLER . "/logSolicitacao.class.php";
$log = new LogSolicitacoes();

require CONTROLLER . "/notaFinal.class.php";
$notaFinal = new NotasFinais();

// PEDIDO DE LIBERAÇÃO DO DIÁRIO
if ($_GET["motivo"]) {
    $paramsLog['dataSolicitacao'] = date('Y-m-d h:i:s');
    $paramsLog['solicitacao'] = 'Docente solicitou abertura do diário, motivo: ' . $_GET['motivo'];
    $paramsLog['codigoTabela'] = $_GET['atribuicao'];
    $paramsLog['nomeTabela'] = 'DIARIO';
    $paramsLog['solicitante'] = $_SESSION['loginCodigo'];
    $ret = $log->insertOrUpdate($paramsLog);
    mensagem($ret['STATUS'], 'PRAZO_DIARIO');
}

if ($_GET["opcao"] == 'controleDiario') {
    $atribuicao = dcrip($_GET["atribuicao"]);
    $ifa=0;
    $liberaDN=0;

    if (isset($_GET["ifa"]))
        $ifa= dcrip($_GET["ifa"]);
    if (isset($_GET["liberaDN"]))
        $liberaDN=dcrip($_GET["liberaDN"]);

    if (!$erro = $notaFinal->fecharDiario($atribuicao)) {
        $paramsLog['nomeTabela'] = 'DIARIO';
        $paramsLog['solicitante'] = $_SESSION['loginCodigo'];
        $paramsLog['dataSolicitacao'] = date('Y-m-d H:m:s');
        $paramsLog['dataConcessao'] = date('Y-m-d H:m:s');
        $paramsLog['codigoTabela'] = $atribuicao;
        $paramsLog['solicitacao'] = 'Professor fechou as notas manualmente.';
        $log->insertOrUpdate($paramsLog);
        $msgFinal='<strong>Notas enviadas.</strong>';
        if ($ifa==1 || $liberaDN==1)
            $msgFinal = '<strong>Notas enviadas. Pode finalizar o diário!</strong>';
        
        ?>
        <div id="1" style="visibility: hidden; max-height: 1px; max-width: 1px; overflow:scroll;display:block;;"></div>
        <div id="2" style="visibility: hidden; max-height: 1px; max-width: 1px; overflow:scroll;display:block;;"></div>
        <script>
        $('#obs, #rec_text').hide();
        $('#rec_label').html('');
        var req1 = $.get("db2/db2DigitaNotas.php?atribuicao=<?=$atribuicao?>", function(data) {
            $("#tabs-1").html(data);
        });
        var req2 = $.get("db2/db2ConsultaDisciplinas.php?atribuicao=<?=$atribuicao?>", function(data) {
            $("#tabs-2").html(data);
        });        $.when(req1, req2).then(function(){
            $.Zebra_Dialog('<?=$msgFinal?>', {
                'type': 'info',
                'title': '<?= $TITLE ?>',
                'buttons': ['OK'],
                'onClose': function (caption) {
                    if (caption == 'OK') {
                        $('#nota_text').html('<?=$msgFinal?>');
                        $('#professor').load('<?= $SITE ?>?atribuicao=<?= crip($atribuicao) ?>');
                    }
                }
            });
        });
        </script>
        <?php

    } else {
        print "Problema ao fechar notas = $erro!";
    }
    die;
}

// INSERT E UPDATE DE AVALIACOES
if ($_POST["opcao"] == 'InsertOrUpdate') {
    $_POST['data'] = dataMysql($_POST['data']);
    unset($_POST['opcao']);

    $_POST['nome'] = crip($_POST['nome']);
    $_POST['sigla'] = crip($_POST['sigla']);

    if ($_POST['substitutiva']) {
        $params['codigo'] = $_POST['tipo'];
        $p = $avaliacao->listRegistros($params);
        $_POST['peso'] = $p[0]['peso'];
        $_POST['substitutiva'] = crip($_POST['tipo']);
        $_POST['tipo'] = $_POST['codigo'];
        unset($_POST['codigo']);
    }

    $ret = $avaliacao->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["atribuicao"] = $_POST['atribuicao'];
}

// DELETE
if ($_GET["opcao"] == 'delete') {
    $ret = $avaliacao->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET['opcao'] = null;
}

// INSERINDO A FORMULA
if ($_POST["opcao"] == 'InsertFormula') {
    unset($_POST['opcao']);
    $ret = $att->insertOrUpdate($_POST);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET['atribuicao'] = $_POST['codigo'];
}

$atribuicao = dcrip($_GET["atribuicao"]);

// INSERINDO O CALCULO ESCOLHIDO
if ($_GET['opcao'] == 'calculo') {
    $params = array('codigo' => crip($atribuicao), 'calculo' => crip($_GET['calculo']));
    $att->insertOrUpdate($params);
    $_GET['opcao'] = null;
}

//LISTANDO O CALCULO ESCOLHIDO
$params = array('codigo' => $atribuicao);
$atrib = $att->listRegistros($params);
$calculo = $atrib[0]['calculo'];
$formula = $atrib[0]['formula'];
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<?php
if ($calculo == 'peso')
    $PONTO = 1;
if ($calculo == 'soma')
    $PONTO = 10;

if ($_GET['opcao'] == 'insert') {

    $tipoAval = strtolower(dcrip($_GET['tipo']));
    if ($tipoAval == 'pontoextra')
        $PONTO = 10;

    $pontos = dcrip($_GET["pontos"]); // pontos ja atribuidos
    $maxPontos = $PONTO - $pontos;

    $avalSiglas = $avaliacao->listAvaliacoes($atribuicao);
    ?>
    <script>
        valida();
        $(document).ready(function () {
    <?php
    if ($calculo == 'peso' || $calculo == 'soma' || $tipoAval == 'pontoextra') {
        ?>
                $("#valor").mask("99.99");
                $("#valor").change(function () {
                    if ($(this).val() > <?= $maxPontos ?>)
                        $(this).val('<?= str_pad(number_format($maxPontos, 2), 5, "0", STR_PAD_LEFT) ?>');
                });
        <?php
        if ($maxPontos > 0.1) {
            $P = "&& $('#valor').val()!=\"00.00\" && $('#valor').val()!=\"\" && $('#valor').val()!=\"__.__\" ";
            $P1 = ', #valor';
        }
    }
    ?>
            $('#data1, #tipo').change(function () {
                $('#sigla').val($('#sigla').val().toUpperCase());
                valida();
            });

            $('#nome <?= $P1 ?>, #sigla').keyup(function () {
                $('#sigla').val($('#sigla').val().toUpperCase());
                valida();
            });
        });
        function valida() {
    <?php
    if (!$_GET['codigo']) {
        ?>
                var Siglas = new Array();
        <?php
        $i = 0;
        foreach ($avalSiglas as $r) {
            ?>
                    Siglas[<?= $i ?>] = '<?= strtoupper($r['sigla']) ?>';
            <?php
            $i++;
        }
        ?>
                if ($('#sigla').val() && Siglas.indexOf($('#sigla').val()) != -1) {
                    $('#Siglas').html('Essa sigla já existe, escolha outra');
                    $('#sigla').val('');
                } else {
                    $('#Siglas').html('');
                }
        <?php
    }
    ?>

            if ($('#data').val() != "" && $('#tipo').val() != null &&
                    $('#nome').val() != "" && $('#sigla').val() != ""
    <?= $P ?>)
                $('#salvar').removeAttr('disabled');
            else
                $('#salvar').attr('disabled', 'disabled');
        }
    </script>
    <?php
    // LISTAGEM
    if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
        // consulta no banco
        $params = array('codigo' => dcrip($_GET["codigo"]));
        $res = $avaliacao->listRegistros($params);
        extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
        $data = dataPTBR($data);
    }
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
            <h2>Cadastro de Avalia&ccedil;&atilde;o</h2>
            <table>
                <tr>
                    <td align="right">Data: </td>
                    <td>
                        <input type="text" readonly size="10" id="data" name="data" value="<?= $data ?>" />
                        <a href='#' id="unlock" title='Perdeu o prazo? Clique aqui e solicite ao coordenador a libera&ccedil;&atilde;o do di&aacute;rio.'>
                            <img style="width: 20px;" src="<?= ICONS ?>/unlock.png"></a>
                    </td>
                </tr>
                <tr>
                    <td align="right">Nome: </td>
                    <td><input style="width: 350px" type="text" id="nome" maxlength="145" name="nome" value="<?= $nome ?>"/></td>
                </tr>
                <tr>
                    <td align="right">Sigla: </td>
                    <td><input type="text" id="sigla" size="2" maxlength="2" name="sigla" value="<?= $sigla ?>"/> <spam id="Siglas"></spam></td>
                </tr>
                <tr>
                    <td align="right">Tipo: </td>
                    <td>
                        <select name="tipo" id="tipo" value="<?= $tipo ?>">
                            <?php
                            require CONTROLLER . "/tipoAvaliacao.class.php";
                            $tipoAvaliacao = new TiposAvaliacoes();

                            if ($tipoAval == 'substitutiva') {
                                $res1 = $avaliacao->listAvaliacoes($atribuicao, 'substitutiva');

                                $tipo = $tipoAvaliacao->listTiposAvaliacoes($atribuicao, $calculo, $PONTO, $pontos, $tipoAval);
                            } else {
                                $res1 = $tipoAvaliacao->listTiposAvaliacoes($atribuicao, $calculo, $PONTO, $pontos, $tipoAval, dcrip($_GET['final']));
                            }
                            foreach ($res1 as $reg) {
                                $selected = "";
                                if ($reg['codigo'] == $tipo)
                                    $selected = "selected";
                                print "<option $selected value='" . $reg['codigo'] . "'>" . $reg['nome'] . "</option>";
                                if ($reg['tipo'] == 'recuperacao')
                                    $tipoAvalRec = $reg['tipo'];
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php
                if (($calculo == 'peso' || $calculo == 'soma' || $tipoAval == 'pontoextra') && ($tipoAval != 'substitutiva' && $tipoAvalRec != 'recuperacao')) {
                    if ($maxPontos <= 0)
                        $enabled = 'disabled';

                    $peso = number_format($res[0]['peso'], 2);
                    $peso = str_pad($peso, 5, "0", STR_PAD_LEFT);
                    ?>
                    <tr>
                        <td align="right">Valor</td>
                        <td><input type="text" id="valor" style="width: 50px" <?= $enabled ?> name="peso" value="<?= $peso ?>"/> (m&aacute;ximo <?= $maxPontos ?>)</td>
                    </tr>
                    <?php
                }
                if ($tipoAval == 'substitutiva') {
                    $codigo = key($tipo);
                    ?>
                    <input type="hidden" name="substitutiva" value="1" />
                    <?php
                }
                ?>
                <tr>
                    <td></td>
                    <td>
                        <input type="hidden" name="atribuicao" value="<?= crip($atribuicao) ?>" />
                        <input type="hidden" name="codigo" value="<?= crip($codigo) ?>" />
                        <input type="hidden" name="opcao" value="InsertOrUpdate" />
                        <input type="submit" disabled value="Salvar" id="salvar" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <br>
    <div style='margin: auto'>
        <a href="javascript:$('#professor').load('<?= $SITE ?>?atribuicao=<?= crip($atribuicao) ?>');void(0);" class='voltar' title='Voltar' >
            <img class='botao' src='<?= ICONS ?>/left.png'/>
        </a>
    </div>
    <?php
}

if ($_GET['opcao'] == '') {
    
    // INSERINDO O CALCULO PESO POR PADRAO, CASO ESTEJA VAZIO
    if (!$calculo && !$_POST) {
        $att->insertIfNotCalculo($atribuicao);
        $calculo = 'peso';
    }

    $res = $avaliacao->listAvaliacoes($atribuicao);
    if ($res[0]['nome'])
        $disabled = 'disabled';
    ?>
    <div id="etiqueta" align="center">
        <table width='900' border='0'>
            <tr>
                <td width="200">
                    <b>Turma: </b><?= $res[0]['numero'] ?><br />
                    <b>Disciplina: </b><?= $res[0]['disciplina'] ?><br />
                    <b>M&eacute;todo de C&aacute;lculo: </b>
                    <select name="campoCalculo" <?= $disabled ?> id="campoCalculo" value="<?= $calculo ?>" onChange="$('#professor').load('<?= $SITE ?>?opcao=calculo&atribuicao=<?= crip($atribuicao) ?>&calculo=' + this.value);">
                        <?php
                        $MC = array('soma', 'media', 'peso', 'formula');
                        foreach ($MC as $c) {
                            $selected = null;
                            if ($c == $calculo)
                                $selected = 'selected';
                            $n = strtoupper($c);
                            ?>
                            <option <?= $selected ?> value='<?= $c ?>'><?= $$n ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <?php if ($calculo == 'peso' || $calculo == 'soma') { ?>
                        <br><b>Pontos atribu&iacute;dos: </b><?= round($res[0]['totalPeso'], 2) ?>
                        <?php
                    }
                    if ($calculo == 'formula') {
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
                        <hr>
                        <div id="html5form" class="main">
                            <form id="form_padrao">
                                <table border="0">
                                    <tr>
                                        <td>
                                            <font size="2">M&eacute;dia: </font><input type="text" size="25" maxlength="100" name="formula" value="<?= $formula ?>" onchange="validaItem(this)" />
                                            <br>
                                            <input type="submit" value="Salvar f&oacute;rmula" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <font size="1">1. Exemplo: <b>($A1+$A2)/2</b>  --> onde A1 &eacute; a sigla da avalia&ccedil;&atilde;o precedida de $</font>
                                            <br />
                                            <font size="1">2. Nas multiplica&ccedil;&otilde;es, utilizar somente 1 fra&ccedil;&atilde;o decimal. Exemplo: <b>($A1+$A2)*0.2</b></font>
                                            <br />
                                            <font size="1">3. Para maior precis&atilde;o utilizar conforme exemplo: <b>($A1+$A2)*(25/100)</b></font>
                                            <br />
                                            <font size="1">4. Utilizar somente Avalia&ccedil;&otilde;es e Pontos Extras na f&oacute;rmula.</font>
                                        </td>
                                    </tr>
                                </table>
                                <input type="hidden" name="opcao" value="InsertFormula" />
                                <input type="hidden" name="codigo" value=<?= crip($atribuicao) ?> />
                            </form>
                        </div>
                        <?php
                    }
                    ?>
                </td>
                <td width = '400' valign = 'top' align='center'>
                    <table id="listagem" style="margin-top: 1px; border: 2px solid green; max-height: 100px;">
                        <?php
                        if ($res[0]['modalidade'] != 1006 && $res[0]['modalidade'] != 1007 && ($res[0]['bimestre'] == 4 || $res[0]['bimestre'] == 0)) {
                            $instrumento = ($res[0]['modalidade'] == 1004) ? 'do Instrumento Final de Avalia&ccedil;&atilde;o' : 'da Recupera&ccedil;&atilde;o Final / Reavalia&ccedil;&atilde;o';
                            ?>
                            <tr  id="obs">
                                <td width = '80' style="text-align: center"><b>Observação:</b></td>
                                <td colspan="2" style="; padding: 5px">Professor, os alunos <?= $instrumento ?> estar&atilde;o dispon&iacute;veis ap&oacute;s a exporta&ccedil;&atilde;o das notas e faltas para o DigitaNotas e o Roda for executado.</td>
                                <td>&nbsp;</td>
                            </tr>
                            <?php 
                        }
                        $fecharDiario = "<br />Pode finalizar o diário.";
                        $paramsQde = array('atribuicao' => $atribuicao);
                        $qdeAvaliacoes = $avaliacao->getQdeAvaliacoes($paramsQde, " AND t.tipo = 'avaliacao' ");

                        if ($DEBUG)
                            echo "<br>Atr: ".$atribuicao;

                        $libera_nota = 0;
                        $trava_nota = 0;
                        $trava_rec = 0;
//                        $final=1;

                        $bimNF = $res[0]['bimestre'];
                        if ($res[0]['bimestre'] == 0)
                            $bimNF = 1;

                        $nf = $notaFinal->checkIfExportDN($atribuicao, null, $bimNF);
                        if (!empty($nf[0]['retorno']) && $nf[0]['flag']==0){
                            echo "<script>$('#obs, #recuperacao').hide();</script>";
                            $nota_text = "<b>ERRO AO EXPORTAR NOTAS, AVISE O ADMINISTRADOR:</b> ".$nf[0]['retorno'];
                            $nota_text .= "<br><b>Em caso de problema de alteração de notas, solicite liberação na secretaria.</b>";
                            $nota_text .= "<br><b>Clique no botão ao lado para tentar novamente.</b>";
//                            $trava_nota=1;
//                            $trava_rec=1;
                            $libera_nota=1;
                            
                        }
                        else{
//                             echo "<br>nf:".$nf;
//                            echo "<br>nfd:".$nfd;
//                            echo "<br>==>\$nf[0]['total'] - count(\$nf)?".($nf[0]['total'] - count($nf));
//                            echo "<br>count(\$nf)?".count($nf);
//                            echo "<br>(round(\$res[0]['totalPeso'],2) >= \$PONTO)?".(round($res[0]['totalPeso'],2) >= $PONTO);
//                            echo "<br>(round(\$res[0]['totalPeso'],2))?".(round($res[0]['totalPeso'],2));
//                            echo "<br>(\$PONTO)?".($PONTO);
//                            
//                            echo "<hr>";
//                            echo "<br>\$rec['notasRec']>0:".($rec['notasRec']>0);
//                            echo "<br>res[0]['bimestrjjje']==0?".($res[0]['bimestre']==0);
//                            echo "<br>(\$rec['totalRec']==\$rec['total'])?".($rec['totalRec']==$rec['total']);
//                            echo "<br>(\$rec['situacoes']==\$rec['reg'] ):".($rec['situacoes']==$rec['reg'] );
                            // ALTERADA
                            if ((!$nf || ($nfd = $nf[0]['total'] - count($nf) && $nf[0]['total']==$situacoes)) && (round($res[0]['totalPeso'],2) >= $PONTO) && $_SESSION['dataExpirou']==0) {
//                                echo "<br>nftotal:".($nf[0]['total']);
//                                echo "<br>sit:".$situacoes;
//                                echo "<br>nfd:".$nfd;
//                                echo "<br>flag5:".($nf[0]['flag5']);
                                $nota_text = 'Professor, ao finalizar suas notas e faltas, clique no bot&atilde;o para exportar para o DigitaNotas';
                                $libera_nota = 1;
                                $trava_nota = 0;
//                                $liberaDN=1;

                            } else {
//                                $nota_text = $rec_text = 'Notas j&aacute; finalizadas. '.$fecharDiario;
//                                echo "<br>".$res[0]['bimestre'];
                                $nota_text = "Novas enviadas!";
                                if ($res[0]['bimestre']>0 && $res[0]['bimestre']<4 && $_SESSION['dataExpirou']==0)
                                    $nota_text = "Novas enviadas! <br>Pode finalizar o diário!";
                                $rec_text = 'Professor, suas notas foram finalizadas, mas o Roda ainda n&atilde;o foi executado para listar os alunos de recupera&ccedil;&atilde;o. Aguarde!';

                                $trava_nota=1;
                                $trava_rec=1;
                            }
//                                echo "<br>totalpeso: ".round($res[0]['totalPeso'],2);
                            if (round($res[0]['totalPeso'],2) < $PONTO || $qdeAvaliacoes['avalCadastradas'] < $qdeAvaliacoes['qdeMinima']) {
                                $nota_text = 'Professor, voc&ecirc; ainda n&atilde;o concluiu suas notas, seus pesos est&atilde;o incompletos ou o n&uacute;mero m&iacute;nimo de avalia&ccedil;&otilde;es n&atilde;o foi aplicado.';
                                $trava_nota = 0;
                                $libera_nota = 0;
                                $trava_rec=0;
                            }

                            $libera_rec = 0;
                            $rec = $notaFinal->checkIfRoda($atribuicao);
                            if ($DEBUG){
                                echo "<br>reg: ".$rec['reg'];
                                echo "<br>total: ".$rec['total'];
                                echo "<br>situacoes: ".$rec['situacoes'];
                                echo "<br>notasRec: ".$rec['notasRec'];
                            }
                            if (!$rec['reg'] || $libera_nota) {
                                $rec_text = 'Professor, as notas ainda n&atilde;o foram finalizadas.';
                            } 
                            else if ($_SESSION['dataExpirou']){
                                echo "<script>$('#obs, #notas, #rec_label').hide();</script>";
                                $rec_text = "Diário Fechado.";
                            }
                            else if ($rec['reavaliados']>0 || ($rec['notasRec']>0 && $res[0]['bimestre']==0) || ($rec['totalRec']==$rec['total'] && $rec['situacoes']==$rec['reg'] )) {
//                                echo "<br>".$res[0]['bimestre'];
                                echo "<script>$('#obs, #notas, #rec_label').hide();</script>";
//                                $rec_label = 'Notas j&aacute; finalizadas. '.$fecharDiario;
                                $rec_text = 'Notas j&aacute; finalizadas. '.$fecharDiario;
                                $trava_rec = 1;                        
                            } 
                            else if ($rec['reg'] && !$rec['total'] && $rec['situacoes']==0 && ($res[0]['bimestre']==1)) {
                                $libera_nota = 0;
                                $rec_text = 'Professor, suas notas foram finalizadas, mas o Roda ainda n&atilde;o foi executado para listar os alunos de recupera&ccedil;&atilde;o. Aguarde!';
//                                $nota_text = 'Professor, suas notas foram finalizadas!'.$fecharDiario;
                                $trava_nota = 0;
                                $liberaDN=0;
//                                $_SESSION['dataExpirou']=1;
                            } else if ($rec['notasRec']==0 && ($rec['reg'] && $rec['total'] && !$rec['totalRec'] || ($rec['situacoes']!=$rec['reg'] && $rec['notasRec']!=$rec['totalRec']))) {
                                $libera_nota = 0;
                                $libera_rec = 1;
                                echo "<script>$('#obs, #notas').hide();</script>";
                                $trava_nota=0;
                                $ifa=1; // HABILITA SOMENTE CRIACAO DE AVALIACAO DO TIPO IFA
                                $liberaDN=1;
                                $rec_text = 'ATENÇÃO: Há alunos aptos à aplicação de '.$instrumento.'!<BR>Professor, após digitar suas notas de '.$instrumento.', clique no bot&atilde;o ao lado para exportar para o DigitaNotas.';
                            } else if ($rec['totalRec']) {
//                                echo "<br>".$rec['totalRec'];
                                $rec_text = 'Notas j&aacute; finalizadas. Aguarde a execu&ccedil;&atilde;o do Roda para finalizar seu di&aacute;rio.';
                                $trava_rec = 1;
                                $final=0;
                                $trava_nota=0;
                            }
                            else if (!$trava_nota && !$$trava_rec){
                                echo "<script>$('#obs, #notas').hide();</script>";
                                $rec_text = "Aguardando execução do Roda.";
                                $trava_rec = 1;          
                                $trava_nota = 1;
                            }
                        }
                        
                        ?>
                            <tr id="notas">
                            <td style="text-align: center; padding-right: 20px;"><b>Notas:</b></td>
                            <td colspan="2" id="nota_text"><?= $nota_text ?></td>
                            <td>
                                <div id="nota_retorno">
                                    <?php if ($libera_nota) {
                                        ?>
                                        <a id="digita-nota" title='Exportar notas para o DigitaNotas' data-content='Aten&ccedil;&atilde;o professor, as notas ser&atilde;o exportadas para o DigitaNotas, altera&ccedil;&otilde;es posteriores somente pela secretaria.' class = 'nav questionario_item' href = "#">
                                            <img style="padding-right: 10px; padding-top: 0px; width: 30px" class='' src = "<?= ICONS . '/sync.png' ?>" />
                                        </a>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <?php if ($instrumento) { ?>
                            <tr id="recuperacao">
                                <td width='100' align='center' id="rec_label"><b>Recuperação:</b></td>
                                <td colspan="2" style="padding: 5px" id="rec_text"><?= $rec_text ?></td>
                                <td style="padding: 5px">
                                    <?php if ($libera_rec) {
                                        ?>
                                        <div id="rec_retorno">
                                            <a id="digita-rec" title='Exportar notas para o DigitaNotas' data-content='Aten&ccedil;&atilde;o professor, as notas ser&atilde;o exportadas para o DigitaNotas, altera&ccedil;&otilde;es posteriores somente pela secretaria.' class = 'nav questionario_item' href = "#">
                                                <img class='botao' src = "<?= ICONS . '/sync.png' ?>" />
                                            </a>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <hr><br>
    <?php if ($res[0]['nome']) { ?>
        <table id="listagem" border="0" align="center">
            <tr>
                <th width="40">#</th>
                <th width="100">Data</th>
                <th>Avalia&ccedil;&atilde;o</th>
                <th>Sigla</th>
                <th>Tipo</th>
                <th width="150">Valor</th>
                <th align="center" width="50">&nbsp;&nbsp;
                <?php
                if (!$_SESSION['dataExpirou'] && !$trava_nota && !$final && $libera_nota){
                ?>
                    <input type="checkbox" id="select-all" value="">
                    <a href="#" class='item-excluir'>
                        <img class='botao' src='<?= ICONS ?>/delete.png' />
                    </a>
                <?php
                }
                ?>
                </th>
            </tr>
            <?php
            $i = count($res);
            foreach ($res as $reg) {
                $bimestre = $reg['bimestre'];
                $recFinal .= $reg['final'];
                $recuperacao .= $reg['recuperacao'];
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";

                $totalPesoOrPonto = $reg['totalPeso'];
                if (($calculo == 'media' || $calculo == 'formula') && $reg['tipo'] != 'pontoExtra')
                    $reg['peso'] = '';

                if ($reg['recuperacao'])
                    $reg['peso'] = $reg['recuperacao'];

                $sub = null;
                if ($reg['tipo'] == 'substitutiva')
                    $sub = ' de ' . $reg['substitutiva'];

                if ($reg['tipo'] == 'pontoExtra')
                    $totalPesoOrPonto = $reg['totalPonto'];

                if ($reg['tipo'] == 'recuperacao'){
                    $final = 1;
                    if (!$trava_nota)
                        echo "<script>$('#nota_text').append('<br><b style=\'color: red\'>Caso deseje alterar as notas das avaliações regulares, exclua a reavaliação/IFA.</b>');</script>";
                }

                if ($reg['tipo'] == 'avaliacao')
                    $tipoIns = 'avaliacao';

                $reg['tipo'] = strtoupper($reg['tipo']);
                $titleAval = strtoupper($reg['recuperacao']);
                ?>
                <tr <?= $cdif ?>><td><?= $i-- ?></td>
                    <td>
                        <?php if (!$_SESSION['dataExpirou'] && $final && $reg['tipo'] == 'AVALIACAO'){ ?>
                        <p style='color: darkgray;' title='Não permitido. Exclua a reavaliação/IFA para alterar notas'>
                            <img class='botao' src='<?= ICONS ?>/warning.png' />
                            <?= $reg['dataFormatada'] ?>
                        </p>
                        <?php }else{ ?>
                        <a class='nav' title='Clique aqui para lan&ccedil;ar as notas.' href="javascript:$('#professor').load('<?= VIEW ?>/professor/nota.php?atribuicao=<?= crip($atribuicao) ?>&avaliacao=<?= crip($reg['codigo']) ?>');void(0);"><?= $reg['dataFormatada'] ?></a>
                        <?php } ?>
                    </td>
                    <td><?= $reg['nome'] ?></td>
                    <td><?= $reg['sigla'] ?></td>
                    <td><?= $$reg['tipo'] . $sub ?> </td>
                    <td><a title='<?= $$titleAval ?>' href='#'><?= $reg['peso'] ?></a></td>
                    <?php
                    if ($_SESSION['dataExpirou']) {
                        $libera_nota=0;
                        ?>
                        <td align='center'><a href='#' title='Di&aacute;rio Fechado'>Fechado</a></td>
                        <?php
                    } else {
                        ?>
                        <td align='center' width="20">
                            <?php
                            if ((!$trava_nota && !$final) || ($final && !$trava_rec) || $libera_nota) {
                                ?>
                                <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>'>

                                <a href="javascript:$('#professor').load('<?= $SITE ?>?opcao=insert&codigo=<?= crip($reg['codigo']) ?>&pontos=<?= crip(round($totalPesoOrPonto - $reg['peso'], 2)) ?>&atribuicao=<?= crip($atribuicao) ?>&tipo=<?= crip($reg['tipo']) ?>&final=<?= crip($reg['final']) ?>');void(0);" class='nav' title='Alterar'>
                                    <img class='botao' src='<?= ICONS ?>/config.png' /></a>
                                <?php
                            }
                            ?>
                        </td>
                        <?php
                    }
                }
                ?>
        </table>
    <?php } ?>
    <center>
        <br />
        <?php
        if ($DEBUG){
            echo "dataExpirou?=".$_SESSION['dataExpirou'];
            echo "<br>trava=".$trava_nota;
            echo "<br>final=".$final; 
            echo "<br>travarec=".$trava_rec;
            echo "<br>libera_nota=".$libera_nota;
            echo "<br>travanota".$trava_nota;
            echo "<br>ifa:".$ifa;
            echo "<br>status:".$status;
        }
        if ((!$trava_nota && !$final) || ($final && !$trava_rec) || $_SESSION['dataExpirou'] || $libera_nota) {
            if ($libera_nota || (($_SESSION['dataExpirou'] || ($calculo == 'media' || $calculo == 'formula') && ($res[0]['totalPeso'] < $PONTO) || !$recuperacao || (!$recFinal && $bimestre == 4)))) {
                ?>
                <?php if ($_SESSION['dataExpirou'] == 0) {
                    if (!$trava_nota){
                    ?>
                    <a class="nav" href="javascript:$('#professor').load('<?= $SITE ?>?opcao=insert&atribuicao=<?= crip($atribuicao) ?>&pontos=<?= crip(round($reg['totalPeso'], 2)) ?>&final=<?= crip($final) ?>&tipo=<?= crip($tipoIns) ?>');void(0);" title="Cadastrar Nova Avalia&ccedil;&atilde;o"><img class='botao' src='<?= ICONS ?>/av.png' /></a>
                    <?php 
                    }
                    if (!$trava_nota && !$ifa){ ?>
                        &nbsp;&nbsp;<a class="nav" href="javascript:$('#professor').load('<?= $SITE ?>?opcao=insert&atribuicao=<?= crip($atribuicao) ?>&tipo=<?= crip('pontoExtra') ?>&pontos=<?= crip(round($reg['totalPonto'], 2)) ?>');void(0);" title="Cadastrar Ponto Extra (adicionado na m&eacute;dia)"><img class='botao' src='<?= ICONS ?>/pExtra.png' /></a>
                        &nbsp;&nbsp;<a class="nav" href="javascript:$('#professor').load('<?= $SITE ?>?opcao=insert&atribuicao=<?= crip($atribuicao) ?>&tipo=<?= crip('substitutiva') ?>');void(0);" title="Cadastrar Prova Substitutiva"><img class='botao' src='<?= ICONS ?>/change.png' /></a>
                    
                    <?php } ?>
                    <?php
                } else {
                    ?>
                    <p style='text-align: center; font-weight: bold; color: red'>Di&aacute;rio Fechado.</p>
                    <a href='#' id="unlock" title='Clique aqui para solicitar a liberação do diário.'><img src="<?= ICONS ?>/unlock.png"></a>
                    <?php
                }
            } else if ($status == 0) {
                ?>
                <p style='text-align: center; font-weight: bold; color: red'>Não é possível cadastrar mais avaliações, pois a soma dos pontos distribuídos é igual a <?= $PONTO ?> ou há recuperação cadastrada.<br />Exclua a recuperação ou alguma avaliação ou altere o peso de alguma avaliação para adicionar uma nova.</p>
                    <?php
                }
            }
            ?>
    </center>
    <?php
}

if ($LIMITE_DIARIO_PROF != 0) {
    // DATA DE INICIO E FIM DA ATRIBUICAO PARA RESTRINGIR O CALENDARIO
    $res = $att->getAtribuicao($atribuicao, $LIMITE_DIARIO_PROF);
}

require CONTROLLER . "/calendario.class.php";
$cal = new Calendarios();
print "<script>\n";
print "var disabledDates = []; \n";
foreach ($cal->getFeriados() as $f) {
    print "disabledDates.push( \"$f\" ); \n";
}
print "</script>\n";
?>
<script>
    $('#rec_text', '#notas').css('height', '50');

    function editDays(date) {
        for (var i = 0; i < disabledDates.length; i++) {
            if (new Date(disabledDates[i]).toString() == date.toString()) {
                return [false];
            }
        }
        return [true];
    }

    function validaItem(item) {
        item.value = item.value.replace(",", ".");
    }

    $(document).ready(function () {
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
                        $('#professor').load('<?= $SITE ?>?opcao=delete&codigo=' + selected + '&atribuicao=<?= crip($atribuicao) ?>');
                    }
                }
            });
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
            maxDate: '<?= $res['fimCalendar'] ?>',
            beforeShowDay: editDays
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
    });

    $("#digita-nota").click(function () {
        $.Zebra_Dialog('<strong>Professor, as notas ser&atilde;o finalizadas e posteriormente exportadas para o DigitaNotas, ap&oacute;s essa opera&ccedil;&atilde;o as notas n&atilde;o poder&atilde;o ser alteradas. \n\
                                <br><br>Somente a secretaria poder&aacute; alterar a nota pelo Nambei.\n\
                                <br><br>Deseja continuar com a exporta&ccedil;&atilde;o?</strong>', {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption) {
                if (caption == 'Sim') {
                    $('#nota_retorno').load('<?= $SITE ?>?opcao=controleDiario&liberaDN=<?=crip($liberaDN)?>&ifa=<?=crip($ifa)?>&atribuicao=<?= crip($atribuicao) ?>');
                }
            }
                });
            });

                        $("#digita-rec").click(function () {
                            $.Zebra_Dialog('<strong>Professor, as notas de recupera&ccedil;&atilde;o ser&atilde;o finalizadas e posteriormente exportadas para o DigitaNotas, ap&oacute;s essa opera&ccedil;&atilde;o as notas n&atilde;o poder&atilde;o ser alteradas. \n\
                                <br><br>Somente a secretaria poder&aacute; alterar a nota pelo Nambei.\n\
                                <br><br>Deseja continuar com a exporta&ccedil;&atilde;o?</strong>', {
                                'type': 'question',
                                'title': '<?= $TITLE ?>',
                                'buttons': ['Sim', 'Não'],
                                'onClose': function (caption) {
                                    if (caption == 'Sim') {
                                        $('#obs, #notas').show();
                                        $('#rec_retorno').load('<?= $SITE ?>?opcao=controleDiario&liberaDN=<?=crip($liberaDN)?>&ifa=<?=crip($ifa)?>&atribuicao=<?= crip($atribuicao) ?>');
                                                            //$('#notas').load('db2/db2DigitaNotas.php?atribuicao=<?= $atribuicao ?>');
                                                        }
                                                    }
                                                });
                                            });

                                            $("#unlock").click(function () {
                                                $.Zebra_Dialog('<strong>Professor, informe o motivo da solicitação:</strong>', {
                                                    'type': 'prompt',
                                                    'promptInput': '<textarea rows="2" cols="30" name="Zebra_valor" maxlength="200" id="Zebra_valor"></textarea>',
                                                    'title': '<?= $TITLE ?>',
                                                    'buttons': ['Sim', 'Não'],
                                                    'onClose': function (caption, valor) {
                                                        if (caption == 'Sim') {
                                                            $('#professor').load('<?= $SITE ?>?motivo=' + encodeURIComponent(valor) + '&atribuicao=' + '<?= crip($atribuicao) ?>');
                                                        }
                                                    }
                                                });
                                            });
</script>