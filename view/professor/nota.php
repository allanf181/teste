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
require SESSAO;

require CONTROLLER . "/nota.class.php";
$nota = new Notas();

require CONTROLLER . "/notaFinal.class.php";
$notaFinal = new NotasFinais();

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
<script src="<?= VIEW ?>/js/jquery.numeric.min.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<?php
$avaliacao = dcrip($_GET["avaliacao"]);
$atribuicao = dcrip($_GET["atribuicao"]);

$nf = $notaFinal->checkIfRoda($atribuicao);
if ($DEBUG)
    var_dump($nf);


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
                <?php if ($resAval['calculo'] == 'peso' && $resAval['tipo'] != 'pontoExtra') { ?>
                    (peso: <?= $resAval['peso'] ?>)
                    <?php
                    $resAval['notaMaxima'] = '10';
                }
                ?>
                <br> Nota m&aacute;xima permitida: <?= $resAval['notaMaxima'] ?>
            </td>
        </tr>
    </table>
</div>
<br><hr>

<?php

if ($_SESSION['dataExpirou'] || ($nf['flag5'] && $resAval['tipo']!='recuperacao') || ($nf['reg'] < $nf['flag5'])|| ($nf['flag5'] && $nf['totalRec']==$nf['total']))
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
<?php
        if ($DEBUG){
//            echo "reav:".$resAval['tipo'];
            var_dump($resAval);
        }
        
        if($nf['flag5'] && !$_SESSION['dataExpirou'] && ($nf['flag5'] && $nf['totalRec']==$nf['total'])){
            echo "<p style='text-align: center; font-weight: bold; color: green; border: 3px solid green; width: 300px; margin: 10px auto 30px auto'>Notas e faltas exportadas.</p>";
        }
        else if($nf['flag5'] && $nf['total']!=$nf['reavaliados'] && $resAval['tipo']!='recuperacao' && ($nf['flag5'] && $nf['totalRec']!=$nf['total'])){
            echo "<p style='text-align: center; font-weight: bold; color: red; border: 3px solid red; width: 300px; margin: 10px auto 30px auto; padding: 5px'>Permitido lançamento de Reavaliação/IFA somente.</p>";
        }
        else if($nf['reg'] < $nf['flag5'] && !$_SESSION['dataExpirou']){
            echo "<p style='text-align: center; font-weight: bold; color: red; border: 3px solid red; width: 300px; margin: 10px auto 30px auto'>Notas e faltas enviadas.<br>Aguardando o Roda.</p>";
        }
        else if ($_SESSION['dataExpirou']){
            ?>
            <p style='text-align: center; font-weight: bold; color: red;margin-bottom: 20px;'>Di&aacute;rio Fechado.<br>
            <a href='#' id="unlock" title='Clique aqui para solicitar a liberação do diário.'><img src="<?= ICONS ?>/unlock.png"></a></p>
            <?php
        }         
