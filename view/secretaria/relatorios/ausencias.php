<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Relatório em que é possível visualizar alunos com mais de 3 faltas em uma mesma disciplina.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

$data = date("d/m/Y", time()); // data atual
if (isset($_GET["mes"]))
    $mes = $_GET["mes"];
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>

<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<table align="center" id="form" width="100%">
    <tr>
        <td align="right" style="width: 100px">Mês:</td>
        <td>
            <select id="mes" name="mes">
                <?php
                foreach (array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro") as $n => $nomeMes) {
                    $selected = "";
                    if ($n == $mes)
                        $selected = "selected";
                    echo "<option $selected value='$n'>$nomeMes</option>\n";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <font size="1">Mostra alunos com mais de 3 faltas no m&ecirc;s em uma mesma disciplina.</font>
        </td>
    </tr>
</table>
<?php
if (!empty($_GET["mes"])) {
    require CONTROLLER . "/frequencia.class.php";
    $frequencia = new Frequencias();
    $res = $frequencia->listAusencias($_GET["mes"] + 1, $ANO);
    ?>
    <table id="frequencias" border="0" align="center" width="100%">
        <tr>
            <th align="center" style='width: 100px'>Prontu&aacute;rio</th>
            <th align="center" style='width: 300px'>Nome</th>
            <th align="center">Disciplina/Cursos</th>
        </tr>
        <?php
        $i = 0;
        foreach ($res as $pront => $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?= $cdif ?>>
                <td align='center'><?= $pront ?></td>
                <td>
                    <a href='#' rel='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codigo']) ?>&timestamp=<?= time() ?>' class='screenshot'>
                        <img style='width: 20px; height: 20px' alt='Embedded Image' src='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codigo']) ?>&timestamp=<?= time() ?>' />
                    </a>
                    <?= mostraTexto($reg['aluno']) ?>
                </td>
                <td align='center'><?php
                    foreach ($reg['disciplina'] as $disc) {
                        print $disc . '<br>';
                    }
                    ?>
                </td>
            </tr>
            <?php
            $i++;
        }
        ?>
    </table>
    <?php
}
?>
<script>
    $('#mes').change(function() {
        $('#index').load('<?= $SITE ?>?&mes=' + $('#mes').val());
    });
</script>