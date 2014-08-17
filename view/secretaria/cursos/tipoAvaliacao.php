<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Lista os tipos de avaliações cadastrados para as modalidades do sistema.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $codigo = $_POST["campoCodigo"];
    $nome = $_POST["campoNome"];
    $tipo = $_POST["campoTipo"];
    $modalidade = $_POST["campoModalidade"];
    $calculo = $_POST["campoCalculo"];
    $arredondar = $_POST["campoArredondar"];
    $notaMaior = $_POST["campoNotaMaior"];
    $notaMenor = $_POST["campoNotaMenor"];
    $sigla = $_POST["campoSigla"];
    $final = $_POST["campoFinal"];
    $notaUltimBimestre = $_POST["campoNotaUltimBimestre"];
    $qdeMinima = $_POST["campoQdeMinima"];
    $notaMaxima = $_POST["campoNotaMaxima"];

    if (empty($codigo)) {
        $resultado = mysql_query("insert into TiposAvaliacoes values(0, '$nome', '$tipo', '$modalidade', '$calculo', '$arredondar', '$notaMaior', '$notaMenor', '$sigla', '$final', '$notaUltimBimestre', '$qdeMinima', '$notaMaxima')");
        if ($resultado == 1)
            mensagem('OK', 'TRUE_INSERT');
        else
            mensagem('NOK', 'FALSE_INSERT');
        $_GET["codigo"] = crip(mysql_insert_id());
    }
    else {
        $resultado = mysql_query("update TiposAvaliacoes set nome='$nome', tipo='$tipo', modalidade='$modalidade', calculo='$calculo',arredondar='$arredondar',notaMaior='$notaMaior',notaMenor='$notaMenor',sigla='$sigla',final='$final',notaUltimBimestre='$notaUltimBimestre',qdeMinima='$qdeMinima',notaMaxima='$notaMaxima' where codigo=$codigo");
        if ($resultado == 1)
            mensagem('OK', 'TRUE_UPDATE');
        else
            mensagem('NOK', 'FALSE_UPDATE');
        $_GET["codigo"] = crip($_POST["campoCodigo"]);
    }
}

if ($_POST["opcao"] == 'Copiar') {
    $codigoCopy = dcrip($_POST["campoCopia"]);
    $modalidade = $_POST["campoModalidade"];

    $sql = "INSERT INTO TiposAvaliacoes
								SELECT 
								    NULL,t.nome,t.tipo,$modalidade,t.calculo,t.arredondar,
								    	 t.notaMaior,t.notaMenor,t.sigla,t.final,t.notaUltimBimestre,
								    	 t.qdeMinima,t.notaMaxima
								    FROM TiposAvaliacoes t
								WHERE t.codigo=$codigoCopy";
    $resultado = mysql_query($sql);
    if ($resultado == 1)
        mensagem('OK', 'TRUE_COPY_MODALIDADE');
    else
        mensagem('NOK', 'FALSE_COPY_MODALIDADE');
    $_GET["codigo"] = crip(mysql_insert_id());
}

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
    $resultado = mysql_query("delete from TiposAvaliacoes where codigo=$codigo");
    if ($resultado == 1)
        mensagem('OK', 'TRUE_DELETE');
    else
        mensagem('INFO', 'DELETE');
    $_GET["codigo"] = null;
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>

<?php
// inicializando as vari?veis do formul?rio
$codigo = "";
$nome = "";
$tipo = "";
$modalidade = "";
$calculo = "";
$arredondar = "";
$notaMaior = "";
$notaMenor = "";
$sigla = "";
$final = "";
$notaUltimBimestre = "";
$qdeMinima = "";
$notaMaxima = "";

