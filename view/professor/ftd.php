<?php
//Esse arquivo é fixo para o professor.
//Permite o registro da Folha de Trabalho Docente.
//Link visível no menu: PADRÃO SIM.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/ensalamento.class.php";
$ensalamento = new Ensalamentos();

require CONTROLLER . "/ftdDado.class.php";
$ftd = new FTDDados();

if ($_GET["dte"] && $_GET["dts"]) {

    $_GET['semestre'] = $SEMESTRE;
    $_GET['ano'] = $ANO;
    $_GET['professor'] = $_SESSION['loginCodigo'];

    $ret = $ftd->insertOrUpdateFTD($_GET);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}

?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<?php

// BUSCANDO NO BANCO, CASO O PROFESSOR JA TENHA SALVO A FTD
$res = $ftd->getDadosFTD($_SESSION['loginCodigo'], $ANO, $SEMESTRE);

$disabled = '';
$VALIDO = 0;
$codigo=null;

if ($res) {
    print "<script>\n";
    foreach($res as $reg) {
        $telefone = $reg['telefone'];
        $celular = $reg['celular'];
        $email = $reg['email'];
        $area = $reg['area'];
        $regime = $reg['regime'];
        $observacao = $reg['observacao'];
        $codigo = $reg['codigo'];

        print " $('#" . $reg['registro'] . "').text('" . $reg['horario'] . "'); \n";

        if ($reg['finalizado'] && $reg['finalizado'] != '0000-00-00 00:00:00')
            $disabled = 'disabled';
        
        if ($reg['valido'] && $reg['valido'] != '00/00/0000 00:00')
            $VALIDO = 1;

        $solicitacao = $reg['solicitacao'];
        $solicitante = $reg['solicitante'];
    }
    print "</script>\n";

    if ($solicitacao) {
        $OPT[0] = $solicitante;
        $OPT[1] = $solicitacao;
        mensagem('INFO', 'INVALID_FTD', $OPT);
        $disabled = '';
    }
    
    if (!$VALIDO && $disabled)
        mensagem('OK', 'FINISH_FTD');
    
    if ($VALIDO) {
        mensagem('OK', 'VALID_FTD', $solicitante);
    }
} else {
    mensagem('OK', 'NOT_SAVE_FTD');

    // CASO O PROFESSOR NAO TENHO FEITO A FTD, O SISTEMA IMPORTA DE ENSALAMENTOS.
    $res = $ensalamento->getEnsalamentosFTD($_SESSION['loginCodigo'], $ANO, $SEMESTRE);
    if ($res) {
        print "<script>\n";
        $P = 0;
        $PT = 1;
        $CT = 0;
        $i = 0;
        foreach ($res as $reg) {
            $email = $reg['email'];
            $celular = $reg['celular'];
            $telefone = $reg['telefone'];

            preg_match('#\[(.*?)\]#', $reg['horario'], $match);
            if ($match[1] != $PT) {
                $P++;
                $L = 0;
            }
            $PT = $match[1];

            $C = $reg['diaSemana'] - 1; //COLUNA

            if ($C != $CT) {
                $L = 0;
                $P = 1;
            }
            $CT = $C;

            if ($reg['fim'] == @$res[$i + 1]['ini']) {
                if (!$INI)
                    $INI = $reg['ini'];
            } else {
                $L++;
                if (!$INI)
                    $INI = $reg['ini'];
                $R = $P . '' . $L . '' . $C . '1';
                print " $('#" . $R . "').text('" . $INI . "'); \n";

                $R = $P . '' . $L . '' . $C . '2';
                print " $('#" . $R . "').text('" . $reg['fim'] . "'); \n";
                $INI = '';
            }
            $i++;
        }
        print "</script>\n";
    }
}
?>
<font size="1" color="red">* Clique sobre o quadro que deseja inserir a hora e tecle ENTER para confirmar.</font>
<br><br>
<center>
    <font size="3"><b>FTD - FOLHA DE TRABALHO DOCENTE <br> <?= $SEMESTRE ?>&ordm; semestre <?= $ANO ?> </b></font>
    <table width="80%" border="0" summary="FTD" id="tabela_boletim">
        <thead>
            <tr>
                <th>Professor: </th>
                <th><input type="text" disabled style="width: 227pt" id="nome"ampoN name="nome" maxlength="45" value="<?= $_SESSION["loginNome"] ?>" /></th>
                <th>&Aacute;rea: </th>
                <th><input type="text" <?= $disabled ?> style="width: 227pt" id="area" name="area" maxlength="45" value="<?= $area ?>"/></th>
            </tr>
            <tr>
                <th>Prontu&aacute;rio: </th>
                <th><input type="text" disabled style="width: 227pt" id="prontuario" name="prontuario" maxlength="45" value="<?= $_SESSION["loginProntuario"] ?>" /></th>
                <th>Email: </th>
                <th><input type="text" <?= $disabled ?> style="width: 227pt" id="email" name="email" maxlength="100" value="<?= $email ?>" /></th>
            </tr>
            <tr>
                <th>Telefone: </th><th><input type="text" <?= $disabled ?> style="width: 227pt" id="telefone" name="telefone" maxlength="45" value="<?= $telefone ?>" /></th>
                <th>Celular: </th><th><input type="text" <?= $disabled ?> style="width: 227pt" id="celular" name="celular" maxlength="45" value="<?= $celular ?>" /></th>
            </tr>
            <tr>
                <th colspan="2">Regime de Trabalho: </th><th colspan="2">
                    <?php
                    $regimes = Array('20H', '40H', 'RDE', 'Substituto', 'Temporario');
                    foreach ($regimes as $r) {
                        $checked = '';
                        if ($regime == $r)
                            $checked = 'checked';
                        ?>
                        <input type="radio" <?= $disabled ?> id="regime" <?= $checked ?> name="regime" value="<?= $r ?>" /><?= $r ?> &nbsp;
                        <?php
                    }
                    ?>
                </th>
            </tr>
    </table>
    <table width="80%" border="0" summary="FTD" id="tabela_boletim">
        <thead>
            <tr align="right">
                <th colspan="8">
                    <img style="width: 30px" src="<?= ICONS ?>/icon-printer.gif" title="Imprimir em PDF" />
                <th colspan="3" align="left">
                    <a href="<?= VIEW ?>/secretaria/relatorios/inc/ftd.php?professor=<?= crip($_SESSION['loginCodigo']) ?>" target="_blank">
                        <span style='font-weight: bold; color: white'>Resumida</span>
                    </a>
                    <br><a href="<?= VIEW ?>/secretaria/relatorios/inc/ftd.php?detalhada=1&professor=<?= crip($_SESSION['loginCodigo']) ?>" target="_blank">
                        <span style='font-weight: bold; color: white'>Detalhada</span>
                    </a>
                </th>
                <th colspan="4" align="center">
                    <input type="submit" <?= $disabled ?> style="width: 50px;" value="Salvar" id="salvar">
                    &nbsp;&nbsp;&nbsp;
                    <input type="submit" style="width: 50px" <?= $disabled ?> value="Enviar" id="enviar">
                </th>
            </tr>
            <?php
            $dias = diasDaSemana();
            $dias[0] = '&ordm; PER&Iacute;ODO';
            $dias[8] = 'TOTAL';
            unset($dias[1]);

            $atividade[7] = 'Aula';
            $atividade[13] = 'Aula';
            $atividade[19] = 'Atendimento';
            $atividade[25] = 'Dedução - 270';
            $atividade[31] = 'Reuniões';
            $atividade[37] = 'Projeto Interno';
            $atividade[43] = 'Projeto Externo';
            $atividade[49] = 'Complement. Aula';

            ksort($dias);
            ?>
            <tr>
                <?php
                foreach ($dias as $dCodigo => $dNome) {
                    $col = (!$dCodigo) ? 1 : 2;
                    if (!$dCodigo)
                        $dNome = '1' . $dNome;
                    ?>
                    <th colspan="<?= $col ?>">
                        <span style='font-weight: bold; color: white'><?= $dNome ?></span>
                    </th>
                    <?php
                }
                ?>
            </tr>

            <tr align="center">
                <?php
                foreach ($dias as $dCodigo => $dNome) {
                    if ($dCodigo == 0) {
                        ?>
                        <th>
                            <span style='font-weight: bold; color: white'>ATIVIDADES</span>
                        </th>
                        <?php
                    } elseif ($dCodigo == 8) {
                        ?>
                        <th colspan="2">
                            <span style='font-weight: bold; color: white'>PER&Iacute;ODO</span>
                        </th>
                        <?php
                    } else {
                        ?>
                        <th>E</th>
                        <th>S</th>
                        <?php
                    }
                }
                ?>
            </tr>
        </thead>
        <?php
        for ($p = 1; $p <= 2; $p++) {
            ?>
            <tr align="center">
                <th>Aula</th>
                <?php
                $c = 1;
                $l = 1;
                for ($i = 1; $i <= 54; $i++) { // LINHAS DA TABELA
                    if ($c >= 7) {
                        ?>
                    <tr align="center">
                        <th><?= $atividade[$i] ?></th>
                        <?php
                        $c = 1;
                    }
                    $IE = $p . $l . $c . '1';
                    $IS = $p . $l . $c . '2';
                    ?>
                    <td id="<?= $IE ?>"></td>
                    <td id="<?= $IS ?>"></td>
                    <?php
                    if ($c == 6) { //TOTAL PERÍODO
                        ?>
                        <th colspan="2" id="<?= $p ?>T<?= $l ?>"></th>
                    </tr>
                    <?php
                    $l++;
                }
                $c++;
            }
            ?>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <?php
                for ($c = 1; $c <= 6; $c++) {
                    $TDP = $p . 'TDP' . $c;
                    ?>
                    <th id="<?= $TDP ?>" colspan="2"></th>
                    <?php
                }
                ?>
                <th colspan="2" id="<?= $p ?>TP"></th>
            </tr>
            <?php
            if ($p == 1) {
                ?>
                <tr>
                    <th colspan="15">&nbsp;</th>
                </tr>
                <tr>
                    <?php
                    foreach ($dias as $dCodigo => $dNome) {
                        $col = (!$dCodigo) ? 1 : 2;
                        if (!$dCodigo)
                            $dNome = '2' . $dNome;
                        ?>
                        <th colspan="<?= $col ?>">
                            <span style='font-weight: bold; color: white'><?= $dNome ?></span>
                        </th>
                        <?php
                    }
                    ?>
                </tr>
                <tr>
                    <?php
                    foreach ($dias as $dCodigo => $dNome) {
                        if ($dCodigo == 0) {
                            ?>
                            <th>
                                <span style='font-weight: bold; color: white'>ATIVIDADES</span>
                            </th>
                            <?php
                        } elseif ($dCodigo == 8) {
                            ?>
                            <th colspan="2">
                                <span style='font-weight: bold; color: white'>PER&Iacute;ODO</span>
                            </th>
                            <?php
                        } else {
                            ?>
                            <th>E</th>
                            <th>S</th>
                            <?php
                        }
                    }
                    ?>
                </tr>
                <?php
            }
        }
        ?>
        <tr>
            <th colspan="15">&nbsp;</th>
        </tr>
        <tr>
            <th>TOTAL DI&Aacute;RIO</th>
            <?php
            //TOTAL DIARIO
            for ($i = 1; $i <= 6; $i++) {
                $TD = 'TD' . $i;
                ?>
                <th id="<?= $TD ?>" colspan="2"></th>
                <?php
            }
            ?>
            <th colspan="2" id="T"></th>
        </tr>

        <tr>
            <th colspan="15">&nbsp;</th>
        </tr>
        <tr>
            <?php
            //INTERVALO
            foreach ($dias as $dCodigo => $dNome) {
                $col = (!$dCodigo) ? 1 : 2;
                if (!$dCodigo)
                    $dNome = 'INTERVALO';
                if ($dCodigo < 8) {
                    ?>
                    <th colspan="<?= $col ?>">
                        <span style='font-weight: bold; color: white'><?= $dNome ?></span>
                    </th>
                    <?php
                } elseif ($dCodigo == 8) {
                    ?>
                    <th colspan="2">&nbsp;</th>
                    <?php
                }
            }
            ?>
        </tr>
        <tr>
            <th rowspan="2">&nbsp;</th>
            <?php
            foreach ($dias as $dCodigo => $dNome) {
                if ($dCodigo < 7) {
                    ?>
                    <th>E</th>
                    <th>S</th>
                    <?php
                } elseif ($dCodigo == 8) {
                    ?>
                    <th colspan="2">&nbsp;</th>
                    <?php
                }
            }
            ?>
        </tr>
        <tr>
            <?php
            for ($i = 1; $i <= 6; $i++) {
                $TE = 'IE' . $i;
                $TS = 'IS' . $i;
                ?>
                <th id="<?= $TE ?>" width="40"></th>
                <th id="<?= $TS ?>" width="40"></th>
                <?php
            }
            ?>
            <th colspan="2"></th>
        </tr>
    </table>
    <?php
    $atividade[1] = 'Aula';
    $atividade[2] = 'Atendimento';
    $atividade[3] = 'Dedução - 270';
    $atividade[4] = 'Reunião de Área';
    $atividade[5] = 'Projeto Interno';
    $atividade[6] = 'Projeto Externo';
    $atividade[7] = 'Complemen. Aula';
    $atividade[8] = 'Dedução Intervalos';
    ?>
    <br><table width='100%' border='0' summary='FTD' id='tabela_boletim'>
        <tr><th>
        <table width='70%' border='0'>
            <tr align='center'>
                <th>
                    <span style='font-weight: bold; color: white'>Atividade</span>
                </th>
                <th>
                    <span style='font-weight: bold; color: white'>Horas/Semana</span>
                </th>
            </tr>
            <?php
            for ($a = 1; $a <= 8; $a++) {
                ?>
                <tr align='left'>
                    <th><?= $atividade[$a] ?></th>
                    <th align='center' id='A<?= $a ?>'></th>
                </tr>
                <?php
            }
            ?>
            <th>
                <span style='font-weight: bold; color: white'>Carga Hor&aacute;ria Total</span>
            </th>
            <th align=center id='AT'></th>
        </table>
    </th><th>
