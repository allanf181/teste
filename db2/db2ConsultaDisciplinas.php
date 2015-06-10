<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

require ('lib/consultaDisciplinasWS.php');

if (isset($_GET["codigo"])) {
    $codigo = $_GET["codigo"];
    $sqlCodigo = "AND n.codigo = $codigo";
}
//else
//    $sqlCodigo = "AND (n.sincronizado IS NULL OR n.sincronizado = '0000-00-00 00:00:00')";


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
        AND n.bimestre = 1
        AND n.recuperacao IS NULL
        AND flag = 5
	GROUP BY p.codigo
        ORDER BY n.bimestre";

$result = mysql_query($sql);

$total = mysql_num_rows($result);

while ($l = mysql_fetch_array($result)) {
    $prontuario = $l[0];
    $disciplina = $l[2].'_'.$l[5];
    $atribuicao = $l[1];
    $bimestre = $l[3];

    $consultaDisciplinasWS = new ConsultaDisciplinasWS();

    //Obtem todas as informações das disciplinas ministradas pelo professor
    $professorObj = $consultaDisciplinasWS->consultaDisciplinas($user, $pass, $campus, $prontuario);
        
    //Pega todas as disciplinas ministradas pelo professor
    if (count($professorObj->disciplinasMinistradas->DisciplinaMinistrada) > 1) {
        $disciplinasMinistradas = $professorObj->disciplinasMinistradas->DisciplinaMinistrada;
    } else {
        $disciplinasMinistradas = $professorObj->disciplinasMinistradas;
    }
    
    //Pega por disciplina ministradas para obter todos os alunos
    foreach ($disciplinasMinistradas as $disciplinasMinistrada) {
        
        //Verificando apenas disciplinas na tabela de NOTASFINAIS
        if ($disciplinasMinistrada->codigoDisciplina != 'HCTS1_3')
            continue;
        
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
        
        print_r($alunosMatriculados);
        die;
        
        //Pega a informação de cada aluno
        foreach ($alunosMatriculados as $alunosMatriculado) {
            
            $flagNotaReavDigitada = rtrim($alunosMatriculado->flagNotaReavDigitada);
            $flagNota1Digitada = rtrim($alunosMatriculado->flagNota1Digitada);

            $situacaoNota = rtrim($alunosMatriculado->situacaoNota);
            $aluno = $alunosMatriculado->prontuario;

            //Verifica a situação
            if ($flagNota1Digitada == "5" && $flagNotaReavDigitada == "0" && $situacaoNota == "5") {
                $sqlUpdate = "\nUPDATE NotasFinais n SET n.recuperacao = 1
                        WHERE n.atribuicao = $atribuicao
                        AND n.bimestre = $bimestre
                        AND n.matricula = (SELECT m.codigo FROM Matriculas m, Pessoas p
                                            WHERE m.aluno = p.codigo
                                            AND m.atribuicao = $atribuicao
                                            AND p.prontuario = '$aluno')";
                print $sqlUpdate;

            }
        }
    }

}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
    $sql = "insert into Atualizacoes values(0,12," . $_SESSION['loginCodigo'] . ", now())";
    mysql_query($sql);
    ?>
    <script>
        $('#db2DigitaNotasRetorno').text('<?= $s ?> sincronizadas, <?= $n ?> nao sincronizados');
    </script><?php
} else {
    $sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
    $resultAdmin = mysql_query($sqlAdmin);
    $admin = mysql_fetch_object($resultAdmin);

    $sql = "insert into Atualizacoes values(0,112," . $admin->codigo . ", now())";
    mysql_query($sql);

    $URL = "DIGITA NOTAS: $s NOTAS SINCRONIZADAS";
    if ($DEBUG)
        print "$URL \n";
    $sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
    mysql_query($sql);
}
?>