if (!empty($_GET["codigo"])) { // se o par?metro n?o estiver vazio
    // consulta no banco
    $sql = "select * from TiposAvaliacoes where codigo=" . dcrip($_GET["codigo"]);
//        echo $sql;
    $resultado = mysql_query($sql);
    $linha = mysql_fetch_row($resultado);

    // armazena os valores nas vari?veis
    $codigo = $linha[0];
    $nome = $linha[1];
    $tipo = $linha[2];
    $modalidade = $linha[3];
    $calculo = $linha[4];
    $arredondar = $linha[5];
    $notaMaior = $linha[6];
    $notaMenor = $linha[7];
    $sigla = $linha[8];
    $final = $linha[9];
    $notaUltimBimestre = $linha[10];
    $qdeMinima = $linha[11];
    $notaMaxima = $linha[12];

    $restricao = " and t.codigo=" . dcrip($_GET["codigo"]);
}

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
<table align="center" width="100%" id="form" >
    <tr><td align="right" style="width: 100px">Nome: </td><td><input type="text" name="campoNome" maxlength="45" value="<?php echo $nome; ?>"/></td></tr>
    <tr><td align="right">Sigla: </td><td><input name="campoSigla" maxlength="3" id="campoSigla" type="text" value="<?php echo $sigla ?>" size="3" maxlength="3" /></td></tr>
    <tr><td align="right">Tipo: </td><td>
            <select name="campoTipo" id="campoTipo" value="<?php print $tipo; ?>">
                <option></option>
                <option <?= ($tipo == 'avaliacao') ? "selected='selected'" : ""; ?> value="avaliacao">Avalia&ccedil;&atilde;o</option>
                <option <?= ($tipo == 'pontoExtra') ? "selected='selected'" : ""; ?> value="pontoExtra">Ponto Extra</option>
                <option <?= ($tipo == 'recuperacao') ? "selected='selected'" : ""; ?> value="recuperacao">Recupera&ccedil;&atilde;o</option>
            </select>
        </td></tr>
    <tr><td align="right">Modalidade: </td><td>
            <select name="campoModalidade" id="campoModalidade" value="<?php echo $modalidade; ?>">
                <option></option>
                <?php
                $resultado = mysql_query("select * from Modalidades order by nome");
                $selected = ""; // controla a alteração no campo select
                while ($linha = mysql_fetch_array($resultado)) {
                    if ($linha[0] == $modalidade)
                        $selected = "selected";
                    echo "<option $selected value='" . $linha[0] . "'>$linha[1]</option>";
                    $selected = "";
                }
                ?>
            </select>
        </td></tr>
    <tr>
        <td align="right">Recupera&ccedil;&atilde;o:</td>
        <td>M&eacute;dia >= <input name="campoNotaMaior" id="campoNotaMaior" type="text" value="<?php echo $notaMaior ?>" size="5" maxlength="5" /> e 
            < <input name="campoNotaMenor" id="campoNotaMenor" type="text" value="<?php echo $notaMenor ?>" size="5" maxlength="5" />
            (Deixar 0 para desabilitar)</td></tr>
    <tr><td align="right">C&aacute;lculo: </td><td>
            <select name="campoCalculo" id="campoCalculo" value="<?php echo $calculo; ?>">
                <option></option>
                <option <?= ($calculo == 'sub_menor_nota') ? "selected='selected'" : ""; ?> value="sub_menor_nota"> Substitui a menor nota</option>
                <option <?= ($calculo == 'sub_media') ? "selected='selected'" : ""; ?> value="sub_media"> Substitui a m&eacute;dia das avalia&ccedil;&otilde;es</option>
                <option <?= ($calculo == 'add_menor_nota') ? "selected='selected'" : ""; ?> value="add_menor_nota"> Adiciona o valor da recupera&ccedil;&atilde;o no valor da menor nota</option>
                <option <?= ($calculo == 'add_media') ? "selected='selected'" : ""; ?> value="add_media"> Adicionar o valor da recupera&ccedil;&atilde;o na m&eacute;dia</option>
            </select>
        </td></tr>
    <?php $checked = '';
    if ($arredondar) $checked = "checked='checked'"; ?>
    <tr><td align="right">Arredondar: </td><td><input type='checkbox' <?php print $checked; ?> name='campoArredondar' value='1' /> Arrendodar o valor da avalia&ccedil;&atilde;o.</td></tr>
    <?php $checked = '';
    if ($final) $checked = "checked='checked'"; ?>
    <tr><td align="right">Final: </td><td><input type='checkbox' <?php print $checked; ?> name='campoFinal' value='1' /> Esse tipo de avalia&ccedil;&atilde;o ser&aacute; aplicado no final dos bimestres.</td></tr>
    <tr><td align="right">Nota M&iacute;nima: </td><td><input type="text" name="campoNotaUltimBimestre" maxlength="5" size="5" value="<?php echo $notaUltimBimestre; ?>"/> no &uacute;ltimo bimestre.</td></tr>
    <tr><td align="right">Quantidade M&iacute;nima: </td><td><input type="text" name="campoQdeMinima" maxlength="2" size="1" value="<?php echo $qdeMinima; ?>"/> de avalia&ccedil;&otilde;es.</td></tr>
    <tr><td align="right">Valor M&aacute;ximo: </td><td><input type="text" name="campoNotaMaxima" maxlength="5" size="5" value="<?php echo $notaMaxima; ?>"/> da avalia&ccedil;&atilde;o.</td></tr>

    <tr><td></td><td>
            <input type="hidden" value="<?php echo $codigo; ?>" name="campoCodigo" />
            <input type="hidden" name="opcao" value="InsertOrUpdate" />
            <table width="100%"><tr><td></td>
                    <td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Limpar</a></td>
                </tr></table>
        </td></tr>
</table>
</form>
</div>

<?php
// inicializando as vari?veis
$item = 1;
$itensPorPagina = 50;
$primeiro = 1;
$anterior = $item - $itensPorPagina;
$proximo = $item + $itensPorPagina;
$ultimo = 1;

// validando a p?gina atual
if (!empty($_GET["item"])) {
    $item = $_GET["item"];
    $anterior = $item - $itensPorPagina;
    $proximo = $item + $itensPorPagina;
}

