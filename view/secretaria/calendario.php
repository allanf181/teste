<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita a visualização do calendário do ano letivo corrente do Campus.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

$ano = $_SESSION["ano"];


if ($_POST["opcao"] == 'InsertOrUpdate') {
    $codigo = dcrip($_POST["campoCodigo"]);

    if ($_POST["campoDataInicio"]) {
        $dataInicio = dataMysql($_POST["campoDataInicio"]);
        $dataInicio = new DateTime($dataInicio);
    }

    if ($_POST["campoDataFim"]) {
        $dataFim = dataMysql($_POST["campoDataFim"]);
        $dataFim = new DateTime($dataFim);
    }

    $ocorrencia = $_POST["campoOcorrencia"];
    $diaLetivo = ($_POST["campoDiaLetivo"]) ? 1 : 0;

    if (empty($codigo)) {
        if ($_POST["campoDataInicio"] && $_POST["campoDataFim"]) {
            while ($dataInicio <= $dataFim) {
                $data = $dataInicio->format('Y-m-d');
                $sql = "insert into Calendarios values(0, '$data', '$diaLetivo', '$ocorrencia')";
                $resultado = mysql_query($sql);
                $dataInicio->add(new DateInterval('P1D'));
            }
        } else {
            $data = dataMysql($_POST["campoDataInicio"]);
            $sql = "insert into Calendarios values(0, '$data', '$diaLetivo', '$ocorrencia')";
            $resultado = mysql_query($sql);
            $_GET["codigo"] = crip(mysql_insert_id());
        }
        if ($resultado == 1)
            mensagem('OK', 'TRUE_INSERT');
        else
            mensagem('NOK', 'FALSE_INSERT');
    }
    else {
        $data = dataMysql($_POST["campoDataInicio"]);
        $resultado = mysql_query("update Calendarios set data='$data', diaLetivo='$diaLetivo', ocorrencia='$ocorrencia' where codigo=$codigo");
        if ($resultado == 1)
            mensagem('OK', 'TRUE_UPDATE');
        else
            mensagem('NOK', 'FALSE_INSERT');

        $_GET["codigo"] = $_POST["campoCodigo"];
    }
}

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
    $resultado = mysql_query("delete from Calendarios where codigo=$codigo");
    if ($resultado == 1)
        mensagem('OK', 'TRUE_DELETE');
    else
        mensagem('NOK', 'FALSE_DELETE');
    $_GET["codigo"] = null;
    $_GET['dataInicio'] = null;
}
?>
<h2><?php print $TITLE; ?></h2>

<?php
// inicializando as variáveis do formulário
$codigo = "";
$ocorrencia = "";
$dataInicio = "";
$dataFim = "";

$dataInicio = $_GET['dataInicio'];
$dataFim = $_GET['dataFim'];

if ($dataFim && $dataInicio) {
    $restricao = " AND data >= '$dataInicio' AND data <= '$dataFim'";
} else {
    if (!empty($dataInicio))
        $restricao = " AND data='$dataInicio'";
    if (!empty($dataFim))
        $restricao = " AND data='$dataFim'";
}

