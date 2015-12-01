<?php
set_time_limit(2*60); // LIMITE DE TEMPO

if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

require ('lib/consultaDisciplinasWS.php');

if (isset($_GET["atribuicao"])) {
    $atribuicao = $_GET["atribuicao"];
    $sqlCodigo = "AND a.codigo = $atribuicao";
}

if (isset($_GET["turma"])) {
    $turma = $_GET["turma"];
    $sqlCodigo = "AND a.codigo IN (SELECT codigo from Atribuicoes where turma=$turma)";
}

$user = 'BA000022';
$pass = '4(HC&m3KbT';
//$pass = 1234; 
$campus = strtoupper($DIGITANOTAS);

$sql = "SELECT p.prontuario, n.atribuicao, d.numero, n.bimestre, a.subturma, a.eventod
	FROM NotasFinais n, Atribuicoes a, Pessoas p, Professores pr, Disciplinas d, Turmas t
	WHERE n.atribuicao = a.codigo
	AND pr.professor = p.codigo
        AND pr.atribuicao = a.codigo
	AND d.codigo = a.disciplina
	AND t.codigo = a.turma
        AND (a.bimestre = 0 OR a.bimestre = 4)
        AND (n.situacao IS NULL or n.situacao = 'Em Curso' or n.situacao = 'MATRICULADO')
        AND flag = 5
        $sqlCodigo
	GROUP BY a.codigo
        ORDER BY n.bimestre,d.numero";
//echo $sql;
$result = mysql_query($sql);

$total = mysql_num_rows($result);

$s=0; // SINCRONIZADAS
$n=0; // NAO-SINCRONIZADAS
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
                $situacaoAluno = $alunosMatriculado->situacao;
//echo "<br>=====================<br>".debug($alunosMatriculado);
//
//echo "<hr />".$alunosMatriculado->nome;
//echo "<br>".$situacaoNota;
//echo "<br>reav? ".$flagNotaReavDigitada;
//echo "<br>sit? ".$alunosMatriculado->situacao;
//echo "<br>sitNota? ".$alunosMatriculado->situacaoNota;
//echo "<br>nota1? ".$flagNota1Digitada;
//echo "<br>notareav? ".$flagNotaReavDigitada;
                $recuperacao = null;

                //Verifica a situação
                // ALTERADA PARA EFETUAR TESTES DE INTEGRACAO COM O WS (VALIDAR SE NOTAS BIMESTRAIS FORAM DIGITADAS?)
//                if ($flagNota1Digitada == "5" && $flagNotaReavDigitada == "0" && $situacaoNota == "5") {
                if ($flagNotaReavDigitada == "0" && $situacaoNota == "5") {
//                if ($flagNota1Digitada == "5" && $flagNotaReavDigitada == "0" && $situacaoAluno == "Em Curso") {
//                    $situacaoAluno=null;// ALUNO DE RECUPERACAO DEVE TER SITUACAO NULA
//                    echo "<br>REC!";
//                if ($flagNota1Digitada == "5" && $flagNotaReavDigitada == "0" && $situacaoNota == "0") {
                    $recuperacao = 1;
                }
                else if ($flagNota1Digitada == "5" && $flagNotaReavDigitada == "0" && !is_null($situacaoAluno) && $situacaoAluno != "Em Curso") {
                    $reprovado = $situacaoAluno;
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
                    if ($flagNotaReavDigitada==5)
                        $situacaoAluno= ", n.situacao='REAVALIADO' ";                        
                    else if ($situacaoAluno)
                        $situacaoAluno= ", n.situacao='$situacaoAluno' ";
                    $sqlUpdate = "\nUPDATE NotasFinais n SET n.recuperacao = '$recuperacao'
                            $situacaoAluno
                        WHERE n.atribuicao = $atribuicao
                        AND n.bimestre = $bimestre
                            
                        AND n.matricula = (SELECT m.codigo FROM Matriculas m, Pessoas p
                                            WHERE m.aluno = p.codigo
                                            AND m.atribuicao = $atribuicao
                                            AND p.prontuario = '$aluno')";
//                    print $sqlUpdate;
                    mysql_query($sqlUpdate);
                }
                else if (!is_null($situacaoAluno)){
                    if ($flagNotaReavDigitada==5)
                        $situacaoAluno= "REAVALIADO";  
                    else {
                        $recuperacao=", recuperacao=null";
                    }

                    $sqlUpdate = "\nUPDATE NotasFinais n SET n.situacao='$situacaoAluno' $recuperacao
                        WHERE n.atribuicao = $atribuicao
                        AND n.bimestre = $bimestre
                        AND n.matricula = (SELECT m.codigo FROM Matriculas m, Pessoas p
                                            WHERE m.aluno = p.codigo
                                            AND m.atribuicao = $atribuicao
                                            AND p.prontuario = '$aluno')";
                    print $sqlUpdate;
                    mysql_query($sqlUpdate);
                    
                }
            }
        }
    }
    
    if (!in_array($disciplina, $disciplinas)) { // MOSTRA UM ERRO CASO A DISCIPLINA NAO CONSTE NO WS
        $URL .= "\nERRO WS CONSULTA DISCIPLINAS: A disciplina $disciplina não consta para o professor $prontuario. ";
    }
    else{
        $URL = count($disciplinas)." disciplinas processadas para o professor após o RODA.";
    }
    $s++;
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
    $sql = "insert into Atualizacoes values(0,14," . $_SESSION['loginCodigo'] . ", now())";
    mysql_query($sql);
    ?>
    <script>
        $('#db2ConsultaDisciplinasRetorno').text('<?= $s ?> disciplinas sincronizadas.');
    </script><?php
} else {
    $sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
    $resultAdmin = mysql_query($sqlAdmin);
    $admin = mysql_fetch_object($resultAdmin);

    $sql = "insert into Atualizacoes values(0,114," . $admin->codigo . ", now())";
    mysql_query($sql);

    $URL = "CONSULTA RODA: ".count($disciplinas)." DISCIPLINAS SINCRONIZADAS";
    if ($DEBUG)
        print "$URL \n";
    $sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
    mysql_query($sql);
}
?>