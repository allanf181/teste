<?php
// verifica se não está sendo chamado diretamente.
if (strpos($_SERVER["HTTP_REFERER"], LOCATION) == false) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
}


require CONTROLLER . "/atendimento.class.php";
$atendimento = new Atendimento();

$params['tipo'] = $PROFESSOR;
$params['ano'] = $ANO;
$params['semestre'] = $SEMESTRE;

$paramsProfessor = $params;

if (dcrip($_GET["professor"])) {
    $params['professor'] = dcrip($_GET["professor"]);
    $sqlAdicional = " AND p.codigo = :professor";
}
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>


<table border="0" width="100%" id="form" width="100%">
    <tr>
        <td>Professor: </td>
        <td>
            <select name="campoProfessor" id="campoProfessor" style="width: 350px">
                <option value=''>Todos</option>
                <?php
                foreach ($atendimento->listAtendimento($paramsProfessor) as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $params['professor'])
                        $selected = "selected";
                    ?>
                    <option <?= $selected ?> value='<?= crip($reg['codigo']) ?>'><?= $reg['nome'] ?></option>
                    <?php
                }
                ?>
            </select>
        </td></tr>
</table>

<?php
// PAGINACAO
$item = 1;
$itensPorPagina = 20;

if (isset($_GET['item']))
    $item = $_GET["item"];

$res = $atendimento->listAtendimento($params, $sqlAdicional);
$totalRegistros = count($atendimento->listAtendimento($params, $sqlAdicional));

$SITENAV = $SITE . '?';

require(PATH . VIEW . '/paginacao.php');

?>

<table border="0" id="form" width="100%">
    <tr><td colspan="3"></td>
    </tr>
    <?php
    foreach ($res as $reg) {
        ?>
        <tr>
            <td colspan="3"><h2><?= $reg['nome'] ?></h2></td>
        </tr>
        <tr>
            <td width="100">
                <img alt="foto" style="width: 100px; height: 90px" src="<?= INC ?>/file.inc.php?type=pic&id=<?= crip($reg['codigo']) ?>" />
            </td>
            <td>
                <?php
                if ($reg['lattes'] != '') {
                    ?>
                    <b>Lattes</b><br><a target="_blank" href="<?= $reg['lattes'] ?>"><?= $reg['lattes'] ?></a>
                    <?php
                }
                ?>
            </td>
            <td width="200">
                <?= $reg['horario'] ?>
            </td>
        </tr>
        <?php
    }
    ?>
</table>

<script>
    $(document).ready(function () {
        $('#campoProfessor').change(function () {
            $('#index').load('<?= $SITE ?>?professor=' + $('#campoProfessor').val());
        });
    });