<?php
// verifica se não está sendo chamado diretamente.
if (strpos($_SERVER["HTTP_REFERER"], LOCATION) == false) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
}
?>

<table width="100%" border="0" width="152" align="right" id='form'>
    <tr>
        <td width="250" align="right">Estado Civil:</td>
        <td width="215"><select style="width:200px;" id="campoEstadoCivil" name="estadoCivil" value="<?php echo $estadoCivil; ?>">
                <option value="">N&atilde;o declarado</option>
                <?php
                require CONTROLLER . '/estadoCivil.class.php';
                $estadocivil = new EstadosCivis();
                $res = $estadocivil->listRegistros();
                foreach ($res as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $estadoCivil)
                        $selected = "selected";
                    print "<option $selected value='" . $reg['codigo'] . "'>" . $reg['nome'] . "</option>";
                }
                ?>
            </select></td>
        <td align="right">Estudou em escola p&uacute;blica: </td>
        <td><select id="campoEscolaPublica" name="escolaPublica" value="<?php echo $escolaPublica; ?>">
                <option value="">N&atilde;o declarado</option>
                <option <?php echo ($escolaPublica == 'n') ? "selected='selected'" : ""; ?> value='n'>N&atilde;o</option>
                <option <?php echo ($escolaPublica == 's') ? "selected='selected'" : ""; ?> value='s'>Sim</option>
            </select></td>
    </tr>
    <tr>
        <td align="right">Qual a renda mensal bruta de sua fam&iacute;lia (soma dos rendimentos de todos que residem com você, incluindo você: </td>
        <td><select id="campoRenda" style="width:200px;" name="renda" value="<?php echo $renda; ?>">
                <option value="">N&atilde;o declarado</option>
                <?php
                require CONTROLLER . '/renda.class.php';
                $rendas = new Rendas();
                $res = $rendas->listRegistros();
                foreach ($res as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $renda)
                        $selected = "selected";
                    print "<option $selected value='" . $reg['codigo'] . "'>" . $reg['nome'] . "</option>";
                }
                ?>
            </select></td>
        <td align="right">Cor: </td>
        <td><select id="campoRaca" style="width:200px;" name="raca" value="<?php echo $raca; ?>">
                <option value=""></option>
                <?php
                require CONTROLLER . '/raca.class.php';
                $racas = new Racas();
                $res = $racas->listRegistros();
                foreach ($res as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $raca)
                        $selected = "selected";
                    print "<option $selected value='" . $reg['codigo'] . "'>" . $reg['nome'] . "</option>";
                }
                ?>
            </select></td>	    	
    </tr>
    <tr>
        <td align="right">Qual sua situa&ccedil;&atilde;o profissional atual: </td>
        <td><select id="campoTrabalho" style="width:200px;" name="situacaoTrabalho" value="<?php echo $situacaoTrabalho; ?>">
                <option value="">N&atilde;o declarado</option>
                <?php
                require CONTROLLER . '/situacaoTrabalho.class.php';
                $trabalho = new SituacoesTrabalho();
                $res = $trabalho->listRegistros();
                foreach ($res as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $situacaoTrabalho)
                        $selected = "selected";
                    print "<option $selected value='" . $reg['codigo'] . "'>" . $reg['nome'] . "</option>";
                }
                ?>
            </select></td>
        <td align="right">Tempo de trabalho: </td>
        <td><select id="campoTempo" style="width:200px;" name="tempo" value="<?php echo $tempo; ?>">
                <option value="">N&atilde;o declarado</option>
                <?php
                require CONTROLLER . '/tempoPesquisa.class.php';
                $tempoPesquisa = new TemposPesquisa();
                $res = $tempoPesquisa->listRegistros();
                foreach ($res as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $tempo)
                        $selected = "selected";
                    print "<option $selected value='" . $reg['codigo'] . "'>" . $reg['nome'] . "</option>";
                }
                ?>
            </select></td>
    </tr>
    <tr>
        <td align="right">Empresa: </td>
        <td><input type="text" size="20" name="empresaTrabalha" maxlength="45" value="<?php echo $empresaTrabalha; ?>"/></td>
        <td align="right">Cargo: </td>
        <td><input type="text" size="20" name="cargoEmpresa" maxlength="45" value="<?php echo $cargoEmpresa; ?>"/></td>
    </tr>
    <tr>
        <td align="right">Se trabalha, em qual característica se enquadra: </td>
        <td><select id="campoTipoTrabalho" style="width:200px;" name="tipoTrabalho" value="<?php echo $tipoTrabalho; ?>">
                <option value="">N&atilde;o declarado</option>
                <?php
                require CONTROLLER . '/tipoTrabalho.class.php';
                $tTrabalho = new TiposTrabalho();
                $res = $tTrabalho->listRegistros();
                foreach ($res as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $tipoTrabalho)
                        $selected = "selected";
                    print "<option $selected value='" . $reg['codigo'] . "'>" . $reg['nome'] . "</option>";
                }
                ?>
            </select></td>
        <td align="right">Quantas pessoas residem com voc&ecirc;, incluindo voc&ecirc;?: </td>
        <td><input type="text" size="2" name="numeroPessoasNaResidencia" maxlength="4" value="<?php echo $numeroPessoasNaResidencia; ?>"/></td>
    </tr>
    <tr>
        <td align="right">Meio de transporte: </td>
        <td><select id="campoMeioTransporte" style="width:200px;" name="meioTransporte" value="<?php echo $meioTransporte; ?>">
                <option value="">N&atilde;o declarado</option>
                <?php
                require CONTROLLER . '/meioTransporte.class.php';
                $meio = new MeiosTransporte();
                $res = $meio->listRegistros();
                foreach ($res as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $meioTransporte)
                        $selected = "selected";
                    print "<option $selected value='" . $reg['codigo'] . "'>" . $reg['nome'] . "</option>";
                }
                ?>
            </select></td>
        <td align="right">Possui necessidades especiais: </td>
        <td><select id="campoNecessidadesEspeciais" style="width:200px;" name="necessidadesEspeciais" value="<?php echo $necessidadesEspeciais; ?>">
                <option value="">N&atilde;o declarado</option>
                <option <?php echo ($necessidadesEspeciais == 'n') ? "selected='selected'" : ""; ?> value='n'>N&atilde;o</option>
                <option <?php echo ($necessidadesEspeciais == 's') ? "selected='selected'" : ""; ?> value='s'>Sim</option>
            </select></td>
    </tr>
    <tr>
        <td align="right">Transporte Gratuito: </td>
        <td><select id="campoTransporteGratuito" name="transporteGratuito" value="<?php echo $transporteGratuito; ?>">
                <option value="">N&atilde;o declarado</option>
                <option <?php echo ($transporteGratuito == 'n') ? "selected='selected'" : ""; ?> value='n'>N&atilde;o</option>
                <option <?php echo ($transporteGratuito == 's') ? "selected='selected'" : ""; ?> value='s'>Sim</option>
            </select></td>
        <td align="right">Se possui necessidades especiais, quais: </td>
        <td><textarea name="descricaoNecessidadesEspeciais"><?php echo $descricaoNecessidadesEspeciais; ?></textarea></td>
    </tr>
    <tr><td colspan="4">
            <input type="hidden" name="opcao" value="InsertOrUpdate" />
            <table width="100%"><tr><td><input type="submit" value="Salvar" id="salvar" /></td>
                    <td><a href="javascript:$('#index').load('<?php print $SITE; ?>'); void(0);">Novo/Limpar</a></td>
                </tr></table>    
</table>