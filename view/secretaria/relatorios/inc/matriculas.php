<?php
require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require FUNCOES;

if (isset($_GET["curso"]) && isset($_GET["turma"])) {

	$curso = dcrip($_GET["curso"]);
	$turma = dcrip($_GET["turma"]);
	$situacao = dcrip($_GET["situacao"]);
	$restricao = ""; // padrão é sem restrição
	
	// Consulta a turma
	$resultado = mysql_query("select t.numero, c.nome from Turmas t, Cursos c where t.curso=c.codigo and t.codigo=$turma");
	$linha=mysql_fetch_array($resultado);
	
	// restrições
	if (!empty($curso))
	    $restricao.= " and c2.codigo=$curso";
	    	
	if (!empty($turma))
	    $restricao.= " and t.codigo=$turma";

	if (!empty($situacao))
	    $restricao.= " and s.codigo=$situacao";
	    	
	$sql="select a.prontuario, upper(a.nome), s.nome, t.numero, SUBSTRING(c2.nome, 1, 32)
        from Pessoas a, PessoasTipos pt, Matriculas m, Turmas t, Cursos c2, Atribuicoes at, Situacoes s
        where pt.pessoa = a.codigo 
        and m.aluno=a.codigo 
        and m.atribuicao=at.codigo
        and m.situacao = s.codigo
        and at.turma=t.codigo 
        and t.curso=c2.codigo 
        $restricao
        and pt.tipo=$ALUNO 
        group by a.nome
        order by c2.nome, t.numero, a.nome";
	
	//echo $sql;

    $titulo = str_repeat(" ", 30)."Relatório de Matrículas";
    if ($turma) $titulo2 = str_repeat(" ", 30)." [$linha[0]] $linha[1]";
		$rodape = $SITE_TITLE;
    $fonte = 'Times';
    $tamanho = 12;
    $alturaLinha = 10;
    $orientacao = "L"; //Landscape 
    //$orientacao = "P"; //Portrait 
    $papel = "A4";
    $colunas = array(
        "al.prontuario",
        "a.nome", 
        "s.nome", 
        "t.numero", 
        "c2.nome", 
        );
    $titulosColunas = array("Prontuário", "Nome", "Situação", "Turma", "Curso");
    $largura = array(22,120,50,20,65,30);

    // gera o relatório em PDF
    include PATH.LIB.'/relatorio_banco.php';


}

?>