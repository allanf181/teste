<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Habilita tela onde é possível a concessão de acesso as funcionalidades disponíveis do Web Diário.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $tipo = $_POST["campoTipo"];
    $perms = $_POST["campoPerms"];
    $nomes = $_POST["campoNome"];
    $menus = $_POST["campoMenu"];

    foreach ($perms as $perm) {
        $nome[] = $nomes[$perm];
        $menu[] = $menus[$perm];
    }
    $perms = implode(",", $perms);
    $nomes = implode(",", $nome);
    $menus = implode(",", $menu);

    $nomes = htmlentities($nomes, ENT_COMPAT, 'UTF-8');

    $resultado = mysql_query("SELECT * FROM Permissoes WHERE tipo = '$tipo'");
    if (mysql_num_rows($resultado) == '') {
        $resultado = mysql_query("insert into Permissoes values(0,'$tipo','$perms','$nomes','$menus')");
        if ($resultado != 1)
            mensagem('NOK', 'FALSE_INSERT');
    }
    else {
        $resultado = mysql_query("update Permissoes set permissao='$perms',nome='$nomes',menu='$menus' where tipo=$tipo");
        if ($resultado != 1)
            mensagem('NOK', 'FALSE_UPDATE');
    }
    if ($resultado == 1) {
        $pergunta = "Aten&ccedil;&atilde;o: o registro foi salvo com sucesso, por&eacute;m o site deve ser recarregado para exibir as novas altera&ccedil;&otilde;es. Deseja recarregar?";
        print "<script>jConfirm('$pergunta', '$TITLE', function(r) {
			if ( r ) location.reload();
			}); </script>\n";
    }
    $_GET["tipo"] = $tipo;
}


if ($_POST["opcao"] == 'Copiar') {
    $codigo = dcrip($_POST["campoCopia"]);
    $tipo = dcrip($_POST["campoTipo"]);
    $update = dcrip($_POST["campoUpdate"]);

    if (!$update)
        $sql = "INSERT INTO Permissoes
        	SELECT 
                NULL,$codigo,p.permissao,p.nome,p.menu
                FROM Permissoes p
                WHERE p.tipo=$tipo";

    if ($update)
        $sql = "UPDATE Permissoes p
                INNER JOIN
                Permissoes p1
                ON p1.tipo = $tipo
                SET p.permissao=p1.permissao,p.nome=p1.nome,p.menu=p1.menu
                WHERE p.tipo = $codigo";

    //print $sql;
    $resultado = mysql_query($sql);
    if ($resultado == 1)
        mensagem('OK', 'TRUE_COPY_PERMISSAO');
    else
        mensagem('NOK', 'PERMISSAO');
}
?>
<h2><font color="white"><?php print $TITLE; ?></font></h2>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>

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

<?php
$nome = "";
$tipo = "";
if (isset($_GET["tipo"]))
    $codigo = $_GET["tipo"];

print "<script>\n";
print "    $('#form_padrao').html5form({ \n";
print "        method : 'POST', \n";
print "        action : '$SITE', \n";
print "        responseDiv : '#index', \n";
print "        colorOn: '#000', \n";
print "        colorOff: '#999', \n";
print "        messages: 'br' \n";
print "    }) \n";
print "</script>\n";

