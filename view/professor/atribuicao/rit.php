<?php
//Esse arquivo é fixo para o professor.
//Permite o registro do RIT do Docente.
//Link visível no menu: PADRÃO SIM.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/tdDado.class.php";
$dados = new TDDados();

require CONTROLLER . "/tdAtvECmt.class.php";
$atvECmt = new TDAtvECmt();

require CONTROLLER . "/tdComponente.class.php";
$componente = new TDComponente();

require CONTROLLER . "/logSolicitacao.class.php";
$log = new LogSolicitacoes();

require CONTROLLER . "/coordenador.class.php";
$coordenador = new Coordenadores();

require CONTROLLER . "/logEmail.class.php";
$logEmail = new LogEmails();

if ($_POST) {
    if (!dcrip($_POST['area'])) {
        print "<span style='font-weight: bold; color: red'>Atenção, é necessário cadastrar um PIT primeiro.</span>";
        $_GET['pano'] = $_POST['pano'];
        $_GET['psemestre'] = $_POST['psemestre'];         
    } else {    
        $_POST['modelo'] = 'RIT';
        $_POST['pessoa'] = $_SESSION['loginCodigo'];
        $_POST['codigo'] = dcrip($_POST['codigo']);
        $_POST['semestre'] = dcrip($_POST['psemestre']);
        $_POST['ano'] = dcrip($_POST['pano']);

        $_GET['pano'] = $_POST['pano'];
        $_GET['psemestre'] = $_POST['psemestre'];    
        unset($_POST['pano']);
        unset($_POST['psemestre']);    

        $ret = $dados->insertOrUpdateFPA($_POST);
        mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

       //ENVIANDO EMAIL
        if ($ret['STATUS'] == 'OK' && $_POST['enviar']) {
            if ($coodEmail = $coordenador->getEmailCoordFromArea(dcrip($_POST['area'])))
                $logEmail->sendEmailLogger($_SESSION['loginNome'], 'Docente enviou RIT para valida&ccedil;&atilde;o.', $coodEmail);
        }    
    }
}

if ($_GET['pano'] && $_GET['psemestre']) {
    $pano = dcrip($_GET['pano']);
    $psemestre = dcrip($_GET['psemestre']);
} else {
    // TENTA BUSCAR O ULTIMO REGISTRO GRAVADO PELO USUARIO
    $sqlAdicional = ' AND p.codigo = :pessoa AND f.modelo = :modelo ORDER BY f.codigo DESC LIMIT 1 ';
    $params = array('pessoa' => $_SESSION['loginCodigo'], 'modelo' => 'RIT');
    $res = $dados->listModelo($params, $sqlAdicional, null, null);
    
    //SE NAO ENCONTROU PIT, IMPORTA DA FPA
    if (!$res) {
        $params['modelo'] = 'PIT';
        $res = $dados->listModelo($params, $sqlAdicional, null, null);
    }
        
    if ($res && ( ($res[0]['ano'] > $ANO) || ($res[0]['ano'] == $ANO && $res[0]['semestre'] > $SEMESTRE))) {
        $pano = $res[0]['ano'];
        $psemestre = $res[0]['semestre'];
    } else {
        $pano = $ANO;
        $psemestre = $SEMESTRE;
    }
}

//LISTA OS REGISTROS DA FPA
$sqlAdicional = ' AND p.codigo = :pessoa AND f.modelo = :modelo ';
$params = array('pessoa' => $_SESSION['loginCodigo'], 'ano' => $pano, 'semestre' => $psemestre, 'modelo' => 'RIT');
$res = $dados->listModelo($params, $sqlAdicional, null, null);
extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);

//SE NAO ENCONTROU PIT, IMPORTA DA FPA
if (!$res) {
    $params['modelo'] = 'PIT';
    $resFPA = $dados->listModelo($params, $sqlAdicional, null, null);
    extract(array_map("htmlspecialchars", $resFPA[0]), EXTR_OVERWRITE);
    $horario = "";
}

//LISTA COMPONENTES
$resC = $componente->listComponentes($codigo);
//LISTA ATIVIDADES
$resAtv = $atvECmt->listAtvECmt($codigo, 'atv');
//LISTA COMPLEMENTACAO
$resComp = $atvECmt->listAtvECmt($codigo, 'cmp');

//PARA CRIAR UM NOVO REGISTRO
if (!$res) {
    $codigo = "";
}

//VERIFICA SE ESTA FINALIZADO OU VALIDADO
if ($res[0]['finalizado'] && $res[0]['finalizado'] != '0000-00-00 00:00:00')
    $disabled = 'disabled';

