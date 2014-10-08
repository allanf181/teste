<?php
//Esse arquivo é fixo para o professor.
//Permite que o professor solicite troca de aula.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

if ( (!$_GET['opcao'] && !$_POST["opcao"]) || $_GET["menu"]) {
    ?>
    <script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
    <h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

    <div id="etiqueta" align="center">
        <table width="80%" align="center" border="0">
            <tr>
                <td valign="top" align="center" width="90">
                    <a class='nav professores_item' href="javascript:$('#troca').load('<?= VIEW ?>/professor/aulaTroca.php?opcao=getTroca'); void(0);">
                        <img style='width: 80px' src='<?= IMAGES; ?>/trocaaula.png' />
                        <br />Solicitar Troca
                    </a>
                </td>
                <td valign="top" align="center" width="90">
                    <a class='nav professores_item' href="javascript:$('#troca').load('<?= VIEW ?>/professor/aulaTroca.php?opcao=pendente'); void(0);">
                        <img style='width: 80px' src='<?= IMAGES; ?>/trocapendencia.png' />
                        <br />Trocas Solicitadas para Voc&ecirc;
                    </a>
                </td>
                <td valign="top" align="center" width="90">
                    <a class='nav professores_item' href="javascript:$('#troca').load('<?= VIEW ?>/professor/aulaTroca.php?opcao=listTroca'); void(0);">
                        <img style='width: 80px' src='<?= IMAGES; ?>/trocas.png' />
                        <br />Suas Trocas
                    </a>
                </td>        
        </table>
        <hr>
    </div>
    <div id="troca" align="center"></div>
    <?php
}

require CONTROLLER . "/aulaTroca.class.php";
$aulaTroca = new AulasTrocas();

// INSERT E UPDATE DE AVALIACOES
if ($_POST["opcao"] == 'InsertOrUpdate') {
    extract(array_map("htmlspecialchars", $_POST), EXTR_OVERWRITE);
    $_POST['dataTroca'] = dataMysql($_POST['dataTroca']);
    $_POST['professor'] = $_SESSION['loginCodigo'];
    $_POST['dataPedido'] = date('Y-m-d H:i:s');
    unset($_POST['opcao']);

    $ret = $aulaTroca->insertOrUpdate($_POST);

    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET['opcao'] = 'listTroca';
}

