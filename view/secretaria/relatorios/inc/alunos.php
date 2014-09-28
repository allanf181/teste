<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . '/aluno.class.php';
$aluno = new Alunos();

require CONTROLLER . '/turma.class.php';
$turmas = new Turmas();

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

if (dcrip($_GET["turno"])) {
    $turno = dcrip($_GET["turno"]);
    $params['turno'] = $turno;
    $sqlAdicional .= " AND at.periodo = :turno ";
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $params['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicional .= " AND c2.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord) ";
}

// valores fixos
$titulosColunas[] = "Prontuário";
$titulosColunas[] = "Nome";
$colunas[] = 'prontuario';
$colunas[] = 'nome';
$largura[] = 14;
$largura[] = 60;

if (($_GET["rg"]) == 'true') {
    $camposExtra = ", a.rg";
    $titulosColunas[] = "RG";
    $colunas[] = 'rg';
    $largura[] = 16;
}
if (($_GET["cpf"]) == 'true') {
    $camposExtra .= ", a.cpf";
    $titulosColunas[] = "CPF";
    $colunas[] = 'cpf';
    $largura[] = 18;
}
if (($_GET["nasc"]) == 'true') {
    $camposExtra .= ", date_format(a.nascimento, '%d/%m/%Y') nascimento ";
    $titulosColunas[] = "Nasc";
    $colunas[] = 'nascimento';
    $largura[] = 14;
}
if (($_GET["endereco"]) == 'true') {
    $camposExtra .= ", a.endereco";
    $colunas[] = 'endereco';
    $titulosColunas[] = "Endereço";
    $largura[] = 60;
}
if (($_GET["bairro"]) == 'true') {
    $camposExtra .= ", a.bairro";
    $colunas[] = 'bairro';
    $titulosColunas[] = "Bairro";
    $largura[] = 25;
}
if (($_GET["cidade"]) == 'true') {
    $camposExtra .= ", c.nome as cidade";
    $titulosColunas[] = "Cidade";
    $colunas[] = 'cidade';
    $largura[] = 25;
}
if (($_GET["telefone"]) == 'true') {
    $camposExtra .= ", a.telefone";
    $colunas[] = 'telefone';
    $titulosColunas[] = "Telefone";
    $largura[] = 18;
}
if (($_GET["celular"]) == 'true') {
    $camposExtra .= ", a.celular";
    $colunas[] = 'celular';
    $titulosColunas[] = "Celular";
    $largura[] = 18;
}
if (($_GET["email"]) == 'true') {
    $camposExtra .= ", a.email";
    $colunas[] = 'email';
    $titulosColunas[] = "Email";
    $largura[] = 40;
}
$largura[] = '';

$params['aluno'] = $ALUNO;
$sqlAdicional .= ' AND ti.codigo=:aluno ';

$linha2 = $aluno->listAlunos($params, $sqlAdicional, $camposExtra);

$titulo = "Relação de Alunos";
$titulo2 = "";

$params['ano'] = $ANO;
$params['semestre'] = $SEMESTRE;
unset($params['aluno']);
unset($params['coord']);
unset($params['turno']);

if ($curso && !$turma) {
    $sqlAdicional = ' AND c.codigo = :curso ';
    $res = $turmas->listTurmas($params, $sqlAdicional);
    $titulo = $res[0]['curso'];
} else if ($curso && $turma) {
    $sqlAdicional = ' AND c.codigo = :curso AND t.codigo = :turma ';
    $res = $turmas->listTurmas($params, $sqlAdicional);
    $titulo = $res[0]['curso'];
    $titulo2 = $res[0]['numero'];
}

if ($turno) {
    require CONTROLLER . '/turno.class.php';
    $turnos = new Turnos();

    $paramsTurno['codigo'] = $turno;
    $turnoNome = $turnos->listRegistros($paramsTurno);
    $titulo2 .= ' [' . $turnoNome[0]['nome'] . ']';
}

$rodape = $SITE_TITLE;
$fonte = 'Times';
$tamanho = 7;
$alturaLinha = 5;
$orientacao = "L"; //Landscape 
//$orientacao = "P"; //Portrait 
$papel = "A4";

// gera o relatório em PDF
include(PATH . LIB . '/relatorio_banco.php');
?>