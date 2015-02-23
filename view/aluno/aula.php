<?php
//Esse arquivo é fixo para o aluno.
//Visualização do conteúdo de aula e falta.
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

require CONTROLLER . "/aula.class.php";
$aula = new Aulas();

$params = array('atribuicao' => $atribuicao);
$res = $aula->listAulasProfessor($atribuicao, 'ORDER BY a.data ASC, a.codigo');
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>
<div class='fundo_listagem'>
    <table width="100%" align="center" style="border: 0px solid black">
        <tr class="listagem_tr">
            <th align="center" style="width: 100px">Data</th>
            <th align="center">Conte&uacute;do</th>
            <th width="50" align='center'>Falta</th></tr>
        <?php
        $i = 0;
        foreach ($res as $reg) {
            $dados = $aula->listAulasAluno($reg['codigo'], $aluno);
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?= $cdif ?>>
                <td align='left'><?= $dados[0]['dataFormatada'] ?></td>
                <td align='left'><?= $dados[0]['conteudo'] ?></td>
                <td align='center'><?= $dados[0]['falta'] ?></td>
            </tr>
            <?php
            $i++;
        }
        ?>
    </table>
</div>