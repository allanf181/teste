<?php

require '../../../../inc/config.inc.php';
require PATH . LIB . '/relatorio.php';

require VARIAVEIS;
require FUNCOES;

require CONTROLLER . '/aluno.class.php';
$aluno = new Alunos();

if (dcrip($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $params['curso'] = $curso;
    $sqlAdicional .= ' AND c2.codigo = :curso ';
}

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND t.codigo = :turma ';
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $params['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicional .= " AND c2.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord) ";
}

$camposExtra .= ", a.rg";
$camposExtra .= ", date_format(a.nascimento, '%d/%m/%Y') nascimento ";
$camposExtra .= ", a.endereco";
$camposExtra .= ", a.bairro";
$camposExtra .= ", c.nome as cidade";
$camposExtra .= ", a.telefone";
$camposExtra .= ", a.celular";
$camposExtra .= ", a.email";
$camposExtra .= ", c2.nome as curso";

$conteudo = $cabecalho;
$conteudo.= "<div id='container' style='font-family: helvetica'>";
$conteudo.= "<h1>Alunos</h1>";
$conteudo.="<table border='1' width='100%'>";
$cor = "white";
$n = 1;

$conteudo.="<tr><th>#</th><th>Prontu&aacute;rio</th><th>Nome</th><th>RG</th><th>Nascimento</th><th>Endere&ccedil;o</th><th>Bairro</th><th>Cidade</th><th>Telefone</th><th>Celular</th><th>E-mail</th><th>Curso</th></tr>";

foreach ($aluno->listAlunos($params, $sqlAdicional, $camposExtra) as $linha) {
    $conteudo.="<tr bgcolor='$cor'><td>$n</td><td>" . $linha['prontuario'] . "</td><td>" . utf8_decode($linha['nome']) . "</td><td>" . utf8_decode($linha['rg']) . "</td><td>" . utf8_decode($linha['nascimento']) . "</td><td>" . utf8_decode($linha['endereco']) . "</td><td>" . utf8_decode($linha['bairro']) . "</td><td>" . utf8_decode($linha['cidade']) . "</td><td>" . utf8_decode($linha['telefone']) . "</td><td>" . utf8_decode($linha['celular']) . "</td><td>" . utf8_decode($linha['email']) . "</td><td>" . utf8_decode($linha['curso']) . "</td></tr>";

    // alterna a cor de fundo da linha
    $n++;
    if ($n % 2 == 0)
        $cor = "gray";
    else
        $cor = "white";
}

$conteudo.="</table>";

echo $conteudo;
?>