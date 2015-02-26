<?php
if (!$LOCATION_CRON) {
    require("$LOCATION_CRON" . "db2Mysql.php");
    require("$LOCATION_CRON" . "db2.php");
    require("$LOCATION_CRON" . "db2Funcoes.php");
    require("$LOCATION_CRON" . "db2Variaveis.inc.php");
    require("$LOCATION_CRON" . "../inc/funcoes.inc.php");
}

mysql_set_charset('latin1');

$i = 0;
$j = 0;

$db2 = "SELECT * 
	FROM ESCOLA.ALUNOS 
	WHERE AL_PRONT IN (SELECT AT_PRONT 
	FROM ESCOLA.ALTURMAS 
	WHERE AT_ANO = $ano)
	OR AL_PRONT IN (SELECT DISTINCT MD_PRONT
		                FROM ESCOLA.NHORARIO, ESCOLA.MATDIS
		                WHERE MD_DISC = NH_DISC
		                AND MD_EVENTOD = NH_EVENTOD
		                AND (NH_VERSAOH = " . $ano . "01 OR NH_VERSAOH = " . $ano . "02)
					)";

$res = db2_exec($conn, $db2);

if (db2_stmt_error() == 42501) {
    $ERRO = "SEM ACESSO NA TABELA ALUNOS";
    mysql_query("INSERT INTO Logs VALUES (0, '" . addslashes($ERRO) . "', now(), 'CRON_ERRO', 1)");
    print $ERRO;
}

while ($row = db2_fetch_object($res)) {
    $aluno = null;
    $row->AL_PRONT = trim(addslashes($row->AL_PRONT));

    // VERIFICA SE O ALUNO EXISTE
    $sql = "SELECT * FROM Pessoas p, PessoasTipos pt 
	    			WHERE pt.pessoa = p.codigo 
	    			AND p.prontuario LIKE '$row->AL_PRONT' AND pt.tipo = $ALUNO";
    $result = mysql_query($sql);
    $aluno = @mysql_fetch_object($result);

    $cidade = inserirCidade(addslashes(conv($row->AL_CIDADE)), addslashes(conv($row->AL_ESTADO)));
    $naturalidade = inserirCidade(addslashes(conv($row->AL_NASCCID)), addslashes(conv($row->AL_UFNASC)));

    //FORMATANDO DADOS
    $nome = formatarTexto(addslashes((conv(rtrim($row->AL_NOME)))));
    $rg = trim(addslashes($row->AL_RG));
    $cpf = addslashes($row->AL_CPF);
    $email = strtolower(trim($row->AL_EMAIL));
    $observacoes = addslashes(conv(rtrim($row->AL_OBS)));
    $endereco = addslashes(conv(rtrim($row->AL_ENDER)));
    $bairro = addslashes(conv(rtrim($row->AL_BAIRRO)));
    $nascimento = addslashes($row->AL_NASC);
    $naturalidade = $naturalidade;
    $cep = addslashes($row->AL_CEP);
    $cidade = $cidade;
    $telefone = '(' . addslashes($row->AL_TELDDD) . ') ' . addslashes($row->AL_TELNUM);
    $celular = '(' . addslashes($row->AL_CELDDD) . ') ' . addslashes($row->AL_CELULAR);
    $sexo = trim($row->AL_SEXO);
    $ano1g = addslashes($row->AL_1G_ANO);
    $escola1g = addslashes(conv(rtrim($row->AL_1G_ESCOLA)));

    if (empty($aluno)) { // NÃO EXISTE, ENTÃO IMPORTA
        $sql = "insert into Pessoas (codigo, prontuario, senha, nome, cidade, rg, cpf, email, observacoes, endereco, bairro, nascimento, naturalidade, "
                . "cep, telefone, celular, sexo, ano1g, escola1g) values (0, "
                . "'" . $row->AL_PRONT . "', "
                . "PASSWORD('" . $row->AL_PRONT . "'), "
                . "'$nome', $cidade, '$rg', '$cpf', '$email', '$observacoes', '$endereco', '$bairro', "
                . "'$nascimento', '$naturalidade', '$cep', '$telefone', '$celular', '$sexo', "
                . " '$ano1g', '$escola1g')";

        if (!mysql_query($sql)) {
            if ($DEBUG)
                print "ERRO: $sql <br>\n";
            mysql_query("insert into Logs values(0, '" . addslashes($sql) . "', now(), 'CRON_ERRO', 1)");
        } else {
            $i++;
            $COD = mysql_insert_id();
            mysql_query("INSERT INTO PessoasTipos VALUES (NULL, $COD, $ALUNO)");
            $REG = "ALUNO NOVO: $nome";
            mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_ALUNO', 1)");
            if ($DEBUG)
                print "$REG <br>\n";
        }
    } else {
        // ALTERANDO OS DADOS DO WD
        if (strcmp(addslashes($aluno->nome), $nome) != 0 || strcmp(addslashes($aluno->rg), $rg) != 0 || strcmp(addslashes($aluno->cpf), $cpf) != 0 || strcmp($aluno->email, $email) != 0 || strcmp(addslashes($aluno->observacoes), $observacoes) != 0 || strcmp(addslashes($aluno->endereco), $endereco) != 0 || strcmp(addslashes($aluno->bairro), $bairro) != 0 || strcmp($aluno->nascimento, $nascimento) != 0 || strcmp($aluno->naturalidade, $naturalidade) != 0 || strcmp(addslashes($aluno->cep), $cep) != 0 || $aluno->cidade != $cidade || strcmp(addslashes($aluno->telefone), $telefone) != 0 || strcmp(addslashes($aluno->celular), $celular) != 0 || $aluno->sexo != $sexo || strcmp(addslashes($aluno->ano1g), $ano1g) != 0 || strcmp(addslashes($aluno->escola1g), $escola1g) != 0
        ) {
            $sql = "UPDATE Pessoas
                                SET nome = '$nome',
	    			rg = '$rg',
	    			cpf = '$cpf',
	    			email = '$email',
	    			observacoes = '$observacoes',
	    			endereco = '$endereco',
	    			bairro = '$bairro',
	    			nascimento = '$nascimento',
	    			naturalidade = '$naturalidade',
	    			cep = '$cep',
	    			cidade = $cidade,
	    			telefone = '$telefone',
	    			celular = '$celular',
	    			sexo = '$sexo',
	    			ano1g = '$ano1g',
	    			escola1g = '$escola1g'
	    			WHERE codigo = $aluno->codigo";
            mysql_query($sql);
            $REG = "ALUNO ALTERACAO: $nome";
            mysql_query("insert into Logs values(0, '$REG', now(), 'CRON_ALUNO', 1)");
            if ($DEBUG)
                print "$REG <br>\n";
            $j++;
        }
    }
}

