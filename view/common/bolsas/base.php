<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0

$script = explode('/', $_SERVER['SCRIPT_NAME']);
?>
<table align="center" width="100%" id="form" border="0">
    <input type="hidden" name="codigo" value="<?= crip($codigo) ?>" />
    <tr>
        <td width="25%" align="center" <?= ($script[5]=='bolsa.php') ? 'style="background-color: #EEE"':'' ?>>
            <a title='Cadastrar bolsas' href="javascript:$('#index').load('<?= $_SESSION['SITE_RAIZ'] ?>');void(0);">
                <img style='width: 48px' src="<?= IMAGES ?>/bolsa.png">
                <br />
                Bolsas
            </a>
        </td>
        <?php
        if (!in_array($PROFESSOR, $_SESSION["loginTipo"]) && !in_array($ALUNO, $_SESSION["loginTipo"])) {
            ?>
            <td width="25%" align="center" <?= ($script[5]=='bolsaAluno.php') ? 'style="background-color: #EEE"':'' ?>>
                <a title='Cadastrar alunos' href="javascript:$('#index').load('<?= VIEW ?>/common/bolsas/bolsaAluno.php');void(0);">
                    <img style='width: 48px' src="<?= IMAGES ?>/atvAcadEmicas.png">
                    <br />
                    Alunos
                </a>
            </td>
            <td width="25%" align="center" <?= ($script[5]=='bolsaDisciplina.php') ? 'style="background-color: #EEE"':'' ?>>
                <a title='Cadastrar disciplinas envolvidas' href="javascript:$('#index').load('<?= VIEW ?>/common/bolsas/bolsaDisciplina.php');void(0);">
                    <img style='width: 48px' src="<?= IMAGES ?>/boletim.png">
                    <br />
                    Disciplinas
                </a>
            </td>
            <?php
        }
        ?>
        <td width="25%" align="center" <?= ($script[5]=='bolsaRelatorio.php') ? 'style="background-color: #EEE"':'' ?>>
            <a title='Reltar&oacute;rio cadastrado pelo bolsista' href="javascript:$('#index').load('<?= VIEW ?>/common/bolsas/bolsaRelatorio.php');void(0);">
                <img style='width: 48px' src="<?= IMAGES ?>/chamada.png">
                <br />
                Relat&oacute;rios
            </a>
        </td>
    </tr> 
</table>
