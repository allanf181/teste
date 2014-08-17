<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Exibe os dados estatísticos obtidos pela pesquisa sócio econômico dos discentes do Campus.
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

if (!in_array($ALUNO, $_SESSION["loginTipo"])) {

	// PARA VISUALIZAÇÃO POR PARTE DA SECRETARIA
	// FUNCOES
	function dadosTabela($titulo, $tabela, $campo, $ano, $semestre,$restricaoCurso,$restricaoTurma){
		global $ALUNO;

	    echo "<table class=\"socioeconomico\" align=\"center\">";
	    echo "<tr><th align=\"'center\" style='width: 50%' colspan='4'>$titulo</th></tr>";
	    
	    $i=0;
	    // busca os campos disponíveis
	    $sql = "select * from $tabela";
	    $res = mysql_query($sql);
	    $campos[0]='Não preenchido';
	    while ($linha = mysql_fetch_array($res))
	        $campos[$linha[0]]=$linha[1];
	    
	    // busca os dados
	    $sql = "select $campo from Pessoas p, PessoasTipos pt, Matriculas m, Atribuicoes a, Turmas t, Cursos c
	        WHERE pt.pessoa = p.codigo AND pt.tipo = $ALUNO
	        and t.curso=c.codigo
	        and m.aluno=p.codigo
	        and m.atribuicao=a.codigo
	        and a.turma=t.codigo
	        and (t.semestre=$semestre OR t.semestre=0)
	        and t.ano=$ano
	            $restricaoCurso $restricaoTurma
	        group by p.codigo
	            ";
	//    echo $sql;
	    $res = mysql_query($sql);
	    while ($linha = mysql_fetch_array($res)){
	        foreach ($campos as $cod => $nome)
	            if ($linha[0]==$cod){
	                $item[$cod]++;
	                $total++;
	            }
	    }
	    foreach ($campos as $cod=>$nome){
	        echo "<tr class='".((++$i%2==0) ? "cdif":"")."'><td align='center' style='width: 300px'>".mostraTexto($nome)."</td><td align='center' >".$item[$cod]."</td><td align='center' >".percentual($item[$cod], $total)."</td>";
	        if ($cod==0)
	            echo "<td rowspan='10' style='width: 20px;background: white'  id='total'>Total:\n".$total."</td>";
	        echo "</tr>";    
	    }
	    echo "</table>";
	}
	function dadosPessoa($titulo, $campo, $limites, $ano, $semestre,$restricaoCurso,$restricaoTurma){
		global $ALUNO;

	    echo "<table class=\"socioeconomico\" align=\"center\">";
	    echo "<tr><th colspan=4>$titulo</th></tr>";
	    
	    $sql = "select $campo from Pessoas  p, PessoasTipos pt, Matriculas m, Atribuicoes a, Turmas t, Cursos c
	        where pt.pessoa = p.codigo AND pt.tipo = $ALUNO
	        and t.curso=c.codigo
	        and m.aluno=p.codigo
	        and m.atribuicao=a.codigo
	        and a.turma=t.codigo
	        and (t.semestre=$semestre OR t.semestre=0)
	        and t.ano=$ano
	            $restricaoCurso $restricaoTurma
	        group by p.codigo"; 
	    //echo $sql;
	    $res = mysql_query($sql);
	    $i=0;
	    
	    while ($linha = mysql_fetch_array($res)){
	        $n=0;
	        if ($linha[0]=='0' || $linha[0]==""){
	            $item[6]++;
	            $total++;
	        }
	        for ($j=0; $j<count($limites); $j++) {
	            if ($linha[0]>=($limites[$n]+1) && $linha[0]<=$limites[++$n]){
	                $item[$j]++;
	                $total++;
	            }
	        }
	        $i++;
	    }
	    
	    $n=0;
	    echo "<tr class='".((++$i%2==1) ? "cdif":"")."'><td align='center' style='width: 300px' >Não preenchido</td>".
	         "<td align='center'>".$item[6]."</td><td align='center' >".percentual($item[6], $total)."</td>";        
	    echo "<td rowspan='10' style='background: white' id='total'>Total:\n".$total."</td>";
	    echo "</tr>";
	    
	    for ($j=0; $j<count($limites); $j++) {
	        if (!empty($limites[$j]))
	        echo "<tr class='".((++$i%2==1) ? "cdif":"")."'><td align='center'   style='width: 500px; background: none'>de ".($limites[$n]+1)." a ".$limites[++$n]."</td>".
	             "<td align='center'>".$item[$j]."</td><td align='center' >".percentual($item[$j], $total)."</td>";        
	        echo "</tr>";
	//        $n++;
	    }
	    echo "</table>";
	
	}
	
	function dadosPessoaEspecificos($titulo, $campo, $dados, $etiquetas, $ano, $semestre,$restricaoCurso,$restricaoTurma){
		global $ALUNO;

	    echo "<table class=\"socioeconomico\" align=\"center\">";
	    echo "<tr><th style='width: 50%' colspan=4>$titulo</th></tr>";
	    
	    $sql = "select $campo from Pessoas p, PessoasTipos pt, Matriculas m, Atribuicoes a, Turmas t , Cursos c
	        where pt.pessoa = p.codigo AND pt.tipo = $ALUNO
	        and t.curso=c.codigo
	        and m.aluno=p.codigo
	        and m.atribuicao=a.codigo
	        and a.turma=t.codigo
	        and (t.semestre=$semestre OR t.semestre=0)
	        and t.ano=$ano
	            $restricaoCurso $restricaoTurma
	        group by p.codigo"; 
	    //echo $sql;
	    $res = mysql_query($sql);
	    $i=0;
	    while ($linha = mysql_fetch_array($res)){
	        foreach ($dados as $cod => $valor) {
	            if ($linha[0]==$valor){
	                $item[$cod]++;
	                $total++;
	            }
	        }
	        $i++;
	    }
	    
	    foreach ($dados as $cod => $valor) {            
	        
	//        if (!empty($dados[$cod+1]))
	        echo "<tr class='".((++$i%2==1) ? "cdif":"")."'><td align='center'   style='width: 300px; background: none'>".mostraTexto($etiquetas[$cod])."</td>".
	             "<td align='center'>".$item[$cod]."</td><td align='center' >".percentual($item[$cod], $total)."</td>";        
	        if ($cod==0)
	            echo "<td rowspan='10' style='background: white; width: 20px' id='total'>Total:\n".$total."</td>";
	        echo "</tr>";
	    }
	    echo "</table>";
	
	}
	// FIM FUNCOES
	
	    $curso="";
	    $restricao="";
	    
	    if (isset($_GET['curso'])){
	        $curso=$_GET['curso'];
	        if (!empty($curso))
	            $restricaoCurso=" and t.curso=$curso";
	    }
	    if (isset($_GET["turma"])){
	        $turma=dcrip($_GET["turma"]);
	        if (!empty($turma))
	            $restricaoTurma=" and t.codigo=$turma";
	        
	    }
	?>
	<h2><?php print $TITLE; ?></h2>
	<table align="center" id="form" width="100%">
	        
	        
	        <tr><td align="right" style="width: 100px">Curso: </td><td>
	                <select name="campoCurso" id="campoCurso" value="<?php echo $curso; ?>" onChange="$('#index').load('<?php print $SITE; ?>?turma=<?php print crip($turma); ?>&curso=' + this.value);">
	                	<option value=''>Todos</option>
	                    <?php
	                    
	                        $resultado = mysql_query("select distinct c.codigo, c.nome from Cursos c, Turmas t where t.curso=c.codigo and (t.semestre=$semestre OR t.semestre=0) and t.ano=$ano order by nome");
	                        $selected=""; // controla a alteração no campo select
	                        while ($linha = mysql_fetch_array($resultado)){
	                            if ($linha[0]==$curso)
	                               $selected="selected";
	                            echo "<option $selected value='$linha[0]'>".mostraTexto($linha[1])."</option>";
	                            $selected="";
	                        }
	                        
	                    ?>
	                </select>
	                
	            </td></tr>
	        <tr><td align="right">Turma: </td><td>
	                <select name="campoTurma" id="campoTurma" value="<?php echo $turma; ?>" style="width: 650px" onChange="$('#index').load('<?php print $SITE; ?>?curso=<?php print $curso; ?>&turma=' + this.value);">
	                    <option value=''>Todas</option>
	                    <?php
	                        $resultado = mysql_query("select t.codigo, t.numero, c.nome, tt.nome from Turmas t,Cursos c,Turnos tt where t.curso = c.codigo and t.turno = tt.codigo and t.ano=$ano and (t.semestre=$semestre OR t.semestre=0) $restricaoCurso order by t.numero");
	                        $selected=""; // controla a alteração no campo select
	                        while ($linha = mysql_fetch_array($resultado)){
	                            if ($linha[0]==$turma)
	                               $selected="selected";
	                            echo "<option $selected value='".crip($linha[0])."'>$linha[1]</option>";
	                            $selected="";
	                        }
	                    ?>
	                </select>
	            
	        </td></tr>   
	        
	    </table><br />
	<?php
	
	    dadosTabela("Sexo", "Sexos", "sexo", $ano, $semestre,$restricaoCurso,$restricaoTurma); 
	    
	    dadosTabela("Cor/Raça", "Racas", "raca", $ano, $semestre,$restricaoCurso,$restricaoTurma); 
	    
	    dadosTabela("Estado Civil", "EstadosCivis", "estadoCivil", $ano, $semestre,$restricaoCurso,$restricaoTurma); 
	
	    dadosPessoa("Nº Residentes na Casa", "numeroPessoasNaResidencia",array(0,2,4,8,10,20), $ano, $semestre,$restricaoCurso,$restricaoTurma); 
	    
	    dadosTabela("Renda Familiar", "Rendas", "renda", $ano, $semestre,$restricaoCurso,$restricaoTurma); 
	    
	    dadosTabela("Situacão de Trabalho", "SituacoesTrabalho", "situacaoTrabalho", $ano, $semestre,$restricaoCurso,$restricaoTurma); 
	
	    dadosTabela("Tipos de Trabalho", "TiposTrabalho", "tipoTrabalho", $ano, $semestre,$restricaoCurso,$restricaoTurma); 
	    
	    dadosTabela("Tempo de Trabalho", "TemposPesquisa", "tempo", $ano, $semestre,$restricaoCurso,$restricaoTurma); 
	    
	    dadosTabela("Meio de Transporte", "MeiosTransporte", "meioTransporte", $ano, $semestre,$restricaoCurso,$restricaoTurma); 
	    
	    dadosPessoaEspecificos("Utiliza Transporte Público", "transporteGratuito",array('','s','n'),array('Não declarado','Sim','Não'), $ano, $semestre,$restricaoCurso,$restricaoTurma); 
	
	    dadosPessoaEspecificos("Necessidades Especiais", "necessidadesEspeciais",array('','s','n'),array('Não declarado','Sim','Não'), $ano, $semestre,$restricaoCurso,$restricaoTurma); 

	    dadosPessoaEspecificos("Estudou em Escola Pública", "escolaPublica",array('','s','n'),array('Não declarado','Sim','Não'), $ano, $semestre,$restricaoCurso,$restricaoTurma); 

}
mysql_close($conexao);
?>