<?php
//Esse arquivo é fixo para o aluno.
//Visualização do Boletim do Aluno.
//Link visível no menu: não, pois para este item é criado um ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;
require PERMISSAO;

require CONTROLLER . "/atvAcadRegistro.class.php";
$atvRegistro = new AtvAcadRegistros();

require CONTROLLER . "/atvAcadItem.class.php";
$atvItem = new AtvAcadItens();

if (dcrip($_GET["aluno"])) {
    $aluno = dcrip($_GET["aluno"]);
    $params['aluno'] = $aluno;
    $sqlAdicional .= ' AND p.codigo = :aluno ';
}
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>

<center>
    <div class='fundo_listagem'>
        <?php
        $params = array('aluno' => $aluno);
        $sqlAdicional = ' AND p.codigo = :aluno AND ra.aluno IS NOT NULL ';

        $res = $atvRegistro->listSituacao($params, $sqlAdicional);
        if ($res) {
            ?>
            <h3>Registro de Atividades Acad&ecirc;micas</h3>
            <br />            
            <table id="listagem" border="0" align="center">
                <tr>
                    <th align="left">Atividade</th>
                    <th align="left" width="120px">Semestre/Ano</th>
                    <th align="left" width="100px">Carga hor&aacute;ria no semestre</th>
                    <th align="left" width="100px">Carga hor&aacute;ria total no curso</th>
                    <th align="left" width="250px">Carga hor&aacute;ria total<br> [Cient&iacute;fica] [Cultural] [Acad&ecirc;mica]</th>
                    <th align="left" width="40px">&nbsp;</th>
                </tr>
                <?php
                // efetuando a consulta para listagem
                $i = 0;
                foreach ($res as $reg) {
                    $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                    $color = null;
                    if ($sit = $atvRegistro->status($reg))
                        $color = 'yellow';
                    ?>
                    <tr <?= $cdif ?> style="background-color: <?= $color ?>">
                        <td><a href="#" title="<?= $reg['atividade'] ?>"><?= abreviar($reg['atividade'], 20) ?></a></td>
                        <td><?= $reg['semAno'] ?></td>
                        <td><?= $reg['CHSem'] . 'h/[' . $reg['CHminSem'] . '-' . $reg['CHmaxSem'] ?>]h</td>
                        <td><?= $reg['CHCurso'] . 'h/' . $reg['CHTotal'] ?>h</td>
                        <td>[<?= $reg['CHCientifica'] . 'h/' . $reg['CHminCientifica'] . 'h] [' . $reg['CHCultural'] . 'h/' . $reg['CHminCultural'] . 'h] [' . $reg['CHAcademica'] . 'h/' . $reg['CHminAcademica'] ?>h]</td>
                        <td>
                            <a href="#" title="<?= $sit ?>">
                                <img src='<?= ICONS ?>/info.png' />
                            </a>
                        </td>
                    </tr>
                    <?php
                    $i++;
                }
                ?>
            </table> 
            <?php
            $res = $atvRegistro->listRegistros($params, $sqlAdicional);
            ?>
            <hr />            
            <br />
            <h3>Descri&ccedil;&atilde;o das Atividades Acad&ecirc;micas</h3>
            <br />
            <table id="listagem" border="0" align="center">
                <tr>
                    <th align="left" width="60">C&oacute;digo</th>
                    <th align="left">Atividade</th>
                    <th align="left">Item</th>
                    <th align="left">Semestre/Ano</th>
                    <th align="left">Carga hor&aacute;ria / Carga hor&aacute;ria m&aacute;xima</th>
                </tr>
                <?php
                // efetuando a consulta para listagem
                $i = 1;
                foreach ($res as $reg) {
                    $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                    ?>
                    <tr <?= $cdif ?>><td align='center'><?= $i ?></td>
                        <td><a href="#" title="<?= $reg['atividade'] ?>"><?= abreviar($reg['atividade'], 20) ?></a></td>
                        <td><a href="#" title="<?= $reg['item'] ?>"><?= abreviar('[' . $reg['tipo'] . '] ' . $reg['item'], 30) ?></a></td>
                        <td><?= $reg['semestre'] . '/' . $reg['ano'] ?></td>
                        <td><?= $reg['CH'] . 'h/' . $reg['CHLimite'] ?>h</td>
                    </tr>
                    <?php
                    $i++;
                }
                ?>
            </table>  
            <?php
        }

        $sqlAdicional = ' AND aa.curso IN (SELECT t.curso '
                . 'FROM Matriculas m, Atribuicoes a, Turmas t '
                . 'WHERE m.atribuicao = a.codigo '
                . 'AND a.turma = t.codigo '
                . 'AND m.aluno = :aluno) ';
        $res = $atvItem->listItens($params, $sqlAdicional);
        if ($res) {
            ?>
            <hr />
            <br />
            <h3>Lista de atividades acad&ecirc;micas disponíveis para entrega</h3>
            <br />
            <table id="listagem" border="1" align="center">
                <tr>
                    <th align="left" width="60">C&oacute;digo</th>
                    <th align="left" width="100">Tipo</th>
                    <th align="left">Atividade</th>
                    <th align="left">Comprova&ccedil;&atilde;o</th>
                    <th align="left">Carga horária</th>
                    <th align="left" width="100">Limite de CH ao longo do curso</th>
                </tr>
                <?php
                // efetuando a consulta para listagem
                $i = 1;
                foreach ($res as $reg) {
                    $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                    ?>
                    <tr <?= $cdif ?>><td align='center'><?= $i ?></td>
                        <td><?= $reg['tipo'] ?></td>
                        <td><?= $reg['atividade'] ?></a></td>
                        <td><?= $reg['comprovacao'] ?></a></td>
                        <td><?= $reg['CH'] ?></a></td>
                        <td><?= $reg['CHLimite'] ?>h</td>
                    </tr>
                    <?php
                    $i++;
                }
                ?>
            </table> 
            <?php
        } else {
            print "Nenhuma atividade acad&ecirc;mica definida para seu curso.";
        }
        ?>            
    </div>
</center>