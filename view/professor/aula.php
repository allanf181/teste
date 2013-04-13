<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

print "<h2>$TITLE</h2>\n";
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<?php
$atribuicao = dcrip($_GET["atribuicao"]);

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $codigo = $_POST["campoCodigo"];
    $data = dataMysql($_POST["campoData"]);
    $quantidade = $_POST["campoQuantidade"];
    $conteudo = $_POST["campoConteudo"];
    $atribuicao = $_POST["campoAtribuicao"];
    $anotacao = $_POST["campoAnotacao"];
    $atividade = $_POST["campoAtividade"];

    if (empty($codigo)) {
        $resultado = mysql_query("insert into Aulas values (0, '$data','$quantidade', '$conteudo', '$anotacao', '$atividade', $atribuicao)");
        if ($resultado == 1)
            mensagem('OK', 'TRUE_INSERT');
        else
            mensagem('NOK', 'FALSE_INSERT');
    }
    else {
        $sql = "update Aulas set data='$data', quantidade=$quantidade, conteudo='$conteudo', anotacao='$anotacao', atividade='$atividade' where codigo=$codigo";
        $resultado = mysql_query($sql);
        if ($resultado == 1)
            mensagem('OK', 'TRUE_UPDATE');
        else
            mensagem('NOK', 'FALSE_UPDATE');
    }
}

if ($_GET["opcao"] == 'delete') {
    $codigo = dcrip($_GET["codigo"]);
    $atribuicao = dcrip($_GET["atribuicao"]);
    $resultado = mysql_query("delete from Aulas where codigo=$codigo");
    if ($resultado == 1)
        mensagem('OK', 'TRUE_DELETE');
    else
        mensagem('NOK', 'FALSE_DELETE');
    $_GET['opcao'] = '';
}

