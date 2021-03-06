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
//debug($_POST);die();
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
    $sqlAdicional = ' AND f.ano= :ano AND f.semestre= :semestre AND p.codigo = :pessoa AND f.modelo = :modelo ORDER BY f.codigo DESC LIMIT 1 ';
    $params = array('ano' => $pano, 'semestre' => $psemestre, 'pessoa' => $_SESSION['loginCodigo'], 'modelo' => 'RIT');
    $res = $dados->listModelo($params, $sqlAdicional, null, null);
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

$sqlAdicional = ' AND f.ano= :ano AND f.semestre= :semestre AND p.codigo = :pessoa AND f.modelo = :modelo ';
$params = array('pessoa' => $_SESSION['loginCodigo'], 'ano' => $pano, 'semestre' => $psemestre, );
        
//IMPORTA PARÃMETROS DA FPA
$params['modelo'] = 'PIT';
$resPIT = $dados->listModelo($params, $sqlAdicional, null, null);
extract(array_map("htmlspecialchars", $resPIT[0]), EXTR_OVERWRITE);
$codigo = null;

//LISTA OS REGISTROS DA PIT
$params['ano'] = $pano;
$params['semestre'] = $psemestre;
$params['modelo'] = 'RIT';
$res = $dados->listModelo($params, $sqlAdicional, null, null);
$horario = $res[0]['horario'];
$codigo = $res[0]['codigo'];

