<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Tela onde é possível a concessão de acesso as funcionalidades disponíveis do WebDiário.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

if ($_POST["opcao"] == 'InsertOrUpdate') {
    unset($_POST['opcao']);
    $perms = $_POST["permissao"];
    $nome = $_POST["nome"];
    $menu = $_POST["menu"];

    $nomes = array();
    $menus = array();
    foreach ($perms as $perm) {
        $nomes[] = $nome[$perm];
        $menus[] = $menu[$perm];
    }

    $_POST['permissao'] = implode(",", $perms);
    $_POST['nome'] = implode(",", $nomes);
    $_POST['menu'] = implode(",", $menus);
    $_POST['nome'] = htmlentities($_POST['nome'], ENT_COMPAT, 'UTF-8');

    $ret = $permissao->insertOrUpdate($_POST);

    $ret['TIPO'] = 'UPDATE';
    $ret['STATUS'] = 'OK';
    $ret['RESULTADO'] = '1';
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

    if (in_array(dcrip($_POST["tipo"]), $_SESSION["loginTipo"])) {
        print "<script> reload(); </script>\n";
    }

    $_GET["tipo"] = $_POST["tipo"];
}


if ($_POST["opcao"] == 'Copiar') {
    unset($_POST['opcao']);
    $ret = $permissao->copyTipo($_POST);
    $ret['TIPO'] = 'TRUE_COPY_PERMISSAO';
    $ret['STATUS'] = 'OK';
    $ret['RESULTADO'] = '';
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET['tipo'] = $_POST['codigo'];
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
$nome = "";
$tipo = "";
if (isset($_GET["tipo"]))
    $codigo = $_GET["tipo"];
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
        <table align="center" id="form" width="100%">
            <tr><td align="right" style="width: 100px">Tipo: </td><td>
                    <select name="tipo" id="tipo" value="<?= $tipo ?>" onChange="$('#index').load('<?= $SITE ?>?tipo=' + this.value);">
                        <option></option>
                        <?php
                        require CONTROLLER . '/tipo.class.php';
                        $tipo = new Tipos();
                        $res = $tipo->listRegistros(null, null, null, 'ORDER BY nome');
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == dcrip($codigo))
                                $selected = "selected";
                            echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </td><td>
                    <?php if ($codigo) {
                        ?>
                        <a href='#' title='Copiar' class='item-copiar' id='<?= $codigo ?>'><img class='botao' src='<?= ICONS ?>/copiar.gif' /></a>
                        <?php
                    }
                    ?>
                </td></tr>
            <tr><td align="right"></td><td>
                    <?php
                    if ($codigo) {
                        $perms = $permissao->listaPermissoes(array(dcrip($codigo)), 'permissao');

                        // Listando os diretórios dentro da VIEW.
                        $regex = '\/..$|\/.$|.svn|\/js\/|\/css\/|\/inc\/|index.html';
                        $cdir = dirToArray(PATH . LOCATION . '/view/', $regex);
                        krsort($cdir);
                        $i = 0;
                        ?>
                        <table border="0">
                            <?php
                            foreach ($cdir as $dir => $reg) {
                                sort($reg);
                                foreach ($reg as $arquivo) {
                                    $descricao = $permissao->fileDescricao($arquivo);
                                    if ($descricao['lista']) {
                                        $j = 0;
                                        foreach ($perms['permissao'] as $perm) {
                                            $checkedPermissao = "";
                                            $checkedLink = "";
                                            $arquivoNomeMenu = "";
                                            $disabled = 'disabled';
                                            if ($arquivo == $perm) {
                                                if (in_array($arquivo, $perms['menu']))
                                                    $checkedLink = "checked='checked'";
                                                $checkedPermissao = "checked='checked'";
                                                $disabled = '';
                                                $arquivoNomeMenu = html_entity_decode($perms['nome'][$j], ENT_COMPAT, 'UTF-8');
                                                break;
                                            }
                                            $j++;
                                        }

                                        if ($dir != $lastDir) {
                                            ?>
                                </tr><tr><td colspan="4">&nbsp;</td></tr>
                            </tr><tr><td colspan="4">&nbsp;</td></tr>
                    </tr><tr><td colspan="4"><hr><font size="3"><b><?= $dir ?></b></font><hr></td></tr>
                    <?php
                    $lastDir = $dir;
                }

                if ($i == 0)
                    print "<tr>\n";
                ?>
                <td>
                    <input type='checkbox' <?= $checkedPermissao ?> name='permissao[]' value='<?= $arquivo ?>' onclick="return libera('<?= $descricao['nome'] ?>', this.checked);" />
                    <b><font size="1"><a href="#" title='<?= $descricao['descricaoArquivo'] ?><br><?= $descricao['descricaoLink'] ?>'><?= $descricao['nome'] ?></a></font></b>
                    <br><input type='checkbox' <?= $disabled ?> <?= $checkedLink ?> name='menu[<?= $arquivo ?>]' id="M_<?= $descricao['nome'] ?>" value='<?= $arquivo ?>' />
                    <font size="1"> - link vis&iacute;vel</font>
                    <br><input type="text" <?= $disabled ?> name="nome[<?= $arquivo ?>]" id="<?= $descricao['nome'] ?>" value="<?= $arquivoNomeMenu ?>" />
                </td>
                <?php
                $i++;
                if ($i == 4) {
                    print "</tr><tr><td colspan=\"4\"><hr></td></tr>\n";
                    $i = 0;
                }
            }
        }
    }
    ?>
    <input type="hidden" name="codigo" value="<?= crip($perms['codigo']) ?>" />
    <?php
}
?>
</table>
</td></tr>
<tr><td></td><td>
        <input type="hidden" name="opcao" value="InsertOrUpdate" />
        <?php if ($codigo) { ?>
            <input type="submit" value="Salvar" />
        <?php } ?>
    </td></tr>
