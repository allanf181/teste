<?php
//Esse arquivo é fixo para o professor.
//Permite o registro da FPA do Docente.
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

require CONTROLLER . "/pessoa.class.php";
$pessoa = new Pessoas();

if ($_POST) {
    $_POST['modelo'] = 'FPA';
    $_POST['pessoa'] = $_SESSION['loginCodigo'];
    $_POST['semestre'] = dcrip($_POST['psemestre']);
    $_POST['ano'] = dcrip($_POST['pano']);

    $_GET['pano'] = $_POST['pano'];
    $_GET['psemestre'] = $_POST['psemestre'];
    unset($_POST['pano']);
    unset($_POST['psemestre']);

    if (!$_POST['regime'] || !$_POST['duracaoAula'] || !$_POST['area']) {
        mensagem('NOK', 'EMPTY_REG');
    } else {
        $ret = $dados->insertOrUpdateFPA($_POST);
//        debug($_POST);die();
        mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

        //ENVIANDO EMAIL
        if ($ret['STATUS'] == 'OK' && $_POST['enviar']) {
            if ($coodEmail = $coordenador->getEmailCoordFromArea(dcrip($_POST['area'])))
                $logEmail->sendEmailLogger($_SESSION['loginNome'], 'Docente enviou FPA para valida&ccedil;&atilde;o.', $coodEmail);
        }
    }
}

if ($_GET['pano'] && $_GET['psemestre']) {
    $pano = dcrip($_GET['pano']);
    $psemestre = dcrip($_GET['psemestre']);
} else {
    // TENTA BUSCAR O ULTIMO REGISTRO GRAVADO PELO USUARIO
    $sqlAdicional = ' AND p.codigo = :pessoa AND f.modelo = :modelo ORDER BY f.codigo DESC LIMIT 1 ';
    $params = array('pessoa' => $_SESSION['loginCodigo'], 'modelo' => 'FPA');
    $res = $dados->listModelo($params, $sqlAdicional, null, null);
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
$params = array('pessoa' => $_SESSION['loginCodigo'], 'ano' => $pano, 'semestre' => $psemestre, 'modelo' => 'FPA');
$res = $dados->listModelo($params, $sqlAdicional, null, null);
extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);

if (!$res) {
    $pessoa = new Pessoas();
    $resPessoa = $pessoa->listRegistros(array('codigo' => $_SESSION['loginCodigo']));
    $email = $resPessoa[0]['email'];
    $telefone = $resPessoa[0]['telefone'];
    $celular = $resPessoa[0]['celular'];
}

//LISTA COMPONENTES
$resC = $componente->listComponentes($codigo);
//echo "<br>$codigo";
//var_dump($resC);
//LISTA ATIVIDADES
$resAtv = $atvECmt->listAtvECmt($codigo, 'atv');
//LISTA COMPLEMENTACAO
$resComp = $atvECmt->listAtvECmt($codigo, 'cmp');

//VERIFICA SE ESTA FINALIZADO OU VALIDADO
if ($res[0]['finalizado'] && $res[0]['finalizado'] != '0000-00-00 00:00:00')
    $disabled = 'disabled';

if ($res[0]['valido'] && $res[0]['valido'] != '0000-00-00 00:00:00')
    $VALIDO = 1;

