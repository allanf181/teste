<?php
//Esse arquivo é fixo para o aluno.
//Visualização do Boletim do Aluno.
//Link visível no menu: não, pois para este item é criado um ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require CONTROLLER . "/matricula.class.php";
$matricula = new Matriculas();

require CONTROLLER . "/nota.class.php";
$nota = new Notas();

require CONTROLLER . "/avaliacao.class.php";
$avaliacao = new Avaliacoes();

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND t.codigo = :turma ';
}

if (dcrip($_GET["aluno"])) {
    $aluno = dcrip($_GET["aluno"]);
    $params['aluno'] = $aluno;
    $sqlAdicional .= ' AND p.codigo = :aluno ';
}
else{
    $aluno = $_SESSION['loginCodigo'];
    $params['aluno'] = $aluno;
    $sqlAdicional .= ' AND p.codigo = :aluno ';
}

if (dcrip($_GET["bimestre"])) {
    $bimestre = dcrip($_GET["bimestre"]);
    $params['bimestre'] = $bimestre;
    $sqlAdicional .= ' AND a.bimestre=:bimestre ';
}
?>
<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>
<center>
    <?php if (!in_array($ALUNO, $_SESSION["loginTipo"]) && !in_array($PROFESSOR, $_SESSION["loginTipo"]) || in_array($COORD, $_SESSION["loginTipo"])) {
        ?>
        <div id="html5form" class="main">
            <form id="form_padrao">
                <table align="center" width="100%" id="form" border="0">
                    <tr>
                        <td align="right" style="width: 100px">Turma: </td>
                        <td>
                            <select name="turma" id="turma" value="<?= $turma ?>">
                                <option></option>
                                <?php
                                require CONTROLLER . '/turma.class.php';
                                $turmas = new Turmas();

                                if (in_array($COORD, $_SESSION["loginTipo"])) {
                                    $paramsTurma['coord'] = $_SESSION['loginCodigo'];
                                    $sqlAdicionalTurma = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord) ";
                                }

                                $paramsTurma[':ano'] = $ANO;
                                $paramsTurma[':semestre'] = $SEMESTRE;
                                foreach ($turmas->listTurmas($paramsTurma, $sqlAdicionalTurma) as $reg) {
                                    $selected = "";
                                    if ($reg['codTurma'] == $turma)
                                        $selected = "selected";
                                    print "<option $selected value='" . crip($reg['codTurma']) . "'>" . $reg['numero'] . " [" . $reg['curso'] . "]</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" style="width: 100px">Aluno: </td>
                        <td>
                            <select name="aluno" id="aluno" style="width: 350px">
                                <option></option>
                                <?php
                                require CONTROLLER . '/pessoa.class.php';
                                $pessoa = new Pessoas();
                                $sqlAdicionalAluno = "AND p.codigo IN (SELECT p.codigo 
                                    FROM Pessoas p, Atribuicoes a, Matriculas m, Turmas t
                                    WHERE t.codigo = a.turma
                                    AND m.atribuicao = a.codigo
                                    AND m.aluno = p.codigo 
                                    AND t.codigo = :turma
                                    GROUP BY p.codigo)";
                                $paramsAluno = array('turma' => $turma);
                                foreach ($pessoa->listPessoasTipos($paramsAluno, $sqlAdicionalAluno, null, null) as $reg) {
                                    $selected = "";
                                    if ($reg['codigo'] == $aluno)
                                        $selected = "selected";
                                    print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" style="width: 100px">Bimestre: </td>
                        <td>
                            <select name="bimestre" id="bimestre" style="width: 350px">
                                <option value=""></option>
                                <?php
                                require CONTROLLER . '/atribuicao.class.php';
                                $atribuicao = new Atribuicoes();
                                foreach ($atribuicao->getFechamentos($turma) as $reg) {
                                    $selected = "";
                                    if ($reg['value'] == $bimestre)
                                        $selected = "selected";
                                    if ($reg['value'] != 'final')
                                        print "<option $selected value='" . crip($reg['value']) . "'>" . $reg['nome'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    if ($aluno) {
        $params['ano'] = $ANO;
        $params['semestre'] = $SEMESTRE;
        $sqlAdicional .= " AND t.ano = :ano "
                . "AND (semestre = 0 OR semestre = :semestre) "
                . "GROUP BY m.codigo ORDER BY a.bimestre, p.nome, d.nome ";
        $res = $matricula->getMatriculas($params, $sqlAdicional);
        $resultadoGlobal = $nota->resultadoModulo($aluno, $turma);
        ?>

        <div class='fundo_listagem'>
            <div id='alunos_cabecalho'>
                <img alt="foto" style="margin-top: 2px; margin-bottom: 2px; width: 95px; height: 125px " src="<?= INC ?>/file.inc.php?type=pic&id=<?= crip($aluno) ?>" />
                <div class="alunos_dados_nome"><?= $res[0]['pessoa'] ?></div><br />
                <div class="alunos_dados_prontuario"><?= $res[0]['prontuario'] ?></div>
            </div>

            <table id="tabela_alunos_cabecalho">
                <tr class='cdif'>
                    <th>Turma</th>
                    <th>Curso</th>
                </tr>
                <tr>
                    <td><?= $res[0]['turma'] ?></td>
                    <td><?= $res[0]['curso'] ?></td>
                </tr>
            </table>
            <br />

            <?php
            require CONTROLLER . "/professor.class.php";
            $professor = new Professores();

            foreach ($res as $reg) {
                if ($reg['bimestreFormat'] && $reg['bimestreFormat'] != $bimAnt) {
                    print "<br /><h2>" . $reg['bimestreFormat'] . "</h2>";
                    $bimAnt = $reg['bimestreFormat'];
                }
                ?>
                <br><table id='tabela_boletim' align='center'>
                    <tr class='cdif'>
                        <th colspan="2"><?= $reg['disciplina'] ?> <?= $reg['bimestreFormat'] ?></th>
                        <th style='width: 100px'><?= $reg['numero'] ?></th>
                        <th colspan="3" style="color: white"><?= $professor->getProfessor($reg['atribuicao'], 1, '<br>', 0, 1) ?></tr>

                    <?php
                    $dados = $nota->resultado($reg['matricula'], $reg['atribuicao']);
                    ?>
                    <tr class='cdif'>
                        <th>Situa&ccedil;&atilde;o</th>
                        <th style='width: 100px'>Aulas Dadas</th>
                        <th style='width:100px'>Carga Hor.</th>
                        <th <?= $col ?> style='width:50px'>Faltas</th>
                        <th style='width: 100px'>Frequ&ecirc;ncia</th>
                        <th style='width: 100px'>M&eacute;dia</th>
                    </tr>
                    <tr>
                        <td align='center'><?= $reg['situacao'] ?></td>
                        <td align='center'><?= $dados['auladada'] ?></td>
                        <td align='center'><?= intval($dados['CH']) ?></td>
                        <td <?= $col ?> align='center'><?= $dados['faltas'] ?></td>
                        <td align='center'><?= arredondar($dados['frequencia']) ?>%</td>
                        <td align='center'><?= $dados['media'] ?></td>
                    </tr>
                    <tr class='cdif'>
                        <th colspan='3'>Avalia&ccedil;&atilde;o</th>
                        <th>Data</th>
                        <th>C&aacute;lculo</th>
                        <th>Nota</th>
                    </tr>
                    <?php
                    // busca as avaliações da disciplina atual
                    $i = 0;
                    $params = array(':aluno' => $aluno, ':atribuicao' => $reg['atribuicao']);
                    $sqlAdicional = ' ORDER BY al.nome ';
                    $aval = $avaliacao->listAvaliacoesAluno($params, $sqlAdicional);
                    foreach ($aval as $a) {
                        if ($a['calculo'] == 'FORMULA')
                            $aval = str_replace('$', '', $a['formula']);
                        else
                            $aval = $$a['calculo'] . ' ' . $a['peso'];

                        if ($a['avaliacao'] == 'recuperacao')
                            $aval = $$a['avalCalculo'];

                        $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                        ?>
                        <tr <?= $cdif ?>>
                            <td align='center' colspan='3'><?= $a['nome'] ?></td>
                            <td align='center'><?= $a['data'] ?></td>
                            <td align='center'><?= $aval ?></td>
                            <td align='center'><?= $a['nota'] ?></td>
                        </tr>
                        <?php
                        $i++;
                    }
                    ?>
                </table>
                <?php
            }

            if ($_SESSION['LINK']) {
                ?>
                <br><div style='margin: auto'><a href="javascript:$('#<?= $_SESSION['VOLTAR'] ?>').load('<?= $_SESSION['LINK'] ?>');void(0);" title='Voltar' ><img class='botao' src='<?= ICONS ?>/left.png'/></a></div>
                        <?php
                    }
                    ?>
                    <?php
                }
                ?>
    </div>
</center>

<script>
    $('#aluno, #turma, #bimestre').change(function () {
        var turma = $('#turma').val();
        var aluno = $('#aluno').val();
        var bimestre = $('#bimestre').val();
        $('#index').load('<?= $SITE ?>?turma=' + turma + '&aluno=' + aluno + '&bimestre=' + bimestre);
    });
</script>