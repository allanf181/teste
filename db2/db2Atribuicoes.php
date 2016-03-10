<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

mysql_set_charset('latin1');

$j = 0;

//PEGANDO O INICIO E FIM DE CADA BIMESTRE
$db2 = "SELECT * FROM ESCOLA.DATABIM WHERE DB_ANO = $ano";
$res1 = db2_exec($conn, $db2);
while ($r = db2_fetch_object($res1)) {
    $B_INI[$r->DB_BIM] = $r->DB_INICIO;
    $B_FIN[$r->DB_BIM] = $r->DB_FIM;
}

//PEGANDO UMA SALA PADRAO
$db2 = "SELECT * FROM ESCOLA.SALAS ORDER BY SL_SALA FETCH FIRST 1 ROWS ONLY";
$res1 = db2_exec($conn, $db2);
while ($r = db2_fetch_object($res1)) {
    $SALA_PADRAO = $r->SL_SALA;
}

for ($n = 1; $n <= 2; $n++) {
    $nS = str_pad($n, 2, "0", STR_PAD_LEFT);

    $db2 = "SELECT MAX(A1.NH_EVENTOD) AS NH_EVENTOD, A1.DA_NOME,A1.NH_PROFESSOR,A1.HA_HORARIO, 
				A1.PR_GUERRA,A1.NH_AULA,A1.NH_DESCR AS NH_DESCR,MAX(A1.X_DESCR) AS X_DESCR,           
				A1.NH_DIASEM,A1.NH_PERIODO,A1.X_HORAULA,A1.X_HORAULA_R,A2.TD_DISC,A2.TD_TURMA,A1.NH_SUBT, A3.NH_SALA
				FROM ESCOLA.V_HORS_TURMA A1, ESCOLA.V_DISC_TURMA A2, ESCOLA.NHORARIO A3
				WHERE A1.NH_VERSAOH = $ano$nS
				AND A1.NH_VERSAOH = A2.VH_VERSAOH
                AND A1.NH_EVENTOD = A2.NH_EVENTOD
                AND A3.NH_VERSAOH = A1.NH_VERSAOH
                AND A3.NH_EVENTOD = A1.NH_EVENTOD
                AND A3.NH_DISC = A2.TD_DISC
                AND A3.NH_AULA = A1.NH_AULA
                AND A3.NH_DIASEM = A1.NH_DIASEM
                AND A3.NH_PERIODO = A1.NH_PERIODO
                AND A3.NH_PROFESSOR = A1.NH_PROFESSOR
				GROUP BY A1.HA_HORARIO,A1.NH_PERIODO,A1.NH_AULA,A1.X_HORAULA,A1.NH_DIASEM,A1.DA_NOME,
					A1.NH_PROFESSOR,A1.PR_GUERRA,A1.NH_DESCR,A1.X_HORAULA_R,A2.TD_DISC,A2.TD_TURMA,A1.NH_SUBT, A3.NH_SALA
				ORDER BY A1.NH_SUBT DESC";
    $res = db2_exec($conn, $db2);

    if (db2_stmt_error() == 42501) {
        $ERRO = "SEM ACESSO NA TABELA ATRIBUICOES (CURSOS ANTIGOS)";
        mysql_query("INSERT INTO Logs VALUES (0, '" . addslashes($ERRO) . "', now(), 'CRON_ERRO', 1)");
        print $ERRO;
    }

    $sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
    $resultAdmin = mysql_query($sqlAdmin);
    $admin = mysql_fetch_object($resultAdmin);

    while ($row = db2_fetch_object($res)) {
        $subturma = trim($row->NH_SUBT);

        $db2_1 = "SELECT T_CURSO, T_MODAL FROM ESCOLA.TURMAS WHERE T_ANO = $ano AND T_TURMA = $row->TD_TURMA";
        $res_1 = db2_exec($conn, $db2_1);
        while ($row_1 = db2_fetch_object($res_1)) {
            $cCodigo = $row_1->T_CURSO . ord($row_1->T_MODAL);
        }

        // BUSCA O CODIGO DA DISCIPLINA
        $DISC = trim($row->TD_DISC);

        $sql = "SELECT * FROM Disciplinas d WHERE d.curso=$cCodigo and d.numero='$DISC'";
        $result = mysql_query($sql);
        $disciplina = mysql_fetch_object($result);

        if ($disciplina->codigo) {
            // BUSCA O CODIGO DO TURNO
            $turno = getTurno($row->NH_PERIODO);
            
            // BUSCA O CODIGO DA TURMA
            $sql = "SELECT * FROM Turmas t WHERE numero='A$row->TD_TURMA' AND ano=$ano AND semestre = $n";
            $result = mysql_query($sql);
            while ($turma = @mysql_fetch_object($result)) {
//                $nn = $n;
//                if ($n == 2)
//                    $nn = 3;
//                for ($i = $nn; $i <= $nn + 1; $i++) {// BIMESTRES
                for ($i = 1; $i <= 4; $i++) {// BIMESTRES
                    $sqlProf = "SELECT * FROM Pessoas WHERE prontuario='$row->NH_PROFESSOR'";
                    $resultProf = mysql_query($sqlProf);
                    if (!$professor = mysql_fetch_object($resultProf))
                        $professor->codigo = $admin->codigo;

                    $sql = "SELECT * FROM Atribuicoes 
		       				WHERE disciplina = $disciplina->codigo 
		       				AND turma = $turma->codigo
		       				AND
									( 
		       					( ( '$subturma' = 'ABCD' AND ( subturma = 'AC' OR subturma = 'BD' ) )
		       				OR ( '$subturma' = 'ABCD' AND ( subturma = '$subturma' ) )
									)
		       				OR
		       					('$subturma' <> 'ABCD' AND subturma = '$subturma')
		       				)
		       				AND bimestre = $i";
                    $resultAtt = mysql_query($sql);
                    if (!$att = mysql_fetch_object($resultAtt)) {
                        // IMPORTA A ATRIBUICAO
                        $sql = "insert into Atribuicoes (codigo, disciplina, turma, bimestre, dataInicio, dataFim, status, periodo, subturma, eventod) "
                                . " values (0, "
                                . "$disciplina->codigo, "
                                . "$turma->codigo, "
                                . "$i, "// BIMESTRE
                                . "'" . $B_INI[$i] . "',"
                                . "'" . $B_FIN[$i] . "',"
                                . "0,$turno, '$subturma', $row->NH_EVENTOD)";
                        if (!$result = mysql_query($sql)) {
                            if ($DEBUG)
                                echo "<br>Erro ao importar ATRIBUICAO: $sql \n";
                            mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
                        } else {
                            $j++;
                            $COD = mysql_insert_id();
                            $REG = "ATRIBUICAO (CURSO ANTIGO): $COD";
                            mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_AA', 1)");
                            if ($DEBUG)
                                print "$REG <br>\n";
                            $sql = "INSERT INTO Professores VALUES (NULL, $professor->codigo, $COD)";
                            if (!$result = mysql_query($sql)) {
                                if ($DEBUG)
                                    echo "<br>Erro ao importar ATRIBUICAO/PROFESSOR: $sql \n";
                                mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
                            }
                        }
                    } else {
                        $COD = $att->codigo;
                        $G = round( (($disciplina->ch * 6) / 5), 2);
                        if ($G != $att->aulaPrevista || $B_INI[$i] != $att->dataInicio || $B_FIN[$i] != $att->dataFim || $turno != $att->periodo ) {
                            $sql = "UPDATE Atribuicoes SET aulaPrevista='".$G."', dataInicio='" . $B_INI[$i] . "', dataFim='" . $B_FIN[$i] . "', periodo = $turno WHERE codigo = $COD";
                            if (!$result = mysql_query($sql)) {
                                if ($DEBUG) echo "<br>Erro ao atualizar ATRIBUICAO: $sql \n";
                                mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
                            }
                        }

                        $sql = "SELECT * FROM Professores 
			     				WHERE professor = $professor->codigo 
			     				AND atribuicao = $COD";
                        $prof2 = mysql_query($sql);
                        if (mysql_num_rows($prof2) == '') {
                            $sql = "INSERT INTO Professores VALUES (NULL, $professor->codigo, $COD)";
                            if (!$result = mysql_query($sql)) {
                                if ($DEBUG) echo "<br>Erro ao importar ATRIBUICAO/PROFESSOR: $sql \n";
                                mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
                            }
                        }
                    }
                    if ($COD) {
                        $HOR = 'AULA ' . $row->NH_AULA . ' [' . $row->NH_PERIODO . ']';
                        $sqlHOR = "SELECT * FROM Horarios WHERE nome='$HOR'";
                        $resultHOR = mysql_query($sqlHOR);
                        $HORARIO = mysql_fetch_object($resultHOR);
                        // PEGANDO A SALA
                        if ($row->NH_SALA == 0)
                            $row->NH_SALA = $SALA_PADRAO;
                        $sqlSA = "SELECT * FROM Salas WHERE nome='SALA $row->NH_SALA'";
                        $resultSA = mysql_query($sqlSA);
                        $SALA = @mysql_fetch_object($resultSA);

                        if ($SALA && $HORARIO) {
                            $sqlENS = "SELECT * FROM Ensalamentos WHERE atribuicao=$COD AND sala=$SALA->codigo AND diaSemana=$row->NH_DIASEM AND horario=$HORARIO->codigo AND professor=$professor->codigo";
                            $resultENS = mysql_query($sqlENS);
                            $ensalamento = mysql_fetch_object($resultENS);
                            if (!$ensalamento) {
                                $sql = "INSERT INTO Ensalamentos VALUES (NULL, $COD, $professor->codigo, $SALA->codigo, $row->NH_DIASEM, $HORARIO->codigo)";
                                if (!$result = mysql_query($sql)) {
                                    if ($DEBUG)
                                        echo "<br>Erro ao importar ENSALAMENTO: $sql \n";
                                    mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
                                } else {
                                    $j++;
                                    $REG = "ENSALAMENTO (CURSO ANTIGO): $COD";
                                    mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_EA', 1)");
                                    if ($DEBUG)
                                        print "$REG <br>\n";
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
    $sql = "insert into Atualizacoes values(0,8," . $_SESSION['loginCodigo'] . ", now())";
    mysql_query($sql);
    ?>
    <script>
        $('#db2AtribuicoesRetorno').text('<?= $j ?> registros processados...');
    </script><?php
} else {
    $sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
    $resultAdmin = mysql_query($sqlAdmin);
    $admin = mysql_fetch_object($resultAdmin);

    $sql = "insert into Atualizacoes values(0,108," . $admin->codigo . ", now())";
    mysql_query($sql);

    $URL = "ATRIBUICOES CURSOS ANTIGOS: $j";
    if ($DEBUG)
        print "$URL \n";
    $sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
    mysql_query($sql);
}
?>