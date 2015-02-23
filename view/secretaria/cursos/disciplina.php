<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Visualização de todas as disciplinas de todos os cursos ministrados e seus respectivos códigos.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

require CONTROLLER . "/disciplina.class.php";
$disciplina = new Disciplinas();

if ($_GET["opcao"] == 'delete') {
    $ret = $disciplina->delete($_GET["codigo"]);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET["codigo"] = null;
    $_GET["curso"] = null;
}
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>

<?php
if (dcrip($_GET["curso"])) {
    $params['curso'] = dcrip($_GET["curso"]);
    $sqlAdicional = " and c.codigo=:curso";
    $curso = dcrip($_GET["curso"]);
}

if ($_GET["numeroDisciplina"]) {
    $params['numeroDisciplina'] = '%'.$_GET["numeroDisciplina"].'%';
    $sqlAdicional .= " AND d.numero LIKE :numeroDisciplina";
    $numeroDisciplina = $_GET["numeroDisciplina"];
}
if ($_GET["nomeDisciplina"]) {
    $params['nomeDisciplina'] = '%'.$_GET["nomeDisciplina"].'%';
    $sqlAdicional .= " AND d.nome LIKE :nomeDisciplina";
    $nomeDisciplina = $_GET["nomeDisciplina"];
}

?>
<script>
    $('#form_padrao').html5form({
        method: 'POST',
        action: '<?= $SITE ?>',
        responseDiv: '#index',
        colorOn: '#000',
        colorOff: '#999',
        messages: 'br'
    })
</script>

<div id="html5form" class="main">
    <form id="form_padrao">
        <table align="center" width="100%" id="form">
            <tr><td align="right" style="width: 100px">Curso: </td><td>
                    <select name="curso" id="curso" value="<?= $curso ?>">
                        <option></option>
                        <?php
                        require CONTROLLER . '/curso.class.php';
                        $cursos = new Cursos();
                        $res = $cursos->listCursos();
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == $curso)
                                $selected = "selected";
                            print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['curso'] . " [" . $reg['modalidade'] . "]</option>";
                        }
                        ?>
                    </select>
                </td></tr>
            <tr><td align="right">N&uacute;mero: </td><td><input type="text" name="numeroDisciplina" maxlength="45" id="numeroDisciplina" value="<?= $numeroDisciplina ?>" />
                    <a href="#" id="setNumero" title="Buscar"><img class='botao' style="width:15px;height:15px;" src='<?= ICONS ?>/search.png' /></a>
                </td></tr>
            <tr><td align="right">Nome: </td><td><input type="text" name="nomeDisciplina" maxlength="45" id="nomeDisciplina" value="<?= $nomeDisciplina ?>" />
                    <a href="#" id="setNome"><img class='botao' style="width:15px;height:15px;" src='<?= ICONS ?>/search.png'/></a>
                </td></tr>
            <tr><td><a href="javascript:$('#index').load('<?= $SITE ?>');void(0);">Limpar</a></td></tr>
        </table>
    </form>
    <?php
    // PAGINACAO
    $item = 1;
    $itensPorPagina = 50;

    if (isset($_GET['item']))
        $item = $_GET["item"];

    $res = $disciplina->listDisciplinas($params, $sqlAdicional, $item, $itensPorPagina);
    $totalRegistros = count($disciplina->listDisciplinas($params, $sqlAdicional));

    $params['curso'] = crip($curso);
    $params['numeroDisciplina'] = $numeroDisciplina;
    $params['nomeDisciplina'] = $nomeDisciplina;
    
    $SITENAV = $SITE . "?" . mapURL($params);

    require(PATH . VIEW . '/system/paginacao.php');
    ?>

    <table id="listagem" border="0" align="center">
        <tr>
            <th align="center" width="40">#</th>
            <th align="left" width="90">N&uacute;mero</th>
            <th align="left">Disciplina</th>
            <th width="60">CH</th>
            <th>Curso</th>	      
            <th align="center" width="40">
                <input type='checkbox' id="select-all" value="" />
                <a href="#" class='item-excluir'><img class='botao' src='<?= ICONS ?>/delete.png' /></a></th></tr>
        <?php
        // efetuando a consulta para listagem
        $i = $item;
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?= $cdif ?>>
                <td align='center'><?= $i ?></td>
                <td align='left'><?= $reg['numero'] ?></td>
                <td><?= (mostraTexto($reg['disciplina'])) ?></td>
                <td><?= $reg['ch'] ?></td>
                <td><a href='#' data-placement="top" data-content='<?= $reg['modalidade'] ?>' title='<?= $reg['curso'] ?>'><?= abreviar(mostraTexto($reg['curso']), 32) ?></a></td>
                <td align='center'>
                    <input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
                </td>
            </tr>
            <?php
            $i++;
        }
        ?>
    </table>

    <?php require(PATH . VIEW . '/system/paginacao.php'); ?>

    <script>
        function atualizar(getLink) {
            var curso = encodeURIComponent($('#curso').val());
            var nome = encodeURIComponent($('#nomeDisciplina').val());
            var numero = encodeURIComponent($('#numeroDisciplina').val());
            var URLS = '<?= $SITE ?>?';

            if (curso != "")
                URLS += '&curso=' + curso;

            if (nome != "")
                URLS += '&nomeDisciplina=' + nome;

            if (numero != "")
                URLS += '&numeroDisciplina=' + numero;

            if (!getLink)
                $('#index').load(URLS + '&item=<?= $item ?>');
            else
                return URLS;
        }

        $(document).ready(function() {

            $(".item-excluir").click(function() {
                $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>', {
                    'type': 'question',
                    'title': '<?= $TITLE ?>',
                    'buttons': ['Sim', 'Não'],
                    'onClose': function(caption) {
                        if (caption == 'Sim') {
                            var selected = [];
                            $('input:checkbox:checked').each(function() {
                                selected.push($(this).val());
                            });

                            $('#index').load('<?= $SITE ?>?opcao=delete&codigo=' + selected + '&item=<?= $item ?>');
                        }
                    }
                });
            });

            $('#select-all').click(function(event) {
                if (this.checked) {
                    // Iterate each checkbox
                    $(':checkbox').each(function() {
                        this.checked = true;
                    });
                } else {
                    $(':checkbox').each(function() {
                        this.checked = false;
                    });
                }
            });

            $('#curso, #nomeDisciplina, #numeroDisciplina').change(function() {
                atualizar();
            });
        });
    </script>