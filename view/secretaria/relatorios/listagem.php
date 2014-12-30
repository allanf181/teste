<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Possibilita gerar diversos relatórios em PDF.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;

require CONTROLLER . '/atribuicao.class.php';
$atribuicao = new Atribuicoes();

require CONTROLLER . '/pessoa.class.php';
$pessoa = new Pessoas();

$data = date("d/m/Y");

if (isset($_GET["relatorio"]) && $_GET["relatorio"] != "")
    $relatorio = $_GET["relatorio"];

if (dcrip($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $params['curso'] = $curso;
    $sqlAdicional .= ' AND c.codigo = :curso ';

    if ($_SESSION['regAnterior'] && $curso != $_SESSION['regAnterior']) {
        unset($_GET["turma"]);
    }
    $_SESSION['regAnterior'] = $curso;
}

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND t.codigo = :turma ';
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $paramsCurso['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicionalCurso = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord)";
}

if (isset($_GET["bimestre"]))
    $bimestre = dcrip($_GET["bimestre"]);

// MOSTRANDO OS GRÁFICOS
if ($_GET['opcao'] == 'grafico') {
    if ($_GET['relatorio'] == 'docente') {
        $params['ano'] = $ANO;
        $params['semestre'] = $SEMESTRE;
        print $atribuicao->getADToJSON($params, $sqlAdicional);
        exit;
    }
    if ($_GET['relatorio'] == 'lancamentos') {
        $params['ano'] = $ANO;
        $params['semestre'] = $SEMESTRE;
        require CONTROLLER . '/aula.class.php';
        $aula = new Aulas();        
        print $aula->listLAToJSON($params, $sqlAdicional);
        exit;
    }
    if ($_GET['relatorio'] == 'frequencia' && $_GET['curso'] && $_GET['turma']) {
        require CONTROLLER . '/frequencia.class.php';
        $frequencia = new Frequencias();        
        print $frequencia->getLFToJSON($params, $sqlAdicional);
        exit;
    }
    if ($_GET['relatorio'] == 'matriculasTotais' && $_GET['curso']) {
        require CONTROLLER . '/matricula.class.php';
        $matricula = new Matriculas();
        print $matricula->getMToJSON($params, $sqlAdicional);
        exit;
    }
}

require PERMISSAO;
require SESSAO;
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<?php
$tipo['alunos'] = array('nome' => 'Alunos', 'curso' => 2, 'turma' => 2, 'turno' => 1);
$tipo['atendimento'] = array('nome' => 'Atendimento');
$tipo['atvAcadEmica'] = array('nome' => 'Atividades Acadêmicas', 'curso' => 1, 'turma' => 1, 'aluno' => 1);
$tipo['docente'] = array('nome' => 'Atribuição Docente', 'curso' => 2, 'turma' => 1);
$tipo['boletim'] = array('nome' => 'Boletim Individual', 'curso' => 1, 'turma' => 1, 'aluno' => 1);
$tipo['boletimTurma'] = array('nome' => 'Boletim Turma', 'curso' => 1, 'turma' => 1, 'fechamento' => 1, 'turno' => 1);
$tipo['carometro'] = array('nome' => 'Carômetro', 'curso' => 1, 'turma' => 1, 'turno' => 1);
$tipo['diario'] = array('nome' => 'Diário', 'curso' => 1, 'turma' => 1, 'disciplina' => 1);
$tipo['disciplinas'] = array('nome' => 'Disciplinas do Curso', 'curso' => 2);
$tipo['fpa'] = array('nome' => 'FPA (Folha P. Atividades)', 'professor' => 1);
$tipo['pit'] = array('nome' => 'PIT (Plano I. Trabalho)', 'professor' => 1);
$tipo['rit'] = array('nome' => 'RIT (Relat&oacute;rio I. Trabalho)', 'professor' => 1);
$tipo['ftdd'] = array('nome' => 'FTD Detalhada', 'professor' => 1);
$tipo['ftdr'] = array('nome' => 'FTD Resumida', 'professor' => 1);
$tipo['lancamentos'] = array('nome' => 'Lançamento de Aulas', 'curso' => 2, 'turma' => 1);
$tipo['chamada'] = array('nome' => 'Lista de Chamada', 'curso' => 1, 'turma' => 1, 'disciplina' => 1);
$tipo['matriculas'] = array('nome' => 'Lista de Matrículas', 'curso' => 2, 'turma' => 1, 'turno' => 1, 'situacao' => 1);
$tipo['presenca'] = array('nome' => 'Lista de Presença', 'curso' => 1, 'turma' => 1, 'turno' => 1, 'data' => 1, 'assunto' => 1);
$tipo['planoEnsino'] = array('nome' => 'Planos de Ensino', 'curso' => 1, 'turma' => 1, 'disciplina' => 1);
$tipo['frequencia'] = array('nome' => 'Relatório de Frequências', 'curso' => 1, 'turma' => 1, 'disciplina' => 1, 'data' => 1);
$tipo['matriculasTotais'] = array('nome' => 'Totalização de Matrículas', 'curso' => 2, 'turma' => 1, 'turno' => 1, 'situacao' => 1);
$tipo['trocas'] = array('nome' => 'Trocas/Reposições', 'curso' => 2, 'turma' => 1);

$rel_curso = null;
$rel_turma = null;
$rel_turno = null;
$rel_situacao = null;
$rel_fechamento = null;
$rel_disciplina = null;
$rel_professor = null;
$rel_aluno = null;
$rel_data = null;
$rel_assunto = null;
?>
<table border="0" width="100%" id="form" width="100%">
    <tr>
        <td colspan="3">
            <select name="relatorio" id="relatorio" style="width: 200px">
                <option value=""></option>
                <?php
                foreach ($tipo as $k => $v) {
                    $selected = null;
                    if ($relatorio == $k) {
                        $selected = 'selected';
                        $rel_curso = $v['curso'];
                        $rel_turma = $v['turma'];
                        $rel_turno = $v['turno'];
                        $rel_situacao = $v['situacao'];
                        $rel_fechamento = $v['fechamento'];
                        $rel_disciplina = $v['disciplina'];
                        $rel_professor = $v['professor'];
                        $rel_aluno = $v['aluno'];
                        $rel_data = $v['data'];
                        $rel_assunto = $v['assunto'];
                    }
                    ?>
                    <option <?= $selected ?> value="<?= $k ?>"><?= $v['nome'] ?></option>
                    <?php
                }
                ?>
            </select>
        </td>
    </tr>

    <!-- ================================ -->         	

    <?php if ($relatorio == 'alunos') { ?>
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td  colspan="2">Campos:
                <input type="checkbox" id="alunos_rg" value="1" checked="checked" /><label for="alunos_rg">RG</label>
                <input type="checkbox" id="alunos_cpf" value="1" checked="checked" /><label for="alunos_cpf">CPF</label>
                <input type="checkbox" id="alunos_nasc" value="1" checked="checked" /><label for="alunos_nasc">D.Nasc</label>
                <input type="checkbox" id="alunos_endereco" value="1" /><label for="alunos_endereco">Endereço</label>
                <input type="checkbox" id="alunos_bairro" value="1" /><label for="alunos_bairro">Bairro</label>
                <input type="checkbox" id="alunos_cidade" value="1" /><label for="alunos_cidade">Cidade</label>
                <input type="checkbox" id="alunos_telefone" value="1" checked="checked" /><label for="alunos_telefone">Telefone</label>
                <input type="checkbox" id="alunos_celular" value="1" checked="checked" /><label for="alunos_celular">Celular</label>
                <input type="checkbox" id="alunos_email" value="1" checked="checked" /><label for="alunos_email">E-mail</label>
            </td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan="3">&nbsp;</td>
    </tr>
    <?php if ($rel_curso) { ?>
        <tr>
            <td>Curso: </td>
            <td>
                <select name="curso" id="curso" style="width: 350px">
                    <option value=""></option>
                    <?php if ($rel_curso == 2) { ?>
                        <option selected value="">Todos os cursos</option>
                        <?php
                    }
                    require CONTROLLER . '/curso.class.php';
                    $cursos = new Cursos();
                    foreach ($cursos->listCursos($paramsCurso, $sqlAdicionalCurso, null, null) as $reg) {
                        $selected = "";
                        if ($reg['codigo'] == $curso)
                            $selected = "selected";
                        print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['curso'] . " [" . $reg['codigo'] . "]</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
    <?php } ?>
    <?php if ($rel_turma) { ?>
        <tr>
            <td>Turma: </td>
            <td>
                <select name="turma" id="turma" style="width: 350px">
                    <option value=""></option>
                    <?php if ($rel_turma == 2) { ?>
                        <option selected value="">Todas as turmas</option>
                        <?php
                    }
                    require CONTROLLER . '/turma.class.php';
                    $turmas = new Turmas();
                    $sqlAdicionaTurma = ' AND c.codigo = :curso ';
                    $paramsTurma = array(':curso' => $curso, ':ano' => $ANO, ':semestre' => $SEMESTRE);
                    foreach ($turmas->listTurmas($paramsTurma, $sqlAdicionaTurma) as $reg) {
                        $selected = "";
                        if ($reg['codTurma'] == $turma)
                            $selected = "selected";
                        print "<option $selected value='" . crip($reg['codTurma']) . "'>" . $reg['numero'] . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }
    if ($rel_fechamento) {
        ?>
        <tr>
            <td>Fechamento: </td>
            <td>
                <select name="bimestre" id="bimestre" style="width: 350px">
                    <option value=""></option>
                    <?php
                    foreach ($atribuicao->getFechamentos($turma) as $reg) {
                        $selected = "";
                        if ($reg['value'] == $bimestre)
                            $selected = "selected";
                        print "<option $selected value='" . crip($reg['value']) . "'>" . $reg['nome'] . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
    <?php } ?>

    <?php if ($rel_disciplina) { ?>
        <tr><td>Disciplina: </td>
            <td><select name="disciplina" id="disciplina" style="width: 350px">
                    <?php if ($rel_disciplina == 2) { ?>
                        <option value="">Todas as disciplinas</option>
                        <?php
                    }
                    $sqlAdicionaDisc = ' AND t.codigo = :turma ';
                    $paramsDisc = array(':turma' => $turma, ':ano' => $ANO, ':semestre' => $SEMESTRE);
                    foreach ($atribuicao->getAllAtribuicoes($paramsDisc, $sqlAdicionaDisc) as $reg) {
                        $selected = "";
                        if ($reg['atribuicao'] == $disciplina)
                            $selected = "selected";
                        print "<option $selected value='" . crip($reg['atribuicao']) . "'>" . $reg['disciplina'] . $reg['bimestre'] . $reg['subturma'] . " [" . $reg['turno'] . "]</option>";
                    }
                    ?>
                </select>
            </td></tr>
        <?php
    }

    if ($rel_turno) {
        ?>
        <tr><td>Per&iacute;odo: </td>
            <td>
                <select name="turno" id="turno" value="<?= $turno ?>">
                    <option></option>
                    <?php
                    require CONTROLLER . '/turno.class.php';
                    $turnos = new Turnos();
                    foreach ($turnos->listRegistros() as $reg) {
                        $selected = "";
                        if ($reg['codigo'] == $turno)
                            $selected = "selected";
                        print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }

    if ($rel_aluno) {
        ?>
        <tr>
            <td>Aluno: </td>
            <td>
                <select name="aluno" id="aluno" style="width: 350px">
                    <option value="">Todos os alunos</option>                    
                    <?php
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
        <?php
    }
    if ($rel_situacao) {
        ?>
        <tr>
            <td>Situa&ccedil;&atilde;o: </td>
            <td>
                <select name="situacao" id="situacao" style="width: 350px">
                    <option value="">Todas as situa&ccedil;&otilde;es</option>
                    <?php
                    require CONTROLLER . '/situacao.class.php';
                    $situacao = new Situacoes();
                    $sqlAdicionalSit = "AND t.codigo = :turma";
                    $paramsSit = array('turma' => $turma);
                    foreach ($situacao->getSituacoesOfTurma($paramsSit, $sqlAdicionalSit) as $reg) {
                        $selected = "";
                        if ($reg['codigo'] == $situacao)
                            $selected = "selected";
                        print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }
    if ($rel_professor) {
        ?>
        <tr>
            <td>Professor: </td>
            <td>
                <select name="professor" id="professor" style="width: 350px">
                    <option selected value="<?= crip('Todos') ?>">Todos</option>
                    <?php
                    $sqlAdicionalProf = ' AND pt.tipo = :prof ';
                    $paramsProf = array('prof' => $PROFESSOR);
                    foreach ($pessoa->listPessoasTipos($paramsProf, $sqlAdicionalProf, null, null) as $reg) {
                        $selected = "";
                        if ($reg['codigo'] == $aluno)
                            $selected = "selected";
                        print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }
    if ($rel_data) {
        ?>
        <tr>
            <td>Data: </td>
            <td>
                <input value="<?= $data ?>" readonly type="text" class="data1" name="data" id="data" />
            </td>
        </tr>
        <?php
    }

    if ($rel_assunto) {
        ?>
        <tr>
            <td>Assunto: </td>
            <td>
                <input value="<?= $assunto ?>" type="text" name="assunto" id="assunto" maxlength="45" />
            </td>
        </tr>
    <?php } ?>        
</td>
<?php if ($relatorio) { ?>
    <tr>
        <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
        <td width="120">
            <a href="#" title="Imprimir em PDF" onclick="relatorio('pdf', '<?= $relatorio ?>')">
                <img style="width: 30px" src="<?= ICONS ?>/files/pdf.png" />
            </a>
            <?php if ($relatorio == 'alunos') { ?>
                <a href="#" title="Imprimir em HTML" onclick="relatorio('html', '<?= $relatorio ?>')">
                    <img style="width: 30px" src="<?= ICONS ?>/files/htm.png" />
                </a>
                <a href="#" title="Imprimir em XLS" onclick="relatorio('xls', '<?= $relatorio ?>')">
                    <img style="width: 30px" src="<?= ICONS ?>/files/xls.png" />
                </a>        
            <?php } ?>
        </td>
    </tr>
<?php } ?>
</table>

<div id="container"></div>
<script src="<?= VIEW ?>/js/highcharts/highcharts.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/highcharts/exporting.js" type="text/javascript"></script>


<?php
    if ($relatorio == 'docente' || $relatorio == 'lancamentos' 
            || $relatorio == 'frequencia' || $relatorio == 'matriculasTotais') {
    ?>
    <script>
                        var options = {
                            chart: {
                                renderTo: 'chart',
                            },
                            credits: {
                                enabled: false
                            },
                            title: {},
                            xAxis: {
                                categories: [{}],
                                title: {},
                            },
                            yAxis: {
                                title: {},
                            },
                            tooltip: {
                                formatter: function () {
                                    var s = '<b>' + this.x + '</b>';

                                    $.each(this.points, function (i, point) {
                                        s += '<br/>' + point.series.name + ': ' + point.y;
                                    });

                                    return s;
                                },
                                shared: true
                            },
                            series: [{}, {}]
                        };
                        
                        var curso = $('#curso').val();
                        var turma = $('#turma').val();

                        $.ajax({
                            url: "<?= VIEW ?>/secretaria/relatorios/listagem.php?opcao=grafico&relatorio=<?= $relatorio ?>&curso=" + curso + "&turma=" + turma,
                            data: 'show=impression',
                            type: 'post',
                            dataType: "json",
                            success: function (data) {
                                options.xAxis.categories = data.item1;
                                options.series[0].name = data.item2Name;
                                options.series[0].data = data.item2;
                                options.series[1].name = data.item3Name;
                                options.series[1].data = data.item3;
                                options.title.text = data.title;
                                options.xAxis.title.text = data.titleX;
                                options.yAxis.title.text = data.titleY;
                                $('#container').highcharts(options);
                            }
                        });
    </script>
    <?php
}
?>
<script>
    $(document).ready(function () {
        $("#data").datepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior'
        });

        $('#turma, #curso, #relatorio, #bimestre').change(function () {
            var turma = $('#turma').val();
            var curso = $('#curso').val();
            var bimestre = $('#bimestre').val();
            $('#index').load('<?= $SITE ?>?relatorio=' + $('#relatorio').val() + '&turma=' + turma + '&curso=' + curso + '&bimestre=' + bimestre);
        });
    });

    function relatorio(impressao, tipo) {
        var bimestre = $('#bimestre').val();
        var aluno = $('#aluno').val();
        var curso = $('#curso').val();
        var turma = $('#turma').val();
        var turno = $('#turno').val();
        var disciplina = $('#disciplina').val();
        var situacao = $('#situacao').val();
        var data = $('#data').val();
        var professor = $('#professor').val();
        var assunto = encodeURIComponent($('#assunto').val());

        var rg = $('#alunos_rg').is(':checked');
        var cpf = $('#alunos_cpf').is(':checked');
        var nasc = $('#alunos_nasc').is(':checked');
        var endereco = $('#alunos_endereco').is(':checked');
        var bairro = $('#alunos_bairro').is(':checked');
        var cidade = $('#alunos_cidade').is(':checked');
        var telefone = $('#alunos_telefone').is(':checked');
        var celular = $('#alunos_celular').is(':checked');
        var email = $('#alunos_email').is(':checked');

        var det = 0;
        if (tipo == 'ftdd')
            det = 1;
        if (tipo == 'ftdr' || tipo == 'ftdd')
            tipo = 'ftd';

        if (impressao == 'pdf' || impressao == 'xls')
            window.open('<?php print VIEW; ?>/secretaria/relatorios/inc/' + tipo + '.php?curso=' + curso + '&turma=' + turma + '&turno=' + turno + '&bimestre=' + bimestre + '&aluno=' + aluno + '&atribuicao=' + disciplina + '&data=' + data + '&situacao=' + situacao
                    + '&rg=' + rg + '&cpf=' + cpf + '&nasc=' + nasc + '&endereco=' + endereco + '&bairro=' + bairro + '&cidade=' + cidade + '&telefone=' + telefone + '&celular=' + celular + '&professor=' + professor + '&assunto=' + assunto +
                    '&email=' + email + '&detalhada=' + det + '&tipoImpressao=' + impressao, '_blank');
        else
            window.open('<?php print VIEW; ?>/secretaria/relatorios/inc/' + tipo + 'Html.php?curso=' + curso + '&turma=' + turma + '&turno=' + turno, '_blank');
    }
</script>