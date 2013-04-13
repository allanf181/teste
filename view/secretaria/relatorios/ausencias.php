<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Relatório em que é possível visualizar alunos com mais de 3 faltas em uma mesma disciplina.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

$data = date("d/m/Y", time()); // data atual
if (isset($_GET["mes"]))
    $mes = $_GET["mes"];
?>
<h2><?php print $TITLE; ?></h2>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>

<table align="center" id="form" width="100%">
    <tr><td align="right" style="width: 100px">Mês:</td><td>
            <select id="campoMes" name="campoMes" onChange="$('#index').load('<?php print $SITE; ?>?turma=<?php print crip($turma); ?>&mes=' + this.value);">
                <?php
                foreach (array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro") as $n => $nomeMes) {
                    $selected = "";
                    if ($n == $mes)
                        $selected = "selected='selected'";
                    echo "<option $selected value='$n'>$nomeMes</option>\n";
                }
                ?>
            </select>
    <tr><td>&nbsp;</td><td><font size="1">Mostra alunos com mais de 3 faltas no m&ecirc;s em uma mesma disciplina.</font></td><td></tr>
        </td></tr>
</table>    
<?php
if (!empty($_GET["mes"])) {
    require CONTROLLER . "/frequencia.class.php";
    $frequencia = new Frequencias();
    $res = $frequencia->listAusencias($_GET["mes"] + 1);
    ?>
    <table id="frequencias" border="0" align="center" width="100%">
        <tr><th align="center" style='width: 100px'>Prontu&aacute;rio</th><th align="center" style='width: 300px'>Nome</th><th align="center">Disciplina/Cursos</th></tr>
                <?php
                $i = 0;
                foreach ($res as $pront => $reg) {
                    $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                    ?>

            <tr <?php print $cdif; ?>><td align='center'><?php print $pront; ?></td>
                <td><?php print mostraTexto($reg['aluno']); ?></td>
                <td align='center'><?php print $reg['disciplina']; ?></td>
            </tr>
                <?php
            }
            $i++;
            ?>
    </table>
    <?php
}
?>