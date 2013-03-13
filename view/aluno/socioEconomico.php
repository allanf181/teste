<?php
//Esse arquivo é fixo para o aluno. Não entra em permissões
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;

// PARA PREENCHIMENTO POR PARTE DO ALUNO

$codigo = $_SESSION["loginCodigo"];

if ($_POST["opcao"] == 'InsertOrUpdate') {
    $raca = $_POST["campoRaca"];
    $estadoCivil = $_POST["campoEstadoCivil"];
    $numeroPessoasNaResidencia = $_POST["campoNumeroPessoasNaResidencia"];
    $renda = $_POST["campoRenda"];
    $situacaoTrabalho = $_POST["campoTrabalho"];
    $tipoTrabalho = $_POST["campoTipoTrabalho"];
    $empresaTrabalha = $_POST["campoEmpresaTrabalha"];
    $cargoEmpresa = $_POST["campoCargoEmpresa"];
    $tempo = $_POST["campoTempo"];
    $meioTransporte = $_POST["campoMeioTransporte"];
    $transporteGratuito = $_POST["campoTransporteGratuito"];
    $necessidadesEspeciais = $_POST["campoNecessidadesEspeciais"];
    $descricaoNecessidadesEspeciais = $_POST["campoDescricaoNecessidadesEspeciais"];
    $escolaPublica = $_POST["campoEscolaPublica"];

    if (!empty($codigo)) {
        $sql = "update Pessoas set escolaPublica='$escolaPublica', raca='$raca', estadoCivil='$estadoCivil', numeroPessoasNaResidencia='$numeroPessoasNaResidencia', renda='$renda', situacaoTrabalho='$situacaoTrabalho', tipoTrabalho='$tipoTrabalho', empresaTrabalha='$empresaTrabalha', cargoEmpresa='$cargoEmpresa', tempo='$tempo', meioTransporte='$meioTransporte', transporteGratuito='$transporteGratuito', necessidadesEspeciais='$necessidadesEspeciais', descricaoNecessidadesEspeciais='$descricaoNecessidadesEspeciais' where codigo=$codigo";
        //print $sql;
        $resultado = mysql_query($sql);
        if ($resultado == 1)
            mensagem('OK', 'TRUE_UPDATE');
        else
            mensagem('NOK', 'FALSE_UPDATE');
    }
}
?>
<h2><?php print_r( $TITLE); ?></h2>
<?php
// inicializando as variÃ¡veis do formulÃ¡rio
$raca = "";
$estadoCivil = "";
$numeroPessoasNaResidencia = "";
$renda = "";
$situacaoTrabalho = "";
$tipoTrabalho = "";
$empresaTrabalha = "";
$cargoEmpresa = "";
$tempo = "";
$meioTransporte = "";
$transporteGratuito = "";
$necessidadesEspeciais = "";
$descricaoNecessidadesEspeciais = "";

// consulta no banco
$sql = "select raca,estadoCivil,numeroPessoasNaResidencia,renda,situacaoTrabalho,
    		tipoTrabalho,empresaTrabalha,cargoEmpresa,tempo,meioTransporte,
        	transporteGratuito,necessidadesEspeciais, descricaoNecessidadesEspeciais, escolaPublica
        	from Pessoas where codigo=" . $codigo;
$resultado = mysql_query($sql);
$linha = mysql_fetch_row($resultado);

// armazena os valores nas variÃ¡veis
$raca = $linha[0];
$estadoCivil = $linha[1];
$numeroPessoasNaResidencia = $linha[2];
$renda = $linha[3];
$situacaoTrabalho = $linha[4];
$tipoTrabalho = $linha[5];
$empresaTrabalha = $linha[6];
$cargoEmpresa = $linha[7];
$tempo = $linha[8];
$meioTransporte = $linha[9];
$transporteGratuito = $linha[10];
$necessidadesEspeciais = $linha[11];
$descricaoNecessidadesEspeciais = $linha[12];
$escolaPublica = $linha[13];