<table width='100%' border='0'>
    <tr align='left'>
        <th>Atividade Docente</th>
        <th align='center' id='AtvDocente'></th>
    </tr>
    <tr align='left'>
        <th>Projetos</th>
        <th align='center' id='Projetos'></th>
    </tr>
    <tr align='left'>
        <th>Dedu&ccedil;&atilde;o Intervalos</th>
        <th align='center' id='Intervalos'></th>
    </tr>
    <tr align='left'>
        <th>Total</th>
        <th align='center' id='Total'></th>
    </tr>
</table>
</tr>
<tr>
    <th colspan='2'>&nbsp</th>
</tr>
<tr>
    <th colspan='2' valign=top>Incluir Observa&ccedil;&atilde;o: 
        <textarea rows='2' cols='73' maxlength='200' id='observacao' name='observacao'><?=$observacao?></textarea>
    </th>
</tr>
</table>
</center >

<script>
<?php if (!$disabled) { ?>
        $(function() {
            $("td").click(function() {
                var conteudoOriginal = $(this).text();
                $(this).addClass("celulaEmEdicao");
                $(this).html("<input type='text' id='celulaEmEdicao' size='3' value='" + conteudoOriginal + "' />");
                $("#celulaEmEdicao").mask("99:99");
                $(this).children().first().focus();

                $(this).children().first().keypress(function(e) {
                    if (e.which == 13) {
                        var novoConteudo = $(this).val();
                        var test = novoConteudo.split(":");
                        if (test != '__,__') {
                            if (test[0] < 24 && test[1] < 60) {
                                $(this).parent().text(novoConteudo);
                                $(this).parent().removeClass("celulaEmEdicao");
                                clean();
                                calcDiario();
                                calcPeriodo();
                                checkRegras();
                            }
                        } else {
                            $(this).parent().text('');
                            $(this).parent().removeClass("celulaEmEdicao");
                            clean();
                            calcDiario();
                            calcPeriodo();
                            checkRegras();
                        }
                    }
                });
                $(this).children().first().blur(function() {
                    $(this).parent().text(conteudoOriginal);
                    $(this).parent().removeClass("celulaEmEdicao");
                });
            });
        });
<?php } ?>

    function clean() {
        //PERIODOS
        for (p = 1; p <= 2; p++) {
            for (l = 1; l <= 9; l++) {
                var TL = p + 'T' + l;
                $("#" + TL).text('');
            }
        }

        //INTERVALOS E TOTAL DIARIO
        for (i = 1; i <= 6; i++) {
            TE = 'IE' + i;
            TS = 'IS' + i;
            $("#" + TE).text('');
            $("#" + TS).text('');

            $("#TD" + i).text('');
            $("#" + i + "TDP1").text('');
            $("#" + i + "TDP2").text('');
        }
    }

    function checkRegras() {
        // Intervalos menores que 1 horas
        for (i = 1; i <= 6; i++) {
            var IE = $("#IE" + i).text();
            var IS = $("#IS" + i).text();

            if (IE && IS) {
                var difI = subtime(IE, IS).split(":");
                if (difI[0] < 01) {
                    alert('INTERVALO MENOR QUE 1 HORA');
                    $("#IE" + i + ",#IS" + i).css({color: '#FF0000'});
                } else
                    $("#IE" + i + ",#IS" + i).css({color: '#000'});
            }
        }
        // Se o total é menor que 32 horas.
        var difT = subtime($("#Total").text(), '32:00').split(":");
        if (difT[0].match(/-/) || (difT[0] > 00 || (difT[0] == 00 && difT[1] > 00))) {
            $("#Total").css({color: '#FF0000'});
        } else
            $("#Total").css({color: '#000'});

        for (p = 1; p <= 2; p++) {
            for (c = 1; c <= 6; c++) {
                var TDP = $("#" + c + "TDP" + p).text();
                var difTDP = TDP.split(":");
                var erro = 0;

                if (difTDP[0] > 06 || (difTDP[0] == 06 && difTDP[1] > 00)) {
                    alert('MÁXIMO DE 6 HORAS NO PERÍODO');
                    erro = 1;
                }

                if (erro)
                    $("#" + p + "TDP" + c).css({color: '#FF0000'});
                else
                    $("#" + p + "TDP" + c).css({color: '#000'});
            }
        }

        // Verificando se há dias com mais de 8 horas, com excessão da quarta-feira.
        for (i = 1; i <= 6; i++) {
            var TD = $("#TD" + i).text();
            var difTD = TD.split(":");
            var erro = 0;

            if (i != 3 && (difTD[0] > 08 || (difTD[0] == 08 && difTD[1] > 00))) {
                alert('MÁXIMO DE 8 HORAS NO DIA');
                erro = 1;
            }

            if (i == 3 && (difTD[0] > 10 || (difTD[0] == 10 && difTD[1] > 00))) {
                alert('MÁXIMO DE 10 HORAS NA QUARTA-FEIRA');
                erro = 1;
            }
            if (erro)
                $("#TD" + i).css({color: '#FF0000'});
            else
                $("#TD" + i).css({color: '#000'});
        }
    }

    function calcIntervalo() {
        var TI = 0;
        for (p = 1; p <= 2; p++) {
            for (c = 1; c <= 6; c++) {
                var S1 = 0;
                var E2 = 0;
                var S2 = 0;
                var E3 = 0;
                var difer = 0;
                for (l = 1; l <= 3; l++) {
                    var IE = p + '' + l + '' + c + '1';
                    var IS = p + '' + l + '' + c + '2';
                    if ($("#" + IE).text() && $("#" + IS).text()) {
                        if (l == 1)
                            S1 = $("#" + IS).text();
                        if (l == 2) {
                            E2 = $("#" + IE).text();
                            if (S1 && E2) {
                                difer = subtime(S1, E2);
                                if (TI != 0)
                                    TI = addtime(TI, difer);
                                else
                                    TI = difer;
                                S2 = $("#" + IS).text();
                            }
                        }
                        if (l == 3) {
                            E3 = $("#" + IE).text();
                            if (S2 && E3) {
                                difer = subtime(S2, E3);
                                if (TI != 0)
                                    TI = addtime(TI, difer);
                                else
                                    TI = difer;
                            }
                        }
                    }
                }
            }
        }
        return TI;
    }

    function calcPeriodo() {
        var aula = 0;
        var atendimento = 0;
        var deducao = 0;
        var rArea = 0;
        var pInterno = 0;
        var pExterno = 0;
        var compAula = 0;
        var CH = 0;

        var T = 0; // total dos 2 periodos

        for (p = 1; p <= 2; p++) {
            var TP = 0; // var para --> Total Período
            for (l = 1; l <= 9; l++) {
                var diff_time = 0;
                var difer = 0;
                for (c = 1; c <= 6; c++) {
                    var l_temp = l;
                    var IE = p + '' + l + '' + c + '1';
                    var IS = p + '' + l + '' + c + '2';
                    if ($("#" + IE).text() && $("#" + IS).text()) {
                        difer = subtime($("#" + IE).text(), $("#" + IS).text());
                        var TL = p + 'T' + l;
                        if (l == l_temp) {
                            if (diff_time != 0)
                                diff_time = addtime(diff_time, difer);
                            else
                                diff_time = difer;
                        } else {
                            l_temp = l;
                            diff_time = 0;
                        }

                        if (TP != 0)
                            TP = addtime(TP, difer);
                        else
                            TP = difer;
                        $("#" + TL).text(diff_time);

                        // Pegando valores para Resumo
                        if (l == 1 || l == 2 || l == 3) {
                            if (aula != 0)
                                aula = addtime(aula, difer);
                            else
                                aula = difer;
                        }
                        if (l == 4) {
                            if (atendimento != 0)
                                atendimento = addtime(atendimento, difer);
                            else
                                atendimento = difer;
                        }
                        if (l == 5) {
                            if (deducao != 0)
                                deducao = addtime(deducao, difer);
                            else
                                deducao = difer;
                        }
                        if (l == 6) {
                            if (rArea != 0)
                                rArea = addtime(rArea, difer);
                            else
                                rArea = difer;
                        }
                        if (l == 7) {
                            if (pInterno != 0)
                                pInterno = addtime(pInterno, difer);
                            else
                                pInterno = difer;
                        }
                        if (l == 8) {
                            if (pExterno != 0)
                                pExterno = addtime(pExterno, difer);
                            else
                                pExterno = difer;
                        }
                        if (l == 9) {
                            if (compAula != 0)
                                compAula = addtime(compAula, difer);
                            else
                                compAula = difer;
                        }
                    }
                }
            }
            var IDTP = p + 'TP';
            $("#" + IDTP).text(TP);

            if (T != 0) // Total dos dois periodos
                if (TP != 0)
                    T = addtime(T, TP);
                else
                    T = T;
            else
                T = TP;
            $("#T").text(T);
        }

        // TABELA ATIVIDADES ABAIXO DA FTD
        $("#A1").text(aula);
        $("#A2").text(atendimento);
        $("#A3").text(deducao);
        $("#A4").text(rArea);
        $("#A5").text(pInterno);
        $("#A6").text(pExterno);
        $("#A7").text(compAula);

        var Intervalos = calcIntervalo();
        $("#Intervalos").text(Intervalos);

        $("#A8").text(Intervalos);

        if (CH != 0 && Intervalos != 0) {
            CH = addtime(CH, Intervalos);
        } else if (Intervalos != 0)
            CH = Intervalos;

        if (CH != 0 && aula != 0) {
            CH = addtime(CH, aula);
        } else if (aula != 0)
            CH = aula;

        if (CH != 0 && atendimento != 0)
            CH = addtime(CH, atendimento);
        else if (atendimento != 0)
            CH = atendimento;

        if (CH != 0 && deducao != 0)
            CH = addtime(CH, deducao);
        else if (deducao != 0)
            CH = deducao;

        if (CH != 0 && rArea != 0)
            CH = addtime(CH, rArea);
        else if (rArea != 0)
            CH = rArea;

        if (CH != 0 && pInterno != 0)
            CH = addtime(CH, pInterno);
        else if (pInterno != 0)
            CH = pInterno;

        if (CH != 0 && pExterno != 0)
            CH = addtime(CH, pExterno);
        else if (pExterno != 0)
            CH = pExterno;

        if (CH != 0 && compAula != 0)
            CH = addtime(CH, compAula);
        else if (compAula != 0)
            CH = compAula;

        $("#AT").text(CH);

        // TABELA LATERAL A ATIVIDADES, ABAIXO DA FTD
        var AtvDocente = 0;
        var Projetos = 0;

        var Total = 0;

        if (AtvDocente != 0 && aula != 0) {
            AtvDocente = addtime(AtvDocente, aula);
        } else if (aula != 0)
            AtvDocente = aula;

        if (AtvDocente != 0 && atendimento != 0)
            AtvDocente = addtime(AtvDocente, atendimento);
        else if (atendimento != 0)
            AtvDocente = atendimento;

        if (AtvDocente != 0 && deducao != 0)
            AtvDocente = addtime(AtvDocente, deducao);
        else if (deducao != 0)
            AtvDocente = deducao;

        if (AtvDocente != 0 && compAula != 0)
            AtvDocente = addtime(AtvDocente, compAula);
        else if (compAula != 0)
            AtvDocente = compAula;

        if (AtvDocente != 0 && rArea != 0)
            AtvDocente = addtime(AtvDocente, rArea);
        else if (rArea != 0)
            AtvDocente = rArea;

        if (Projetos != 0 && pInterno != 0)
            Projetos = addtime(Projetos, pInterno);
        else if (pInterno != 0)
            Projetos = pInterno;

        if (Projetos != 0 && pExterno != 0)
            Projetos = addtime(Projetos, pExterno);
        else if (pExterno != 0)
            Projetos = pExterno;

        $("#AtvDocente").text(AtvDocente);
        $("#Projetos").text(Projetos);

        //GERAR TOTAL
        if (Total != 0 && Intervalos != 0)
            Total = addtime(Total, Intervalos);
        else if (Intervalos != 0)
            Total = Intervalos;

        if (Total != 0 && AtvDocente != 0)
            Total = addtime(Total, AtvDocente);
        else if (AtvDocente != 0)
            Total = AtvDocente;

        if (Total != 0 && Projetos != 0)
            Total = addtime(Total, Projetos);
        else if (Projetos != 0)
            Total = Projetos;

        $("#Total").text(Total);
    }

    function calcDiario() {
        for (c = 1; c <= 6; c++) {
            var first_time = 0; // gerar intervalo
            var last_time = 0; // gerar intervalo
            var diff_time = 0;
            var P1 = 0;
            var P2 = 0;
            for (l = 1; l <= 9; l++) {
                for (p = 1; p <= 2; p++) {
                    var c_temp = c;
                    var IE = p + '' + l + '' + c + '1';
                    var IS = p + '' + l + '' + c + '2';
                    if ($("#" + IE).text() && $("#" + IS).text()) {

                        var d = subtime($("#" + IE).text(), $("#" + IS).text()); // Calcular o total de cada dia
                        // em cada periodo
                        if (p == 1) { //calculo o intervalo da entrada
                            if (first_time != 0) {
                                var diff_first = subtime(first_time, $("#" + IS).text());
                                if (!diff_first.match(/-/))
                                    first_time = $("#" + IS).text();
                            } else {
                                first_time = $("#" + IS).text();
                            }

                            // calculo total diario de cada periodo
                            if (P1 != 0)
                                P1 = addtime(P1, d);
                            else
                                P1 = d;
                            $("#1TDP" + c).text(P1);
                        }
                        if (p == 2) { //calculo o intervalo da saida
                            if (last_time != 0) {
                                var diff_last = subtime(last_time, $("#" + IE).text());
                                if (diff_last.match(/-/))
                                    last_time = $("#" + IE).text();
                            } else {
                                last_time = $("#" + IE).text();
                            }

                            // calculo total diario de cada periodo
                            if (P2 != 0)
                                P2 = addtime(P2, d);
                            else
                                P2 = d;
                            $("#2TDP" + c).text(P2);

                        }
                        // mostra os intervalos se tiver entrada e saida.
                        if (last_time && first_time) {
                            $("#IS" + c).text(last_time);
                            $("#IE" + c).text(first_time);
                        }

                        var difer = subtime($("#" + IE).text(), $("#" + IS).text());

                        if (difer.match(/-/)) { //verificando se a data esta invertida
                            $("#" + IE).text('');
                            $("#" + IS).text('');
                        }

                        var TD = 'TD' + c;
                        if (c == c_temp) {
                            if (diff_time != 0)
                                diff_time = addtime(diff_time, difer);
                            else
                                diff_time = difer;
                        } else {
                            c_temp = c;
                            diff_time = 0;
                        }
                        $("#" + TD).text(diff_time);
                    }
                }
            }
        }
    }

    function subtime(start, end) {
        if (start)
            start = start.split(":");
        if (end)
            end = end.split(":");
        var startDate = new Date(0, 0, 0, start[0], start[1], 0);
        var endDate = new Date(0, 0, 0, end[0], end[1], 0);
        var diff = endDate.getTime() - startDate.getTime();
        var hours = Math.floor(diff / 1000 / 60 / 60);
        diff -= hours * 1000 * 60 * 60;
        var minutes = Math.floor(diff / 1000 / 60);
        return (hours <= 9 ? "0" : "") + hours + ":" + (minutes <= 9 ? "0" : "") + minutes;
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

    $(document).ready(function() {
        calcDiario();
        calcPeriodo();
        checkRegras();

        $("#celular").mask("(99) 99999-9999");
        $("#telefone").mask("(99) 9999-9999");

        $("#enviar").click(function() {
            $.Zebra_Dialog('<strong>Deseja salvar sua FTD e enviar para seu coordenador? <br><br> A FTD ser&aacute; bloqueada, podendo ser desbloqueada somente pelo coordenador.</strong>', {
                'type': 'question',
                'title': '<?= $TITLE ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function(caption) {
                    salvar(1);
                }
            });
        });
        
        $("#salvar").click(function() {
            salvar(0);
        });
    });

    function salvar(tipo) {
        var DTE = [];
        var DTS = [];
        var TP = [];
        var TPT = [];
        var TD = [];
        var TDT = [];
        var ITE = [];
        var ITS = [];
        var A = [];
        var i = 0;
        var j = 0;
        var n = 0;
        for (p = 1; p <= 2; p++) {
            for (l = 1; l <= 9; l++) {
                for (c = 1; c <= 8; c++) {
                    var IE = p + '' + l + '' + c + '' + 1;
                    var IS = p + '' + l + '' + c + '' + 2;
                    if ($("#" + IE).text() != '')
                        DTE[i] = IE + '-' + $("#" + IE).text();
                    if ($("#" + IS).text() != '')
                        DTS[i] = IS + '-' + $("#" + IS).text();
                    if ($("#" + IE).text() != '' && $("#" + IS).text() != '')
                        i++;

                    if ($("#" + p + "TDP" + c).text() != '')
                        TD[p +''+ c] = p + 'TDP' + c + '-' + $("#" + p + "TDP" + c).text();

                    if ($("#TD" + c).text() != '')
                        TDT[c] = 'TD' + c + '-' + $("#TD" + c).text();

                    if ($("#IE" + c).text() != '')
                        ITE[c] = 'IE' + c + '-' + $("#IE" + c).text();
                    if ($("#IS" + c).text() != '')
                        ITS[c] = 'IS' + c + '-' + $("#IS" + c).text();

                    if ($("#A" + c).text() != '')
                        A[c] = 'A' + c + '-' + $("#A" + c).text();
                }
                if ($("#" + p + "T" + l).text() != '') {
                    TP[j] = p + 'T' + l + '-' + $("#" + p + "T" + l).text();
                    j++;
                }
            }
            if ($("#" + p + "TP").text() != '') {
                TPT[n] = p + 'TP' + '-' + $("#" + p + "TP").text();
                n++;
            }
        }

        var AT = encodeURIComponent($("#AT").text());
        var AtvDocente = encodeURIComponent($("#AtvDocente").text());
        var Projetos = encodeURIComponent($("#Projetos").text());
        var Intervalos = encodeURIComponent($("#Intervalos").text());
        var Total = encodeURIComponent($("#Total").text());

        var telefone = encodeURIComponent($("#telefone").val());
        var celular = encodeURIComponent($("#celular").val());
        var email = encodeURIComponent($("#email").val());
        var area = encodeURIComponent($("#area").val());
        var regime = encodeURIComponent($("input[type='radio']:checked").val());
        var observacao = encodeURIComponent($("#observacao").val());
        $('#index').load('<?= $SITE ?>?AT=' + AT + '&AtvDocente=' + AtvDocente + '&Projetos=' + Projetos + '&Intervalos=' + Intervalos + '&Total=' + Total + '&ITS=' + ITS + '&ITE=' + ITE + '&TDT=' + TDT + '&A=' + A + '&TP=' + TP + '&TPT=' + TPT + '&TD=' + TD + '&dte=' + DTE + '&dts=' + DTS + '&observacao=' + observacao + '&telefone=' + telefone + '&celular=' + celular + '&email=' + email + '&area=' + area + '&regime=' + regime + '&tipo=' + tipo <?php if ($codigo) print "+ '&codigo=$codigo'";?> );
    }
</script>