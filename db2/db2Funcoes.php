<?php

function conv($string) {
    $final = '';

    $string = ( trim(addslashes($string)) );

    if (strlen($string) == 1)
        return $string;
    else {

        // print "------------------------<br>\n# $string #<br>\n-----------------------<br>\n";
        $c = 0;
        for ($i = 0; $i < strlen($string); $i++) {
            $jump = 0;

            $a = @ord($string[$i - 1]);
            $p = @ord($string[$i]);
            $q = @ord($string[$i + 1]);
            $r = @ord($string[$i + 2]);

            // print "<br>\n# $final: a: $a, p: $p, q:$q, r:$r, c:$c#<br><br>\n\n";

            if ($p == 194 && $q == 186)
                $p = 186; //º;
            if ($p == 195 && $q == 135)
                $p = 199; //&Ccedil;
            if ($p == 195 && $q == 167)
                $p = 199; //&Ccedil;

            if ($p == 195 && $q == 160)
                $p = 192; //&Aacute;
            if ($p == 195 && $q == 173)
                $p = 205; //&Iacute;

            if ($p == 206 && $q == 147)
                $p = 205; //&Iacute;

            if ($p == 190)
                $p = 192; //&Agrave;;

            if ($p == 226 && $q == 149) {       // 3 bytes
                if ($r == 152)
                    $p = 193;     //&Aacute;   
                if ($r == 153)
                    $p = 192;     //&Agrave;
                if ($r == 171)
                    $p = 195;     //&Atilde;
                if ($r == 147)
                    $p = 194;     //&Acirc;
                $i++;
            }
            if ($p == 226 && $q == 150) {      // 3 bytes
                if ($r == 140)
                    $p = 202;
                $i++;
            }
            if ($p == 226 && $q == 137) {      // 3 bytes
                if ($r == 136)
                    $p = 213;      //&Otilde
                $i++;
            }
            if ($p == 226 && $q == 140) {        // 3 bytes
                if ($r == 160)
                    $p = 195;        //&Atilde;
                $i++;
            }
            if ($p == 195 && $q == 161)
                $p = 193; //&Aacute;
            if ($p == 195 && $q == 179)
                $p = 211; //&Oacute;
            if ($p == 206 && $q == 166)
                $p = 211; //&Oacute;
            if ($p == 206 && $q == 166)
                $p = 212; //&Ocirc;
            if ($p == 206 && $q == 152)
                $p = 212; //&Ocirc;
            if ($p == 206 && $q == 181)
                $p = 218; //&Uacute;
            if ($p == 206 && $q == 169)
                $p = 213; //&Otilde;
            if ($p == 195 && $q == 169)
                $p = 201; //&Eacute;
            if ($p == 195 && $q == 170)
                $p = 202; //&Ecirc;
            if ($p == 195 && $q == 137)
                $p = 201; //&Eacute;
            if ($p == 195 && $q == 163)
                $p = 211; //&Oacute;

            if ($a == 199 && $p == 194)
                $p = 195; //&Atilde;
            if ($q == 79 && $p == 194)
                $p = 195; //&Atilde;
            if ($p == 204)
                $p = 205; //&Iacute;

            if ($q == 78 && $p == 193)
                $p = 194; //&Acirc
            if ($p == 231 && $q == 97)
                $p = 199; //&Ccedil;;
            if ($p == 225 && $q == 112)
                $p = 193; //&Aacute;



                
// NAYLOR SCRIPT CRON
            if ($a != 32 && $q != 32 && $p == 200)
                $p = 202; //&Eacute;
            if ($a != 32 && $q != 32 && $p == 192)
                $p = 193; //&Aacute;

            if (!$c && $p == 194 && $q == 149) {
                $p = 193;
                $c = 1;
            } //&Aacute;
            if (!$c && $p == 206 && $q == 63) {
                $p = 218;
                $c = 1;
            } //&Uacute;
            if (!$c && $p == 195 && $q == 180) {
                $p = 212;
                $c = 1;
            } //&Ocirc;
            if (!$c && $p == 195 && $q == 149) {
                $p = 195;
                $c = 1;
            } //&Aacute;
            if (!$c && $p == 193 && $q == 149) {
                $p = 193;
                $c = 1;
            } //&Atilde;
            if (!$c && $p == 195 && $q == 186) {
                $p = 218;
                $c = 1;
            } //&Atilde;

            if ($r == 147 && $p == 193 && $q == 149)
                $p = 194; //&Acirc;

            if ($p == 149 && $q == 152)
                $jump = 1;
            if ($p == 149 && $q == 171)
                $jump = 1;
            if ($p == 92 && $q == 39)
                $jump = 1;
            if ($p == 63 && $q == 78)
                $jump = 1;
            if ($p == 149 && $q == 147)
                $jump = 1;

            // QUANDO O SCRIPT É EXECUTADO PELO CRON
            if ($p == 137 || $p == 147 || $p == 135 || $p == 179 || $p == 152 || $p == 161 || $p == 160 || $p == 180 || $p == 170 || $p == 169 || $p == 186 || $p == 167 || $p == 162 || $p == 171 || $p == 136 || $p == 166 || $p == 173 || $p == 181 || $p == 140 || $p == 153
            )
                $jump = 1;

            if (!$jump)
                $final .= chr($p);
        }

        return $final;
    }
}

