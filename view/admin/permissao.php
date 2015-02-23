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

if ($_POST["opcao"] == 'Salvar') {
    unset($_POST['opcao']);
    unset($_POST['tipoCopia']);
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
    $_POST['codigo'] = $_POST['tipo'];
    $_POST['tipo'] = $_POST['tipoCopia'];
    unset($_POST['tipoCopia']);
    unset($_POST['opcao']);
    unset($_POST['permissao']);
    unset($_POST['menu']);
    unset($_POST['nome']);
    
    $ret = $permissao->copyTipo($_POST);
    $ret['TIPO'] = 'TRUE_COPY_PERMISSAO';
    $ret['STATUS'] = 'OK';
    $ret['RESULTADO'] = '';
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET['tipo'] = $_POST['codigo'];
}
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
if (dcrip($_GET["tipo"]))
    $tipo = $_GET["tipo"];

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
        <table align="center" border="0" id="form" width="100%">
            <tr>
                <td colspan="2" align="left" style="width: 100px">Tipo: 
                    <select name="tipo" id="tipo">
                        <option></option>
                        <?php
                        require CONTROLLER . '/tipo.class.php';
                        $tp = new Tipos();
                        $res = $tp->listRegistros(null, 'ORDER BY nome', null, null);
                        foreach ($res as $reg) {
                            $selected = "";
                            if ($reg['codigo'] == dcrip($tipo))
                                $selected = "selected";
                            echo "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <?php if ($tipo) {
                        ?>
                        <a href='#' title='Copiar Permiss&atilde;o' data-content='Permite replicar as permiss&otilde;es de um tipo para outro.' class='item-copiar'><img class='botao' src='<?= ICONS ?>/copiar.gif' /></a>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div id='showCopia'>
                        <br /><table id='listagem' style="width:400px; border: 1px solid black;">
                            <tr>
                                <td colspan="2" align="right">
                                    <a href="#" onClick="$('#showCopia').hide();">Cancelar</a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">Copiar as permiss&otilde;es de:</td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="tipoCopia" id="tipoCopia">
                                        <?php
                                        foreach ($res as $reg) {
                                            echo "<option value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <input type="submit" value="Copiar" id='Copiar' name='Copiar' />
                                </td>
                            </tr>
                        </table>
                    </div>                
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <?php
                    if ($tipo) {
                        $perms = $permissao->listaPermissoes(array(dcrip($tipo)), 'permissao');

                        // Listando os diretórios dentro da VIEW.
                        $regex = '\/..$|\/.$|.svn|\/js\/|\/css\/|\/inc\/|index.html|\/common\/|\/system\/';
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
                                            <tr>
                                                <td colspan="4">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"><hr><font size="3"><b><?= $dir ?></b></font><hr></td>
                                            </tr>
                                            <?php
                                            $lastDir = $dir;
                                        }

                                        if ($i == 0)
                                            print "<tr>\n";
                                        ?>
                                        <td>
                                            <input type='checkbox' <?= $checkedPermissao ?> name='permissao[]' value='<?= $arquivo ?>' onclick="return libera('<?= $descricao['nome'] . $i ?>', this.checked);" />
                                            <b><font size="1"><a href="#" data-placement="top" title='<?= $descricao['descricaoArquivo'] ?>' data-content='<?= $descricao['descricaoLink'] ?>'><?= $descricao['nome'] ?></a></font></b>
                                            <br><input type='checkbox' <?= $disabled ?> <?= $checkedLink ?> name='menu[<?= $arquivo ?>]' id="M_<?= $descricao['nome'] . $i ?>" value='<?= $arquivo ?>' />
                                            <font size="1"> - link vis&iacute;vel</font>
                                            <br><input type="text" <?= $disabled ?> name="nome[<?= $arquivo ?>]" id="<?= $descricao['nome'] . $i ?>" value="<?= $arquivoNomeMenu ?>" />
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
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="3">
                    <?php if ($tipo) { ?>
                        <input type="hidden" name='opcao' id='opcao' value='' />
                        <input type="submit" value="Salvar" id='Salvar' name='Salvar' />
                    <?php } ?>
                </td></tr>
        </table>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#showCopia').hide();

        $('#tipo').change(function() {
            $('#index').load('<?= $SITE ?>?tipo=' + $('#tipo').val());
        });
            
        $(".item-copiar").click(function() {
            $('#showCopia').show();
        });

        $("#Copiar").mouseover(function() {
            $('#opcao').val('Copiar');
        });

        $("#Salvar").mouseover(function() {
            $('#opcao').val('Salvar');
        });
    });
    function reload() {
        $.Zebra_Dialog('<strong>Aten&ccedil;&atilde;o: o registro foi salvo com sucesso, por&eacute;m o site deve ser recarregado para exibir as novas altera&ccedil;&otilde;es. Deseja recarregar?</strong>', {
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