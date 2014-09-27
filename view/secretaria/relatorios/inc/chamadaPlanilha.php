<?php
require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . '/atribuicao.class.php';
$atribuicao = new Atribuicoes();

require CONTROLLER . '/aula.class.php';
$aula = new Aulas();

$orientacao = 'L'; // PORTRAIT

if (dcrip($_GET["atribuicao"])) {
    $params['atribuicao'] = dcrip($_GET["atribuicao"]);
    $sqlAdicional = ' WHERE a.codigo=:atribuicao GROUP BY al.codigo ORDER BY al.nome ';

    $linha2 = $aula->listAlunosByAula($params, $sqlAdicional);

    $res = $atribuicao->getAtribuicao(dcrip($_GET["atribuicao"]));

    $titulo = "Lista de Chamada: ".$res['disciplina']." [".$res['numeroDisciplina']."]";
    $titulo2 = $res['numeroDisciplina'];

    $titulosColunas = array("Prontuário", "Nome", "Situação");
    $colunas = array("prontuario", "aluno", "situacao");
    $largura = array(20, 55, 30);

    // gera o relatório em XLS
    include PATH.LIB.'/relatorio_planilha.php';
}
?>