$paramsLog['codigoTabela'] = $codigo;
$paramsLog['nomeTabela'] = 'FPA';
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
    mensagem('OK', 'VALID_FORM');
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<br />
<center>
    <div id="html5form" class="main">
        <form id="form_padrao">
            <input type="hidden" value="<?= $codigo ?>" name="codigo" id="codigo" />
            <font size="3">
            <b>  
                Instituto Federal de Educação, Ciência e Tecnologia de São Paulo - IFSP
                Formulário de Preferência de Atividades - FPA (Anexo I - Resolução nº 109 de 4 de novembro de 2015)
                <br> <?= $psemestre ?>&ordm; semestre <?= $pano ?>
            </b></font>
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
                        <th><input type="text" disabled style="width: 264pt" id="nome"ampoN name="nome" maxlength="45" value="<?= $_SESSION["loginNome"] ?>" /></th>
                        <th>&Aacute;rea: </th>
                        <th>
                            <select name="area" id="area" value="<?= $area ?>" <?= $disabled ?> style="width: 264pt">
                                <option></option>
                                <?php
                                require CONTROLLER . "/area.class.php";
                                $areas = new Areas();
                                foreach ($areas->listRegistros(null, ' WHERE codigo IN (SELECT area FROM Coordenadores) ORDER BY nome ', null, null) as $reg) {
                                    $selected = "";
                                    if ($reg['codigo'] == $codArea)
                                        $selected = "selected";
                                    print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                                }
                                ?>
                            </select>
                        </th>
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
                            <a href="<?= VIEW ?>/secretaria/relatorios/inc/fpa.php?professor=<?= crip($_SESSION['loginCodigo']) ?>&pano=<?= crip($pano) ?>&psemestre=<?= crip($psemestre) ?>" target="_blank">
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

                        require CONTROLLER . "/horario.class.php";
                        $horarios = new horarios();
                        $hor1 = $horarios->listHorarios();
                        $inicio = array("","","","");
                        foreach ($hor1 as $h => $hh){
                            if (empty($tempoDuracaoAula))
                                $tempoDuracaoAula="00:".(date("i",strtotime($hh['fim']))-date("i",strtotime($hh['inicio'])));
                            $hinicio = strtotime($hh['inicio']);
                            for ($i=1; $i<=3; $i++){
                                if (strpos($hh[1],substr($periodo[$i], 0, 1))){
                                    if ($inicio[$i]=="")
                                        $inicio[$i]=$hh['inicio'];
                                    if ($hinicio>$hfim){
                                        $tempoIntervalo[$i]=date("i",$hinicio-$hfim);
                                        $intervalo[$i]=$tempIntervalo;
                                        
                                    }
                                }
                            }
                            $hfim=strtotime($hh['fim']);
                            $tempIntervalo=$hh['fim'];
                        }                        
                        
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
                                    <td class="celula_disponibilidade"><input id="CE<?= $IS ?>" <?= $disabled ?> name="horario[]" value="<?= $IS ?>" type="checkbox" class="celulas" <?php if (in_array($IS, $horarios)) print 'checked'; ?> /></td>
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
                        <tr style="display: none">
                            <td>
                                Dura&ccedil;&atilde;o da Aula no Campus:<br />
                                <input type="radio" <?= $disabled ?> id="duracaoAula" <?php if ($duracaoAula == '00:45' || (isset($tempoDuracaoAula) && $tempoDuracaoAula=='00:45')) print 'checked'; ?> name="duracaoAula" value="00:45" /><font size="1">45min</font><br />
                                <input type="radio" <?= $disabled ?> id="duracaoAula" <?php if ($duracaoAula == '00:50' || (isset($tempoDuracaoAula) && $tempoDuracaoAula=='00:50')) print 'checked'; ?> name="duracaoAula" value="00:50" /><font size="1">50min</font>
                            </td>
                        </tr>
                        <tr style="display: none"><td><hr /></td></tr>
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
                                <table border="0" style="display: none">
                                    <?php
                                    for ($p = 1; $p <= 3; $p++) {
                                        $var = 'horario' . $p;
                                        $hor = explode(',', $$var);
                                        $t = 'h' . $p;
                                        $$t = $hor[2];
                                        ?>
                                        <tr>
                                            <td>
                                                <font size="1">Dura&ccedil;&atilde;o do intervalo no per&iacute;odo <?= $periodo[$p] ?>:</font>
                                            </td>
                                            <td>
                                                <select id="Intervalo<?= $p ?>" <?= $disabled ?> name="Intervalo<?= $p ?>">
                                                    <?php
                                                    for ($i = 5; $i <= 30; $i++) {
                                                        $n = str_pad($i, 2, "0", STR_PAD_LEFT);
                                                        ?>
                                                        <option <?php if ($hor[0] == "00:$n" || "00:".$tempoIntervalo[$p] == "00:$n") print 'selected'; ?> value="00:<?= $n ?>">00:<?= $tempoIntervalo[$p] ?></option>
                                                        <?php
                                                        $i+=4;
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <font size="1">Que horas representa o n&uacute;mero 1 no per&iacute;odo <?= $periodo[$p] ?>:</font>
                                            </td>
                                            <td>
                                                <input type="text" id="Periodo<?= $p ?>" <?= $disabled ?> name="Periodo<?= $p ?>" size="3" value="<?= $inicio[$p] ?>" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <font size="1">Que horas come&ccedil;a o intervalo neste per&iacute;odo <?= $periodo[$p] ?>:</font>
                                            </td>
                                            <td>
                                                <input style="width: 35px" type="text"id="IniIntervalo<?= $p ?>" <?= $disabled ?> name="IniIntervalo<?= $p ?>" value="<?= $intervalo[$p] ?>" />
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
                <font size="2"><b>Componentes curriculares de interesse do docente</b></font>
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
                            <th>Prioridade</th>
                        </tr>
                        <?php
                        for ($t = 0; $t <= 9; $t++) {
                            ?>
                            <tr>
                                <th>
                                    <input class="componente camposCursos" type="text" onfocus="return valores('cursos', 'C<?= $t ?>');" <?= $disabled ?> size="30" maxlength="145" id="C<?= $t ?>" name="C<?= $t ?>" value="<?= $resC[$t]['curso'] ?>"/>
                                </th>
                                <th><input class="componente camposDisciplinas" type="text"  <?= $disabled ?> size="30" maxlength="45" id="N<?= $t ?>" name="N<?= $t ?>" value="<?= $resC[$t]['nome'] ?>"/></th>
                                <th><input class="componente" type="text"  <?= $disabled ?> size="5" maxlength="45" id="S<?= $t ?>" name="S<?= $t ?>" value="<?= $resC[$t]['sigla'] ?>"/></th>
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
                                    <select class="componente" id="Pr<?= $t ?>" <?= $disabled ?> name="Pr<?= $t ?>" >
                                        <option></option>
                                        <option <?= $resC[$t]['prioridade']=="1" ? "selected" : ""?> value="1">Prioritária</option>
                                        <option <?= $resC[$t]['prioridade']=="2" ? "selected" : ""?> value="2">Secundária</option>
                                        <?php
                                        ?>
                                    </select>
                                </th>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th colspan="4" align="right">Quantidade de aulas consideradas prioritárias</th>
                            <th colspan="2" id="qtdPrioritarias">&nbsp;</th>
                        </tr>
                        <tr>
                            <th colspan="4" align="right">Total aulas</th>
                            <th colspan="2" id="aulas">&nbsp;</th>
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
                    <tr>
                            <th>Nome</th>
                            <th>Duração(h)</th>
                    </tr>
                        <?php
                        for ($t = 0; $t <= 6; $t++) {
                            ?>
                            <tr>
                                <th><input class="atividade ui-widget" <?= $disabled ?> type="text" onfocus="return valores('atividades','AtvD<?= $t ?>');" size="60" maxlength="200" id="AtvD<?= $t ?>" name="AtvD<?= $t ?>" value="<?= $resAtv[$t]['descricao'] ?>"/></th>
                                <th><input class="atividade" <?= $disabled ?> type="number" style="width: 60px" size="3" maxlength="2" id="AtvA<?= $t ?>" name="AtvA<?= $t ?>" value="<?= $resAtv[$t]['aulas'] ?>"/></th>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th align="right">Atividades de Apoio ao Ensino (Total em horas)</th>
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
                    <tr>
                            <th>Nome</th>
                            <th>Duração(h)</th>
                    </tr>
                        <?php
                        for ($t = 0; $t <= 6; $t++) {
                            ?>
                            <tr>
                                <th><input class="complementacao" <?= $disabled ?> onfocus="return valores('complementacao','CompD<?= $t ?>');" type="text" size="60" maxlength="200" id="CompD<?= $t ?>" name="CompD<?= $t ?>" value="<?= $resComp[$t]['descricao'] ?>"/></th>
                                <th><input class="complementacao" <?= $disabled ?> type="number" style="width: 60px" size="3" maxlength="2" id="CompA<?= $t ?>" name="CompA<?= $t ?>" value="<?= $resComp[$t]['aulas'] ?>"/></th>
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

    
    
</center >
<script>
    var totalCelulas = 0;
    var totalHoras = 0;
    callFunction();
    calcIntervalo();
    desabilitarSalvar();

    function calcComponente() {
        total = 0;
        componentes = 0;
        prioridades = 0;
        if ($("input[id=duracaoAula]:radio:checked").val()) {
            for (i = 0; i <= 9; i++) {
                if ($("#A" + i).val()) {
                    total += parseInt($("#A" + i).val());
                    componentes++;
                    if ($("#Pr" + i).val()==1) // PRIORIDADE
                        prioridades += parseInt($("#A" + i).val());
                }
            }
            totalAulas = (total * $("input[id=duracaoAula]:radio:checked").val().substring(5, 3)) / 60;
//            totalAulas = total;

            var adicional = 0; // carga adicional para mais de 4 componentes curriculares
            if (componentes > 4) {
                adicional = (componentes - 4);
            }
            $("#qtdPrioritarias").html(Math.round(prioridades));
            $("#aulas").html(Math.round(totalAulas));

            totalHoras += (Math.round(totalAulas) * 2) + adicional;
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
                        // permitindo escolher mais do que o limite mínimo
//                        $("#obsCELL").html('Número máximo de células ultrapassado!');
//                        desabilitarEnviar();
                    }
                    if (((totalCelulas - celulasSel) - 1) > 0) {
                        $("#obsCELL").html('Falta selecionar ' + ((totalCelulas - celulasSel) - 1) + ' células!');
                        desabilitarEnviar();
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

        if (totalHoras < maxCH){ // permite valores maiores que a CH max para facilitar ao coordenador
            $("#obsCH").html('Carga horária final ['+totalHoras+'] incompatível com a jornada de trabalho de ' + maxCH + 'h indicada, favor corrigir!');
        }
        else{
            $("#obsCH").html('');
        }
        $("#ministrar").html(ministrar);
        $("#celulas").html(celulas);
        totalCelulas = celulas;
    }

    function calcIntervalo() {
        for (p = 1; p <= 3; p++) {
            ini = $("#Periodo" + p).val();
            vl = ini;
            if ($("input[id=duracaoAula]:radio:checked").val() && ini) {
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
        validar();
        //calcIntervalo()
    }

    $("input[id=duracaoAula]:radio").change(function () {
        if (!$("input[id=regime]:radio:checked").val() && $("#pano").val() && $("#psemestre").val()) {
            mensagem('Selecione um Regime de Trabalho primeiro!!!');
            $(this).removeAttr('checked');
            return false;
        }
    });

    $("input:text,.componente,.atividade,.complementacao,#Periodo1,#Periodo2,#Periodo3").on('keyup change',function () {
        callFunction();
    });

    $("#IniIntervalo1,#IniIntervalo2,#IniIntervalo3,#Intervalo1,#Intervalo2,#Intervalo3").on('keyup change',function () {
        callFunction();
    });

    $("input:checkbox,input:radio,#dedicarEnsino").click(function () {
        callFunction();
    });

    $("#Periodo1,#Periodo2,#Periodo3").keyup(function () {
        $('#IniIntervalo1').find('option').remove();
        $('#IniIntervalo2').find('option').remove();
        $('#IniIntervalo3').find('option').remove();
        calcIntervalo();
    });

    $("#subHorario").click(function () {
        callPeriodo('Favor preencher todos os campos com os horários!');
        calcIntervalo();
    });

    $('#pano, #psemestre').change(function () {
        var pano = $('#pano').val();
        var psemestre = $('#psemestre').val();
        $('#index').load('<?= $SITE ?>?pano=' + pano + '&psemestre=' + psemestre);
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
            url: '<?= $SITE ?>',
            type: 'POST',
            data: {enviar: enviar},
        };

        $(this).ajaxSubmit(options);
        return false;
    });

    $("#enviar").click(function (event) {
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
            source: itens
        });
    }
    
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
        if ($("#telefone").prop('disabled')==false){
            $("#enviar").prop( "disabled", false );
            $("#enviar").css("background-color", "");
        }
    }
    
    function validar(){   
        if ($('#obsCELL').html()!=="" || $('#obsCH').html()!=="") 
            desabilitarEnviar();
        else
            habilitarEnviar();
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

    // HABILITA O BOTAO SALVAR CASO TENHA PREENCHIDO OS CAMPOS
    $('#area, #regime, .celulas, .componente, .atividade, .complementacao').change(function (event) {
        if ($('#area').val()!="" && $('#regime:checked').length>0){ 
            habilitarSalvar();
        }
    });    
    
    $(".celula_disponibilidade").click(function(event){
        if (event.target.type !== 'checkbox')
            $(this).find("input").trigger('click')
        checkCelulas();
    });
    
</script>