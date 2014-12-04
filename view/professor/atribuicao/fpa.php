<?php
//Esse arquivo é fixo para o professor.
//Permite o registro da Folha de Trabalho Docente.
//Link visível no menu: PADRÃO SIM.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/tdDados.class.php";
$dados = new TDDados();

require CONTROLLER . "/tdFpaAtvECmt.class.php";
$atvECmt = new TDFPAAtvECmt();

require CONTROLLER . "/tdFpaComponente.class.php";
$componente = new TDFPAComponente();

require CONTROLLER . "/tdVars.class.php";
$tdVars = new TDVars();

require CONTROLLER . "/logSolicitacao.class.php";
$log = new LogSolicitacoes();

if ($_POST) {
    $_POST['modelo'] = 'FPA';
    $_POST['semestre'] = $SEMESTRE;
    $_POST['ano'] = $ANO;
    $_POST['pessoa'] = $_SESSION['loginCodigo'];

    $ret = $dados->insertOrUpdateFPA($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}

//LISTA OS REGISTROS DA FPA
$params = array('pessoa' => $_SESSION['loginCodigo'], 'ano' => $ANO, 'semestre' => $semestre);
$res = $dados->listRegistros($params);
extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);

//LISTA OS DADOS DA PESSOA
$pessoa = new Pessoas();
$paramsP = array('codigo' => $_SESSION['loginCodigo']);
$resP = $pessoa->listRegistros($paramsP);
$email = $resP[0]['email'];
$telefone = $resP[0]['telefone'];
$celular = $resP[0]['celular'];

//LISTA COMPONENTES
$resC = $componente->listComponentes($codigo);
//LISTA ATIVIDADES
$resAtv = $atvECmt->listAtvECmt($codigo, 'atv');
//LISTA COMPLEMENTACAO
$resComp = $atvECmt->listAtvECmt($codigo, 'cmp');

//VERIFICA SE ESTA FINALIZADO OU VALIDADO
$resVars = $tdVars->listVars($codigo, 'FPA');
if ($resVars[0]['finalizado'] && $resVars[0]['finalizado'] != '0000-00-00 00:00:00')
    $disabled = 'disabled';

if ($resVars[0]['valido'] && $resVars[0]['valido'] != '00/00/0000 00:00')
    $VALIDO = 1;

