<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

require ('lib/consultaDisciplinasWS.php');

$user = 'BA000022';
$pass = '4(HC&m3KbT';
$campus = strtoupper($DIGITANOTAS);

$sql = "SELECT p.prontuario, n.atribuicao, d.numero, n.bimestre, a.subturma, a.eventod
	FROM NotasFinais n, Atribuicoes a, Pessoas p, Professores pr, Disciplinas d, Turmas t
	WHERE n.atribuicao = a.codigo
	AND pr.professor = p.codigo
        AND pr.atribuicao = a.codigo
	AND d.codigo = a.disciplina
	AND t.codigo = a.turma
        AND t.ano = $ano
        AND t.semestre = $semestre
        AND (a.bimestre = 0 OR a.bimestre = 4)
        AND n.bimestre = 1
        AND n.recuperacao IS NULL
        AND flag = 5
	GROUP BY p.codigo
        ORDER BY n.bimestre";

$result = mysql_query($sql);

$total = mysql_num_rows($result);

while ($l = mysql_fetch_array($result)) {
    $prontuario = $l[0];
    $disciplina = $l[2] . '_' . $l[5];
    $atribuicao = $l[1];
    $bimestre = $l[3];
    $subturma = $l[4];

    $consultaDisciplinasWS = new ConsultaDisciplinasWS();

    //Obtem todas as informações das disciplinas ministradas pelo professor
    $professorObj = $consultaDisciplinasWS->consultaDisciplinas($user, $pass, $campus, $prontuario);
    
    //Pega todas as disciplinas ministradas pelo professor
    if (count($professorObj->disciplinasMinistradas->DisciplinaMinistrada) > 1) {
        $disciplinasMinistradas = $professorObj->disciplinasMinistradas->DisciplinaMinistrada;
    } else {
        $disciplinasMinistradas = $professorObj->disciplinasMinistradas;
    }

    $disciplinas = array();
    
    //Pega por disciplina ministradas para obter todos os alunos
    foreach ($disciplinasMinistradas as $disciplinasMinistrada) {

        $disciplinas[] = $disciplinasMinistrada->codigoDisciplina;
        
        //Verificando apenas disciplinas na tabela de NOTASFINAIS
        if ($disciplinasMinistrada->codigoDisciplina == $disciplina) {

            //Verifica se tem alunos matriculados caso tenha monta a lista de alunos matriculados
            if ($disciplinasMinistrada->qtdMatriculados != 0) {
                if (count($disciplinasMinistrada->alunoMatriculadoDisciplina->AlunoMatriculadoDisciplina) > 1) {
                    $alunosMatriculados = $disciplinasMinistrada->alunoMatriculadoDisciplina->AlunoMatriculadoDisciplina;
                } else if (count($disciplinasMinistrada->alunoMatriculadoDisciplina->AlunoMatriculadoDisciplina) == 1) {
                    $alunosMatriculados = $disciplinasMinistrada->alunoMatriculadoDisciplina;
                }
            } else {
                $alunosMatriculados = null;
            }

            //Pega a informação de cada aluno
            foreach ($alunosMatriculados as $alunosMatriculado) {

                $flagNotaReavDigitada = rtrim($alunosMatriculado->flagNotaReavDigitada);
                $flagNota1Digitada = rtrim($alunosMatriculado->flagNota1Digitada);

                $situacaoNota = rtrim($alunosMatriculado->situacaoNota);
                $aluno = $alunosMatriculado->prontuario;

                $recuperacao = null;

                //Verifica a situação
                if ($flagNota1Digitada == "5" && $flagNotaReavDigitada == "0" && $situacaoNota == "5") {
                    $recuperacao = 1;
                }

                $situacao = array();
                $situacao[] = "1"; // Aprovado
                $situacao[] = "2"; // Reprovado por Media
                $situacao[] = "3"; // Reprovado por Faltas
                $situacao[] = "4"; // Reprovado
                $situacao[] = "6"; // Dispensado
                $situacao[] = "8"; // Aprovado pelo Conselho de Classe
                $situacao[] = "9"; // Ficou para Conselho de Classe
                $situacao[] = "A"; // Aprovado apos Recuperacao
                $situacao[] = "B"; // Reprovado apos Recuperacao

                if ($flagNota1Digitada == "5" && in_array($situacaoNota, $situacao)) {
                    $recuperacao = 2;
                }

                if ($recuperacao) {
                    $sqlUpdate = "\nUPDATE NotasFinais n SET n.recuperacao = $recuperacao
                        WHERE n.atribuicao = $atribuicao
                        AND n.bimestre = $bimestre
                        AND n.matricula = (SELECT m.codigo FROM Matriculas m, Pessoas p
                                            WHERE m.aluno = p.codigo
                                            AND m.atribuicao = $atribuicao
                                            AND p.prontuario = '$aluno')";
                    print $sqlUpdate;
                    //mysql_query($sqlUpdate);
                }
            }
        }
    }
    
    if (in_array($disciplina, $disciplinas)) {
        $URL .= "A disciplina $disciplina não consta para o professor $prontuario. ";
    }
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
    $sql = "insert into Atualizacoes values(0,14," . $_SESSION['loginCodigo'] . ", now())";
    mysql_query($sql);
    ?>
    <script>
        $('#db2ConsultaDisciplinaRetorno').text('<?= $URL ?>');
    </script><?php
} else {
    $sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
    $resultAdmin = mysql_query($sqlAdmin);
    $admin = mysql_fetch_object($resultAdmin);

    $sql = "insert into Atualizacoes values(0,114," . $admin->codigo . ", now())";
    mysql_query($sql);

    if ($DEBUG)
        print "$URL \n";
    $sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
    mysql_query($sql);
}
?>