if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
    // consulta no banco
    $resultado = mysql_query("SELECT codigo, DATE_FORMAT(data, '%d/%m/%Y'), DATE_FORMAT(data, '%d/%m/%Y'), ocorrencia
	        						 FROM Calendarios WHERE codigo=" . dcrip($_GET["codigo"]));
    $linha = mysql_fetch_row($resultado);

    // armazena os valores nas variáveis
    $codigo = $linha[0];
    $dataInicio = $linha[1];
    $diaLetivo = $linha[2];
    $ocorrencia = $linha[3];

    $restricao = " and Calendarios.codigo=" . dcrip($_GET["codigo"]);
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
    <input type="hidden" value="<?php echo crip($codigo); ?>" name="campoCodigo" id="campoCodigo" />
    <tr><td  style="width: 100px" align="right">Data: </td><td>
            <input type="text" size="10" value="<?php echo $dataInicio; ?>" name="campoDataInicio" id="campoDataInicio" />
            a <input type="text" size="10" value="<?php echo $dataFim; ?>" name="campoDataFim" id="campoDataFim" />
        </td>
    </tr>
    <tr><td align="right">Ocorr&ecirc;ncia:</td><td><input type="text" size="50" maxlength="255" name="campoOcorrencia" id="campoOcorrencia" value="<?php echo $ocorrencia; ?>" />
        </td></tr>
    <tr><td align="right"></td><td>
<?php if ($diaLetivo) $checked = 'checked'; ?>
            <input type="checkbox" id="campoDiaLetivo" name="campoDiaLetivo" <?php echo $checked; ?> /> Dia letivo
        </td>
    </tr>
    <tr><td></td><td>
            <input type="hidden" name="opcao" value="InsertOrUpdate" />
            <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                    <td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Novo/Limpar</a></td>
                </tr></table>
        </td></tr>
</table>
</form>
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
$resultado = mysql_query("SELECT count(*) 
	    							FROM Calendarios 
	    							WHERE date_format(data, '%Y') = '$ano' $restricao");
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

$SITENAV = $SITE . "?dataInicio=$dataInicio&dataFim=$dataFim";

require(PATH . VIEW . '/navegacao.php');
?>

<table id="listagem" border="0" align="center">
    <tr><th>Data</th><th>Ocorr&ecirc;ncia</th><th>Dia Letivo</th><th width="50">A&ccedil;&atilde;o</th></tr>
<?php
// efetuando a consulta para listagem
$sql = "SELECT codigo, date_format(data, '%d/%m/%Y'), ocorrencia, diaLetivo 
	    		FROM Calendarios 
	    		WHERE date_format(data, '%Y') = '$ano' $restricao 
	    		ORDER BY data DESC limit " . ($item - 1) . ",$itensPorPagina";
//echo $sql;
$resultado = mysql_query($sql);
$i = $item;
while ($linha = mysql_fetch_array($resultado)) {
    $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
    $codigo = crip($linha[0]);
    $diaLetivo = ($linha[3]) ? 'Sim' : 'N&atilde;o';
    echo "<tr $cdif><td>$linha[1]</td><td>" . mostraTexto($linha[2]) . "</td><td>$diaLetivo</td><td align='center'><a href='#' title='Excluir' class='item-excluir' id='" . crip($linha[0]) . "'><img class='botao' src='" . ICONS . "/remove.png' /></a><a href='#' title='Alterar' class='item-alterar' id='" . crip($linha[0]) . "'><img class='botao' src='" . ICONS . "/config.png' /></a></td></tr>";
    $i++;
}
?>
</table>
    <?php require(PATH . VIEW . '/navegacao.php'); ?>

<script>
    function atualizar(getLink) {
        var dataInicio = $('#campoDataInicio').val();
        var dataFim = $('#campoDataFim').val();
        var URLS = '<?php print $SITE; ?>?dataInicio=' + dataInicio + '&dataFim=' + dataFim;
        if (!getLink)
            $('#index').load(URLS + '&item=<?php print $item; ?>');
        else
            return URLS;
    }

    function valida() {
        if ($('#campoDataInicio').val() == "" || $('#campoOcorrencia').val() == "") {
            $('#salvar').attr('disabled', 'disabled');
        } else {
            $('#salvar').enable();
        }
    }

    $(document).ready(function() {
        valida();
        $('#campoDataInicio, #campoOcorrencia').change(function() {
            valida();
        });

        $("#campoDataInicio, #campoDataFim").datepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior'
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

<?php if (!$_GET["codigo"]) { ?>
            $('#campoDataInicio, #campoDataFim').change(function() {
                atualizar();
            });
<?php } ?>
    });
</script>


<style>
    .calendario {
        width: 400px;
        /*border-collapse: collapse;*/
        /*border: 1px solid #333;*/
        /*background-color: #FBFBFB;*/
        text-align: center;
        /*     color: white;*/
    }

    caption {
        padding: 5px 0 5px 0;
        font: small-caps bold 11px verdana, arial, tahoma;
        background-color: #999;
        border: 1px solid #333;
    }

    .calendario_td:hover{
        background: red;
    }
</style>

<?php
$sql = "SELECT codigo, date_format(data, '%d') as dia, date_format(data, '%m') as mes, ocorrencia, diaLetivo 
			FROM Calendarios WHERE date_format(data, '%Y') = '$ano'";
$resultado = mysql_query($sql);
while ($linha = mysql_fetch_array($resultado)) {
    $ocorrencia[$linha[2]][$linha[1]][$linha[0]] = $linha[3];
    $diaOcor[$linha[2]][$linha[1]][$linha[0]] = $linha[4];
    $diaEq[$linha[2]][$linha[1]] = $linha[3];
}

$domingo = "style=color:#C30;";

for ($j = 1; $j <= 12; $j++) {
    $mes = $j;
    $dia = date("d");
    $ano_ = substr($ano, -2);

    print "<table><tr><td valign=\"top\">\n";

    print "<h3 align='center'>" . ucfirst(meses($mes)) . " " . $ano . "</h3>\n";
    print "<table id='tabela_boletim' style='width: 400px' summary=\"Calendário\" class=\"calendario\">\n";

    print "<thead>\n";
    print "<tr>\n";
    foreach (diasDaSemana() as $dCodigo => $dNome) {
        print "<th style='color: white; width: 10%' abbr=\"Domingo\" title=\"$dNome\"><b>$dNome</b></th>\n";
    }
    print "</tr>\n";
    print "</thead>\n";
    print "<tbody>\n";

    $Data = strtotime($mes . "/" . $dia . "/" . $ano_);
    $Dia = date('w', strtotime(date('n/\0\1\/Y', $Data)));
    $Dias = date('t', $Data);
    $n = 0;
    for ($i = 1, $d = 1; $d <= $Dias;) {
        $cdif = "class='cdif2'";
        if ($n % 2 == 0)
            $cdif = "class='cdif'";

        echo("<tr $cdif >");
        for ($x = 1; $x <= 7 && $d <= $Dias; $x++, $i++) {
            if ($i > $Dia) {
                $destaque = '';
                if ($x == 1) {
                    $destaque = $domingo;
                }
                //if ($d == $dia) { $destaque = $hoje; }
                $d = str_pad($d, 2, "0", STR_PAD_LEFT);
                $j = str_pad($j, 2, "0", STR_PAD_LEFT);
                if ($ocorrencia[$j][$d]) {
                    foreach ($ocorrencia[$j][$d] as $oCodigo => $oNome) {
                        if ($diaOcor[$j][$d][$oCodigo] == 0)
                            $color = 'red';
                        else
                            $color = 'blue';
                        $destaque = "style=color:$color; background: red";
                    }
                }
                echo("<td " . $destaque . " title='" . $oNome . "'>" . $d++ . "</td>");
                $oNome = "";
            }
            else {
                echo("<td class='calendario'> </td>");
            }
        }
        for (; $x <= 7; $x++) {
            echo("<td class='calendario'> </td>");
        }
        echo("</tr>");
        $n++;
    }

    print "</tbody>\n";
    print "</table>\n";
    print "</td><td>\n";

    for ($o = 1; $o <= 31; $o++) {
        $o = str_pad($o, 2, "0", STR_PAD_LEFT);
        $j = str_pad($j, 2, "0", STR_PAD_LEFT);
        if ($ocorrencia[$j][$o]) {
            foreach ($ocorrencia[$j][$o] as $oCodigo => $oNome) {
                $no = str_pad($o + 1, 2, "0", STR_PAD_LEFT);
                if ($diaEq[$j][$o] != $diaEq[$j][$no]) {
                    if ($diaOcor[$j][$o][$oCodigo] == 0)
                        $color = 'red';
                    else
                        $color = 'blue';
                    print "<p><font size=\"1px\" color=\"$color\">$diaEqInicio $o - " . mostraTexto($ocorrencia[$j][$o][$oCodigo]) . "</font></p>\n";
                    $diaEqInicio = "";
                } else {
                    $diaEqInicio .= "$o, ";
                }
            }
        }
    }

    print "</td>\n";
    print "</tr></table>\n";
}

mysql_close($conexao);
?>