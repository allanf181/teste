<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita a visualização das notas finais dos alunos de um determinado curso após o fechamento das notas.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . '/notaFinal.class.php';
$nota = new NotasFinais();
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
if (dcrip($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $params['curso'] = $curso;
    $sqlAdicional .= ' AND c.codigo = :curso ';

    if ($_SESSION['regAnterior'] && $curso != $_SESSION['regAnterior']) {
        unset($_GET["turma"]);
        unset($_GET["professor"]);
    }
    $_SESSION['regAnterior'] = $curso;
}

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND t.codigo = :turma ';
}
?>
<table align="center" id="form" width="100%">
    <tr>
        <td align="right" style="width: 100px">Curso: </td>
        <td>
            <select name="curso" id="curso" value="<?php echo $curso; ?>" style="width: 350px">
                <option></option>
                <?php
                require CONTROLLER . '/curso.class.php';
                $cursos = new Cursos();
                foreach ($cursos->listCursos($paramsCurso, $sqlAdicionalCurso, null, null) as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $curso)
                        $selected = "selected";
                    print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['curso'] . " [" . $reg['codigo'] . "]</option>";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td align="right">Turma: </td>
        <td>
            <select name="turma" id="turma" style="width: 350px">
                <option></option>
                <?php
                require CONTROLLER . '/turma.class.php';
                $turmas = new Turmas();
                $sqlAdicionaTurma = ' AND c.codigo = :curso ';
                $paramsTurma = array(':curso' => $curso, ':ano' => $ANO, ':semestre' => $SEMESTRE);
                foreach ($turmas->listTurmas($paramsTurma, $sqlAdicionaTurma) as $reg) {
                    $selected = "";
                    if ($reg['codTurma'] == $turma)
                        $selected = "selected";
                    print "<option $selected value='" . crip($reg['codTurma']) . "'>" . $reg['numero'] . " [" . $reg['curso'] . "]</option>";
                }
                ?>
            </select>
        </td>
    </tr>
</table>
<br />
<?php
if (!empty($curso) && !empty($turma)) {
    ?>
    <table id="form" border="0" align="center" width="100%">
        <tr>
            <th align="center" width="220">Aluno</th>
            <th align="center" width="200">Disciplina</th>
            <th align="center" width="50">Turma</th>
            <th width="140" align="center">Sincronizado</th>
            <th width="140" align="center">Retorno</th>
            <th width="20" align="center">&nbsp;</th>
            <th width="20" align="center">FLAG</th>
        </tr>
        <?php
        //LISTAGEM
        $res = $nota->listNotasFinais($params, $sqlAdicional);
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?= $cdif ?>>
                <td><?= mostraTexto($reg['aluno']) ?></td>
                <td align='left'><?= mostraTexto($reg['disciplina']).$reg['bimestre'] ?></td>
                <td align=left><?= $reg['turma'] ?></td>
                <td align=left><?= $reg['sincronizado'] ?></td>
                <td align=left>
                    <div id="S<?= $reg['codigo'] ?>"><?= $reg['retorno'] ?></div>
                </td>
                <?php
                if ($reg['flag'] != 5) {
                    ?>
                    <td align=left>
                        <a href='#' title='Sincronizar' class='sync' id='<?= $reg['codigo'] ?>'>
                            <?php
                            if ($reg['retorno'] == '') {
                                ?>
                                <img src="<?= ICONS ?>/sync.png" class='botao'>
                                <?php
                            }
                            if ($reg['retorno'] == '1') {
                                ?>
                                <img src="<?= ICONS ?>/true.png" class='botao'>
                                <?php
                            }
                            if ($reg['retorno'] == '0') {
                                ?>
                                <img src="<?= ICONS ?>/exclamation.png" class='botao'>
                                <?php
                            }
                            ?>
                        </a>
                    </td>
                    <?php
                } else {
                    ?>
                    <td align=left>
                        <img src="<?= ICONS ?>/true.png" class='botao'>
                    </td>
                    <?php
                }
                ?>
                <td align=center><?= $reg['flag'] ?></td>
                <?php
                $i++;
            }
            ?>
    </table>
    <?php
}
?>
<script>
    function valida() {
        curso = $('#curso').val();
        turma = $('#turma').val();
        $('#index').load('<?= $SITE ?>?curso=' + curso + '&turma=' + turma);
    }

    $('#curso, #turma').change(function() {
        valida();
    });
    $(document).ready(function() {
        $(".sync").click(function() {
            var codigo = $(this).attr('id');
            var div1 = 'S' + codigo;
            $('#' + div1).load('db2/db2DigitaNotas.php?codigo=' + codigo);
            return false;
        });
    });
</script>