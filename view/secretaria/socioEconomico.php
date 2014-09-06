<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Exibe os dados estatísticos obtidos pela pesquisa socioeconômica dos discentes do Campus.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/pessoa.class.php";
$pessoa = new Pessoas();

if (dcrip($_GET["turma"])) {
    $turma = dcrip($_GET["turma"]);
    $params['turma'] = $turma;
    $sqlAdicional = ' AND t.codigo = :turma ';
}

if (dcrip($_GET["curso"])) {
    $curso = dcrip($_GET["curso"]);
    $params['curso'] = $curso;
    $sqlAdicional .= ' AND c.codigo = :curso ';
}
?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<table align="center" id="form" width="100%">
    <tr>
        <td align="right" style="width: 100px">Curso: </td>
        <td>
            <select name="curso" id="curso" value="<?php echo $curso; ?>" style="width: 350px">
                <option></option>
                <?php
                require CONTROLLER . '/curso.class.php';
                $cursos = new Cursos();
                foreach ($cursos->listCursos() as $reg) {
                    $selected = "";
                    if ($reg['codigo'] == $curso)
                        $selected = "selected";
                    print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['curso'] . " [" . $reg['codigo'] . "]</option>";
                }
                ?>
            </select>
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
</table>
<br />
<?php
$dados[] = array('titulo' => 'Sexo', 'campo' => 'sexo', 'tabela' => 'Sexos');
$dados[] = array('titulo' => 'Cor/Raça', 'campo' => 'raca', 'tabela' => 'Racas');
$dados[] = array('titulo' => 'Estado Civil', 'campo' => 'estadoCivil', 'tabela' => 'EstadosCivis');
$dados[] = array('titulo' => 'Renda Familiar', 'campo' => 'renda', 'tabela' => 'Rendas');
$dados[] = array('titulo' => 'Situacão de Trabalho', 'campo' => 'situacaoTrabalho', 'tabela' => 'SituacoesTrabalho');
$dados[] = array('titulo' => 'Tipos de Trabalho', 'campo' => 'tipoTrabalho', 'tabela' => 'TiposTrabalho');
$dados[] = array('titulo' => 'Tempo de Trabalho', 'campo' => 'tempo', 'tabela' => 'TemposPesquisa');
$dados[] = array('titulo' => 'Meio de Transporte', 'campo' => 'meioTransporte', 'tabela' => 'MeiosTransporte');
$dados[] = array('titulo' => 'Nº Residentes na Casa', 'campo' => 'numeroPessoasNaResidencia', 'tabela' => 'Pessoas', 'group' => array('0|0|Não declarado', '1|2|de 1 a 2', '3|4|de 3 a 4', '5|8|de 5 a 8', '9|12|de 9 a 12', '13|20|de 13 a 20'));
$dados[] = array('titulo' => 'Utiliza Transporte Público', 'campo' => 'transporteGratuito', 'tabela' => 'Pessoas', 'group' => array('0|0|Não declarado', 's|s|Sim', 'n|s|Não'));
$dados[] = array('titulo' => 'Necessidades Especiais', 'campo' => 'necessidadesEspeciais', 'tabela' => 'Pessoas', 'group' => array('0|0|Não declarado', 's|s|Sim', 'n|s|Não'));
$dados[] = array('titulo' => 'Estudou em Escola Pública', 'campo' => 'escolaPublica', 'tabela' => 'Pessoas', 'group' => array('0|0|Não declarado', 's|s|Sim', 'n|s|Não'));

$params['ano'] = $ANO;
$params['semestre'] = $SEMESTRE;
$params['aluno'] = $ALUNO;
    
foreach ($dados as $reg) {
    ?>
    <table class="socioeconomico" align="center">
        <tr>
            <th align="center" style='width: 50%' colspan='4'><?= $reg['titulo'] ?></th>
        </tr>
        <?php
        $total = 0;
        foreach ($pessoa->dadosSocioEconomico($reg['tabela'], $reg['campo'], $params, $sqlAdicional, $reg['group']) 
                as $reg) {
            $total += $reg['totalGeral'];
            ?>
            <tr class='<?= (( ++$i % 2 == 0) ? "cdif" : "") ?>'>
                <td align='center' style='width: 300px'><?= mostraTexto($reg['nome']) ?></td>
                <td align='center' ><?= $reg['total'] ?></td>
                <td align='center' ><?= percentual($reg['total'], $total) ?></td>
            </tr>
            <?php
        }
        ?>
        <tr style="background: #E0E0E0; font-weight: bold;  ">
            <td align='center'>TOTAL</td>
            <td align='center'><?= $total ?></td>
            <td align='center'><?= percentual($total, $total) ?></td>
        </tr>
    </table>
    <?php
}
?>
<script>
    function valida() {
        turma = $('#turma').val();
        curso = $('#curso').val();
        $('#index').load('<?php print $SITE; ?>?&turma=' + turma + '&curso=' + curso);
    }

    $('#turma, #curso').change(function() {
        valida();
    });
</script>    