?>
<div id="html5form" class="main">
    <form id="form_padrao">
        <table id="listagem" border="0" align="center">
            <tr>
                <th align="center" width="80">Prontuário</th>
                <th align="center">Aluno</th>
                <th width="60" align='center'>Nota</th>
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
                    $bimNF = $resAval['bimestre'];
                    if ( ($resAval['sigla'] == 'IFA' || $resAval['sigla'] == 'REF') && !$notaFinal->checkIfRecuperacao($atribuicao, $reg['matricula']))
                        continue;
                    if ($resAval['sigla'] == 'IFA' || $resAval['sigla'] == 'REF') {
                        if ($bimNF == "0")
                            $bimNF = "1";
                        $tipoDN = 2;
                    }

                    $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                    ?>
                    <tr <?= $cdif ?>>
                        <td align='center'><?= $reg['prontuario'] ?></td>
                        <td><a href='#' rel='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codAluno']) ?>' class='screenshot' title='<?= mostraTexto($reg['aluno']) ?>'>
                                <img style='width: 20px; height: 20px' src='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codAluno']) ?>' />
                            </a>
                            <a class='nav' title='Clique aqui para ver o boletim do aluno.' href="javascript:$('#professor').load('<?= VIEW ?>/professor/boletim.php?aluno=<?= crip($reg['codAluno']) ?>&turma=<?= crip($reg['turma']) ?>&bimestre=<?= crip($reg['bimestre']) ?>');void(0);"><?= mostraTexto($reg['aluno']) ?></a>
                        </td>
                        <td align='center'>
                            <?php
                            $matSituacao = $ma->getAlteracaoMatricula($reg['codAluno'], $atribuicao, $resAval['data']);
                            // IGNORANDO CASO A DATA DE MATRÍCULA SEJA POSTERIOR A DATA DA AVALIACAO
                            if (!$matSituacao || ($matSituacao['listar'] && $matSituacao['habilitar'])) {
                                ?>
                                <input type='hidden' name='codigo[<?= $reg['matricula'] ?>]' value='<?= $reg['codNota'] ?>'>
                                <input <?= $disabled ?> id='A<?= $reg['codAluno'] ?>' tabindex='<?= $i ?>' style='width: 30px' type='text' value='<?= $reg['nota'] ?>' size='4' maxlength='4' name='matricula[<?= $reg['matricula'] ?>]' class="campoNota" />
                                <?php
                                
                                // BIMESTRAL
                                if ($reg['bimestre'] > 0) { 
                                    foreach ($AT_BIM as $nBim => $at) {
                                        $dados = $nota->resultado($matricula->getMatricula($reg['codAluno'], $at, $nBim), $at, 0, $_SESSION['dataExpirou']);
                                        $media = $dados['media'];
                                        ?>
                                        <td align='center'><?= $media ?></td>
                                    <?php
                                    }

                                    if ($reg['bimestre'] == 4 && $nBim == 4 && $resAval['tipo'] == 'recuperacao' && !$situacao[$reg['codAluno']])
                                        $resAval['final'] = 1;
                                    $dados1 = $nota->resultadoBimestral($reg['codAluno'], $resAval['turmaCodigo'], $resAval['discNumero']);
                                    $media = $dados1['media'];
                                    $situacao = $notaFinal->getSituacaoMatricula($reg['matricula']);
                                    ?>
                                    <td align='center'><?= $media ?></td>
                                    <td align='center'><?= $situacao ?></td>
                                    <?php
                                    
                                } else {
                                    $dados = $nota->resultado($reg['matricula'], $atribuicao, $resAval['final']);
//                                    $mediaArredondada = $notaFinal->getMediaArredondada($atribuicao, $reg['matricula'],$dados['media']);
//debug($dados);
//echo "<br>Ma: ".$mediaArredondada;
//                                    $iconeMediaArredondada=null;
//                                    if ($mediaArredondada>=0 && ($dados['notaRecuperacao'] != $dados['media'])){
//                                        if ($dados['notaRecuperacao']>$mediaArredondada)
//                                            $dados['media'] = $dados['notaRecuperacao'];
//                                        else{
//                                            $dados['media'] = $mediaArredondada;
                                            $iconeMediaArredondada = iconeMediaArredondada($dados['origemMedia']);
//                                        }
//                                    }

                                    ?>
                                    <td align='center'><?= $dados['media'].$iconeMediaArredondada ?></td>
                                    <td align='center'><?= $notaFinal->getSituacaoMatricula($reg['matricula']); ?></td>
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
        <table align="center" width="100%" style="margin-top: 10px">
            <tr><td></td><td>
                    <input type="hidden" value="<?= crip($avaliacao) ?>" name="avaliacao" />
                    <input type="hidden" value="<?= crip($atribuicao) ?>" name="atribuicao" />
                    <input type="hidden" name="opcao" value="InsertOrUpdate" />
                    <?php if (!$disabled) { ?>
                        <center><input type="submit" id="btnEnviar" value="Salvar" name="salvar" /></center>
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
//debug($resAval);
?>
<script>
    
//    
//    $('.campoNota').change(function(){
//        $(this).css('background','');
//        if ($(this).val() < 0 || $(this).val() > <?= $resAval['notaMaxima'] ?>) {
//            $(this).css('background','red');
//        }
//        
//    });
    
    $('.campoNota').focus(function(){
         $(this).select();
    });
    
    $('.campoNota').numeric();// PLUGIN jquery.numeric.min.js
    
    $('#btnEnviar').prop('disabled', true);
    $('.campoNota').keyup(function(){ 
        $(this).val($(this).val().replace(",", ".")) ;
        $('#btnEnviar').prop('disabled', false);
        $(this).css('background','');
        $('.campoNota').each(function(){
            if ($(this).val()!=""){
                if ($(this).val() < 0 || $(this).val() > <?= $resAval['notaMaxima'] ?>) {
                    $('#btnEnviar').prop('disabled', true);
                    $(this).css('background','red');
                }
                <?php if ($resAval['sigla']=="REF" || $resAval['sigla']=="IFA"){ // VALIDANDO FORM EM CASO DE RECUPERACAO ?>
                decimal = $(this).val() - parseInt($(this).val());
                jaArredondado = (decimal < 0.01) || ((decimal > 0.49) && (decimal < 0.51));
                if (!jaArredondado){
                    $('#btnEnviar').prop('disabled', true);
                    $(this).css('background','red');
                }
                <?php }?>                
            }
        });        
    });
<?php
//}
?>
</script>