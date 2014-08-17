<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita visualizar o percentual de frequência de todos os alunos de uma determinada disciplina.
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

$data=date("d/m/Y", time());// data atual
if (isset($_GET["mes"]))
	$mes=$_GET["mes"];
if (isset($_GET["turma"])){
	$turma=dcrip($_GET["turma"]);
    $restricao.=" and Turmas.codigo=$turma";
}

?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>

<script>
    $(document).ready(function(){
        $('#maximizar').click(gerenciaMaximizar);
            var lastTd;
            var lastImage;
            var imagem1;
            var haItemAtivo;
            var textoLink;
            
            if ($('#menu').is(':hidden'))
                $('#maximizar').text('restaurar');
            
            function gerenciaMaximizar(){
                if ($('#maximizar').text()=='restaurar'){
                    textoLink='Maximizar';
                    $('#menu, #header, #menuEsquerdo').show();
                    $('body').css('width','100%');
                    $('#wrap').css('margin','0 auto');
                    $('#wrap').css('background',null);
                    $('#wrap').css('width','1024px');                    
                    $('body').css('background','#e1f2d0 url(<?php print $IMAGES; ?>/bg.jpg) repeat-y top center');
                    $('.right').css('width','794px');
                    $('#maximizar').text(textoLink);
                    $('#titulo').css('width', '100%');
                    
                }
                else{
                    textoLink='restaurar';
                    $('#menu, #header, #menuEsquerdo').hide();
                    $('body').css('width','100%');
                    $('#wrap').css('margin','0');
                    $('#wrap').css('background',null);
                    $('#wrap').css('width','100%');
                    $('body').css('background','white');
                    $('.right').css('width','100%');
                    $('#maximizar').text(textoLink);
                    $('#titulo').css('width', '250px');
                }
            }
            
        if ($('#maximizar').text()=='restaurar')
                $('#titulo').css('width', '250px');
            else
                $('#titulo').css('width', '100%');

    });
</script>
    <table align="center" id="form" width="100%">
        <tr><td align="right" style="width: 100px">Mês:</td><td>
                <select id="campoMes" name="campoMes" onChange="$('#index').load('<?php print $SITE; ?>?turma=<?php print crip($turma); ?>&mes=' + this.value);">
                    <?php
                    foreach (array("Janeiro","Fevereiro","Março","Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro") as $n=> $nomeMes){                        
                        $selected = "";
                        if ($n==$mes)
                            $selected = "selected='selected'";
                        echo "<option $selected value='$n'>$nomeMes</option>\n";
                    }
                    ?>
                    
                </select>
            </td></tr>
        <tr><td align="right">Turma: </td><td>
                <select name="campoTurma" id="campoTurma" value="<?php echo $turma; ?>" style="width: 650px" onChange="$('#index').load('<?php print $SITE; ?>?mes=<?php print $mes; ?>&turma=' + this.value);">
                    <?php
    					if (in_array($COORD, $_SESSION["loginTipo"]))
							$restricaoCoord = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador=".$_SESSION['loginCodigo'].")";
                        
                        $sql = "SELECT t.codigo, t.numero, c.nome, m.nome, m.codigo, c.codigo
	                        							FROM Turmas t, Cursos c, Modalidades m
	                        							WHERE t.curso = c.codigo 
	                        							AND m.codigo = c.modalidade
	                        							AND ano = $ano 
	                        							AND (semestre=$semestre OR semestre=0) 
	                        							$restricaoCoord
	                        							ORDER BY c.nome, t.numero";
                        
                        $resultado = mysql_query($sql);
                        $selected=""; // controla a alteração no campo select
                        if (mysql_num_rows($resultado)){
                            echo "<option></option>";
                            while ($linha = mysql_fetch_array($resultado)){
                                if ($linha[0]==$turma)
                                   $selected="selected";
                         				if ($linha[4] < 1000 || $linha[4] >= 2000) $linha[2] = "$linha[2] [$linha[3]]";				
                                echo "<option $selected value='".crip($linha[0])."'>[$linha[1]] $linha[2] ($linha[5])</option>";
                                $selected="";
                            }
                        }
                        else{
                            echo "<option>Não há turmas cadastrados neste semestre/ano letivo</option>";
                        }
                    ?>
                </select>
        </td></tr>  
    </table>    
    <?php
    if (!empty($_GET["turma"])) { // se o parâmetro não estiver vazio
        
        // consulta no banco
        $sql = "SELECT p.codigo, date_format(au.data, '%d/%m'), au.quantidade, 
        		IfNULL(f.quantidade,0), upper(p.nome), au.codigo, d.nome,
        		at.codigo, au.data, m.aluno
                FROM Atribuicoes at
                join Disciplinas d on at.disciplina=d.codigo
                join Aulas au on au.atribuicao=at.codigo
                join Frequencias f on f.aula=au.codigo
                join Matriculas m on f.matricula=m.codigo
                join Pessoas p on m.aluno=p.codigo
                where at.turma=$turma
                and date_format(au.data, '%m')=".($mes+1)."
                group by p.nome, au.codigo
                order by au.data, p.nome
                ";
        //print $sql;
        $resultado = mysql_query($sql);
        while ($linha = mysql_fetch_array($resultado)) {
            $datas[] = $linha[1];
            $aulas[$linha[1]][$linha[5]] = $linha[2];
            $disciplinas[$linha[5]] = $linha[6];
           
						$frequencias[$linha[0]][$linha[5]] = ($A = getFrequenciaAbono($linha[9], $linha[7], $linha[8])) ? $A['sigla'] : $linha[3];
            //$frequencias[$linha[0]][$linha[5]] .= $linha[3];
            $nomes[$linha[0]] = $linha[4];

						$professor='';			
						foreach(getProfessor($linha[7]) as $key => $reg)
							$professor[] = $reg['nome'];
						$professores[$linha[5]] = implode("<br>", $professor);            
        }
    }
    
    if (!empty($turma)){
    ?>
    <table id="frequencias" border="0" align="center" width="100%">
        <tr><th align="center" width="10">#</th><th align="center"  style='width: 300px'>Nome</th>    
            <?php
            if ($datas)
            foreach (array_unique($datas) as $data) {
                $d[] = $data;
                echo "<td align='center'>";
                foreach ($aulas[$data] as $cod=>$n) {
                    $textoData=$data;
                }
                echo "$textoData</td>";
            }
            $i = 1;
            if ($nomes)
            foreach ($nomes as $c => $nome) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                echo "<tr $cdif><td align='center'>$i</td><td>".mostraTexto($nome)."</td>";
                foreach ($aulas as $data => $codAulas) {
                    $conteudo="";
                    $cor = '';
                    foreach ($codAulas as $codAula => $n) {
                        if ($frequencias[$c][$codAula] > 0 && $frequencias[$c][$codAula] <= $codAulas)
                            $cor = '#FFCCCC';
                        $conteudo.= "<a href='#' title='".mostraTexto($disciplinas[$codAula])."(".mostraTexto($professores[$codAula]).")\n$n aulas' > (".$frequencias[$c][$codAula].")</a>";
                    }
                     echo "<td align='center' bgcolor='$cor'>$conteudo</td>";
                }
                $i++;
            }
            ?>
    </table>
    <div style="text-align: center; margin-top: 10px"><a href="#" id="maximizar">Maximizar</a></div>
<?php 
}
mysql_close($conexao);
?>