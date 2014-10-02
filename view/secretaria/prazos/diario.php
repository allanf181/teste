<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Permite que os coordenadores dos cursos liberem, de forma temporária, o diário para que os professores finalizem seus registros e/ou façam suas alterações.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/professor.class.php";
$prof = new Professores();

require CONTROLLER . "/atribuicao.class.php";
$att = new Atribuicoes();

require CONTROLLER . "/prazoDiario.class.php";
$prazo = new PrazosDiarios();

if ($_GET["opcao"] == 'controleDiario') {
    $_GET['pessoa'] = $_SESSION['loginNome'];

    $GET = $_GET;

    unset($_GET['opcao']);
    unset($_GET['turma']);
    unset($_GET['curso']);
    unset($_GET['professor']);
    unset($_GET['_']);

    $ret = $att->changePrazo($_GET);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

    $_GET = $GET;
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
if (dcrip($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $params['curso'] = $curso;
    $sqlAdicional .= ' AND c.codigo = :curso ';

    if ($_SESSION['regAnterior'] && $curso != $_SESSION['regAnterior']) {
        unset($_GET["turma"]);
        unset($_GET["professor"]);
    }
    $_SESSION['regAnterior'] = $curso;
}

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional .= ' AND t.codigo = :turma ';
}

if (dcrip($_GET["professor"])) {
    $professor = dcrip($_GET["professor"]);
    $params['professor'] = $professor;
    $sqlAdicional .= ' AND p.professor = :professor ';
}

if (in_array($COORD, $_SESSION["loginTipo"])) {
    $paramsCurso['coord'] = $_SESSION['loginCodigo'];
    $sqlAdicionalCurso = " AND c.codigo IN (SELECT curso FROM Coordenadores co WHERE co.coordenador= :coord)";
}
?>
<table align="center" id="form" width="100%">
    <tr>
        <td align="right" style="width: 100px">Curso: </td>
        <td>
            <select name="curso" id="curso" value="<?php echo $curso; ?>" style="width: 350px">
                <option></option>
                <?php
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
        <td rowspan="4"><input type="submit" name="liberar" id="liberar" value="Liberar"><br /><br />
            <input type="submit" name="fechar" id="fechar" value="Fechar">
        </td>
    </tr>
    <tr>
        <td align="right">Turma: </td>
        <td>
            <select name="turma" id="turma" style="width: 350px">
                <option></option>
                <?php
                require CONTROLLER . '/turma.class.php';
                $turmas = new Turmas();
                $sqlAdicionaTurma = ' AND c.codigo = :curso ';
                $paramsTurma = array(':curso' => $curso, ':ano' => $ANO, ':semestre' => $SEMESTRE);
                foreach ($turmas->listTurmas($paramsTurma, $sqlAdicionaTurma) as $reg) {
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
        <td align="right">Professor: </td>
        <td>
            <select name="professor" id="professor" style="width: 350px">
                <option></option>
                <?php
                $sqlAdicionalProf = "AND pr.atribuicao "
                        . "IN (SELECT a1.codigo "
                        . "FROM Atribuicoes a1 "
                        . "WHERE a1.turma = :turmaP)";
                $paramsProf['tipo'] = $PROFESSOR;
                $paramsProf['turmaP'] = $turma;

                foreach ($prof->listProfessores($paramsProf, $sqlAdicionalProf) as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $professor)
                        $selected = "selected";
                    print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                }
                ?>
            </select>
        </td>
    </tr>		        	            
</table>
<?php
if (!empty($curso)) {
    ?>
    <br /><table id="listagem" border="0" align="center">
        <tr>
            <th align="center" width="40">#</th>
            <th align="left">Disciplina</th>
            <th align="left">Professor</th>
            <th align="left">Turma</th>
            <th align="left">Fechado</th>
            <th align="left">Liberado em:</th>            
            <th width="40" align="center">
                <input type='checkbox' id='select-all' name='select-all' class='campoTodos' value='' />
            </th>
        </tr>
        <?php
        $params['ano'] = $ANO;
        $params['semestre'] = $SEMESTRE;
        $res = $att->getAllAtribuicoes($params, $sqlAdicional);
        $i = 1;
        if ($res) {
            $paramsPrazo['ano'] = $ANO;
            $paramsPrazo['semestre'] = $SEMESTRE;
            $sqlAdicionalPrazo = ' AND pd.atribuicao = :atribuicao ';
            foreach ($res as $reg) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                $paramsPrazo['atribuicao'] = $reg['atribuicao'];
                $concessao=null;
                $motivo=null;
                if ($attPrazo = $prazo->listPrazos($paramsPrazo, $sqlAdicionalPrazo)) {
                    $concessao = abreviar($attPrazo[0]['dataConcessao'], 22);
                    $motivo = $attPrazo[0]['motivo'];
                    if (!$attPrazo[0]['dConcessao'])
                        $cdif="style='background-color: red;'";
                }
                ?>
                <tr <?= $cdif ?>>
                    <td align='center'><?= $i ?></td>
                    <td>
                        <a title='Abrir Di&aacute;rio' target='_blank' href='<?= VIEW ?>/secretaria/relatorios/inc/diario.php?atribuicao=<?= crip($reg['atribuicao']) ?>'><?= mostraTexto($reg['disciplina']).$reg['bimestre'].$reg['subturma'] ?> [<?=$reg['turno']?>]</a>
                    </td>
                    <td align='left'><?= $prof->getProfessor($reg['atribuicao'], '<br>', 1, 1) ?></td>
                    <td align=left><?= $reg['turma'] ?></td>
                    <td align='left'><?= $reg['origem'] ?></td>
                    <td align='left'><a href='#' title='<?=$motivo?>'><?= $concessao ?></a></td>

                    <td align='center'>
                        <input type='checkbox' id='campoPrazo' name='campoPrazo[]' value='<?= $reg['atribuicao'] ?>' />
                    </td>
                </tr>
                <?php
                $i++;
            }
        }
        ?>
    </table>
    <?php
    // LISTAGEM DE PRAZOS PRORROGADOS NO SEMESTRE
    $itensPorPagina = 10;
    $item = 1;

    if (isset($_GET['item']))
        $item = $_GET["item"];

    $res = $prazo->listPrazos($params, $sqlAdicional, $item, $itensPorPagina);
    $totalRegistros = count($prazo->listPrazos($params, $sqlAdicional, null, null));

    $params['curso'] = crip($params['curso']);
    $params['turma'] = crip($params['turma']);
    $params['professor'] = crip($params['professor']);
    $SITENAV = $SITE . '?' . mapURL($params);
    require PATH . VIEW . '/paginacao.php';
    ?>

    <table id="listagem" border="0" align="center">
        <tr>
            <th align="center" width="40">#</th>
            <th align="left">Data</th>
            <th align="left">Disciplina</th>
            <th>Professor</th>
            <th width="150">Motivo</th>
        </tr>
        <?php
        $i = $item;
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?= $cdif ?>>
                <td align='center'><?= $i ?></td>
                <td><?= $reg['data'] ?></td>
                <td><?= $reg['disciplina'] ?></td>
                <td><?= $prof->getProfessor($reg['atribuicao'], '<br>', 1, 1) ?></td>
                <td>
                    <a href='#' title='<?= $reg['motivo'] ?>'><?= abreviar($reg['motivo'], 25) ?></a>
                </td>
            </tr>
            <?php
            $i++;
        }
        ?>
    </table>
    <?php
}
?>
<br /><br />
<script>
    $('#select-all').click(function(event) {
        if (this.checked) {
            // Iterate each checkbox
            $(':checkbox').each(function() {
                this.checked = true;
            });
        } else {
            $(':checkbox').each(function() {
                this.checked = false;
            });
        }
    });

    $(document).ready(function() {
        function valida() {
            turma = $('#turma').val();
            curso = $('#curso').val();
            professor = $('#professor').val();
            $('#index').load('<?php print $SITE; ?>?&turma=' + turma + '&curso=' + curso + '&professor=' + professor);
        }

        $('#turma, #curso, #professor').change(function() {
            valida();
        });

        $('#liberar').click(function() {
            controle('liberou');
        });

        $('#fechar').click(function() {
            controle('fechou');
        });

        function controle(botao) {
            curso = $('#curso').val();
            turma = $('#turma').val();
            professor = $('#professor').val();

            if (botao == 'liberou')
                var modo = 'Confirma a liberação temporária do prazo de gerenciamento do diário? <br><br> Motivo:';

            if (botao == 'fechou')
                var modo = 'Confirma a fechar o diário? <br><br> Motivo:';

            $.Zebra_Dialog('<strong>' + modo + '</strong>', {
                'type': 'prompt',
                'title': '<?= $TITLE ?>',
                'buttons': ['Sim', 'Não'],
                'onClose': function(caption, valor) {
                    if (caption == 'Sim') {
                        var selected = [];
                        $('input:checkbox:checked').each(function() {
                            selected.push($(this).val());
                        });
                        $('#index').load('<?php print $SITE; ?>?opcao=controleDiario&curso=' + curso + '&turma=' + turma + '&motivo=' + encodeURIComponent(valor) + '&professor=' + professor + '&botao=' + botao + '&codigo=' + selected);
                    }
                }
            });
        }
    });
</script>