// REGISTRA A ATUALIZACAO
if (!$LOCATION_CRON) {
    $sql = "insert into Atualizacoes values(0,1," . $_SESSION['loginCodigo'] . ", now())";
    mysql_query($sql);
    ?>
    <script>
        $('#db2AlunosRetorno').text('<?= $i ?> importados, <?= $j ?> atualizados');
    </script><?php
} else {
    $sqlAdmin = "SELECT * FROM Pessoas WHERE prontuario='admin'";
    $resultAdmin = mysql_query($sqlAdmin);
    $admin = mysql_fetch_object($resultAdmin);

    $sql = "insert into Atualizacoes values(0,101," . $admin->codigo . ", now())";
    mysql_query($sql);

    $URL = "ALUNOS IMPORTADOS: $i |ATUALIZADOS: $j";
    if ($DEBUG)
        print "$URL \n";
    $sql = "insert into Logs values(0, '$URL', now(), 'CRON', 1)";
    mysql_query($sql);
}

function inserirEstado($estado) {
    $sql = "INSERT INTO Estados VALUES (0,'$estado','$estado')";
    mysql_query($sql);
    return mysql_insert_id();
}

function inserirCidade($cidade, $estado) {
    $estado = strtoupper($estado);
    $cidade = formatarTexto($cidade);
    $sql = "select * from Estados where sigla='$estado'";
    $result = mysql_query($sql);
    if (!$estados = mysql_fetch_object($result))
        $codEstado = inserirEstado($estado);
    else
        $codEstado = $estados->codigo;

    $sql = "select * from Cidades where estado='$codEstado' and nome='$cidade'";
    $result = mysql_query($sql);
    if (!$cidades = mysql_fetch_object($result)) {
        $sql = "INSERT INTO Cidades VALUES (0,'$cidade','$codEstado')";
        mysql_query($sql);
        return mysql_insert_id();
    } else {
        return $cidades->codigo;
    }
}
?>