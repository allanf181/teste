<?php
require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require PERMISSAO;
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<?php

require CONTROLLER . "/ensalamento.class.php";
$ensalamento = new Ensalamentos();
$res = $ensalamento->getEnsalamento($codigo, $tipo, $ANO, $SEMESTRE, $subturma);

foreach ($res as $reg) {
    $reg['horario'] = str_ireplace("[$match[1]]", "", $reg['horario']);
    $link = $reg['disciplina'] . ' (' . $reg['professor'] . '): ' . $reg['sala'] . ' - ' . $reg['localizacao'];
    $horas[$reg['diaSemana']][] = "<a href='#' title='$link'>" . $reg['inicio'] . ' - ' . $reg['fim'] . '<br>' . $reg['discNumero'] . ' - ' . $reg['horario'] . "</a>";
    $turmaNome = $reg['turma'];
}

require CONTROLLER . "/turno.class.php";
$t = new Turnos();
$res = $t->listRegistros();
foreach ($res as $reg)
    $turnos[$reg['sigla']] = $reg['nome'];

if ($tipo == 'professor')
    $MOSTRA = 'HOR&Aacute;RIO INDIVIDUAL';
else
    $MOSTRA = "Turma $turmaNome [$subturma]";

if ($atribuicao)
    $MOSTRA = $discNome[$atribuicao];
?>
<h2><font color="white"><?= $MOSTRA ?></h2>
<center>
    <table width="80%" border="0" class='ensalamento'>
        <thead>
            <tr>
                <?php
                foreach (diasDaSemana() as $dCodigo => $dNome) {
                    ?>
                    <th abbr="Domingo" title="<?= $dNome ?>"><span style='font-weight: bold; color: white'><?= $dNome ?></span></th>
                    <?php
                }
                ?>
            </tr>
        </thead>
        <tr align="center">
            <?php
            for ($i = 1; $i <= 7; $i++) {
                $TA = '';
                ?>
                <td style='width: 10%' valign="top">
                    <?php
                    if (isset($horas[$i]))
                        foreach ($horas[$i] as $disc) {
                            preg_match('#\[(.*?)\]#', $disc, $match);
                            $T = $match[1];
                            if ($T != $TA) {
                                print strtoupper($turnos[$T]) . "<hr>\n";
                                $TA = $T;
                            }
                            print str_ireplace("[$match[1]]", "", $disc);
                            print '<br>-----------------<br>';
                        }
                    ?>
                </td>
                <?php
            }
            ?>
        </tr>
    </table>
</center>