if ($_GET['opcao'] == 'getTroca') {
    // LISTAGEM
    if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
        // consulta no banco
        $params = array('codigo' => dcrip($_GET["codigo"]));
        $res = $aulaTroca->listRegistros($params);
        extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
        $data = dataPTBR($data);
    }
    ?>
    <script>
        $('#form_padrao').html5form({
            method: 'POST',
            action: '<?= $SITE ?>',
            responseDiv: '#troca',
            colorOn: '#000',
            colorOff: '#999',
            messages: 'br'
        })
    </script>
    <div id="etiqueta" align="center">
        <span>Aten&ccedil;&atilde;o, o professor substituto selecionado ser&aacute; consultado sobre a troca.</span>
        <br /><span>Caso confirmado pelo professor, o processo &eacute; direcionado para o coordenador validar.</span>
        <br /><span>Ap&oacute;s validado pelo coordenador, os alunos s&atilde;o informados via sistema.</span>
    </div>
    <hr><br>    
    <div id="html5form" class="main">
        <form id="form_padrao">
            <table align="center">
                <tr>
                    <td align="right">Aula: </td>
                    <td>
                        <select name="atribuicao" id="atribuicao" style="width: 350px">
                            <option></option>
                            <?php
                            require CONTROLLER . "/atribuicao.class.php";
                            $disc = new Atribuicoes();
                            print_r($disc);
                            foreach ($disc->getAtribuicoesFromPapel($_SESSION['loginCodigo'], 'professor', $ANO) as $reg) {
                                $selected = "";
                                if ($reg['codigo'] == $atribuicao)
                                    $selected = "selected";
                                print "<option $selected value='" . crip($reg['atribuicao']) . "'>" . $reg['disciplina'] . ' [' . $reg['turma'] . '][' . $reg['subturma'] . '] [' . $reg['turno'] . "]</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>                
                <tr>
                    <td align="right">Data da Aula: </td>
                    <td><input type="text" readonly class="data" size="10" id="dataTroca" name="dataTroca" value="<?php echo $dataTroca; ?>" /></td>
                </tr>
                <tr>
                    <td align="right">Motivo: </td>
                    <td><textarea maxlength="500" rows="5" cols="80" id="motivo" name="motivo" style="width: 600px; height: 60px"><?php echo $atividade; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td align="right">Professor Substituto: </td>
                    <td>
                        <select name="professorSubstituto" id="professorSubstituto" style="width: 350px">
                            <option></option>
                            <?php
                            require CONTROLLER . "/professor.class.php";
                            $prof = new Professores();
                            $paramsProf['tipo'] = $PROFESSOR;

                            foreach ($prof->listProfessores($paramsProf) as $reg) {
                                $selected = "";
                                if ($reg['codigo'] == $professorSubstituto)
                                    $selected = "selected";
                                print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr><td></td><td>
                        <input type="hidden" name="codigo" value="<?php echo $codigo; ?>" />
                        <input type="hidden" name="opcao" value="InsertOrUpdate" />
                        <input type="submit" disabled value="Salvar" id="salvar" />
                    </td></tr>
            </table>
        </form>
    </div>
    <?php
}

if ($_GET['opcao'] == 'pendente') {
    require CONTROLLER . "/ensalamento.class.php";
    $ensalamento = new Ensalamentos();
    ?>
    <br />    
    <table id="listagem" border="0" align="center">
        <tr class="listagem_tr">
            <th align="center" width="170">Data Solicitação</th>
            <th align='center' width="100">Data Troca</th>
            <th align='center'>Professor Solicitante</th>
            <th align='center'>Motivo</th>
            <th align='center'>Aula</th>
            <th align='center'>Seu Parecer</th>
        </tr>
        <?php
        $i = 0;
        foreach ($aulaTroca->hasTrocas($_SESSION['loginCodigo']) as $reg) {
            $params = array('ano'=>$ANO, 'semestre'=> $SEMESTRE, 'atribuicao'=> $reg['atribuicao'], 'diaSemana' => date('N')-1 );
            $sqlAdicional = ' AND a.codigo = :atribuicao AND e.diaSemana = :diaSemana GROUP BY inicio ORDER BY inicio';
            $aula = $ensalamento->listEnsalamentos($params, $sqlAdicional);
            $i++;
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?= $cdif ?>>
                <td><?= $reg['dataPedido'] ?></td>
                <td><?= $reg['dataTroca'] ?></td>
                <td><?= $reg['professor'] ?></td>
                <td><?= $reg['motivo'] ?></td>
                <td>
                    <?php
                    foreach($aula as $a) {
                        print $a['inicio'].'<br>';
                    }
                    ?>
                </td>
                <td><?= $reg['parecerCoordenador'] ?><?= $reg['parecerCoordenador'] ?></td>
            </tr>
        <?php } ?>
    </table>
    <?php
}

if ($_GET['opcao'] == 'listTroca') {
    ?>
    <br />    
    <table id="listagem" border="0" align="center">
        <tr class="listagem_tr">
            <th align="center" width="170">Data Solicitação</th>
            <th align='center' width="100">Data Troca</th>
            <th align='center'>Professor Substituto</th>
            <th align='center'>Parecer</th>
            <th align='center'>Coordenador</th>
            <th align='center'>Parecer</th>
        </tr>
        <?php
        $i = 0;
        foreach ($aulaTroca->listTrocas($_SESSION['loginCodigo']) as $reg) {
            $i++;
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?= $cdif ?>>
                <td><?= $reg['dataPedido'] ?></td>
                <td><?= $reg['dataTroca'] ?></td>
                <td><?= $reg['professorSubstituto'] ?></td>
                <td><?= $reg['avalProfSub'] ?></td>
                <td><?= $reg['coordenador'] ?></td>
                <td><?= $reg['parecerCoordenador'] ?></td>
            </tr>
        <?php } ?>
    </table>
    <?php
}
?>

<script>
    valida();
    function valida() {
        if ($('#data').val() != "" && $('#conteudo').val() != "" && $('#quantidade').val() != "")
            $('#salvar').enable();
        else
            $('#salvar').attr('disabled', 'disabled');
    }

    $(document).ready(function () {
        $('#dataTroca, #professorSubstituto, #quantidade, #plano').hover(function () {
            valida();
        });

        $('#dataTroca, #professorSubstituto, #quantidade, #plano').change(function () {
            valida();
        });

        $('#motivo').maxlength({
            events: [], // Array of events to be triggerd    
            maxCharacters: 500, // Characters limit   
            status: true, // True to show status indicator bewlow the element    
            statusClass: "status", // The class on the status div  
            statusText: "caracteres restando", // The status text  
            notificationClass: "notification", // Will be added when maxlength is reached  
            showAlert: false, // True to show a regular alert message    
            alertText: "Limite de caracteres excedido!", // Text in alert message   
            slider: true // True Use counter slider    
        });

        $("#dataTroca").datepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior',
            minDate: '<?= $res['inicioCalendar'] ?>',
            maxDate: '<?= $res['fimCalendar'] ?>'
        });
    });
</script>