if ($_GET['opcao'] == 'insert') {
    // inicializando as variáveis do formulário
    $codigo = "";
    //$data = date("d/m/Y", time()); // data atual
    $quantidade = "";
    $conteudo = "";
    $anotacao = "";
    $atribuicao = dcrip($_GET["atribuicao"]);

    if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
        $sql = "select a.codigo, DATE_FORMAT(data, '%d/%m/%Y'),
        	a.quantidade, a.conteudo, at.prazo, a.anotacao, a.atividade
            from Aulas a, Atribuicoes at
            where a.atribuicao=at.codigo
            and a.codigo=" . dcrip($_GET["codigo"]);
        //print $sql;
        $resultado = mysql_query($sql);
        $linha = mysql_fetch_row($resultado);
        $codigo = $linha[0];
        $data = $linha[1];
        $quantidade = $linha[2];
        $conteudo = $linha[3];
        $anotacao = $linha[5];
        $atividade = $linha[6];
    }

    print "<script>\n";
    print "    $('#form_padrao').html5form({ \n";
    print "        method : 'POST', \n";
    print "        action : '$SITE', \n";
    print "        responseDiv : '#professor', \n";
    print "        colorOn: '#000', \n";
    print "        colorOff: '#999', \n";
    print "        messages: 'br' \n";
    print "    }) \n";
    print "</script>\n";

    print "<div id=\"html5form\" class=\"main\">\n";
    print "<form action=\"$SITE\" method=\"post\" id=\"form_padrao\">\n";
    ?>
    <table align="center">
        <tr><td align="right">Semana:</td><td><select name="campoPlano" id="campoPlano"><option></option>;
    <?php
    $sql = "SELECT pa.conteudo, pa.semana, pe.numeroAulaSemanal 
                FROM PlanosAula pa, PlanosEnsino pe, Atribuicoes a, Disciplinas d, Turmas t
                WHERE pa.atribuicao = pe.atribuicao
                AND pe.atribuicao = a.codigo 
                AND a.disciplina = d.codigo 
                AND t.codigo = a.turma
                AND d.numero IN (SELECT d1.numero FROM Atribuicoes a1, Disciplinas d1 
                                    WHERE a1.disciplina = d1.codigo AND a1.codigo = $atribuicao)
                AND t.numero IN (SELECT t2.numero FROM Atribuicoes a2, Turmas t2 
                                    WHERE a2.turma = t2.codigo AND a2.codigo = $atribuicao )";
    
    $resultado = mysql_query($sql);
    while ($linha = mysql_fetch_array($resultado)) {
        echo "<option $selected value='$linha[0]'>Semana $linha[1] [" . abreviar($linha[0], 85) . "]</option>";
    }
    ?></select></td></tr>

        <tr><td align="right">Data: </td><td><input type="text" readonly class="data" size="10" id="data1" name="campoData" value="<?php echo $data; ?>" /></td></tr>
    <?php if (!$codigo) { ?>
            <tr><td align="right" style="width: 100px">Quantidade: </td><td>
                    <select name="campoQuantidade" id="2" value="<?php echo $quantidade; ?>">
        <?php
        for ($i = 1; $i <= 4; $i++) {
            $selected = '';
            if ($i == $quantidade)
                $selected = "selected";
            echo "<option $selected value=\"$i\">$i</option>";
        }
        ?>
                    </select>
                <?php } else { ?>
            <tr><td align="right">Quantidade: </td><td><input readonly style="width: 50px" type="text" maxlength="4" id="2" name="campoQuantidade" value="<?php echo $quantidade; ?>" />
                <?php } ?>
                M&aacute;ximo: 4 aulas. Caso tenha mais aulas, registre outra aula.</td></tr>
        <tr><td align="right">Bases/Conhecimentos Desenvolvidos: </td><td><textarea maxlength="200" rows="5" cols="80" id="3" name="campoConteudo" style="width: 600px; height: 150p"><?php echo $conteudo; ?></textarea></td></tr>
        <tr><td align="right">Atividades: </td><td><textarea maxlength="200" rows="5" cols="80" id="3" name="campoAtividade" style="width: 600px; height: 150p"><?php echo $atividade; ?></textarea></td></tr>
        <tr><td align="right">Anota&ccedil;&atilde;o de Aula: </td><td><textarea maxlength="200" rows="5" cols="80" id="4" name="campoAnotacao" style="width: 600px; height: 150p"><?php echo $anotacao; ?></textarea></td></tr>
        <tr><td align="right" colspan="2">A anota&ccedil;&atilde;o de aula n&atilde;o entra no di&aacute;rio, apenas o conte&uacute;do.</td></tr>

        <tr><td></td><td>
                <input type="hidden" name="campoAtribuicao" value="<?php echo $atribuicao; ?>" />
                <input type="hidden" name="campoCodigo" value="<?php echo $codigo; ?>" />
                <input type="hidden" name="opcao" value="InsertOrUpdate" />
                <input type="submit" disabled value="Salvar" id="salvar" />
            </td></tr>
    </table>
    </form>
    <?php
    echo "<br><div style='margin: auto'><a href=\"javascript:$('#professor').load('".$SITE."?atribuicao=".crip($atribuicao)."'); void(0);\" class='voltar' title='Voltar' ><img class='botao' src='" . ICONS . "/left.png'/></a></div>";

}
if ($_GET['opcao'] == '') {
    $sql = "select date_format(data, '%d/%m/%Y') data_formatada,
    a.quantidade, a.codigo, a.conteudo, a.data, d.nome, tu.nome,
    at.status, DATEDIFF(at.prazo, NOW()) as prazo, t.numero
    from Aulas a, Atribuicoes at, Disciplinas d, Turmas t, Turnos tu 
    where a.atribuicao=at.codigo 
    and at.disciplina=d.codigo 
    and at.turma=t.codigo 
    and t.turno=tu.codigo 
    and at.codigo=$atribuicao
    order by data";
    //echo $sql;
    $resultado = mysql_query($sql);
    $i = 1;
    $aulasDadas = 0;
    $linhasTabela = "";
    $disciplina = "";
    $FP = 0;

    while ($linha = mysql_fetch_array($resultado)) {
        $i % 2 == 1 ? $cdif = "class='cdif'" : $cdif = "class='cdif2'";
        $linhasTabela.= "<tr $cdif><td>$i</td><td><a class='nav' title='Clique aqui para lan&ccedil;ar as faltas.' href=\"javascript:$('#professor').load('" . VIEW . "/professor/frequencia.php?atribuicao=" . crip($atribuicao) . "&aula=" . crip($linha[2]) . "'); void(0);\">$linha[0]</a></td><td>$linha[1]</td><td>" . htmlspecialchars($linha[3]) . "</td>";
        if ($linha[7] || ($_SESSION['dataExpirou'] && ($linha[8] < 0 || $linha[8] == ''))) {
            $linhasTabela.="<td><a href='#' title='Di&aacute;rio Fechado'>Fechado</a></td>";
            $FP = 1;
        } else {
            $codigo = crip($linha[2]);
            $linhasTabela.= "<td><a href='#' title='Excluir' class='item-excluir' id=\"$codigo\"><img class='botao' src='" . ICONS . "/remove.png' /></a><a href=\"javascript:$('#professor').load('$SITE?opcao=insert&codigo=$codigo&atribuicao=" . crip($atribuicao) . "'); void(0);\" class='nav' title='Alterar'><img class='botao' src='" . ICONS . "/config.png' /></a></td>";
        }
        $linhasTabela.= "</tr>";
        $aulasDadas+=$linha[1];
        $disciplina = "$linha[5] ($linha[6])";
        $status = $linha[7];
        $turma = $linha[9];
        $i++;
    }
    if ($i == 1) {
        $sql = "select d.nome, at.status, DATEDIFF(at.prazo, NOW()) as prazo, t.numero
    	 		 FROM Atribuicoes at, Disciplinas d, Turmas t
    			 WHERE at.disciplina=d.codigo 
    			 AND at.turma = t.codigo
			    and at.codigo=$atribuicao";
        //echo $sql;
        $resultado = mysql_query($sql);
        while ($linha = mysql_fetch_array($resultado)) {
            $disciplina = $linha[0];
            $turma = $linha[3];
            $aulasDadas = 0;
            $i = 1;
            if ($linha[1] || ($_SESSION['dataExpirou'] && ($linha[2] < 0 || $linha[2] == ''))) {
                $linhasTabela.="<td colspan='5'><a href='#' title='Di&aacute;rio Fechado'>Fechado</a></td>";
                $FP = 1;
            }
        }
    }

    mysql_close($conexao);
    ?>

    <div id="etiqueta" align="center">
    <?php echo "<span class='rotulo_professor'>Turma:</span> $turma"; ?><br />
        <?php echo "<span class='rotulo_professor'>Disciplina:</span> $disciplina"; ?><br />
        <?php echo "<span class='rotulo_professor'>Dias: </span>" . ($i - 1); ?><br />
        <?php echo "<span class='rotulo_professor'>Aulas dadas: </span>$aulasDadas"; ?><br />
    </div>
    <hr><br>

    <table id="listagem" border="0" align="center">
        <tr class="listagem_tr"><th align="center" width="40">#</th><th align="center" width="100">Data</th><th align='center' width="50">Qtd</th><th align='center'>Conte&uacute;do</th><th align='center' width="40">A&ccedil;&atilde;o</th></tr>
    <?php echo $linhasTabela; ?>
    </table>

    <br />

    <center>    
        <input type="hidden" id="campoAtribuicao" name="campoAtribuicao" value="<?php echo crip($atribuicao); ?>" />
    <?php if ($FP == 0) print "<a class=\"nav\" href=\"javascript:$('#professor').load('$SITE?opcao=insert&atribuicao=" . crip($atribuicao) . "'); void(0);\" title=\"Cadastrar Nova\"><img class='botao' src='" . ICONS . "/add.png' /></a>"; ?>
    </center>

<?php
}