//LISTA COMPONENTES
$resC = $componente->listComponentes($codigo);
if (!$resC && !is_null($resPIT[0]['valido'])){ // PIT AINDA NAO FOI CADASTRADA, BUSCANDO DADOS DA PIT
//    var_dump($resFPA[0]);
    $params['modelo'] = 'PIT';
    $res2 = $dados->listModelo($params, $sqlAdicional, null, null);
    $codigo = $res2[0]['codigo'];
    $resC = $componente->listComponentes($codigo);
    if ($resC)
        echo "<script>msg('Os dados de sua PIT foram importados. Preencha os horários, atualize o que for necessário e Salve ou Envie para avaliação do coordenador.')</script>";
}

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
            <?php
                            
    if (!$resPIT || (is_null($resPIT[0]['valido']))){
        mensagem('ERRO', 'PIT_NAO_VALIDADA');        
    }
    else{

            ?>
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
                            <a href="<?= VIEW ?>/secretaria/relatorios/inc/rit.php?professor=<?= crip($_SESSION['loginCodigo']) ?>&pano=<?= crip($pano) ?>&psemestre=<?= crip($psemestre) ?>" target="_blank">
                                <span style='font-weight: bold; color: white'>Imprimir</span>
                            </a>
                        </th>
                        <th colspan="4" align="center">
                            <?php
                            if (!$disabled){
                            ?>
                            <input type="submit" <?= $disabled ?> style="width: 50px;" value="Salvar" id="salvar">
                            &nbsp;&nbsp;&nbsp;
                            <input type="submit" style="width: 50px" <?= $disabled ?> value="Enviar" id="enviar">
                            <?php
                            }
                            ?>

                        </th>
                    </tr>
                </thead>
            </table>
            <link rel="stylesheet" type="text/css" href="view/css/aba.css" media="screen" />
            <script src="view/js/aba.js"></script>

            <div id="obsCH"></div>
            <div id="obsCELL"></div>
            
            <table style="width: 865px" border="0" summary="FTD">
                <tr>
                    <th>
                    <ul class="tabs">
                        <li><a href="#Dados2">Atividades de Ensino</a></li>
                        <li><a href="#Dados3">Atividades de Apoio</a></li>
                        <li><a href="#Dados4">Complementa&ccedil;&atilde;o</a></li>
                        <li><a href="#Dados1">Altera&ccedil;&otilde;es</a></li>
                    </ul>
                    </th>
                </tr>
            </table>

           
            <div class="cont_tab" id="Dados2">
                <font size="2"><b>Atividades de Ensino<br>Componentes Curriculares ministrados no período considerado neste relatório</b></font>
                <br />
                <br />
                <table style="width: 865px" border="0" summary="FTD" >
                    <tr align="right" valign="top">
                        <th>
                    <table style="width: 100%" id="tabela_boletim">
                        <tr>
                            <th>Curso</th>
                            <th>Nome</th>
                            <th>Sigla</th>
                            <th>Turno</th>
                            <th>Aulas</th>
                            <th>Oferta</th>
                        </tr>
                        <?php
                        $periodo[1] = 'Matutino';
                        $periodo[2] = 'Vespertino';
                        $periodo[3] = 'Noturno';

                        for ($t = 0; $t <= 9; $t++) {
                            ?>
                            <tr>
                                <th><input class="componente camposCursos" type="text" <?= $disabled ?> size="30" maxlength="145" id="C<?= $t ?>" onfocus="return valores('cursos', 'C<?= $t ?>')" name="C<?= $t ?>" value="<?= $resC[$t]['curso'] ?>"/></th>
                                <th><input class="componente camposDisciplinas" type="text" <?= $disabled ?> size="30" maxlength="45" id="N<?= $t ?>" name="N<?= $t ?>" value="<?= $resC[$t]['nome'] ?>"/></th>
                                <th><input class="componente" type="text" <?= $disabled ?> size="5" maxlength="45" id="S<?= $t ?>" name="S<?= $t ?>" value="<?= $resC[$t]['sigla'] ?>"/></th>
                                <th>
                                    <select class="componente" id="T<?= $t ?>" <?= $disabled ?> name="T<?= $t ?>" >
                                        <?php
                                        for ($p = 1; $p <= 3; $p++) {
                                            if ($resC[$t]['turno'] == $periodo[$p][0])
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
                                <th><input class="componente" <?= $disabled ?> type="number" style="width: 50px" size="3" maxlength="2" id="A<?= $t ?>" name="A<?= $t ?>" value="<?= $resC[$t]['aulas'] ?>"/></th>
                                <th>
                                    <select class="componente" id="R<?= $t ?>" <?= $disabled ?> name="R<?= $t ?>" >
                                        <option></option>
                                        <option <?= ($resC[$t]['referencia']=="1") ? "selected" : ""?> value="1">1º Sem.</option>
                                        <option <?= ($resC[$t]['referencia']=="2") ? "selected" : ""?> value="2">2º Sem.</option>
                                        <option <?= ($resC[$t]['referencia']=="3") ? "selected" : ""?> value="3">1ºe2º Sem.</option>
                                        <option <?= ($resC[$t]['referencia']=="4") ? "selected" : ""?> value="4">Anual</option>
                                        <?php
                                        ?>
                                    </select>
                                </th>
                                <th><input class="componente" type="hidden" id="Pr<?= $t ?>" name="Pr<?= $t ?>" value="<?= $resC[$t]['prioridade'] ?>"/></th>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th colspan="4" align="right">Tempo total dedicado às aulas (Total em horas)</th>
                            <th colspan="2" id="regencia">&nbsp;</th>
                        </tr>
                    </table>
                    </th>
                    </tr>
                </table>
            </div>

            <div class="cont_tab" id="Dados3">
                <font size="2"><b>Atividades de Apoio ao Ensino no período considerado neste relatório</b></font>
                <br />
                <br />
                <table style="width: 865px" border="0" summary="FTD" >
                    <tr align="right" valign="top">
                        <th>
                    <table style="width: 100%" id="tabela_boletim">
                        <tr>
                            <th>Nome</th>
                            <th>Duração(h)</th>
                            <th>Referência</th>
                        </tr>
                        
                        <?php
                        for ($t = 0; $t <= 6; $t++) {
                            ?>
                            <tr>
                                <th><input class="atividade" <?= $disabled ?> type="text" size="60" maxlength="200" onclick="return valores('atividades', 'AtvD<?= $t ?>');" id="AtvD<?= $t ?>" name="AtvD<?= $t ?>" value="<?= $resAtv[$t]['descricao'] ?>"/></th>
                                <th><input class="atividade" <?= $disabled ?> type="number" style="width: 60px" size="3" maxlength="2" id="AtvA<?= $t ?>" name="AtvA<?= $t ?>" value="<?= $resAtv[$t]['aulas'] ?>"/></th>
                                <th>
                                    <select class="atividade" id="AtvR<?= $t ?>" <?= $disabled ?> name="AtvR<?= $t ?>" >
                                        <option></option>
                                        <option <?= ($resAtv[$t]['referencia']==1) ? "selected" : ""?> value="1">1º Sem.</option>
                                        <option <?= ($resAtv[$t]['referencia']==2) ? "selected" : ""?> value="2">2º Sem.</option>
                                        <option <?= ($resAtv[$t]['referencia']==3) ? "selected" : ""?> value="3">1ºe2º Sem.</option>
                                        <option <?= ($resAtv[$t]['referencia']==4) ? "selected" : ""?> value="4">Anual</option>
                                        <?php
                                        ?>
                                    </select>
                                </th>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th align="right">Atividades de Apoio ao Ensino (Total em horas)</th>
                            <th colspan="2" id="atvEnsino">&nbsp;</th>
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
                        <tr>
                            <th>Nome</th>
                            <th>Duração(h)</th>
                            <th>Referência</th>
                        </tr>
                        <?php
                        for ($t = 0; $t <= 6; $t++) {
                            ?>
                            <tr>
                                <th><input class="complementacao" <?= $disabled ?> type="text" onfocus="return valores('complementacao','CompD<?= $t ?>');" size="60" maxlength="200" id="CompD<?= $t ?>" name="CompD<?= $t ?>" value="<?= $resComp[$t]['descricao'] ?>"/></th>
                                <th><input class="complementacao" <?= $disabled ?> type="number" style="width: 60px" size="3" maxlength="2" id="CompA<?= $t ?>" name="CompA<?= $t ?>" value="<?= $resComp[$t]['aulas'] ?>"/></th>
                                <th>
                                    <select class="complementacao" id="CompR<?= $t ?>" <?= $disabled ?> name="CompR<?= $t ?>" >
                                        <option></option>
                                        <option <?= ($resComp[$t]['referencia']==1) ? "selected" : ""?> value="1">1º Sem.</option>
                                        <option <?= ($resComp[$t]['referencia']==2) ? "selected" : ""?> value="2">2º Sem.</option>
                                        <option <?= ($resComp[$t]['referencia']==3) ? "selected" : ""?> value="3">1ºe2º Sem.</option>
                                        <option <?= ($resComp[$t]['referencia']==4) ? "selected" : ""?> value="4">Anual</option>
                                        <?php
                                        ?>
                                    </select>
                                </th>                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th align="right">Complementa&ccedil;&atilde;o de Atividades (Total em horas)</th>
                            <th colspan="2" id="compAtv">&nbsp;</th>
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
            
             <div class="cont_tab" id="Dados1">
                <font size="2"><b>Alterações em relação ao(s) PIT(s) (Justificativas)</b></font>
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

        </form>
    </div>
    <table style="width: 865px" id="tabela_boletim">
        <tr>
            <th align="right">Total de horas semanais (obrigatoriamente 20h ou 40h, dependendo do regime de trabalho)</th>
            <th id="TH">&nbsp;</th>
        </tr>
    </table>    

</center >
<?php
$hor1 = explode(',', $horario1);
$hor2 = explode(',', $horario2);
$hor3 = explode(',', $horario3);
    }
?>
<script>
    var totalCelulas = 0;
    var totalHoras = 0;
    callFunction();
    desabilitarSalvar();

    function calcComponente() {
        total = 0;
        componentes = 0;
        if ('<?= $duracaoAula ?>') {
            for (i = 0; i <= 9; i++) {
                if ($("#A" + i).val()) {
                    total += parseInt($("#A" + i).val());
                    componentes++;
                }
            }
            totalAulas = (total * '<?= substr($duracaoAula, 3, 2) ?>') / 60;

            var adicional = 0; // carga adicional para mais de 4 componentes curriculares
            if (componentes > 4) {
                adicional = (componentes - 4);
            }
            
            $("#regencia").html(Math.round(totalAulas));
            $("#ensino").html(Math.round(totalAulas + adicional));
            totalHoras += (Math.round(totalAulas) * 2) + adicional;
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

        if (totalHoras != maxCH){
            $("#obsCH").html('Carga horária final incompatível com a jornada de trabalho de ' + maxCH + 'h indicada, favor corrigir!');
            desabilitarEnviar();
        }
        else{
            $("#obsCH").html('');
            $("#obsCELL").html('');
            habilitarEnviar();
        }
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
        validar();
    }

    $("#horario,.componente,.atividade,.complementacao").on('keyup change',function () {
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
    
    function valores(flag,campo) {
        if (flag == 'atividades'){
            var itens = [
                "Atendimento ao aluno",
                "Atendimento do NDE",
                "Atendimento pedagógicas",
                "Reunião de área",
                "Reunião de curso",
                "Recuperação paralela", 
                "Supervisão ou orientação de estágio",
                "Supervisão ou orientação de trabalhos acadêmicos"
            ];
        }
        else if (flag == 'horarios'){
            var itens = [
                <?php
                $n=sizeof($resC);
                for ($i=0; $i<$n; $i++){
                    $valor = $resC[$i]['sigla'];
                    if ($i<$n-1)
                        echo "\"$valor\",";
                    else
                        echo "\"$valor\"";
                }
                ?>
            ];
            
        }
        else if (flag == 'cursos'){
            var itens = [
                <?php
                require CONTROLLER . "/curso.class.php";
                $cursos1 = new Cursos();
                $c1 = $cursos1->listCursos();
                $n=sizeof($c1);
                for ($i=0; $i<$n; $i++){
                    $valor = $c1[$i]['codigo']."-".$c1[$i]['curso'];
                    if ($i<$n-1)
                        echo "\"$valor\",";
                    else
                        echo "\"$valor\"";
                }
                ?>
            ];
        }
        else if (flag == 'complementacao'){
            var itens =[
                "Projeto de Iniciação Científica (especificar)",
                "Projeto de Extensão (especificar)",
                "Coordenação de curso (especificar)",
                "Coordenação de área (especificar)",
                "Curso de capacitação (especificar)",
                "Comissão ou comitê (especificar)"                
            ];
        }
        $("#" + campo).autocomplete({
            source: itens,
            minLength: 0
        });
    }   
    
    $('.camposCursos').change(function (event) {
        var id = event.target.id.slice(-1);
        var cod = event.target.value.substring(0,event.target.value.indexOf('-'));
        
        $.getJSON('<?= VIEW ?>/secretaria/cursos/disciplina.php?search=', {codigo: cod, ajax: 'true'}, function (j) {
            var itens = [""];
            for (var i = 0; i < j.length; i++) {
                itens.push(j[i].nome+" ["+j[i].sigla+"]");
            }
            $("#N"+id).autocomplete({
                source: itens
            });
        });
    });

    $('.camposDisciplinas').change(function (event) {
        var cod = event.target.value.substring(event.target.value.indexOf(' [')+2,event.target.value.indexOf(']'));
        var id = event.target.id.slice(-1);
        $("#S"+id).val(cod);
    });      
    
    function desabilitarSalvar(){
        $("#salvar").prop( "disabled", true );
        $("#salvar").css("background-color", "gray");
    }
    
    function desabilitarEnviar(){
        $("#enviar").prop( "disabled", true );
        $("#enviar").css("background-color", "gray");
    }
    
    function habilitarSalvar(){
        $("#salvar").prop( "disabled", false );
        $("#salvar").css("background-color", "");
    }
    
    function habilitarEnviar(){
        if ($("#salvar").prop('disabled')==false){
            $("#enviar").prop( "disabled", false );
            $("#enviar").css("background-color", "");
        }
    }
    
    function validar(){   
        if ($('#obsCELL').html()!=="" || $('#obsCH').html()!=="")
            desabilitarEnviar();
        else{
            habilitarSalvar();        
            habilitarEnviar();        
        }
    }
</script>