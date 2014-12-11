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

require CONTROLLER . "/logSolicitacao.class.php";
$log = new LogSolicitacoes();

if ($_GET["opcao"] == 'historico') {
    $_GET['tabela'] = 'DIARIO';
    // COPIA DE:
    require PATH.VIEW.'/common/logSolicitacao.php';
    die;
}

if ($_GET["opcao"] == 'controleDiario') {
    $_GET['pessoa'] = $_SESSION['loginNome'];
    $_GET['codPessoa'] = $_SESSION['loginCodigo'];

    $ret = $att->changePrazo($_GET);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
}
?>
<script src="<?php print VIEW; ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<?php
$params = array();
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
            <th align="left" width="40">#</th>
            <th align="left">Disciplina</th>
            <th align="left">Professor</th>
            <th align="left">Situa&ccedil;&atilde;o</th>
            <th align="left">Hist&oacute;rico</th>
            <th width="20" align="center">
                <input type='checkbox' id='select-all' name='select-all' class='campoTodos' value='' />
            </th>
        </tr>
        <?php
        $params['ano'] = $ANO;
        $params['semestre'] = $SEMESTRE;
        $res = $att->getAllAtribuicoes($params, $sqlAdicional);
        $i = 1;
        if ($res) {
            foreach ($res as $reg) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                if (!$reg['status']) {
                    $origem = 'unlock';
                } else {
                    $origem = 'lock';
                }
                $sqlAdicionalLog = " AND (dataConcessao IS NULL OR dataConcessao = '0000-00-00 00:00:00') ";
                $paramsLog['nomeTabela'] = 'DIARIO';
                $paramsLog['codigoTabela'] = $reg['atribuicao'];
                if ($attLog = $log->listSolicitacoes($paramsLog, $sqlAdicionalLog)) {
                    $cdif="style='background-color: red;'";
                }
                ?>
                <tr <?= $cdif ?>>
                    <td align='left'><?= $i ?></td>
                    <td>
                        <a title='Clique aqui para visualizar o di&aacute;rio de <br><?=$reg['disciplina']?>' target='_blank' href='<?= VIEW ?>/secretaria/relatorios/inc/diario.php?atribuicao=<?= crip($reg['atribuicao']) ?>'><?= abreviar(mostraTexto($reg['disciplina']),20).$reg['bimestre'].$reg['subturma'] ?> [<?= $reg['turma'] ?>] [<?=$reg['turno']?>]</a>
                    </td>
                    <td align='left'><?= $prof->getProfessor($reg['atribuicao'], 1, '<br>', 1, 1, 20) ?></td>
                    <td align='left'>
                        <a href='#' title='<?=$reg['origem']?>'>
                            <img class='botao' src='<?= ICONS.'/'.$origem ?>.png' /><?= abreviar($reg['origem'],35) ?>
                        </a>
                    </td>
                    <td align='center'>
                        <a href='#' title='Ver hist&oacute;rico de solicita&ccedil;&otilde;es'>
                            <img class='botao search' id='<?= crip($reg['atribuicao']) ?>' src='<?= ICONS ?>/search.png' />
                        </a>
                    </td>
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
}
?>
<br /><br />
<script>
    $(".search").click(function (event) {
        var codigo = $(this).attr('id');
        new $.Zebra_Dialog('<strong>Hist&oacute;rico de Solicita&ccedil;&otilde;es</strong>', {
            source: {'iframe': {
                    'src': 'view/secretaria/prazos/diario.php?opcao=historico&codigo=' + codigo,
                    'height': 300
                }},
            width: 600,
            title: 'DIÁRIO'
        });
    });
    
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