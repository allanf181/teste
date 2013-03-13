<?php
require '../../../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require FUNCOES;

// lista os alunos cadastrados

$restricao = ""; // padrÃ£Ã©em restriÃ§

if (!empty($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $restricao = " AND c.codigo=$curso";
}

$sql = "SELECT SUBSTRING(p.nome, 1, 37) as professor, SUBSTRING(d.nome, 1, 35) as disc, d.ch,
                (SELECT SUM(quantidade) FROM Aulas au WHERE au.atribuicao = a.codigo) as aulas,
                t.numero,
                SUBSTRING(c.nome, 1, 27) as curso
                FROM Disciplinas d, Cursos c, Atribuicoes a, Pessoas p, Turmas t, Professores pr
                WHERE d.curso = c.codigo
                AND p.codigo = pr.professor
                AND pr.atribuicao = a.codigo
                AND t.codigo = a.turma
                AND a.disciplina = d.codigo
                and t.ano=$ano 
				and (t.semestre=$semestre OR t.semestre=0)
                $restricao order by d.nome";
//print $sql;
$titulo = "LANÇAMENTO DE AULAS POR DISCIPLINA";
$titulo2 = "";
$rodape = $SITE_TITLE;

$fonte = 'Times';
$tamanho = 10;
$alturaLinha = 10;
$orientacao = "L"; //Landscape
//$orientacao = "P"; //Portrait
$papel = "A4";
$colunas = array("professor", "disc", "d.ch", "aulas", "t.numero", "curso");
$titulosColunas = array("PROFESSOR", "DISCIPLINA", "C. HORÁRIA", "QDE. AULAS", "TURMA", "CURSO");
$largura = array(70,80,22,23,20,60);

// gera o relatÃ³ em PDF
include PATH.LIB.'/relatorio_banco.php';
?>