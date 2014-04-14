
<?php
require $_SESSION['CONFIG'] ;
require LIB.'/relatorio.php';

require MYSQL;
require VARIAVEIS;

// lista os alunos cadastrados

if (isset ($_GET["curso"])&& isset ($_GET["turma"])){

	if (!empty($_GET["curso"]))
	    $curso = dcrip($_GET["curso"]);
	if (!empty($_GET["turma"]))
	    $turma = dcrip($_GET["turma"]);
	$restricao = ""; // padrão é sem restrição
	
	$conteudo = $cabecalho;
	$conteudo.= "<div id='container' style='font-family: helvetica'>";
	$conteudo.= "<h1>Alunos</h1>";
	$conteudo.="<table border='1' width='100%'>";
	$cor = "white";
	$n = 1;
	
	// restrições
	if (!empty($semestre))
	    $restricao.= " and (t.semestre=$semestre OR t.semestre=0)";
	if (!empty($curso))
	    $restricao.= " and t.curso=$curso";
	if (!empty($turma))
	    $restricao.= " and t.codigo=$turma";
	
	$sql = "select a.prontuario, a.nome, a.rg, 
	        date_format(a.nascimento, '%d/%m/%Y') nascimento , a.endereco, a.bairro, c.nome, 
	        a.telefone, a.celular, a.email, c2.nome 
	        from Pessoas a, PessoasTipos pt, Cidades c, Matriculas m, Turmas t, Cursos c2, Atribuicoes at
	        where pt.pessoa = a.codigo  
	        and a.cidade=c.codigo 
	        and m.aluno=a.codigo 
	        and m.atribuicao=at.codigo
	        and at.turma=t.codigo 
	        and t.curso=c2.codigo 
	        and t.ano=$ano 
	        $restricao
	        and pt.tipo=$ALUNO
	        group by a.nome
	        order by a.nome";
	        
	$resultado = mysql_query($sql);
	
	//print $sql;
	
	$conteudo.="<tr><th>#</th><th>Prontu&aacute;rio</th><th>Nome</th><th>RG</th><th>Nascimento</th><th>Endere&ccedil;o</th><th>Bairro</th><th>Cidade</th><th>Telefone</th><th>Celular</th><th>E-mail</th><th>Curso</th></tr>";
	
	while ($linha = mysql_fetch_array($resultado)) {
	    $conteudo.="<tr bgcolor='$cor'><td>$n</td><td>$linha[0]</td><td>".utf8_decode ($linha[1])."</td><td>".utf8_decode ($linha[2])."</td><td>".utf8_decode ($linha[3])."</td><td>".utf8_decode ($linha[4])."</td><td>".utf8_decode ($linha[5])."</td><td>".utf8_decode ($linha[6])."</td><td>".utf8_decode ($linha[7])."</td><td>".utf8_decode ($linha[8])."</td><td>".utf8_decode ($linha[9])."</td><td>".utf8_decode ($linha[10])."</td></tr>";
	
	
	    // alterna a cor de fundo da linha
	    $n++;
	    if ($n % 2 == 0)
	        $cor = "gray";
	    else
	        $cor = "white";
	}
	
	$conteudo.="</table>";
	
	echo $conteudo;
	
	//geraPDF($conteudo, 0); // retrato
	//geraPDF($conteudo, 1); // paisagem
}
?>