print "<div id=\"html5form\" class=\"main\">\n";
print "<form action=\"$SITE\" method=\"post\" id=\"form_padrao\">\n";
?>
<table align="center" id="form" width="100%">
    <tr><td align="right" style="width: 100px">Tipo: </td><td>
            <select name="campoTipo" id="campoTipo" value="<?php echo $tipo; ?>" onChange="$('#index').load('<?php print $SITE; ?>?tipo=' + this.value);">
                <option></option>
                <?php
                $resultado = mysql_query("select * from Tipos order by nome");
                $selected = ""; // controla a alteração no campo select
                while ($linha = mysql_fetch_array($resultado)) {
                    if ($linha[0] == $codigo)
                        $selected = "selected";
                    echo "<option $selected value='$linha[0]'>$linha[1]</option>";
                    $selected = "";
                }
                ?>
            </select>
        </td><td>
            <?php if ($codigo) {
                ?>
                <a href='#' title='Copiar' class='item-copiar' id='" . crip($linha[0]) . "'><img class='botao' src='<?php print ICONS; ?>/copiar.gif' /></a>
                <?php
            }
            ?>
        </td></tr>
    <tr><td align="right"></td><td>
            <?php
            if ($codigo) {
                $is_update = 0;
                $resultado = mysql_query("SELECT permissao,nome,menu FROM Permissoes WHERE tipo = '$codigo'");
                if (mysql_num_rows($resultado) != '') {
                    $is_update = 1;
                    $permsPer = explode(",", mysql_result($resultado, 0, "permissao"));
                    $nomesPer = explode(",", mysql_result($resultado, 0, "nome"));
                    $menusPer = explode(",", mysql_result($resultado, 0, "menu"));
                }
                $path = $_SERVER['DOCUMENT_ROOT'] . "/$LOCATION/";
                $diretorio = dir($path);
                print "<table border=\"0\">\n";
                $i = 0;

                $cdir = dirToArray($path . 'view/');

                krsort($cdir);
                foreach ($cdir as $dir => $reg) {
                    sort($reg);
                    foreach ($reg as $arquivo) {
                        $arquivoNome = pathinfo($arquivo, PATHINFO_BASENAME);
                        $getDescription = file($path . $arquivo);
                        $descricao = htmlentities(substr($getDescription[2], 2), ENT_COMPAT, 'UTF-8');
                        $lista = trim(substr($getDescription[4], 2));
                        if ($lista) {
                            $j = 0;
                            foreach ($permsPer as $perm) {
                                $checked1 = "";
                                $checked2 = "";
                                $arqNome = "";
                                $disabled = 'disabled';
                                if ($arquivo == $perm) {
                                    if (in_array($arquivo, $menusPer))
                                        $checked2 = "checked='checked'";
                                    $checked1 = "checked='checked'";
                                    $disabled = '';
                                    $arqNome = $nomesPer[$j];
                                    $arqNome = html_entity_decode($arqNome, ENT_COMPAT, 'UTF-8');
                                    break;
                                }
                                $j++;
                            }

                            if ($dir != $lastDir) {
                                print "</tr><tr><td colspan=\"4\">&nbsp;</td></tr>\n";
                                print "</tr><tr><td colspan=\"4\">&nbsp;</td></tr>\n";
                                print "</tr><tr><td colspan=\"4\"><hr><font size=\"3\"><b>$dir</b></font><hr></td></tr>\n";
                                $lastDir = $dir;
                            }

                            if ($i == 0)
                                print "<tr>\n";
                            print "<td>\n";
                            $nc = str_replace('.php', '', $arquivo);
                            print "<input type='checkbox' $checked1 name='campoPerms[]' value='$arquivo' onclick=\"return libera('$nc', this.checked);\" />\n";
                            print "<b><font size=\"1\"><a href=\"#\" title=\"$descricao\">$arquivoNome</a></font></b>";
                            print "<br><input type='checkbox' $disabled $checked2 name='campoMenu[$arquivo]' id=\"M_$nc\" value='$arquivo' />\n";
                            print "<font size=\"1\"> - link vis&iacute;vel</font>";
                            print "<br><input type=\"text\" $disabled name=\"campoNome[$arquivo]\" id=\"$nc\" value=\"$arqNome\" />\n";
                            print "</td>\n";
                            $i++;
                            if ($i == 4) {
                                print "</tr><tr><td colspan=\"4\"><hr></td></tr>\n";
                                $i = 0;
                            }
                        }
                    }
                }
            }
            print "</table>\n";
            ?>   	
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
        background-color: #cccccc;
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
    <?php
    print "<script>\n";
    print "    $('#form_copiar').html5form({ \n";
    print "        method : 'POST', \n";
    print "        action : '$SITE', \n";
    print "        responseDiv : '#index', \n";
    print "        colorOn: '#000', \n";
    print "        colorOff: '#999', \n";
    print "        messages: 'br' \n";
    print "    }) \n";
    print "</script>\n";

    print "<div id=\"html5form\" class=\"main\">\n";
    print "<form action=\"$SITE\" method=\"post\" id=\"form_copiar\">\n";
    ?>
    <table border="0" id="popup">
        <tr><td colspan="2" align="right"><a href="#" onClick="hide('popDiv');">Fechar</a></td></tr>
        <tr><td colspan="2">Copiar as permiss&otilde;es de:</td></tr>
        <input type="hidden" name="campoCopia" id="campoCopia" value="<?php print crip($codigo); ?>">
        <input type="hidden" name="campoUpdate" id="campoUpdate" value="<?php print crip($is_update); ?>">
        <tr><td><select name="campoTipo" id="campoTipo">
                    <?php
                    $resultado = mysql_query("SELECT codigo,nome FROM Tipos ORDER BY nome");
                    $selected = ""; // controla a alteração no campo select
                    while ($linha = mysql_fetch_array($resultado)) {
                        echo "<option value='" . crip($linha[0]) . "'>$linha[1]</option>";
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
<?php
mysql_close($conexao);
?>
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
</script>