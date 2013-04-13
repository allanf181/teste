<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Exibe a Folha de Trabalho Docente dos Professores
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

?>
<h2><font color="white"><?php print $TITLE; ?></font></h2>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<?php
// INICIO DA FTP PARA GED E COORDENAÇÃO

if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($COORD, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {

    if ($_GET["opcao"] == 'controleFTD') {
        $codigo = $_GET["codigo"];
        $v = $_GET["valor1"];
        $conferido = $_GET["conferido"];

        if ($conferido != 'false' && !$v) {
            $sql = "update FTDDados set valido=now(), solicitante='" . $_SESSION['loginCodigo'] . "' where codigo=$codigo";
        } else
            $sql = "update FTDDados set finalizado='', valido='', solicitacao='$v', solicitante='" . $_SESSION['loginCodigo'] . "' where codigo=$codigo";
        //print $sql;
        $resultado = mysql_query($sql);
        if ($resultado == 1)
            mensagem('OK', 'TRUE_INSERT');
        else
            mensagem('NOK', 'FALSE_INSERT');
    }

    // inicializando as variáveis
    $item = 1;
    $itensPorPagina = 50;
    $primeiro = 1;
    $anterior = $item - $itensPorPagina;
    $proximo = $item + $itensPorPagina;
    $ultimo = 1;

    // validando a página atual
    if (!empty($_GET["item"])) {
        $item = $_GET["item"];
        $anterior = $item - $itensPorPagina;
        $proximo = $item + $itensPorPagina;
    }

    // validando a página anterior
    if ($item - $itensPorPagina < 1)
        $anterior = 1;

    // descobrindo a quantidade total de registros
    $resultado = mysql_query("SELECT COUNT(*) 
											    		FROM Pessoas p, PessoasTipos pt, FTDDados fd
															WHERE p.codigo = pt.pessoa
															AND p.codigo = fd.professor
															AND pt.tipo = $PROFESSOR
															ORDER BY p.nome");
    $linha = mysql_fetch_row($resultado);
    $ultimo = $linha[0];

    // validando o próximo item
    if ($proximo > $ultimo) {
        $proximo = $item;
        $ultimo = $item;
    }

    // validando o último item
    if ($ultimo % $itensPorPagina > 0)
        $ultimo = $ultimo - ($ultimo % $itensPorPagina) + 1;

    $SITENAV = $SITE . '?';

    require PATH . VIEW . '/paginacao.php';
    ?>
    <table id="form" border="0" align="center" width="100%">
        <tr><th align="center" width="40">#</th><th>Professor</th><th>Entregue</th>
    <?php
    if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"]))
        echo "<th align=\"center\" style=\"width: 100px\">Coordenador</th>";

    if (in_array($COORD, $_SESSION["loginTipo"]) || in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
        echo "<th width=\"80\" title='Solicitar Corre&ccedil;&atilde;o?'>Corre&ccedil;&atilde;o</th>";
        echo "<th width=\"30\" title='Marcar como conferido?'>Conf?</th>";
    }
    echo "</tr>";
    // efetuando a consulta para listagem
    $sql = "SELECT fd.codigo, p.nome,
    		date_format(fd.finalizado, '%d/%m/%Y %H:%i'),
    		date_format(fd.valido, '%d/%m/%Y %H:%i'), fd.solicitacao,
    		(SELECT nome FROM Pessoas WHERE codigo = fd.solicitante),
    		p.codigo
				FROM Pessoas p, PessoasTipos pt, FTDDados fd
				WHERE p.codigo = pt.pessoa
				AND p.codigo = fd.professor
				AND fd.ano = '$ano'
				AND (fd.semestre = '$semestre' OR fd.semestre = 0)
				AND pt.tipo = $PROFESSOR
				ORDER BY p.nome";
    //print $sql;
    $resultado = mysql_query("$sql limit " . ($item - 1) . ",$itensPorPagina");
    $i = $item;
    while ($linha = mysql_fetch_array($resultado)) {
        $codigo = $linha[0];
        $checked = '';
        $correcao = 0;
        $bloqueado = '';
        if ($linha[2] == '' || $linha[2] == '00/00/0000 00:00')
            $linha[2] = 'NÃO';
        $valido = $linha[3];
        $solicitacao = $linha[4];
        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
        echo "<tr $cdif><td>$i</td>";
        echo "<td><a target='_blank' href='" . VIEW . "/secretaria/relatorios/inc/ftd.php?detalhada=1&professor=" . crip($linha[6]) . "'>" . mostraTexto($linha[1]) . "</a></td>";
        print "<td>$linha[2]</td>";

        if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
            if ($valido != "00/00/0000 00:00" && $valido != "")
                echo "<td>$linha[5]</td>";
            else
                echo "<td style='color:red; font-weight: bold'>pendente</td>";
        }

        // VERIFICA SE JÀ FOI CORRIGIDO
        if ($valido != "00/00/0000 00:00" && $valido != "") {
            echo "<td>Corrigido</a></td>";
            $checked = "checked='checked'";
        } else if ($solicitacao) {
            echo "<td align='center' colspan='3'><a href='#' title='Correção solicitada por $linha[5]'>Correção solicitada</a></td>";
            $correcao = 1;
        } else {
            if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"]) || in_array($COORD, $_SESSION["loginTipo"]))
                echo "<td align='center'><a href='#' title='Solicitar correção' onclick=\"return FTD('" . $linha[0] . "', '" . $linha[1] . "')\";><img class='botao campoCorrecao' id='" . crip($linha[0]) . "' name='$linha[0]' src='" . ICONS . "/cancel.png' /></a></td>";
        }

        if (!$correcao) {
            if ($valido != "00/00/0000 00:00" || $valido != "") {
                if (!in_array($ADM, $_SESSION["loginTipo"]) && !in_array($COORD, $_SESSION["loginTipo"]) && !in_array($GED, $_SESSION["loginTipo"]))
                    $bloqueado = "disabled='disabled' title='pendente'";

                if (!in_array($ADM, $_SESSION["loginTipo"]) && !in_array($GED, $_SESSION["loginTipo"]) && $checked)
                    $bloqueado = "disabled='disabled' title='Somente GED'";

                echo "<td align='center'>
			    <input $bloqueado type='checkbox' $checked id='" . $linha[0] . "' value='$valor' onclick=\"return confFTD(this.value, this.checked, '" . $linha[1] . "','" . $linha[0] . "')\";
			    </td>";
            } else {
                echo "<td>&nbsp;</td>";
            }
        }
        echo "</tr>";
        $i++;
    }
    mysql_close($conexao);
    ?>
        <script>
            function FTD(codigo, nome) {
                modo = 'Confirma a solicitação de correção na FTD de ' + nome + '? \n\n Motivo:';
                jPrompt(modo, '', '<?php print $TITLE; ?>', function(r)
                {
                    if (r) {
                        r = encodeURI(r);
                        $('#index').load('<?php print $SITE; ?>?opcao=controleFTD&codigo=' + codigo + '&valor1=' + r);
                    }
                });
            }
            function confFTD(value, checked, nome, codigo) {
                if (!checked)
                    modo = 'Confirma abrir a FTD de ' + nome + '?';
                else
                    modo = 'Confirma a conferência da FTD de ' + nome + '?\n\nAtenção: somente o GED poderá abrir novamente!';

                jConfirm(modo, 'Fechamento', function(r) {
                    if (r)
                        $('#index').load('<?php print $SITE; ?>?opcao=controleFTD&codigo=' + codigo + '&conferido=' + checked);
                    else
                        document.getElementById(codigo).checked = !checked;
                });
            }
        </script>
    <?php
}

// FIM FTD COORD
?>