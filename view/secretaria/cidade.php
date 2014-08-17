<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita visualizar os dados das cidades importadas do Nambei.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

// PARA QUALQUER APLICAÇÃO UTILIZANDO AJAX PESQUISANDO CIDADES
if ($_GET["ajaxCidade"]) {
    header('Cache-Control: no-cache');
    header('Content-type: application/xml; charset="utf-8"', true);
    $cod_estados = mysql_real_escape_string($_GET['codigo']);
    $cidades = array();
    $sql = "SELECT codigo, nome
		FROM Cidades
		WHERE estado=$cod_estados
		ORDER BY nome";
    $res = mysql_query($sql);

    while ($row = mysql_fetch_assoc($res)) {
        $cidades[] = array(
            'codigo' => $row['codigo'],
            'nome' => $row['nome'],
        );
    }
    echo( json_encode($cidades) );
    die;
}

require SESSAO;

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $codigo = $_POST["campoCodigo"];
    $nome = $_POST["campoNome"];
    $estado = dcrip($_POST["campoEstado"]);

    if (empty($codigo)) {
        $resultado = mysql_query("insert into Cidades values(0,'$nome','$estado')");
        if ($resultado == 1)
            mensagem('OK', 'TRUE_INSERT');
        else
            mensagem('NOK', 'FALSE_INSERT');

        $_GET["codigo"] = crip(mysql_insert_id());
    }
    else {
        $resultado = mysql_query("update Cidades set nome='$nome', estado='$estado' where codigo=$codigo");
        if ($resultado == 1)
            mensagem('OK', 'TRUE_UPDATE');
        else
            mensagem('NOK', 'FALSE_UPDATE');

        $_GET["codigo"] = crip($_POST["campoCodigo"]);
    }
}

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
    $resultado = mysql_query("delete from Cidades where codigo=$codigo");
    if ($resultado == 1)
        mensagem('OK', 'TRUE_DELETE');
    else
        mensagem('NOK', 'TRUE_DELETE');
    $codigo = null;
    $_GET["codigo"] = null;
    $_GET["estado"] = null;
}
?>

<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>


<?php
// inicializando as vari�veis do formul�rio
$codigo = "";
$nome = "";
$estado = "";
$restricao = "";

if (isset($_GET["estado"])) {
    $estado = dcrip($_GET["estado"]);
    $restricao .= " AND Cidades.estado=$estado";
}

if (!empty($_GET["codigo"])) {

    // consulta no banco
    $sql = "select * from Cidades where codigo=" . dcrip($_GET["codigo"]);
    //echo $sql;
    $resultado = mysql_query($sql);
    $linha = mysql_fetch_row($resultado);

    // armazena os valores nas vari�veis
    $codigo = $linha[0];
    $nome = $linha[1];
    $estado = $linha[2];
    $restricao = " AND Cidades.codigo=" . dcrip($_GET["codigo"]);
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
<table align="center" width="100%" id="form">
    <tr><td align="right">Estado: </td><td>
            <select name="campoEstado" id="campoEstado" value="<?php echo $estado; ?>">
                <option></option>
                <?php
                $resultado = mysql_query("select * from Estados order by nome");
                $selected = ""; // controla a alteração no campo select
                while ($linha = mysql_fetch_array($resultado)) {
                    if ($linha[0] == $estado)
                        $selected = "selected";
                    echo "<option $selected value='" . crip($linha[0]) . "'>$linha[1]</option>";
                    $selected = "";
                }
                ?>
            </select>
        </td></tr>
    <tr><td align="right">Nome: </td><td><input type="text" maxlength="145" name="campoNome" id="campoNome" value="<?php echo $nome; ?>"/></td></tr>
    <tr><td></td><td>
            <input type="hidden" value="<?php echo $codigo; ?>" name="campoCodigo" />
            <input type="hidden" name="opcao" value="InsertOrUpdate" />
            <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                    <td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Novo/Limpar</a></td>
                </tr></table>
        </td></tr>
</table>
</form>
</div>

<?php
// inicializando as vari�veis
$item = 1;
$itensPorPagina = 50;
$primeiro = 1;
$anterior = $item - $itensPorPagina;
$proximo = $item + $itensPorPagina;
$ultimo = 1;

// validando a p�gina atual
if (!empty($_GET["item"])) {
    $item = $_GET["item"];
    $anterior = $item - $itensPorPagina;
    $proximo = $item + $itensPorPagina;
}

// validando a p�gina anterior
if ($item - $itensPorPagina < 1)
    $anterior = 1;

// descobrindo a quantidade total de registros
$resultado = mysql_query("SELECT count(*) FROM Cidades, Estados 
        						WHERE Cidades.estado = Estados.codigo $restricao");
$linha = mysql_fetch_row($resultado);
$ultimo = $linha[0];

// validando o pr�ximo item
if ($proximo > $ultimo) {
    $proximo = $item;
    $ultimo = $item;
}

// validando o �ltimo item
if ($ultimo % $itensPorPagina > 0)
    $ultimo = $ultimo - ($ultimo % $itensPorPagina) + 1;

$SITENAV = "$SITE?";
if ($restricao)
    $SITENAV = $SITE . "?estado=" . crip($estado);

require(PATH . VIEW . '/navegacao.php');
?>

<table id="listagem" border="0" align="center">
    <tr><th align="center" width="40">#</th><th align="left">Cidade</th><th>Estado</th><th align="center" width="40">Ação</th></tr>
    <?php
    // efetuando a consulta para listagem
    $sql = "SELECT * FROM Cidades, Estados 
        WHERE Cidades.estado = Estados.codigo 
        $restricao
        ORDER BY Cidades.nome limit " . ($item - 1) . ",$itensPorPagina";
    //echo $sql;
    $resultado = mysql_query($sql);
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        $codigo = crip($linha[0]);
        echo "<tr $cdif><td align='center'>$i</td><td>$linha[1]</td><td align='left'>$linha[5]</td><td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='" . ICONS . "/remove.png' /></a><a href='#' title='Alterar' class='item-alterar' id='" . crip($linha[0]) . "'><img class='botao' src='" . ICONS . "/config.png' /></a></td></tr>";
        $i++;
    }
    ?>
</table>

<?php
require(PATH . VIEW . '/navegacao.php');

mysql_close($conexao);
?>   
<script>
    function atualizar(getLink) {
        var estado = $('#campoEstado').val();
        var URLS = '<?php print $SITE; ?>?estado=' + estado;
        if (!getLink)
            $('#index').load(URLS + '&item=<?php print $item; ?>');
        else
            return URLS;
    }

    function valida() {
        if ($('#campoEstado').val() == "" || $('#campoNome').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').enable();
        }
    }

    $(document).ready(function() {
        valida();
        $('#campoEstado, #campoNome').keyup(function() {
            valida();
        });

        $(".item-excluir").click(function() {
            var codigo = $(this).attr('id');
            jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
                if (r)
                    $('#index').load(atualizar(1) + '&opcao=delete&codigo=' + codigo + '&item=<?php print $item; ?>');
            });
        });

        $(".item-alterar").click(function() {
            var codigo = $(this).attr('id');
            $('#index').load(atualizar(1) + '&codigo=' + codigo);
        });

        $('#campoEstado').change(function() {
            atualizar();
        });
    });
</script>