$paramsLog['codigoTabela'] = $codigo;
$paramsLog['nomeTabela'] = 'FPA';
$l = $log->listSolicitacoes($paramsLog, " AND ( l.dataConcessao = '0000-00-00 00:00:00' OR l.dataConcessao IS NULL) " );
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
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<br />
<center>
    <div id="html5form" class="main">
        <form id="form_padrao">
            <input type="hidden" value="<?php echo $codigo; ?>" name="codigo" id="codigo" />
            <font size="3"><b>FPA - FORMUL&Aacute;RIO DE PREFER&Ecirc;NCIA DE ATIVIDADES <br> <?= $SEMESTRE ?>&ordm; semestre <?= $ANO ?> </b></font>
            <table style="width: 865px" border="0" summary="FTD" id="tabela_boletim">
                <thead>
                    <tr>
                        <th>Professor: </th>
                        <th><input type="text" disabled style="width: 264pt" id="nome"ampoN name="nome" maxlength="45" value="<?= $_SESSION["loginNome"] ?>" /></th>
                        <th>&Aacute;rea: </th>
                        <th><input type="text" <?= $disabled ?> style="width: 264pt" id="area" name="area" maxlength="45" value="<?= $area ?>"/></th>
                    </tr>
                    <tr>
                        <th>Prontu&aacute;rio: </th>
                        <th><input type="text" disabled style="width: 264pt" id="prontuario" name="prontuario" maxlength="45" value="<?= $_SESSION["loginProntuario"] ?>" /></th>
                        <th>Email: </th>
                        <th><input type="text" <?= $disabled ?> style="width: 264pt" id="email" name="email" maxlength="100" value="<?= $email ?>" /></th>
                    </tr>
                    <tr>
                        <th>Telefone: </th><th><input type="text" <?= $disabled ?> style="width: 264pt" id="telefone" name="telefone" maxlength="45" value="<?= $telefone ?>" /></th>
                        <th>Celular: </th><th><input type="text" <?= $disabled ?> style="width: 264pt" id="celular" name="celular" maxlength="45" value="<?= $celular ?>" /></th>
                    </tr>
                    <tr>
                        <th>Apelido: </th>
                        <th><input type="text" <?= $disabled ?> style="width: 264pt" id="apelido" name="apelido" maxlength="100" value="<?= $apelido ?>"/></th>

                        <th>Regime: </th>
                        <th>
                            <?php
                            $regimes = Array('20H', '40H', 'RDE', 'Substituto', 'Temporario');
                            foreach ($regimes as $r) {
                                $checked = '';
                                if ($regime == $r)
                                    $checked = 'checked';
                                ?>
                                |<input type="radio" <?= $disabled ?> id="regime" <?= $checked ?> name="regime" value="<?= $r ?>" /><font size="1"><?= $r ?></font>
                                <?php
                            }
                            ?>
                        </th>
                    </tr>
            </table>

            <table style="width: 865px" border="0" summary="FTD" id="tabela_boletim">
                <thead>
                    <tr align="right">
                        <th align="left">
                            <img style="width: 30px" src="<?= ICONS ?>/icon-printer.gif" title="Imprimir em PDF" />
                            <a href="<?= VIEW ?>/secretaria/relatorios/inc/fpa.php?professor=<?= crip($_SESSION['loginCodigo']) ?>" target="_blank">
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
                    <li><a href="#Dados1">Disponibilidade</a></li>
                    <li><a href="#Dados2">Componentes</a></li>
                    <li><a href="#Dados3">Atividades</a></li>
                    <li><a href="#Dados4">Complementação</a></li>
                </ul>
                </th>
                </tr>
            </table>

            <div class="cont_tab" id="Dados1">
                <font size="2"><b>Disponibilidade de hor&aacute;rio para atribui&ccedil;&atilde;o de componentes curriculares</b></font>
                <br />
                <br />
                <table style="width: 865px" border="0" summary="FTD" >
                    <tr>
                        <th align="left">
                            Células selecionadas: <span id="celulasSel"></span><br />
                    <table style='width:400px' border="0" id="tabela_boletim">
                        <?php
                        $dias = diasDaSemana();
                        $dias[0] = 'Aula';
                        unset($dias[1]);

                        $aula[1] = '1';
                        $aula[7] = '2';
                        $aula[13] = '3';
                        $aula[19] = '4';
                        $aula[25] = '5';
                        $aula[31] = '6';

                        ksort($dias);
                        ?>
                        <tr>
                            <?php
                            //IMPRIME O DIA DA SEMANA
                            foreach ($dias as $dCodigo => $dNome) {
                                ?>
                                <th>
                                    <span style='font-weight: bold; color: white'><?= $dNome ?></span>
                                </th>
                                <?php
                            }
                            ?>
                        </tr>
                        <?php
                        $periodo[1] = 'Matutino';
                        $periodo[2] = 'Vespertino';
                        $periodo[3] = 'Noturno';

                        for ($p = 1; $p <= 3; $p++) {
                            ?>
                            <th colspan="7">
                                <span style='font-weight: bold; color: white'><?= $periodo[$p] ?></span>
                            </th>
                            <tr align="center">
                                <?php
                                $c = 7;
                                $l = 0;
                                $horarios = explode(',', $horario);
                                for ($i = 1; $i <= 36; $i++) { // LINHAS DA TABELA
                                    if ($c >= 7) {
                                        ?>
                                    <tr align="center">
                                        <th id="A<?= $p . $i ?>"><?= $aula[$i] ?></th>
                                        <?php
                                        $c = 1;
                                        $l++;
                                    }
                                    $IS = $p . $l . $c;
                                    ?>
                                    <td><input id="CE<?= $IS ?>" <?=$disabled?> name="horario[]" value="<?= $IS ?>" type="checkbox" <?php if (in_array($IS, $horarios)) print 'checked'; ?> /></td>
                                    <?php
                                    $c++;
                                }
                                ?>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                    </th>
                    <th>&nbsp;&nbsp;</th>
                    <th align="left" valign='top'>
                    <table border='0'>
                        <tr>
                            <td>
                                Dura&ccedil;&atilde;o da Aula no Campus:<br />
                                <input type="radio" <?= $disabled ?> id="duracaoAula" <?php if ($duracaoAula == '00:45') print 'checked'; ?> name="duracaoAula" value="00:45" /><font size="1">45min</font><br />
                                <input type="radio" <?= $disabled ?> id="duracaoAula" <?php if ($duracaoAula == '00:50') print 'checked'; ?> name="duracaoAula" value="00:50" /><font size="1">50min</font>
                            </td>
                        </tr>
                        <tr><td><hr /></td></tr>
                        <tr>
                            <td>
                                <input type="checkbox" <?= $disabled ?> id="dedicarEnsino" <?php if ($dedicarEnsino) print 'checked'; ?> name="dedicarEnsino" value="1" />
                                <font size="1">Sim, desejo dedicar-me prioritariamente a atividades de ensino.</font>
                            </td>
                        </tr>
                        <tr><td><hr /></td></tr>
                        <tr>
                            <td><font size="1">Pelo regime de trabalho selecionado e pela dura&ccedil;&atilde;o da hora-aula o docente poder&aacute;:<br /></font>
                                <br />
                                <font style='font-weight: bold; color: red'>Ministrar at&eacute; <span id="ministrar"></span> aulas.
                                <br />Selecionar <span id="celulas"></span> c&eacute;lulas.
                                </font>
                            </td>
                        </tr>
                        <tr><td><hr /></td></tr>
                        <tr>
                            <td>
                                <font size="1">Caso o docente deseje substituir a numera&ccedil;&atilde;o das aulas nos turnos pelos hor&aacute;rios das mesmas no campus, e se os cursos onde o docente tem aulas obedecem &agrave; mesma distribui&ccedil;&atilde;o de hor&aacute;rio, complete as informa&ccedil;&otilde;es abaixo:</font>
                                <br /><input type="checkbox" <?= $disabled ?> id="subHorario" <?= $checked ?> name="subHorario" <?php if ($subHorario) print 'checked'; ?> value="1" />
                                <font size="1">Sim, desejo substituir a numera&ccedil;&atilde;o pelos hor&aacute;rios.</font>
                            </td>
                        </tr>
                        <tr><td><hr /></td></tr>
                        <tr>
                            <td>
                                <table border="0">
                                    <?php
                                    for ($p = 1; $p <= 3; $p++) {
                                        $var = 'horario' . $p;
                                        $hor = explode(',', $$var);
                                        $t = 'h' . $p;
                                        $$t = $hor[2];
                                        ?>
                                        <tr>
                                            <td>
                                                <font size="1">Duração do intervalo no per&iacute;odo <?= $periodo[$p] ?>:</font>
                                            </td>
                                            <td>
                                                <select id="Intervalo<?= $p ?>" <?=$disabled?> name="Intervalo<?= $p ?>">
                                                    <?php
                                                    for ($i = 5; $i <= 30; $i++) {
                                                        $n = str_pad($i, 2, "0", STR_PAD_LEFT);
                                                        ?>
                                                        <option <?php if ($hor[0] == "00:$n") print 'selected'; ?> value="00:<?= $n ?>">00:<?= $n ?></option>
                                                        <?php
                                                        $i+=4;
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <font size="1">Que horas representa o número 1 no período <?= $periodo[$p] ?>:</font>
                                            </td>
                                            <td>
                                                <input type="text" id="Periodo<?= $p ?>" <?=$disabled?> name="Periodo<?= $p ?>" size="3" value="<?= $hor[1] ?>" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <font size="1">Que horas começa o intervalo neste período <?= $periodo[$p] ?>:</font>
                                            </td>
                                            <td>
                                                <select id="IniIntervalo<?= $p ?>" <?=$disabled?> name="IniIntervalo<?= $p ?>" value="<?= $hor[2] ?>"></select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><hr>
                                            </td>
                                        </tr>                                        
                                        <?php
                                    }
                                    ?>
                                </table>
                            </td>
                        </tr>
                    </table>
                    </th>
                    </tr>
                </table>
            </div>

            <div class="cont_tab" id="Dados2">
                <font size="2"><b>Componentes curriculares de interesse do docente (por ordem de prioridade)</b></font>
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
                        for ($t = 0; $t <= 9; $t++) {
                            ?>
                            <tr>
                                <th><input class="componente" type="text" <?=$disabled?> size="5" maxlength="45" id="S<?= $t ?>" name="S<?= $t ?>" value="<?= $resC[$t]['sigla'] ?>"/></th>
                                <th><input class="componente" type="text" <?=$disabled?> size="40" maxlength="45" id="N<?= $t ?>" name="N<?= $t ?>" value="<?= $resC[$t]['nome'] ?>"/></th>
                                <th><input class="componente" type="text" <?=$disabled?> size="40" maxlength="145" id="C<?= $t ?>" name="C<?= $t ?>" value="<?= $resC[$t]['curso'] ?>"/></th>
                                <th>
                                    <select class="componente" id="P<?= $t ?>" <?=$disabled?> name="P<?= $t ?>" >
                                        <?php
                                        for ($p = 1; $p <= 3; $p++) {
                                            if ($resC[$t]['periodo'] == $periodo[$p][0])
                                                $selected = 'selected';
                                            else
                                                $selected = '';
                                            ?>
                                            <option <?= $selected ?> <?=$disabled?> value="<?= $periodo[$p][0] ?>"><?= $periodo[$p][0] ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </th>
                                <th><input class="componente" <?=$disabled?> type="text" size="3" maxlength="2" id="A<?= $t ?>" name="A<?= $t ?>" value="<?= $resC[$t]['aulas'] ?>"/></th>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th colspan="4" align="right">Regência de Aulas (em horas)</th>
                            <th id="regencia">&nbsp;</th>
                        </tr>
                        <tr>
                            <th colspan="4" align="right">Organização do Ensino (em horas)</th>
                            <th id="ensino">&nbsp;</th>
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
                                <th><input class="atividade" <?=$disabled?> type="text" size="60" maxlength="200" id="AtvD<?= $t ?>" name="AtvD<?= $t ?>" value="<?= $resAtv[$t]['descricao'] ?>"/></th>
                                <th><input class="atividade" <?=$disabled?> type="text" size="3" maxlength="2" id="AtvA<?= $t ?>" name="AtvA<?= $t ?>" value="<?= $resAtv[$t]['aulas'] ?>"/></th>
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
                        <br />- Atendimento ao aluno (mínimo 1h);
                        <br />- Reuniões (De área, de cursos, Pedagógicas, NDE, etc) - Mínimo 2h;
                        <br />- Recuperação paralela;
                        <br />- Supervisão ou orientação de estágio ou de trabalhos acadêmicos;
                        <br />- Outras atividades com descrição semanal.</font>
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
                                <th><input class="complementacao" <?=$disabled?> type="text" size="60" maxlength="200" id="CompD<?= $t ?>" name="CompD<?= $t ?>" value="<?= $resComp[$t]['descricao'] ?>"/></th>
                                <th><input class="complementacao" <?=$disabled?> type="text" size="3" maxlength="2" id="CompA<?= $t ?>" name="CompA<?= $t ?>" value="<?= $resComp[$t]['aulas'] ?>"/></th>
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
                    <th align="left"><font size="1">Como exemplos de atividades que podem ser realizadas e descritas nos campos da tabela de Complementação de atividades, tem-se:
                        <br />
                        <br />- Projetos de pesquisa;
                        <br />- Projetos de extensão;
                        <br />- Coordenações, gerências ou direções;
                        <br />- Participação em comissões e comitês;
                        <br />- Cursos de capacitação;
                        <br />- Outras atividades com descrição semanal;
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
<script>
    var totalCelulas = 0;
    var totalHoras = 0;
    calcIntervalo();
    callFunction();

    function calcComponente() {
        total = 0;
        if ($("input[id=duracaoAula]:radio:checked").val()) {
            for (i = 0; i <= 9; i++) {
                if ($("#A" + i).val())
                    total += parseInt($("#A" + i).val());
            }
            totalAulas = (total * $("input[id=duracaoAula]:radio:checked").val().substring(5, 3)) / 60;

            $("#regencia").html(Math.round(totalAulas));
            $("#ensino").html(Math.round(totalAulas));

            totalHoras += Math.round(totalAulas) * 2;
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

    function checkCelulas() {
        celulasSel = 0;
        for (p = 1; p <= 3; p++) {
            c = 7;
            l = 0;
            for (i = 1; i <= 36; i++) {
                if (c >= 7) {
                    c = 1;
                    l++;
                }
                IS = p + '' + l + '' + c;
                if ($("#CE" + IS).is(':checked')) {
                    $("#obsCELL").html('');
                    if (celulasSel >= totalCelulas) {
                        $("#obsCELL").html('Número máximo de células ultrapassado!');
                    }
                    if (((totalCelulas - celulasSel) - 1) > 0) {
                        $("#obsCELL").html('Falta selecionar ' + ((totalCelulas - celulasSel) - 1) + ' células!');
                    }
                    celulasSel++;
                    $("#celulasSel").html(celulasSel);
                }
                c++;
            }
        }
    }

    function calcAulas() {
        var maxCH = 40;
        if (!$("input[id=regime]:radio:checked").val()) {
            mensagem('Selecione um Regime de Trabalho primeiro!!!');
            return false;
        }
        var ministrar = 16;
        var celulas = 32;
        if ($("input[id=duracaoAula]:radio:checked").val() == '00:50'
                && $("input[id=regime]:radio:checked").val() != '20H') {
            ministrar = ministrar - 2;
            celulas = celulas - 3;
        }

        if ($("input[id=duracaoAula]:radio:checked").val() == '00:50'
                && $("input[id=regime]:radio:checked").val() == '20H') {
            ministrar = ministrar - 1;
            celulas = celulas - 2;
        }

        if ($("#dedicarEnsino").is(':checked')
                && $("input[id=regime]:radio:checked").val() != '20H') {
            ministrar += 5;
        }

        if ($("input[id=regime]:radio:checked").val() == '20H') {
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

    function calcIntervalo() {
        for (p = 1; p <= 3; p++) {
            ini = $("#Periodo" + p).val();
            vl = ini;
            for (l = 1; l <= 6; l++) {
                vl = addtime(vl, $("input[id=duracaoAula]:radio:checked").val());
                if (vl == '<?= $h1 ?>' || vl == '<?= $h2 ?>' || vl == '<?= $h3 ?>')
                    sel = true;
                else
                    sel = false;
                $('#IniIntervalo' + p).append($('<option>', {selected: sel, value: vl, text: vl}));
            }
        }
    }

    function calcPeriodo(tipo) {
        for (p = 1; p <= 3; p++) {
            ini = $("#Periodo" + p).val();
            j = 1;
            for (l = 1; l <= 31; l++) {
                if (tipo == true) {
                    if (l != 1) {
                        ini = addtime(ini, $("input[id=duracaoAula]:radio:checked").val());
                    } else {
                        ini = ini;
                    }

                    if ($('#IniIntervalo' + p).val() == ini)
                        ini = addtime(ini, $('#Intervalo' + p).val());

                    fim = addtime(ini, $("input[id=duracaoAula]:radio:checked").val());

                    $("#A" + p + '' + l).text(ini + ' - ' + fim);
                } else {
                    $("#A" + p + '' + l).text(j);
                    j++;
                }
                l = l + 5;
            }
        }
    }

    function callFunction() {
        totalHoras = 0;
        callPeriodo();
        calcComponente();
        calcAtividade();
        calcComplementacao();
        calcAulas();
        checkCelulas();
    }

    $(".componente,.atividade,.complementacao,#Periodo1,#Periodo2,#Periodo3").keyup(function () {
        callFunction();
    });

    $("#IniIntervalo1,#IniIntervalo2,#IniIntervalo3,#Intervalo1,#Intervalo2,#Intervalo3").change(function () {
        callFunction();
    });

    $("input:checkbox,input:radio,#dedicarEnsino").click(function () {
        $('#IniIntervalo1').find('option').remove();
        calcIntervalo();
        callFunction();
    });

    $("#subHorario").click(function () {
        callPeriodo('Favor preencher todos os campos com os horários!');
    });

    function callPeriodo(message) {
        for (i = 1; i <= 3; i++) {
            if ($("#Periodo" + i).val().indexOf("_") >= 0 || $("#Periodo" + i).val() == '' || $("#IniIntervalo" + i).val() == '') {
                if (message) {
                    $("#subHorario").attr('checked', false);
                    mensagem('Favor preencher todos os campos com os horários!');
                }
                return false;
            }
        }
        calcPeriodo($("#subHorario").is(':checked'));
    }

    $("#Periodo1,#Periodo2,#Periodo3").mask("99:99");
    $("#celular").mask("(99) 99999-9999");
    $("#telefone").mask("(99) 9999-9999");

    var enviar = 0;
    $('#form_padrao').submit(function () {
        var options = {
            target: '#index',
            url: '<?php print $SITE; ?>',
            type: 'POST',
            data: {enviar: enviar},
        };

        $(this).ajaxSubmit(options);
        return false;
    });

    $("#enviar").click(function () {
        event.preventDefault();
        $.Zebra_Dialog('<strong>Deseja salvar sua FPA e enviar para seu coordenador? <br><br> A FPA ser&aacute; bloqueada, podendo ser desbloqueada somente pelo coordenador.</strong>', {
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

    function mensagem(mensagem) {
        $.Zebra_Dialog('<strong>' + mensagem + '</strong>', {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Ok'],
            'onClose': function (caption) {
            }
        });
    }

    function addtime(start, end) {
        if (start)
            start = start.split(":");
        if (end)
            end = end.split(":");
        var hours = parseInt(start[0]) + parseInt(end[0]);
        var minutes = parseInt(start[1]) + parseInt(end[1]);
        if (minutes >= 60) {
            hours++;
            minutes -= 60;
        }
        return (hours <= 9 ? "0" : "") + hours + ":" + (minutes <= 9 ? "0" : "") + minutes;
    }
</script>