</table>
</form>
</div>

</table>

<style>
    .ontop {
        z-index: 999;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        display: none;
        position: absolute;				
        background-color: #666;
        color: #aaaaaa;
        opacity: .95;
    }
    #popup {
        width: 400px;
        height: 200px;
        position: absolute;
        color: #000000;
        background-color: #fff;
        top: 50%;
        left: 50%;
        margin-top: -100px;
        margin-left: -150px;
    }
</style>

<div id="popDiv" class="ontop">
    <script>
        $('#form_copiar').html5form({
            method: 'POST',
            action: '<?= $SITE ?>',
            responseDiv: '#index',
            colorOn: '#000',
            colorOff: '#999',
            messages: 'br'
        })
    </script>

    <div id="html5form" class="main">
        <form id="form_copiar">
            <table border="0" id="popup">
                <tr><td colspan="2" align="right"><a href="#" onClick="hide('popDiv');">Fechar</a></td></tr>
                <tr><td colspan="2">Copiar as permiss&otilde;es de:</td></tr>
                <input type="hidden" name="codigo" id="codigo" value="<?= $codigo ?>">
                <tr><td><select name="tipo" id="tipo">
                            <?php
                            foreach ($res as $reg) {
                                $selected = "";
                                if ($reg['codigo'] == $codigo)
                                    $selected = "selected";
                                echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                            }
                            ?>
                        </select>
                    </td></tr>
                <tr>
                    <td colspan="2">
                        <input type="hidden" name="opcao" value="Copiar" />    
                        <input type="submit" value="Copiar" onClick="hide('popDiv');" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<script>
    function pop(div) {
        document.getElementById(div).style.display = 'block';
    }
    function hide(div) {
        document.getElementById(div).style.display = 'none';
    }

    document.onkeydown = function(evt) {
        evt = evt || window.event;
        if (evt.keyCode == 27) {
            hide('popDiv');
        }
    };

    $(document).ready(function() {
        $(".item-copiar").click(function() {
            pop('popDiv');
        });
    });
    function reload() {
        $.Zebra_Dialog('<strong>Aten&ccedil;&atilde;o: o registro foi salvo com sucesso, por&eacute;m o site deve ser recarregado para exibir as novas altera&ccedil;&otilde;es. Deseja recarregar?', {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function(caption) {
                if (caption == 'Sim') {
                    location.reload();
                }
            }
        });
    }
</script>
<script>
    function libera(campo, valor) {
        if (valor) {
            document.getElementById(campo).disabled = false;
            document.getElementById('M_' + campo).disabled = false;
        }
        else
        {
            document.getElementById(campo).disabled = true;
            document.getElementById('M_' + campo).checked = false;
            document.getElementById('M_' + campo).disabled = true;
            document.getElementById(campo).value = '';
        }
    }
</script>