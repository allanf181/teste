<?php
require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;
require FUNCOES;

$atribuicao = "";

$atribuicao = dcrip($_GET["atribuicao"]);

if (!empty($atribuicao))
    $restricao.= " and at.codigo=$atribuicao";


$sql = "select d.nome, t.numero from Disciplinas d, Turmas t, Atribuicoes a
    where a.turma=t.codigo
    and a.disciplina=d.codigo
    and a.codigo=$atribuicao;";
//echo $sql;
$resultado = mysql_query($sql);
$l = mysql_fetch_row($resultado);


$sql = "select a.prontuario, a.nome, s.nome, '', '', '', '', '', '', '', '', '', ''
from Pessoas a, PessoasTipos pt, Cidades c, Matriculas m, Turmas t, Atribuicoes at, Situacoes s
where pt.pessoa = a.codigo 
and m.situacao=s.codigo
and a.cidade=c.codigo 
and m.aluno=a.codigo 
and m.atribuicao=at.codigo
and at.turma=t.codigo
and t.ano=$ano $restricao 
and pt.tipo=$ALUNO
group by a.codigo
order by a.nome";

//echo $sql;

    $titulo = "Lista de Chamada $l[0] [$l[1]]";
    $titulo2 = "";
    $rodape = $SITE_TITLE;
    $fonte = 'Times';
    $tamanho = 8;
    $alturaLinha = 10;
    $orientacao = "L"; //Landscape
//    $orientacao = "P"; //Portrait
    $papel = "A4";
    $titulosColunas = array("Prontuário", "Nome", "Situação", "___/___", "___/___", "___/___", "___/___", "___/___", "___/___", "___/___", "___/___", "___/___", "___/___");
    $colunas = array("a.prontuario", "a.nome", "s.nome", "", "", "", "", "", "", "", "", "", "");
    $largura = array(20, 85, 20, 15, 15, 15, 15, 15, 15, 15, 15, 15, 0);

    // gera o relatório em PDF
    include PATH.LIB.'/relatorio_banco.php';

?>