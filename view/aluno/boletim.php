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

$turma = dcrip($_GET["turma"]);
$aluno = dcrip($_GET["aluno"]);
if (dcrip($_GET["bimestre"]))
	$bimestre = "AND at.bimestre = ". dcrip($_GET["bimestre"]);

// armazena os dados do cabeçalho do boletim
$sql = "select a.nome, t.numero, tu.nome, c.nome, a.prontuario 
		from Matriculas m, Pessoas a, Turmas t, Turnos tu, Cursos c, Modalidades mo 
		where m.aluno=a.codigo 
		and t.turno=tu.codigo 
		and c.codigo=t.curso 
		and mo.codigo = c.modalidade 
		and t.codigo='$turma'
		and a.codigo=$aluno";
	//echo $sql;
$result = mysql_query($sql);
$i=0;
while ($l = mysql_fetch_array($result)){
	$i++;
    $modulo = $l[1];
    $turno = $l[2];
    $curso = $l[3];
	$nome = $l[0];
	$prontuario = $l[4];
}

?>

<div id='alunos_cabecalho'>
    <img alt="foto" style="width: 150px; height: 130px" src="<?php print INC; ?>/file.inc.php?type=pic&id=<?php echo crip($aluno); ?>" />
    <div class="alunos_dados_nome"><?php echo $nome; ?></div><br />
    <div class="alunos_dados_prontuario"><?php echo "$prontuario"; ?></div>
</div>

<table id="tabela_alunos_cabecalho">
    <tr class='cdif'><th>Turma</th><th>Curso</th>
	<?php
	$resultadoGlobal = resultadoModulo($aluno, $turma);

	echo "<th style=\"width: 100px\">M&eacute;dia Global</th>\n";
	echo "<th style=\"width: 120px\">Frequ&ecirc;ncia Global</th>\n";
	?>
    <tr><td><?php echo $modulo; ?></td><td><?php echo $curso; ?></td>
	<?php
	echo "<td align=\"center\">".$resultadoGlobal['mediaGlobal']."</td>\n";
	echo "<td align=\"center\">".arredondar($resultadoGlobal['frequenciaGlobal'])."%</td>\n";
	?>
</tr>
</table>
<br />

<?php

// armazena os dados do cabeçalho do boletim
$sql = "select d.numero, d.nome, m.codigo, a.prontuario, at.bimestre, m.codigo, at.codigo, s.nome, at.status, d.codigo
		from Matriculas m, Pessoas a, Turmas t, Turnos tu, Cursos c, Atribuicoes at, Disciplinas d, Situacoes s
		where m.aluno=a.codigo 
		and at.turma=t.codigo 
		and d.codigo=at.disciplina
		and m.atribuicao=at.codigo
		and t.turno=tu.codigo 
		and c.codigo=t.curso
		and m.situacao=s.codigo
		and a.codigo=$aluno
		and t.codigo=$turma
		$bimestre
		order by at.bimestre, d.nome";
//echo $sql;
$resultado = mysql_query($sql);
$i=0;

while ($linha = mysql_fetch_array($resultado)){
	$i++;
    $dnome = $linha[1];
    $dcodigo = $linha[0];
    $aluno = $linha[2];
    $bimestre = $linha[4];
    $matricula = $linha[5];
    $atribuicao = $linha[6];
    $situacao = $linha[7];
    $status[] = $linha[8];

		$professores='';
		foreach(getProfessor($linha[6]) as $key => $reg)
			$professores[] = "<a target=\"_blank\" href=".$reg['lattes']."><font color='white'>".$reg['nome']."</font></a>";
		$professor = implode("<br>", $professores);

	if ($bimestre) $bimestre = " - $bimestre&ordm; BIMESTRE"; else $bimestre = '';
	
   	echo "<br><table id='tabela_boletim' align='center'>";
    echo "<tr class='cdif'><th colspan=\"2\">$dnome $bimestre</th><th style='width: 100px'>$dcodigo</th><th colspan=\"3\">$professor</tr>";

    // busca as avaliações da disciplina atual
    $sql = "SELECT av.nome, av.sigla, av.peso, ti.nome, ti.tipo, 
    		DATE_FORMAT(av.data, '%d/%m/%Y'), n.nota, a.calculo 
    		FROM Atribuicoes a 
    		left join Avaliacoes av on av.atribuicao=a.codigo 
    		left join Matriculas m on m.atribuicao=a.codigo 
    		left join Notas n on n.avaliacao=av.codigo and n.matricula=m.codigo
			left join TiposAvaliacoes ti on av.tipo=ti.codigo
 			left join Pessoas al on m.aluno=al.codigo 
 			left join Situacoes s on s.codigo = m.situacao 
 			WHERE n.matricula=$matricula 
 			AND a.codigo=$atribuicao
 			ORDER BY al.nome";
	//echo $sql;
	$avaliacoes = mysql_query($sql);

	$dados = resultado($matricula, $atribuicao);
	$aulaDada = $dados['auladada'];
	$media = $dados['media'];
	$frequencia = arredondar($dados['frequencia']);
	if (!$faltas = $dados['faltas']) $faltas = 0;
	
	if ($media) $situacao = $dados['situacao'];
  
    echo "<tr class='cdif'><th>Situa&ccedil;&atilde;o</th><th style='width: 100px'>Aulas Dadas</th><th style='width:100px'>Carga Hor.</th><th style='width:50px'>Faltas</th><th style='width: 100px'>Frequ&ecirc;ncia</th><th style='width: 100px'>M&eacute;dia</th></tr>";
    echo "<tr><td align='center'>$situacao</td><td align='center'>$aulaDada</td><td align='center'>".intval($dados['CH'])."</td><td align='center'>$faltas</td><td align='center'>$frequencia%</td><td align='center'>$media</td></tr>";
    echo "<tr class='cdif'><th colspan='3'>Avalia&ccedil;&atilde;o</th><th>Data</th><th>C&aacute;lculo</th><th>Nota</th></tr>";
    
   	$i=0;
	while ($avaliacao = mysql_fetch_row($avaliacoes)) {
		$avaliacaoData = $avaliacao[5];
	    $avaliacaoNome = $avaliacao[0]." (".$avaliacao[3].")";
	    $avaliacaoNota = $avaliacao[6];
	    $avaliacaoPeso = $avaliacao[2];
	    $avaliacaoTipo = $avaliacao[4];
		$avaliacaoCalculo = $avaliacao[7];
		
		if ($avaliacaoCalculo == 'media') $avaliacaoPeso = 'M&eacute;dia';
		if ($avaliacaoCalculo == 'soma') $avaliacaoPeso = 'Soma';
		if ($avaliacaoTipo == 'recuperacao') $avaliacaoPeso = '-';
                
                $cdif="class='cdif'";
                if ($i%2==1)
                    $cdif="class='cdif2'";
                
        echo "<tr $cdif><td align='center' colspan='3'>$avaliacaoNome</td><td align='center'>$avaliacaoData</td><td align='center'>$avaliacaoPeso</td><td align='center'>$avaliacaoNota</td></tr>";
	$i++;
        
        }
    echo "</table>";
    
    // reseta os vetores
    $avaliacaoData = null;
    $avaliacaoNome = null;
    $avaliacaoNota = null;
    $avaliacaoPeso = null;
    $avaliacaoTipo = null;
    $avaliacaoCalculo = null;
}

mysql_close($conexao);
?>
<?php
    echo "<br><div style='margin: auto'><a href=\"javascript:$('#index').load('$VOLTAR'); void(0);\" class='voltar' title='Voltar' ><img class='botao' src='".ICONS."/left.png'/></a></div>";
?>