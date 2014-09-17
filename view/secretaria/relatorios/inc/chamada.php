<?php
require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . '/atribuicao.class.php';
$atribuicao = new Atribuicoes();

require CONTROLLER . '/aula.class.php';
$aula = new Aulas();

if (dcrip($_GET["atribuicao"])) {
    $params['atribuicao'] = dcrip($_GET["atribuicao"]);
    $sqlAdicional = ' WHERE a.codigo=:atribuicao GROUP BY al.codigo ORDER BY al.nome ';

    $linha2 = $aula->listAlunosByAula($params, $sqlAdicional);

    $res = $atribuicao->getAtribuicao(dcrip($_GET["atribuicao"]));

    $titulo = "Lista de Chamada: ".$res['disciplina']." [".$res['numeroDisciplina']."]";
    $titulo2 = "";
    $rodape = $SITE_TITLE;
    $fonte = 'Times';
    $tamanho = 8;
    $alturaLinha = 10;
    $orientacao = "L"; //Landscape
    $papel = "A4";
    $titulosColunas = array("Prontuário", "Nome", "Situação", "___/___", "___/___", "___/___", "___/___", "___/___", "___/___", "___/___", "___/___", "___/___", "___/___");
    $colunas = array("prontuario", "aluno", "situacao", "", "", "", "", "", "", "", "", "", "");
    $largura = array(20, 85, 20, 15, 15, 15, 15, 15, 15, 15, 15, 15, 0);

    // gera o relatório em PDF
    include PATH.LIB.'/relatorio_banco.php';
}
?>