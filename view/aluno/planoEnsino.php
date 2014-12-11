<?php
//Esse arquivo é fixo para o aluno.
//Permite a visualização do plano de ensino.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;
require PERMISSAO;

$atribuicao = dcrip($_GET["atribuicao"]);
$aluno = $_SESSION["loginCodigo"];

require CONTROLLER . "/professor.class.php";
$professor = new Professores();

require CONTROLLER . "/planoEnsino.class.php";
$plano = new PlanosEnsino();

$params['atribuicao'] = dcrip($_GET['atribuicao']);
$sqlAdicional = " AND a.codigo = :atribuicao  "
        . " AND (pe.valido <> '' AND pe.valido <> '0000-00-00 00:00:00')";

$res = $plano->listPlanoEnsino($params, $sqlAdicional);
?>
<script src="<?php print VIEW; ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<?php
if ($res) {
    $numeroAulaSemanal = $res[0]["numeroAulaSemanal"];
    $totalHoras = $res[0]["totalHoras"];
    $totalAulas = $res[0]["totalAulas"];
    $numeroProfessores = $res[0]["numeroProfessores"];
    $ITEM['2 - EMENTA'] = $res[0]["ementa"];
    $ITEM['3 - OBJETIVO'] = $res[0]["objetivo"];
    $ITEM['4 - CONTEÚDO PROGRAMÁTICO'] = $res[0]["conteudoProgramatico"];
    $ITEM['5 - METODOLOGIA'] = $res[0]["metodologia"];
    $ITEM['6 - RECURSOS DIDÁTICOS'] = $res[0]["recursoDidatico"];
    $ITEM['7 - AVALIAÇÃO'] = $res[0]["avaliacao"];
    $ITEM['7.1 - RECUPERAÇÃO PARALELA'] = $res[0]["recuperacaoParalela"];
    $ITEM['7.2 - '.$res[0]["rfTitle"]] = $res[0]["recuperacaoFinal"];
    $ITEM['8 - BIBLIOGRAFIA BÁSICA'] = $res[0]["bibliografiaBasica"];
    $ITEM['8.1 - BIBLIOGRAFIA COMPLEMENTAR'] = $res[0]["bibliografiaComplementar"];
    $disciplina = $res[0]["disciplina"];
    $numero = $res[0]["numero"];
    $CH = $res[0]["ch"];
    $modalidade = $res[0]["modalidade"];
    $finalizado = $res[0]["finalizado"];
    $curso = $res[0]["curso"];
    ?>
    <div class='fundo_listagem'>
        <table border="1" width="100%" cellpadding="2" cellspacing="0">
            <tr>
                <td colspan="2" align="center">
                    <h3>
                        <font size="4">PLANO DE ENSINO</font>
                    </h3>
                </td>
                <td>CAMPUS: <b> <?php print $SITE_CIDADE; ?></b></td>
            </tr>
            <tr>
                <td colspan="3">1 - IDENTIFICAÇÃO</td>
            </tr>
            <tr>
                <td colspan="3">CURSO: <b><?php print $curso; ?></b></td>
            </tr>
            <tr>
                <td colspan="3">COMPONENTE CURRICULAR: <b><?php print $disciplina; ?></b></td>
            </tr>
            <tr>
                <td colspan="3">CÓDIGO DISCIPLINA: <b><?php print $numero; ?></b></td>
            </tr>
            <tr>
                <td>SEMESTRE/ANO: <b><?php print "$SEMESTRE/$ANO"; ?></b></td>
                <td>NÚMERO DE AULAS SEMANAIS: <b><?php print $numeroAulaSemanal; ?></b></td>
                <td>ÁREA: <b><?php print $modalidade; ?></b></td>
            </tr>
            <tr><td>TOTAL DE HORAS: <b><?php print $totalHoras; ?></b></td><td>TOTAL DE AULAS: <b><?php print $totalAulas; ?></b></td>
                <td>NÚMERO DE PROFESSORES: <b><?php print $numeroProfessores; ?></b></td></tr>
            <tr><td colspan="3">PROFESSOR(A) RESPONSÁVEL: <b><?= $professor->getProfessor(dcrip($_GET['atribuicao']), 1, '', 1, 1) ?></b></td></tr>
        </table>
        <br>
        <?php
        foreach ($ITEM as $chave => $valor) {
            ?>
            <table border="1" width="100%" cellpadding="2" cellspacing="0">
                <tr><td><b><?php print $chave; ?></b></td></tr>
                <tr><td><?php print nl2br($valor); ?></td></tr>
            </table>
            <br>
            <?php
        }
        ?>
    </div>
    <?php
} else {
    print "Plano de Ensino ainda n&atilde;o disponibilizado.";
}
?>