print "<script>\n";
print "    $('#form_padrao').html5form({ \n";
print "        method : 'POST', \n";
print "        action : '$SITE', \n";
print "        responseDiv : '#index', \n";
print "        colorOn: '#000', \n";
print "        colorOff: '#000', \n";
print "        messages: 'br' \n";
print "    }) \n";
print "</script>\n";
print "<div id=\"html5form\" class=\"main\">\n";
print "<form action=\"$SITE\" method=\"post\" id=\"form_padrao\">\n";
?>
<input type="hidden" name="opcao" value="InsertOrUpdate" /> 
<table width="772" border="0" width="152" align="right">
    <tr>
        <td width="155" align="right">Estado Civil:</td>
        <td width="215"><select style="width:200px;" id="campoEstadoCivil" name="campoEstadoCivil" value="<?php echo $estadoCivil; ?>">
                <option value="">N&atilde;o declarado</option>
                <?php
                $resultado = mysql_query("select * from EstadosCivis order by nome");
                $selected = ""; // controla a altera&ccedil;&atilde;o no campo select
                while ($linha = mysql_fetch_array($resultado)) {
                    if ($linha[0] == $estadoCivil)
                        $selected = "selected";
                    echo "<option $selected value='$linha[0]'>$linha[1]</option>";
                    $selected = "";
                }
                ?>
            </select></td>
        <td align="right">Estudou em Escola P&uacute;blica: </td>
        <td><select id="campoEscolaPublica" name="campoEscolaPublica" value="<?php echo $escolaPublica; ?>">
                <option value="">N&atilde;o declarado</option>
                <option <?php echo ($escolaPublica == 'n') ? "selected='selected'" : ""; ?> value='n'>N&atilde;o</option>
                <option <?php echo ($escolaPublica == 's') ? "selected='selected'" : ""; ?> value='s'>Sim</option>
            </select></td>
    </tr>  
    <tr>
        <td align="right">Renda: </td>
        <td><select id="campoRenda" style="width:200px;" name="campoRenda" value="<?php echo $renda; ?>">
                <option value="">N&atilde;o declarado</option>
                <?php
                $resultado = mysql_query("select * from Rendas order by nome");
                $selected = ""; // controla a altera&ccedil;&atilde;o no campo select
                while ($linha = mysql_fetch_array($resultado)) {
                    if ($linha[0] == $renda)
                        $selected = "selected";
                    echo "<option $selected value='$linha[0]'>$linha[1]</option>";
                    $selected = "";
                }
                ?>
            </select></td>
        <td align="right">Ra&ccedil;a: </td>
        <td><select id="campoRaca" style="width:200px;" name="campoRaca" value="<?php echo $raca; ?>">
                <option value=""></option>
                <?php
                $resultado = mysql_query("select * from Racas order by nome");
                $selected = ""; // controla a altera&ccedil;&atilde;o no campo select
                while ($linha = mysql_fetch_array($resultado)) {
                    if ($linha[0] == $raca)
                        $selected = "selected";
                    echo "<option $selected value='$linha[0]'>$linha[1]</option>";
                    $selected = "";
                }
                ?>
            </select></td>	    	
    </tr>
    <tr>
        <td align="right">Trabalho: </td>
        <td><select id="campoTrabalho" style="width:200px;" name="campoTrabalho" value="<?php echo $situacaoTrabalho; ?>">
                <option value="">N&atilde;o declarado</option>
                <?php
                $resultado = mysql_query("select * from SituacoesTrabalho order by nome");
                $selected = ""; // controla a altera&ccedil;&atilde;o no campo select
                while ($linha = mysql_fetch_array($resultado)) {
                    if ($linha[0] == $situacaoTrabalho)
                        $selected = "selected";
                    echo "<option $selected value='$linha[0]'>$linha[1]</option>";
                    $selected = "";
                }
                ?>
            </select></td>
        <td align="right">Tempo de Trabalho: </td>
        <td><select id="campoTempo" style="width:200px;" name="campoTempo" value="<?php echo $tempo; ?>">
                <option value="">N&atilde;o declarado</option>
                <?php
                $resultado = mysql_query("select * from TemposPesquisa order by nome");
                $selected = ""; // controla a altera&ccedil;&atilde;o no campo select
                while ($linha = mysql_fetch_array($resultado)) {
                    if ($linha[0] == $tempo)
                        $selected = "selected";
                    echo "<option $selected value='$linha[0]'>$linha[1]</option>";
                    $selected = "";
                }
                ?>
            </select></td>
    </tr>
    <tr>
        <td align="right">Empresa: </td>
        <td><input type="text" size="20" name="campoEmpresaTrabalha" maxlength="45" value="<?php echo $empresaTrabalha; ?>"/></td>
        <td align="right">Cargo: </td>
        <td><input type="text" size="20" name="campoCargoEmpresa" maxlength="45" value="<?php echo $cargoEmpresa; ?>"/></td>
    </tr>
    <tr>
        <td align="right">Tipo: </td>
        <td><select id="campoTipoTrabalho" style="width:200px;" name="campoTipoTrabalho" value="<?php echo $tipoTrabalho; ?>">
                <option value="">N&atilde;o declarado</option>
                <?php
                $resultado = mysql_query("select * from TiposTrabalho order by nome");
                $selected = ""; // controla a altera&ccedil;&atilde;o no campo select
                while ($linha = mysql_fetch_array($resultado)) {
                    if ($linha[0] == $tipoTrabalho)
                        $selected = "selected";
                    echo "<option $selected value='$linha[0]'>$linha[1]</option>";
                    $selected = "";
                }
                ?>
            </select></td>
        <td align="right">N&ordm; Pessoas Resid&ecirc;ncia: </td>
        <td><input type="text" size="2" name="campoNumeroPessoasNaResidencia" maxlength="4" value="<?php echo $numeroPessoasNaResidencia; ?>"/></td>
    </tr>
    <tr>
        <td align="right">Meio transporte: </td>
        <td><select id="campoMeioTransporte" style="width:200px;" name="campoMeioTransporte" value="<?php echo $meioTransporte; ?>">
                <option value="">N&atilde;o declarado</option>
                <?php
                $resultado = mysql_query("select * from MeiosTransporte order by nome");
                $selected = ""; // controla a altera&ccedil;&atilde;o no campo select
                while ($linha = mysql_fetch_array($resultado)) {
                    if ($linha[0] == $meioTransporte)
                        $selected = "selected";
                    echo "<option $selected value='$linha[0]'>$linha[1]</option>";
                    $selected = "";
                }
                ?>
            </select></td>
        <td align="right">Necessidades Especiais: </td>
        <td><select id="campoNecessidadesEspeciais" style="width:200px;" name="campoNecessidadesEspeciais" value="<?php echo $necessidadesEspeciais; ?>">
                <option value="">N&atilde;o declarado</option>
                <option <?php echo ($necessidadesEspeciais == 'n') ? "selected='selected'" : ""; ?> value='n'>N&atilde;o</option>
                <option <?php echo ($necessidadesEspeciais == 's') ? "selected='selected'" : ""; ?> value='s'>Sim</option>
            </select></td>
    </tr>
    <tr>
        <td align="right">Transporte Gratuito: </td>
        <td><select id="campoTransporteGratuito" name="campoTransporteGratuito" value="<?php echo $transporteGratuito; ?>">
                <option value="">N&atilde;o declarado</option>
                <option <?php echo ($transporteGratuito == 'n') ? "selected='selected'" : ""; ?> value='n'>N&atilde;o</option>
                <option <?php echo ($transporteGratuito == 's') ? "selected='selected'" : ""; ?> value='s'>Sim</option>
            </select></td>
        <td align="right">Necessidades Especiais: </td>
        <td><textarea name="campoDescricaoNecessidadesEspeciais"><?php echo $descricaoNecessidadesEspeciais; ?></textarea></td>
    </tr>
    <tr><td></td><td>
            <input type="hidden" name="opcao" value="InsertOrUpdate" />
            <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                </tr></table>
        </td></tr>
</table>
</div>
