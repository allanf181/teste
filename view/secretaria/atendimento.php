<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite a visualização do horário de atendimento do professor para os alunos.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/ftdDados.class.php";
$ftd = new FTDDados();

require CONTROLLER . "/professor.class.php";
$prof = new Professores();

if (dcrip($_GET["professor"])) {
    $params['professor'] = dcrip($_GET["professor"]);
    $sqlAdicional = " AND p.codigo = :professor";
}

$params['tipo'] = $PROFESSOR;
$paramsList['tipo'] = $PROFESSOR;
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>


<table border="0" width="100%" id="form" width="100%">
    <tr>
        <td>Professor: </td>
        <td>
            <select name="campoProfessor" id="campoProfessor" style="width: 350px">
                <option value=''>Todos</option>
                <?php
                foreach ($prof->listProfessores($paramsList) as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == dcrip($_GET["professor"]))
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

$res = $prof->listProfessores($params, $sqlAdicional, $item, $itensPorPagina);
$totalRegistros = count($prof->listProfessores($params, $sqlAdicional));

$SITENAV = $SITE.'?';

require(PATH . VIEW . '/paginacao.php');

print "<table border=\"0\" id=\"form\" width=\"100%\">\n";
print "<tr><td colspan=\"3\">\n";

print "</td></tr>\n";

$i = $item;
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
            <?php
            foreach ($ftd->getAtendimentoAluno($reg['codigo'], $ANO, $SEMESTRE) as $dia => $h) {
                $diaSemana = $dias[$dia + 1];
                $ES = $h[1] . ' &agrave;s ' . $h[2];
                print "$diaSemana das $ES<br>";
            }
            ?>
        </td>
    </tr>
    <?php
}
?>
</table>

<script>
    $(document).ready(function() {
        $('#campoProfessor').change(function() {
            $('#index').load('<?php print $SITE; ?>?professor=' + $('#campoProfessor').val());
        });
    });