function situacaoNovo() {
    //global $conn; 
    //$db2 = "SELECT CODIGO, DESCRICAO FROM ESCOLA.CONCEIT WHERE TABELA = 'ESCOLA.MATDIS' AND COLUNA = 'MD_SITUACAO'";
    //$res1 = db2_exec($conn, $db2);
    //while ($r = db2_fetch_object($res1)){
    //$sql = "insert into Situacoes (codigo, nome) values($r->CODIGO,'".formatarTexto(conv($r->DESCRICAO))."');";
    ///$res2 = mysql_query($sql);
    //}
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(1,'Em Curso', 'EC', 1, 1);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(2,'Cancelado', 'CD', 1, 0);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(3,'Aprovado', 'AP', 1, 1);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(4,'Reprovado', 'RP', 1, 1);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(5,'Dispensado', 'DS', 1, 0);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(6,'Trancado', 'TR', 1, 0);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(7,'Retido pelo Modulo', 'RM', 1, 1);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(8,'Aprovado pelo Modulo', 'AM', 1, 1);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(9,'Aprovado pelo Conselho', 'AC', 1, 1);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(10,'Intecambio', 'IN', 1, 0);";
    $res = mysql_query($sql);
}

function situacaoAntigo() {
    //global $conn; 
    //$db2 = "SELECT CODIGO, DESCRICAO FROM ESCOLA.CONCEIT WHERE TABELA = 'ESCOLA.ALTURMAS' AND COLUNA = 'AT_STATUS'";
    //$res1 = db2_exec($conn, $db2);
    //while ($r = db2_fetch_object($res1)){
    //	$sql = "insert into Situacoes (codigo, nome) values(10$r->CODIGO,'".formatarTexto(conv($r->DESCRICAO))."');";
    //    $res2 = mysql_query($sql);
    //}
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(100,'OK', 'OK', 1, 1);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(101,'Trancou Matricula', 'TM', 1, 0);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(102,'Transferiu de Escola', 'TE', 1, 0);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(103,'Transferiu de Turma', 'TT', 1, 0);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(104,'Cancelou Matricula', 'CM', 1, 0);";
    $res = mysql_query($sql);
    $sql = "insert into Situacoes (codigo, nome, sigla, listar, habilitar) values(105,'Fazendo Intercambio', 'FI', 1, 0);";
    $res = mysql_query($sql);
}

function modalidadesCursoNovo() {

    $MOD[1001] = 'Médio';
    $MOD[1002] = 'Regular/Complementar';
    $MOD[1003] = 'Modular - Tec. Profission.';
    $MOD[1004] = 'Universitário (Disciplinar)';
    $MOD[1005] = 'Profissionalizante (Módulo único)';
    $MOD[1006] = 'Pós-Graduação';
    $MOD[1007] = 'Formação Pedagógica';

    foreach ($MOD as $cod => $nome) {
        $sql = "insert into Modalidades (codigo, nome) values($cod,'" . formatarTexto(utf8_decode($nome)) . "')";
        $res = mysql_query($sql);

        mysql_set_charset('utf8');
        //INSERIR AVALIAÇÃO
        $sql = "INSERT INTO TiposAvaliacoes VALUES (NULL, 'Avaliação', 'avaliacao', $cod, '', 1, 0, 0, 'AVA', 0, 0, 2, 0)";
        $res = mysql_query($sql);

        $sql = "INSERT INTO TiposAvaliacoes VALUES (NULL, 'Ponto Extra', 'pontoExtra', $cod, '', 0, 0, 0, 'PEX', 0, 0, 0, '10')";
        $res = mysql_query($sql);

        $sql = "INSERT INTO TiposAvaliacoes VALUES (NULL, 'Substitutiva', 'substitutiva', $cod, '', 0, 0, 0, 'SUB', 0, 0, 0, '10')";
        $res = mysql_query($sql);
        
        if ($cod == 1004) {
            $sql = "INSERT INTO TiposAvaliacoes VALUES (NULL, 'Instrumento Final de Avaliação', 'recuperacao', $cod, 'sub_media', 1, 4, 6, 'IFA', 0, 6, 1, 10)";
            $res = mysql_query($sql);
        }

        if ($cod == 1001 || $cod == 1003) {
            $sql = "INSERT INTO TiposAvaliacoes VALUES (NULL, 'Reavaliação Final', 'recuperacao', $cod, 'sub_media', 1, 4, 6, 'REF', 1, 4, 0, 10)";
            $res = mysql_query($sql);
        }

        mysql_set_charset('latin1');
    }
}