// validando a p?gina anterior
if ($item - $itensPorPagina < 1)
    $anterior = 1;

// descobrindo a quantidade total de registros
$resultado = mysql_query("SELECT COUNT(*) FROM TiposAvaliacoes t, Modalidades c
    								WHERE t.modalidade = c.codigo $restricao");
$linha = mysql_fetch_row($resultado);
$ultimo = $linha[0];

// validando o pr?ximo item
if ($proximo > $ultimo) {
    $proximo = $item;
    $ultimo = $item;
}

// validando o ?ltimo item
if ($ultimo % $itensPorPagina > 0)
    $ultimo = $ultimo - ($ultimo % $itensPorPagina) + 1;

$SITENAV = $SITE . '?';

require(PATH . VIEW . '/navegacao.php');
?>

<table id="listagem" border="0" align="center">
    <tr><th align="center" width="40">#</th><th align="left">Nome</th><th>Tipo</th><th>Modalidade</th><th width="60">A&ccedil;&atilde;o</th></tr>
    <?php
    // efetuando a consulta para listagem
    $sql = "SELECT t.codigo, t.nome, c.nome, t.tipo, t.final, c.codigo FROM TiposAvaliacoes t, Modalidades c
    		WHERE t.modalidade = c.codigo $restricao ORDER BY t.nome limit " . ($item - 1) . ",$itensPorPagina";
//    echo $sql;
    $resultado = mysql_query($sql);
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        if ($linha[3] == 'avaliacao')
            $linha[3] = 'Avalia&ccedil;&atilde;o';
        if ($linha[3] == 'recuperacao')
            $linha[3] = 'Recupera&ccedil;&atilde;o';
        if ($linha[3] == 'pontoExtra')
            $linha[3] = 'Ponto Extra';
        if ($linha[4])
            $linha[4] = 'Final';
        else
            $linha[4] = '';
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        echo "<tr $cdif><td align='left'>$i</td><td align='left'>" . mostraTexto($linha[1]) . "</td><td align='left'>$linha[3] $linha[4]</td><td align='left'>" . mostraTexto($linha[2]) . " [$linha[5]]</td><td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='" . ICONS . "/remove.png' /></a><a href='#' title='Alterar' class='item-alterar' id='" . crip($linha[0]) . "'><img class='botao' src='" . ICONS . "/config.png' /></a><a href='#' title='Copiar' class='item-copiar' id='" . crip($linha[0]) . "'><img class='botao' src='" . ICONS . "/copiar.gif' /></a></td></tr>";
        $i++;
    }
    ?>
</table>

<?php require(PATH . VIEW . '/navegacao.php'); ?>

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
        <tr><td colspan="2">Copiar o tipo de avali&ccedil;&atilde;o para a modalidade abaixo:</td></tr>
        <input type="hidden" name="campoCopia" id="campoCopia" value="">
        <tr><td><select name="campoModalidade" id="campoModalidade">
<?php
$resultado = mysql_query("SELECT * FROM Modalidades ORDER BY nome");
$selected = ""; // controla a alteração no campo select
while ($linha = mysql_fetch_array($resultado)) {
    echo "<option value='$linha[0]'>$linha[1] [$linha[0]]</option>";
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

    retorno = true;
    function validaForm() {
        return retorno;
    }
    function validaItem(item) {
        var valor = $('#' + item).val();
        valor = valor.replace(",", ".");
        if (valor < 0 || valor > 100) {
            $('#' + item).val('');
        }
        else
            $('#' + item).val(valor);
    }

    function valida() {
        if ($('#campoTipo').val() == "avaliacao") {
            $('#campoCalculo').val('');
            $('#campoNotaMaior').val('');
            $('#campoNotaMenor').val('');
        }

        if ($('#campoCalculo').val() != "")
            $('#campoTipo').val('recuperacao');

        if ((($('#campoTipo').val() == "recuperacao" && $('#campoCalculo').val() != "") ||
                ($('#campoTipo').val() == "avaliacao")) && ($('#campoModalidade').val() != "" && $('#campoSigla').val() != "")) {
            $('#salvar').enable();
        } else {
            $('#salvar').attr('disabled', 'disabled');
        }
    }

    $(document).ready(function() {
        valida();
        $('#campoNotaMaior, #campoNotaMenor').change(function() {
            validaItem($(this).attr('id'));
        });

        $('#campoTipo, #campoCalculo, #campoModalidade, #campoSigla').change(function() {
            valida();
        });

        $(".item-excluir").click(function() {
            var codigo = $(this).attr('id');
            jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
                if (r)
                    $('#index').load('<?php print $SITE; ?>?opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
            });
        });

        $(".item-alterar").click(function() {
            var codigo = $(this).attr('id');
            $('#index').load('<?php print $SITE; ?>?codigo=' + codigo);
        });

        $(".item-copiar").click(function() {
            $('#campoCopia').val($(this).attr('id'));
            pop('popDiv');
        });
    });
</script>

<?php mysql_close($conexao); ?>