// DATA DE INICIO E FIM DA ATRIBUICAO PARA RESTRINGIR O CALENDARIO
$sql = "SELECT DATE_FORMAT( dataFim,  '%d/%m/%Y' ), 
            DATE_FORMAT( dataInicio,  '%d/%m/%Y' ), 
            DATEDIFF(prazo, NOW())
            FROM Atribuicoes 
            WHERE codigo = $atribuicao";

$resultado = mysql_query($sql);
while ($linha = mysql_fetch_array($resultado)) {
    $dataFim = $linha[0];
    $dataInicio = $linha[1];
    $prazo = $linha[2];
}

$sql = "SELECT DATEDIFF(data, NOW())
    	 		 FROM PrazosAulas
    			 WHERE atribuicao=$atribuicao";
//echo $sql;
$resultado = mysql_query($sql);
while ($linha = mysql_fetch_array($resultado))
    $diff_data = $linha[0];

if ($diff_data <= 0 && $diff_data > -($LIMITE_AULA_PROF))
    $limiteNovo = $LIMITE_AULA_PROF + ($LIMITE_AULA_PROF + ($diff_data));
else
    $limiteNovo = $LIMITE_AULA_PROF;

if (!$prazo || $prazo < 0)
    $dataInicio = date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") - $limiteNovo, date("Y")));
?>

<script>
    valida();
    function valida() {
        if ($('#data1').val() != "" && $('#2').val() != "" && $('#3').val() != "")
            $('#salvar').enable();
        else
            $('#salvar').attr('disabled', 'disabled');
    }

    $(document).ready(function() {
        $(".item-excluir").click(function() {
            var codigo = $(this).attr('id');
            jConfirm('Deseja continuar com a exclus&atilde;o?', '<?php print $TITLE; ?>', function(r) {
                if (r)
                    $('#professor').load('<?php print $SITE; ?>?opcao=delete&codigo=' + codigo + '&atribuicao=<?php print crip($atribuicao); ?>');
            });
        });

        $('#data1, #2, #3').change(function() {
            valida();
        });

        $('#campoPlano').change(function() {
            $('#3').val($('#campoPlano').val());
        });

        $('#3').maxlength({
            events: [], // Array of events to be triggerd    
            maxCharacters: 500, // Characters limit   
            status: true, // True to show status indicator bewlow the element    
            statusClass: "status", // The class on the status div  
            statusText: "caracteres restando", // The status text  
            notificationClass: "notification", // Will be added when maxlength is reached  
            showAlert: false, // True to show a regular alert message    
            alertText: "Limite de caracteres excedido!", // Text in alert message   
            slider: true // True Use counter slider    
        });

        $("#data1").datepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior',
            minDate: '<?php print $dataInicio; ?>',
            maxDate: '<?php print $dataFim; ?>'
        });
    });
</script>