<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Navegação do Sistema
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//0

if (isset($_GET["item"]))
        $estado = $_GET["item"];

if (isset($_GET["ordem"]))
        $estado = $_GET["ordem"];

?>

<table id="listagemPaginacaoTopo" align="center">
        <tr>
            <td colspan="4" id="rodapeTabela">
                <?php 
               
                if ($item!=$primeiro){
                ?>
                    <a class="nav" href="javascript:$('#index').load('<?php print $SITENAV; ?>&item=<?php echo $primeiro; ?>&ordem=<?php echo $ordem; ?>'); void(0);" title="primeiro"><img class="botao" src="<?php print ICONS; ?>/rewind.png" /></a>
                <?php    
                } else {
                ?>
                    <img class="botao" title="botao desativado" src="<?php print ICONS; ?>/rewind_dis.png" />
                <?php    
                } // fecha o if
                if ($item!=$anterior){
                ?>
                    <a class="nav" href="javascript:$('#index').load('<?php print $SITENAV; ?>&item=<?php echo $anterior; ?>&ordem=<?php echo $ordem; ?>'); void(0);" title="anterior"><img class="botao" src="<?php print ICONS; ?>/wleft.png" /></a>
                <?php    
                } else {
                ?>
                    <img class="botao" title="botao desativado" src="<?php print ICONS; ?>/wleft_dis.png" />
                <?php    
                } // fecha o if
                
                // ADICIONANDO NUMEROS DAS PAGINAS
                $pagina = '';
                $pagAtual = intval($item/$itensPorPagina);
                if ($pagAtual >= 12) $pagina=$pagAtual-11;
                $ultimoItem = (intval($ultimo/$itensPorPagina) >= 23) ? ($itensPorPagina * (23+$pagina)) : $ultimo;
                for ($i=($pagina*$itensPorPagina); $i < $ultimoItem; $i+=$itensPorPagina){
                    if ( $ultimo/$itensPorPagina >= $pagina) {
	                    $size = ($pagina == $pagAtual) ?  4 : 2;
	                    $pagina++;
	                    //$pagina = str_pad($pagina, strlen(intval($ultimo/$itensPorPagina)), 0, STR_PAD_LEFT);
	                	print "<font size=\"$size\"><a href=\"javascript:$('#index').load('$SITENAV&item=".($i+1)."&ordem=$ordem'); void(0);\" title=\"Página $pagina\">$pagina</a></font>\n";
					}
                }
                // FIM DOS NUMEROS DE PAGINAS
                
                if ($item!=$proximo){
                ?>
                    <a class="nav" href="javascript:$('#index').load('<?php print $SITENAV; ?>&item=<?php echo $proximo; ?>&ordem=<?php echo $ordem; ?>'); void(0);" title="próximo"><img class="botao" src="<?php print ICONS; ?>/wright.png" /></a>
                <?php    
                } else {
                ?>
                    <img class="botao" title="botao desativado" src="<?php print ICONS; ?>/wright_dis.png" />
                <?php    
                } // fecha o if
                if ($item!=$ultimo){
                ?>
                    <a class="nav" href="javascript:$('#index').load('<?php print $SITENAV; ?>&item=<?php echo $ultimo; ?>&ordem=<?php echo $ordem; ?>'); void(0);" title="último"><img class="botao" src="<?php print ICONS; ?>/fastforward.png" /></a>
                <?php    
                } else {
                ?>
                    <img class="botao" title="botao desativado" src="<?php print ICONS; ?>/fastforward_dis.png" />
                <?php    
                } // fecha o if
                ?>
            </td>
        </tr>
        
  </table>
