<?php
//Esse arquivo é fixo para o aluno.
//Visualização do Boletim do Aluno.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;
require PERMISSAO;

$turma = dcrip($_GET["turma"]);
$aluno = dcrip($_GET["aluno"]);

$params = array('aluno' => $aluno,'turma' => $turma);
$sqlAdicional = ' AND p.codigo=:aluno AND t.codigo=:turma ';

if (dcrip($_GET["bimestre"])) {
    $bimestre = dcrip($_GET["bimestre"]);
    $params['bimestre'] = $bimestre;
    $sqlAdicional .= ' AND a.bimestre=:bimestre ';
}

$sqlAdicional .= ' ORDER BY a.bimestre, d.nome ';
require CONTROLLER . "/matricula.class.php";
$matricula = new Matriculas();

require CONTROLLER . "/nota.class.php";
$nota = new Notas();

$res = $matricula->getMatriculas($params, $sqlAdicional);
$resultadoGlobal = $nota->resultadoModulo($aluno, $turma);

require CONTROLLER . "/avaliacao.class.php";
$avaliacao = new Avaliacoes();
?>
<center>
    <div class='fundo_listagem'>

        <div id='alunos_cabecalho'>
            <img alt="foto" style="width: 150px; height: 130px" src="<?= INC ?>/file.inc.php?type=pic&id=<?= crip($aluno) ?>" />
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
            ?>
            <br><table id='tabela_boletim' align='center'>
                <tr class='cdif'>
                    <th colspan="2"><?= $reg['disciplina'] ?> <?= $reg['bimestreFormat'] ?></th>
                    <th style='width: 100px'><?= $reg['numero'] ?></th>
                    <th colspan="3" style="color: white"><?= $professor->getProfessor($reg['atribuicao'],'<br>', 0, 1) ?></tr>

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
                    <td align='center'><?=$dados['media']?></td>
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
                $aval = $avaliacao->listAvaliacoesAluno($params,$sqlAdicional);
                foreach ($aval as $a) {
                    if ($a['calculo'] == 'FORMULA')
                        $aval = str_replace ('$', '', $a['formula']);
                    else
                        $aval =  $$a['calculo'].' '.$a['peso'];
                    
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
            <br><div style='margin: auto'><a href="javascript:$('#<?=$_SESSION['VOLTAR']?>').load('<?=$_SESSION['LINK']?>'); void(0);" title='Voltar' ><img class='botao' src='<?= ICONS ?>/left.png'/></a></div>
           <?php
        }
        ?>
    </div>
</center>