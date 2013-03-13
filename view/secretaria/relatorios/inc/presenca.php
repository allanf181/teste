<?php
require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require FUNCOES;

if ($ano && $semestre){

	$turma="";
	$data = $_GET["data"];
	$restricao = ""; // padrão é sem restrição
	
	if (isset($_GET["turma"]) && !empty($_GET["turma"])){
	    $turma = dcrip($_GET["turma"]);
	}
	
	// restrições
	if (!empty($semestre))
	    $restricao.= " and (t.semestre=$semestre OR t.semestre=0)";
	if (!empty($turma))
	    $restricao.= " and t.codigo='$turma'";
	
	$sql = "select t.numero from Turmas t where t.codigo=$turma";
	$res = mysql_query($sql);
	$l = mysql_fetch_row($res);
	
	$sql = "select a.prontuario, a.nome, ''
	        from Pessoas a, PessoasTipos pt, Cidades c, Matriculas m, Turmas t, Atribuicoes at
	        where pt.pessoa = a.codigo and a.cidade=c.codigo and m.aluno=a.codigo and at.turma=t.codigo and m.atribuicao=at.codigo
	        and t.ano=$ano $restricao
	        and pt.tipo = $ALUNO
	        group by a.nome";

    //echo $sql;

    $titulo = "Lista de Presença $l[0] $data";
    $titulo2= "Assunto:";
		$rodape = $SITE_TITLE;

    $fonte = 'Times';
    $tamanho = 10;
    $alturaLinha = 10;
    //$orientacao = "L"; //Landscape
    $orientacao = "P"; //Portrait
    $papel = "A4";
    $titulosColunas = array("Prontuário", "Nome", "Assinatura");
    $colunas = array("a.prontuario", "a.nome", "");
    $largura = array(20, 120, 0);

    // gera o relatório em PDF
    include PATH.LIB.'/relatorio_banco.php';


}

?>