function modalidadesCursoAntigo() {
    global $conn;
    $ano = $_SESSION['ano'];
    $db2 = "SELECT DISTINCT MD_NOME, MD_MODAL FROM ESCOLA.TURMAS, ESCOLA.MODALID WHERE T_MODAL = MD_MODAL AND T_ANO = $ano";
    $res2 = db2_exec($conn, $db2);
    while ($r = db2_fetch_object($res2)) {
        if (!is_int(trim($r->MD_MODAL)))
            $r->MD_MODAL += (ord($r->MD_MODAL) + 2000); // PARA BASE DE SP...
        $sql = "insert into Modalidades VALUES ($r->MD_MODAL,'" . formatarTexto(addslashes((conv($r->MD_NOME)))) . "')";
        $res3 = mysql_query($sql);

        mysql_set_charset('utf8');
        //INSERIR AVALIAÇÃO
        $sql = "INSERT INTO TiposAvaliacoes VALUES (NULL, 'Avaliação', 'avaliacao', $r->MD_MODAL, '', 1, 0, 0, 'AVA', 0, 0, 2, 0)";
        $res = mysql_query($sql);

        $sql = "INSERT INTO TiposAvaliacoes VALUES (NULL, 'Ponto Extra', 'pontoExtra', $r->MD_MODAL, '', 0, 0, 0, 'PEX', 0, 0, 0, '10')";
        $res = mysql_query($sql);

        $sql = "INSERT INTO TiposAvaliacoes VALUES (NULL, 'Substitutiva', 'substitutiva', $r->MD_MODAL, '', 0, 0, 0, 'SUB', 0, 0, 0, '10')";
        $res = mysql_query($sql);
        
        //INSERIR RECUPERAÇÃO
        $sql = "INSERT INTO TiposAvaliacoes VALUES (NULL, 'Recuperação - Adiciona valor na média', 'recuperacao', $r->MD_MODAL, 'add_media', 1, 0, 0, 'REB', 0, 0, 0, 5)";
        $res = mysql_query($sql);

        $sql = "INSERT INTO TiposAvaliacoes VALUES (NULL, 'Recuperação - Adiciona valor na menor nota', 'recuperacao', $r->MD_MODAL, 'add_menor_nota', 1, 0, 0, 'REB', 0, 0, 0, 5)";
        $res = mysql_query($sql);

        //REAVALIAÇÃO FINAL
        $sql = "INSERT INTO TiposAvaliacoes VALUES (NULL, 'Reavaliação Final', 'recuperacao', $r->MD_MODAL, 'sub_media', 1, 0, 6, 'REF', 1, 4, 0, 10)";
        $res = mysql_query($sql);

        mysql_set_charset('latin1');
    }
}

function turnos() {
    // TURNOS
    global $conn;
    $db2 = "SELECT * FROM ESCOLA.PERIODOS";
    $res1 = db2_exec($conn, $db2);
    while ($r = db2_fetch_object($res1)) {
        $sql = "INSERT INTO Turnos (codigo, nome, sigla) VALUES (NULL,'" . conv($r->P_NOME) . "','" . conv($r->P_PERIODO) . "')";
        $res2 = mysql_query($sql);
    }
}

function getTurno($periodo) {
    $sql = "SELECT codigo FROM Turnos WHERE sigla = '$periodo'";
    $res1 = mysql_query($sql);
    $turno = mysql_fetch_object($res1);
    return $turno->codigo;
}
