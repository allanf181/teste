<?php
// verifica se não está sendo chamado diretamente.
if (strpos($_SERVER["HTTP_REFERER"], LOCATION) == false) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
}
?>
<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<?php

if (!class_exists('Ensalamentos'))
    require CONTROLLER . "/ensalamento.class.php";

if (!class_exists('Professor'))
    require CONTROLLER . "/professor.class.php";

$prof = new Professores();

$ensalamento = new Ensalamentos();
$res = $ensalamento->getEnsalamento($codigo, $tipo, $ANO, $SEMESTRE, $subturma);

foreach ($res as $reg) {
    $reg['horario'] = str_ireplace("[$match[1]]", "", $reg['horario']);
    $link = $reg['disciplina'] . '<br>Turma '.$reg['turma'].' ('.$reg['subturma'].') <br>' . $reg['professor'] . '<br>' . $reg['sala'];
    $horas[$reg['diaSemana']][] = $prof->getProfessor($reg['atribuicao'], 0, ' ', 1, 1)."<br><a href='#' title='$link'>" . $reg['inicio'] . ' - ' . $reg['fim'] . '<br>' . $reg['discNumero'] . ' - ' . $reg['horario'] . "</a>";
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
<h2><font color="white"><?= $MOSTRA ?></font></h2>
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