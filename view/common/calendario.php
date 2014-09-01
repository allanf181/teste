
<link rel="stylesheet" type="text/css" href="<?php print VIEW; ?>/css/calendario.css" media="screen" />
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
if (!class_exists('Calendarios'))
    require CONTROLLER . "/calendario.class.php";

$calendario = new Calendarios();
$paramsCal = array('ano' => $ANO);
$resCal = $calendario->listCalendario($paramsCal);
foreach ($resCal as $reg) {
    if ($reg['dataFim'] && $reg['dataFim'] != '00/00/0000') {
        $end = dataMysql($reg['dataFim']);
        $start = dataMysql($reg['dataInicio']);
        $datediff = strtotime($end) - strtotime($start);
        $datediff = floor($datediff / (60 * 60 * 24));
        for ($i = 0; $i < $datediff + 1; $i++) {
            $mes = date("m", strtotime($start . ' + ' . $i . 'day'));
            $dia = date("d", strtotime($start . ' + ' . $i . 'day'));
            $evento[$mes][$dia][$reg['codigo']] = $reg['ocorrencia'];
            $diaOcor[$mes][$dia][$reg['codigo']] = $reg['diaLetivo'];
            $diaEq[$mes][$dia] = $reg['ocorrencia'];
        }
    } else {
        $evento[$reg['mes']][$reg['dia']][$reg['codigo']] = $reg['ocorrencia'];
        $diaOcor[$reg['mes']][$reg['dia']][$reg['codigo']] = $reg['diaLetivo'];
        $diaEq[$reg['mes']][$reg['dia']] = $reg['ocorrencia'];
    }
}

$domingo = "style=color:#C30;";

for ($j = 1; $j <= 12; $j++) {
    $mes = $j;
    $dia = date("d");
    $ano_ = substr($ANO, -2);
    ?>
    <div class='fundo_listagem'>
        <table><tr><td valign="top">

                    <h3 align='center'><?= ucfirst(meses($mes)) . " " . $ano ?></h3>
                    <table id='tabela_boletim' style='width: 400px' summary="Calendário" class="calendario">

                        <thead>
                            <tr>
                                <?php
                                foreach (diasDaSemana() as $dCodigo => $dNome) {
                                    ?>
                                    <th style='color: white; width: 10%' abbr="Domingo" title="<?= $dNome ?>"><b><?= $dNome ?></b></th>
                                    <?php
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Data = strtotime($mes . "/" . $dia . "/" . $ano_);
                            $Dia = date('w', strtotime(date('n/\0\1\/Y', $Data)));
                            $Dias = date('t', $Data);
                            $n = 0;
                            for ($i = 1, $d = 1; $d <= $Dias;) {
                                $cdif = "class='cdif2'";
                                if ($n % 2 == 0)
                                    $cdif = "class='cdif'";
                                ?>
                                <tr <?= $cdif ?> >
                                    <?php
                                    for ($x = 1; $x <= 7 && $d <= $Dias; $x++, $i++) {
                                        if ($i > $Dia) {
                                            $destaque = '';
                                            if ($x == 1) {
                                                $destaque = $domingo;
                                            }
                                            $d = str_pad($d, 2, "0", STR_PAD_LEFT);
                                            $j = str_pad($j, 2, "0", STR_PAD_LEFT);
                                            if ($evento[$j][$d]) {
                                                foreach ($evento[$j][$d] as $oCodigo => $oNome) {
                                                    if ($diaOcor[$j][$d][$oCodigo] == 0)
                                                        $color = 'red';
                                                    else
                                                        $color = 'blue';
                                                    $destaque = "style=color:$color; background: red";
                                                }
                                            }
                                            if ($d == date("d") && $j == date("m")) {
                                                $destaque = "style=color:#000;";
                                                $oNome .= ' (HOJE)';
                                                $font = '4';
                                            } else
                                                $font = '2';
                                            ?>
                                            <td><a href='#' <?= $destaque ?> title='<?= $oNome ?>'><font size='<?= $font ?>'><?= $d++ ?></font></a></td>
                                            <?php
                                            $oNome = "";
                                        }
                                        else {
                                            ?>
                                            <td class='calendario'> </td>
                                            <?php
                                        }
                                    }
                                    for (; $x <= 7; $x++) {
                                        ?>
                                        <td class='calendario'> </td>
                                        <?php
                                    }
                                    ?>
                                </tr>
                                <?php
                                $n++;
                            }
                            ?>
                        </tbody>
                    </table>
                </td><td>
                    <?php
                    for ($o = 1; $o <= 31; $o++) {
                        $o = str_pad($o, 2, "0", STR_PAD_LEFT);
                        $j = str_pad($j, 2, "0", STR_PAD_LEFT);
                        if ($evento[$j][$o]) {
                            foreach ($evento[$j][$o] as $oCodigo => $oNome) {
                                $no = str_pad($o + 1, 2, "0", STR_PAD_LEFT);
                                if ($diaEq[$j][$o] != $diaEq[$j][$no]) {
                                    if ($diaOcor[$j][$o][$oCodigo] == 0)
                                        $color = 'red';
                                    else
                                        $color = 'blue';
                                    ?>
                                    <p><font size="1px" color="<?= $color ?>"><?= "$diaEqInicio $o - " . mostraTexto($evento[$j][$o][$oCodigo]) ?></font></p>
                                    <?php
                                    $diaEqInicio = "";
                                } else {
                                    $diaEqInicio .= "$o, ";
                                }
                            }
                        }
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
    <?php
}
?>