if ($res[0]['valido'] && $res[0]['valido'] != '0000-00-00 00:00:00')
    $VALIDO = 1;

$paramsLog['codigoTabela'] = $codigo;
$paramsLog['nomeTabela'] = 'RIT';
$l = $log->listSolicitacoes($paramsLog, " AND ( l.dataConcessao = '0000-00-00 00:00:00' OR l.dataConcessao IS NULL) ");
if ($l[0]['solicitacao']) {
    $OPT[0] = $l[0]['solicitante'];
    $OPT[1] = $l[0]['solicitacao'];
    mensagem('INFO', 'INVALID_FORM', $OPT);
    $disabled = '';
}

if (!$VALIDO && $disabled)
    mensagem('OK', 'FINISH_FORM');

if ($VALIDO)
    mensagem('OK', 'VALID_FORM', $solicitante);
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<br />
<center>
    <div id="html5form" class="main">
        <form id="form_padrao">
            <input type="hidden" value="<?= crip($codigo) ?>" name="codigo" id="codigo" />
            <input type="hidden" value="<?= crip($apelido) ?>" name="apelido" id="apelido" />
            <input type="hidden" value="<?= crip($codArea) ?>" name="area" id="area" />
            <input type="hidden" value="<?= crip($regime) ?>" name="regime" id="regime" />
            <input type="hidden" value="<?= crip($dedicarEnsino) ?>" name="dedicarEnsino" id="dedicarEnsino" />
            <input type="hidden" value="<?= crip($duracaoAula) ?>" name="duracaoAula" id="duracaoAula" />
            <input type="hidden" value="<?= crip($subHorario) ?>" name="subHorario" id="subHorario" />
            <input type="hidden" value="<?= crip($horario1) ?>" name="horario1" id="horario1" />
            <input type="hidden" value="<?= crip($horario2) ?>" name="horario2" id="horario2" />
            <input type="hidden" value="<?= crip($horario3) ?>" name="horario3" id="horario3" />

            <font size="3"><b>RIT - RELAT&Oacute;RIO INDIVIDUAL DE TRABALHO DOCENTE <br> <?= $psemestre ?>&ordm; semestre <?= $pano ?> </b></font>
            <table style="width: 865px" border="0" summary="FTD" id="tabela_boletim">
                <tr>
                    <th>
                        <span style='font-weight: bold; color: white'>
                            Escolha o semestre/ano refer&ecirc;ncia da sua FPA: 
                        </span>
                    </th>
                    <th>
                        <select name="psemestre" id="psemestre" value="<?= $psemestre ?>" style="width: 50pt">
                            <option></option>
                            <option <?php if ($psemestre == '1') print 'selected'; ?> value='<?= crip('1') ?>'>1</option>
                            <option <?php if ($psemestre == '2') print 'selected'; ?> value='<?= crip('2') ?>'>2</option>
                        </select> /
                        <select name="pano" id="pano" value="<?= $pano ?>" style="width: 50pt">
                            <option></option>
                            <?php
                            for ($i = ($ANO - 1); $i <= ($ANO + 1); $i++) {
                                $selected = "";
                                if ($pano == $i)
                                    $selected = "selected";
                                print "<option $selected value='" . crip($i) . "'>" . $i . "</option>";
                            }
                            ?>
                        </select>
                    </th>
                </tr>
            </table>
            <table style="width: 865px" border="0" summary="FTD" id="tabela_boletim">
                <thead>
                    <tr>
                        <th>Professor: </th>
                        <th><?= $_SESSION["loginNome"] ?></th>
                        <th>&Aacute;rea: </th>
                        <th><?= $area ?></th>
                    </tr>
                    <tr>
                        <th>Prontu&aacute;rio: </th>
                        <th><?= $_SESSION["loginProntuario"] ?></th>
                        <th>Email: </th>
                        <th><?= $email ?></th>
                    </tr>
                    <tr>
                        <th>Telefone: </th><th><?= $telefone ?></th>
                        <th>Celular: </th><th><?= $celular ?></th>
                    </tr>
                    <tr>
                        <th>Apelido: </th>
                        <th><?= $apelido ?></th>

                        <th>Regime: </th>
                        <th><?= $regime ?>
                        </th>
                    </tr>
            </table>

            <table style="width: 865px" border="0" summary="FTD" id="tabela_boletim">
                <thead>
                    <tr align="right">
                        <th align="left">
                            <img style="width: 30px" src="<?= ICONS ?>/icon-printer.gif" title="Imprimir em PDF" />
                            <a href="<?= VIEW ?>/secretaria/relatorios/inc/rit.php?professor=<?= crip($_SESSION['loginCodigo']) ?>" target="_blank">
                                <span style='font-weight: bold; color: white'>Imprimir</span>
                            </a>
                        </th>
                        <th colspan="4" align="center">
                            <input type="submit" <?= $disabled ?> style="width: 50px;" value="Salvar" id="salvar">
                            &nbsp;&nbsp;&nbsp;
                            <input type="submit" style="width: 50px" <?= $disabled ?> value="Enviar" id="enviar">
                        </th>
                    </tr>
                </thead>
            </table>
            <link rel="stylesheet" type="text/css" href="view/css/aba.css" media="screen" />
            <script src="view/js/aba.js"></script>

            <table style="width: 865px" border="0" summary="FTD">
                <tr>
                    <th>
                <ul class="tabs">
                    <li><a href="#Dados1">Altera&ccedil;&otilde;es</a></li>
                    <li><a href="#Dados2">Atividades de Ensino</a></li>
                    <li><a href="#Dados3">Atividades de Apoio</a></li>
                    <li><a href="#Dados4">Complementa&ccedil;&atilde;o</a></li>
                </ul>
                </th>
                </tr>
            </table>

            <div class="cont_tab" id="Dados1">
                <font size="2"><b>Altera&ccedil;&otilde;es em rela&ccedil;&atilde;o ao PIT</b></font>
                <br />
                <br />
                <table style="width: 865px" border="0" summary="FTD" >
                    <tr align="right" valign="top">
                        <th>
                    <table style="width: 100%" id="tabela_boletim">
                        <tr>
                            <th>Justificativas</th>
                        </tr>
                        <tr>
                            <th>        
                        <div class='fundo_listagem' style="background: #fff;">
                            <textarea <?= $disabled ?> maxlength='500' id='horario' name='horario'><?= $horario ?></textarea>
                        </div>
                        </th>
                        </tr>
                    </table>
                    </th>
                    </tr>
                </table>
            </div>

            <div class="cont_tab" id="Dados2">
                <font size="2"><b>Atividades de Ensino<br>Reg&ecirc;ncia de Aulas</b></font>
                <br />
                <br />
                <table style="width: 865px" border="0" summary="FTD" >
                    <tr align="right" valign="top">
                        <th>
                    <table style="width: 100%" id="tabela_boletim">
                        <tr>
                            <th>Sigla</th>
                            <th>Nome</th>
                            <th>Curso</th>
                            <th>Per&iacute;odo</th>
                            <th>Aulas</th>
                        </tr>
                        <?php
                        $periodo[1] = 'Matutino';
                        $periodo[2] = 'Vespertino';
                        $periodo[3] = 'Noturno';

                        for ($t = 0; $t <= 9; $t++) {
                            ?>
                            <tr>
                                <th><input class="componente" type="text" <?= $disabled ?> size="5" maxlength="45" id="S<?= $t ?>" name="S<?= $t ?>" value="<?= $resC[$t]['sigla'] ?>"/></th>
                                <th><input class="componente" type="text" <?= $disabled ?> size="40" maxlength="45" id="N<?= $t ?>" name="N<?= $t ?>" value="<?= $resC[$t]['nome'] ?>"/></th>
                                <th><input class="componente" type="text" <?= $disabled ?> size="40" maxlength="145" id="C<?= $t ?>" name="C<?= $t ?>" value="<?= $resC[$t]['curso'] ?>"/></th>
                                <th>
                                    <select class="componente" id="P<?= $t ?>" <?= $disabled ?> name="P<?= $t ?>" >
                                        <?php
                                        for ($p = 1; $p <= 3; $p++) {
                                            if ($resC[$t]['periodo'] == $periodo[$p][0])
                                                $selected = 'selected';
                                            else
                                                $selected = '';
                                            ?>
                                            <option <?= $selected ?> <?= $disabled ?> value="<?= $periodo[$p][0] ?>"><?= $periodo[$p][0] ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </th>
                                <th><input class="componente" <?= $disabled ?> type="text" size="3" maxlength="2" id="A<?= $t ?>" name="A<?= $t ?>" value="<?= $resC[$t]['aulas'] ?>"/></th>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th colspan="4" align="right">Reg&ecirc;ncia de Aulas (em horas)</th>
                            <th id="regencia">&nbsp;</th>
                        </tr>
                        <tr>
                            <th colspan="4" align="right">Tempo Organiza&ccedil;&atilde;o do Ensino (em horas)</th>
                            <th id="ensino">&nbsp;</th>
                        </tr>
                        <tr>
                            <th colspan="4" align="right">Tempo total dedicado &agrave; Aulas e Organiza&ccedil;&atilde;o de Ensino (em horas)</th>
                            <th id="totalRegEns">&nbsp;</th>
                        </tr>                        
                    </table>
                    </th>
                    </tr>
                </table>
            </div>

            <div class="cont_tab" id="Dados3">
                <font size="2"><b>Atividades de Apoio ao Ensino</b></font>
                <br />
                <br />
                <table style="width: 865px" border="0" summary="FTD" >
                    <tr align="right" valign="top">
                        <th>
                    <table style="width: 100%" id="tabela_boletim">
                        <?php
                        for ($t = 0; $t <= 6; $t++) {
                            ?>
                            <tr>
                                <th><input class="atividade" <?= $disabled ?> type="text" size="60" maxlength="200" onclick="return valores('AtvD<?= $t ?>');" id="AtvD<?= $t ?>" name="AtvD<?= $t ?>" value="<?= $resAtv[$t]['descricao'] ?>"/></th>
                                <th><input class="atividade" <?= $disabled ?> type="text" size="3" maxlength="2" id="AtvA<?= $t ?>" name="AtvA<?= $t ?>" value="<?= $resAtv[$t]['aulas'] ?>"/></th>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th align="right">Atividades de Apoio ao Ensino (em horas)</th>
                            <th id="atvEnsino">&nbsp;</th>
                        </tr>
                    </table>
                    </th>
                    <th>&nbsp;</th>
                    <th align="left"><font size="1">Como exemplos de atividades que podem ser realizadas e descritas nos campos da tabela de Apoio ao Ensino, tem-se:
                        <br />
                        <br />- Atendimento ao aluno (m&iacute;nimo 1h);
                        <br />- Reuni&otilde;es (De &aacute;rea, de cursos, Pedag&oacute;gicas, NDE, etc) - M&iacute;nimo 2h;
                        <br />- Recupera&ccedil;&atilde;o paralela;
                        <br />- Supervis&atilde;o ou orienta&ccedil;&atilde;o de est&aacute;gio ou de trabalhos acad&ecirc;micos;
                        <br />- Outras atividades com descri&ccedil;&atilde;o semanal.</font>
                    </th>
                    </tr>
                </table>
            </div>

            <div class="cont_tab" id="Dados4">
                <font size="2"><b>Complementa&ccedil;&atilde;o de Atividades</b></font>
                <br />
                <br />
                <table style="width: 865px" border="0" summary="FTD" >
                    <tr align="right" valign="top">
                        <th>
                    <table style="width: 100%" id="tabela_boletim">
                        <?php
                        for ($t = 0; $t <= 6; $t++) {
                            ?>
                            <tr>
                                <th><input class="complementacao" <?= $disabled ?> type="text" size="60" maxlength="200" id="CompD<?= $t ?>" name="CompD<?= $t ?>" value="<?= $resComp[$t]['descricao'] ?>"/></th>
                                <th><input class="complementacao" <?= $disabled ?> type="text" size="3" maxlength="2" id="CompA<?= $t ?>" name="CompA<?= $t ?>" value="<?= $resComp[$t]['aulas'] ?>"/></th>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th align="right">Complementa&ccedil;&atilde;o de Atividades (em horas)</th>
                            <th id="compAtv">&nbsp;</th>
                        </tr>
                    </table>
                    </th>
                    <th>&nbsp;</th>
                    <th align="left"><font size="1">Como exemplos de atividades que podem ser realizadas e descritas nos campos da tabela de Complementa&ccedil;&atilde;o de atividades, tem-se:
                        <br />
                        <br />- Projetos de pesquisa;
                        <br />- Projetos de extens&atilde;o;
                        <br />- Coordena&ccedil;&otilde;es, ger&ecirc;ncias ou dire&ccedil;&otilde;es;
                        <br />- Participa&ccedil;&atilde;o em comiss&otilde;es e comit&ecirc;s;
                        <br />- Cursos de capacita&ccedil;&atilde;o;
                        <br />- Outras atividades com descri&ccedil;&atilde;o semanal;
                        </font>
                    </th>
                    </tr>
                </table>
            </div>
        </form>
    </div>
    <table style="width: 865px" id="tabela_boletim">
        <tr>
            <th align="right">Total de horas semanais (obrigatoriamente 20h ou 40h, dependendo do regime de trabalho)</th>
            <th id="TH">&nbsp;</th>
        </tr>
    </table>    

    <span id="obsCH" style="color: red;">&nbsp;</span>
    <br />
    <span id="obsCELL" style="color: red;">&nbsp;</span>
</center >
<?php
$hor1 = explode(',', $horario1);
$hor2 = explode(',', $horario2);
$hor3 = explode(',', $horario3);
?>
<script>
    var totalCelulas = 0;
    var totalHoras = 0;
    callFunction();

    function calcComponente() {
        total = 0;
        if ('<?= $duracaoAula ?>') {
            for (i = 0; i <= 9; i++) {
                if ($("#A" + i).val())
                    total += parseInt($("#A" + i).val());
            }
            totalAulas = (total * '<?= substr($duracaoAula, 3, 2) ?>') / 60;

            $("#regencia").html(Math.round(totalAulas));
            $("#ensino").html(Math.round(totalAulas));
            totalHoras += Math.round(totalAulas) * 2;
            $("#totalRegEns").html(totalHoras);

            $("#TH").html(totalHoras);
        }
    }

    function calcAtividade() {
        total = 0;
        for (i = 0; i <= 6; i++) {
            if ($("#AtvA" + i).val())
                total += parseInt($("#AtvA" + i).val());
        }
        $("#atvEnsino").html(Math.round(total));

        totalHoras += Math.round(total);
        $("#TH").html(totalHoras);
    }

    function calcComplementacao() {
        total = 0;
        for (i = 0; i <= 6; i++) {
            if ($("#CompA" + i).val())
                total += parseInt($("#CompA" + i).val());
        }
        $("#compAtv").html(Math.round(total));

        totalHoras += Math.round(total);
        $("#TH").html(totalHoras);
    }

    function calcAulas() {
        var maxCH = 40;

        var ministrar = 16;
        var celulas = 32;
        if ('<?= $duracaoAula ?>' == '00:50'
                && '<?= $regime ?>' != '20H') {
            ministrar = ministrar - 2;
            celulas = celulas - 3;
        }

        if ('<?= $regime ?>' == '00:50'
                && '<?= $regime ?>' == '20H') {
            ministrar = ministrar - 1;
            celulas = celulas - 2;
        }

        if ('<?= $dedicarEnsino ?>'
                && '<?= $regime ?>' != '20H') {
            ministrar += 5;
        }

        if ('<?= $regime ?>' == '20H') {
            ministrar = ministrar - 5;
            celulas = celulas - 12;
            maxCH = 20;
        }

        if (totalHoras != maxCH)
            $("#obsCH").html('Carga horária final incompatível com a jornada de trabalho de ' + maxCH + 'h indicada, favor corrigir!');
        else
            $("#obsCH").html('');

        $("#ministrar").html(ministrar);
        $("#celulas").html(celulas);
        totalCelulas = celulas;
    }

    function callFunction() {
        totalHoras = 0;
        calcComponente();
        calcAtividade();
        calcComplementacao();
        calcAulas();
    }

    $(".componente,.atividade,.complementacao").keyup(function () {
        callFunction();
    });

    $('#pano, #psemestre').change(function () {
        var pano = $('#pano').val();
        var psemestre = $('#psemestre').val();
        $('#index').load('<?= $SITE ?>?pano=' + pano + '&psemestre=' + psemestre);
    });
    
    var enviar = 0;
    $('#form_padrao').submit(function () {
        var options = {
            target: '#index',
            url: '<?= $SITE ?>',
            type: 'POST',
            data: {enviar: enviar},
        };

        $(this).ajaxSubmit(options);
        return false;
    });

    $("#enviar").click(function (event) {
        event.preventDefault();
        $.Zebra_Dialog('<strong>Deseja salvar sua RIT e enviar para seu coordenador? <br><br> A RIT ser&aacute; bloqueada, podendo ser desbloqueada somente pelo coordenador.</strong>', {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption) {
                if (caption == 'Sim') {
                    enviar = 1;
                    $("#form_padrao").submit();
                }
            }
        });
    });

    $(document).ready(function () {
        $('#horario').maxlength({
            events: [], // Array of events to be triggerd    
            maxCharacters: 500, // Characters limit   
            status: true, // True to show status indicator bewlow the element    
            statusClass: "status", // The class on the status div  
            statusText: "caracteres restando", // The status text  
            notificationClass: "notification", // Will be added when maxlength is reached  
            showAlert: false, // True to show a regular alert message    
            alertText: "Limite de caracteres excedido!", // Text in alert message   
            slider: true // True Use counter slider    
        });
    });
    
    function valores(campo) {
        var availableTags = [
            "Atendimento ao aluno",
            "Atendimento do NDE",
            "Atendimento pedagógicas",
            "Reunião de área",
            "Reunião de curso",
            "Recuperação paralela",
            "Supervisão ou orientação de estágio",
            "Supervisão ou orientação de trabalhos acadêmicos"
        ];
        $("#" + campo).autocomplete({
            source: availableTags
        });
    }    
</script>