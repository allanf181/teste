<?php

require '../../../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;

require CONTROLLER . '/matricula.class.php';
$matricula = new Matriculas();

$data = $_GET["data"];

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND t.codigo = :turma ';

    if (dcrip($_GET["turno"])) {
        $turno = dcrip($_GET["turno"]);
        $params['turno'] = $turno;
        $sqlAdicional .= ' AND a.periodo = :turno ';
    }

    $sqlAdicional .= ' AND s.habilitar = 1 AND s.listar = 1 ';

    $params['ano'] = $ANO;
    $params['semestre'] = $SEMESTRE;
    $sqlAdicional .= " AND t.ano = :ano "
            . "AND (semestre = 0 OR semestre = :semestre) "
            . "GROUP BY p.codigo ORDER BY a.bimestre, p.nome, d.nome ";
    $linha2 = $matricula->getMatriculas($params, $sqlAdicional);

    $titulo = "Lista de Presença [" . $linha2[0]['turma'] . "] - $data";
    $titulo2 = "Assunto: ".$_GET['assunto'];
    $rodape = $SITE_TITLE;

    $fonte = 'Times';
    $tamanho = 10;
    $alturaLinha = 10;
//$orientacao = "L"; //Landscape
    $orientacao = "P"; //Portrait
    $papel = "A4";
    $titulosColunas = array("Prontuário", "Nome", "Assinatura");
    $colunas = array("prontuario", "pessoa", "");
    $largura = array(20, 120, 0);

// gera o relatório em PDF
    include PATH . LIB . '/relatorio_banco.php';
} else
    print "SELECIONE UMA TURMA.";
?>