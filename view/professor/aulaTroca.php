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
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<?php
if ((!$_GET['opcao'] && !$_POST["opcao"]) || $_GET["menu"]) {
    ?>
    <h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
    <div id="etiqueta" align="center">
        <table width="90%" align="center" border="0">
            <tr>
                <td valign="top" align="center" width="90">
                    <a class='nav professores_item' href="javascript:$('#troca').load('<?= VIEW ?>/professor/aulaTroca.php?opcao=getTroca');void(0);">
                        <img style='width: 80px' src='<?= IMAGES; ?>/trocaaula.png' />
                        <br />Solicitar Troca/Reposi&ccedil;&atilde;o
                    </a>
                </td>
                <td valign="top" align="center" width="90">
                    <a class='nav professores_item' href="javascript:$('#troca').load('<?= VIEW ?>/professor/aulaTroca.php?opcao=pendente');void(0);">
                        <img style='width: 80px' src='<?= IMAGES; ?>/trocapendencia.png' />
                        <br />Trocas Solicitadas para Voc&ecirc;
                    </a>
                </td>
                <td valign="top" align="center" width="90">
                    <a class='nav professores_item' href="javascript:$('#troca').load('<?= VIEW ?>/professor/aulaTroca.php?opcao=listTroca');void(0);">
                        <img style='width: 80px' src='<?= IMAGES; ?>/trocas.png' />
                        <br />Trocas/Reposi&ccedil;&otilde;es que Voc&ecirc; Solicitou
                    </a>
                </td>
                <?php
                if (in_array($COORD, $_SESSION["loginTipo"])) {
                    ?>
                    <td valign="top" align="center" width="90">
                        <a class='nav professores_item' href="javascript:$('#troca').load('<?= VIEW ?>/professor/aulaTroca.php?opcao=validacao');void(0);">
                            <img style='width: 80px' src='<?= IMAGES; ?>/validacao.png' />
                            <br />Valida&ccedil;&atilde;o de Trocas
                        </a>
                    </td>
                    <?php
                }
                ?>
        </table>
        <hr>
    </div>
    <div id="troca" align="center"></div>
    <?php
}

require CONTROLLER . "/aulaTroca.class.php";
$aulaTroca = new AulasTrocas();

require CONTROLLER . "/logEmail.class.php";
$logEmail = new LogEmails();

require CONTROLLER . "/pessoa.class.php";
$pessoa = new Pessoas();

require CONTROLLER . "/coordenador.class.php";
$coordenador = new Coordenadores();

// PARECER
if ($_GET["opcao"] == 'parecer') {
    unset($_GET['_']);
    unset($_GET['opcao']);

    $ret = $aulaTroca->insertOrUpdate($_GET);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);

    //ENVIANDO EMAIL
    if ($ret['STATUS'] == 'OK') {
        $params['codigo'] = $_GET['codigo'];
        $res = $aulaTroca->listRegistros($params);

        if ($res[0]['tipo'] == 'troca' && $res[0]['professorSubAceite'] == 'S' && !$res[0]['coordenadorAceite']) {
            if ($coodEmail = $coordenador->getEmailCoordFromAtribuicao($res[0]['atribuicao']))
                $logEmail->sendEmailLogger($_SESSION['loginNome'], 'Docente Substituto aceitou um troca de aula. Necessita da sua valida&ccedil;&atilde;o.', $coodEmail);
        }
        if ($res[0]['tipo'] == 'troca' && $res[0]['professorSubAceite'] == 'N' && !$res[0]['coordenadorAceite']) {
            if ($email = $pessoa->getEmailFromPessoa($res[0]['professor']))
                $logEmail->sendEmailLogger($_SESSION['loginNome'], 'Docente Substituto n&atilde;o aceitou uma troca de aula solicitada por voc&ecirc;.', $email);
        }
        if ($res[0]['tipo'] == 'troca' && $res[0]['professorSubAceite'] == 'S' && $res[0]['coordenadorAceite']) {
            if ($res[0]['coordenadorAceite'] == 'N')
                $valido = ' n&atilde;o';
            if ($email = $pessoa->getEmailFromPessoa($res[0]['professor']))
                $logEmail->sendEmailLogger($_SESSION['loginNome'], 'O Coordenador' . $valido . ' validou uma troca de aula solicitada por voc&ecirc;.', $email);

            if ($email = $pessoa->getEmailFromPessoa($res[0]['professorSub']))
                $logEmail->sendEmailLogger($_SESSION['loginNome'], 'O Coordenador' . $valido . ' validou uma troca de aula solicitada para voc&ecirc;.', $email);
        }

        if ($res[0]['tipo'] == 'reposicao' && $res[0]['coordenadorAceite']) {
            if ($res[0]['coordenadorAceite'] == 'N')
                $valido = ' n&atilde;o';
            if ($email = $pessoa->getEmailFromPessoa($res[0]['professor']))
                $logEmail->sendEmailLogger($_SESSION['loginNome'], 'O Coordenador' . $valido . ' validou uma reposi&ccedil;&atilde;o solicitada por voc&ecirc;.', $email);
        }
    }
}

// INSERT E UPDATE
if ($_POST["opcao"] == 'InsertOrUpdate') {
    $_POST['dataTroca'] = dataMysql($_POST['dataTroca']);
    $_POST['professor'] = $_SESSION['loginCodigo'];
    $_POST['dataPedido'] = date('Y-m-d H:i:s');
    unset($_POST['opcao']);

    $_POST['aula'] = implode(',', $_POST['aula']);

    $ret = $aulaTroca->insertOrUpdate($_POST);
    mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
    $_GET['opcao'] = 'listTroca';

    //ENVIANDO EMAIL
    if ($ret['STATUS'] == 'OK') {
        if ($_POST['tipo'] == 'troca') {
            if ($email = $pessoa->getEmailFromPessoa(dcrip($_POST['professorSub'])))
                $logEmail->sendEmailLogger($_SESSION['loginNome'], 'Docente solicitou uma troca de aula.', $email);
        }
        if ($_POST['tipo'] == 'reposicao') {
            if ($coodEmail = $coordenador->getEmailCoordFromAtribuicao(dcrip($_POST['atribuicao'])))
                $logEmail->sendEmailLogger($_SESSION['loginNome'], 'Docente solicitou uma reposi&ccedil;&atilde;o de aula.', $coodEmail);
        }
    }
}


$day = 'date.getDay() === -1';

if ($_GET['opcao'] == 'getTroca') {
    if ($_GET['atribuicao'])
        $atribuicao = dcrip($_GET['atribuicao']);

    if ($_GET['dataTroca'])
        $dataTroca = ($_GET['dataTroca']);

    if ($_GET['tipo'])
        $tipo = ($_GET['tipo']);

    //PEGANDO O ENSALAMENTO PARA RESTRINGIR O CALENDARIO
    require CONTROLLER . "/ensalamento.class.php";
    $ensalamento = new Ensalamentos();
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
                    <td align="right">Disciplina: </td>
                    <td> 
                        <?php
                        $params = array('ano' => $ANO, 'semestre' => $SEMESTRE, 'professor' => $_SESSION['loginCodigo']);
                        $sqlAdicional = ' AND p.codigo = :professor GROUP BY d.numero, t.numero, a.subturma, a.eventod';
                        $aa = $ensalamento->listEnsalamentos($params, $sqlAdicional);
                        ?>
                        <select name="atribuicao" id="atribuicao" style="width: 350px">
                            <option></option>
                            <?php
                            foreach ($aa as $reg) {
                                $selected = "";
                                if ($reg['atribuicao'] == $atribuicao)
                                    $selected = "selected";
                                print "<option $selected value='" . crip($reg['atribuicao']) . "'>" . $reg['disciplina'] . ' [' . $reg['turma'] . '] ' . $reg['subturma'] . ' [' . $reg['turno'] . ']' . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="right">Tipo: </td>
                    <td> 
                        <select name="tipo" id="tipo" style="width: 350px">
                            <option <?php if ($tipo == 'troca') print $selected; ?> value="troca">Troca de Aula</option>
                            <option <?php if ($tipo == 'reposicao') print $selected; ?> value="reposicao">Reposição</option>
                        </select>
                    </td>
                </tr>                 
                <tr>
                    <td align="right">Data da Aula: </td>
                    <td><input type="text" readonly class="data" size="10" id="dataTroca" name="dataTroca" value="<?php $dataTroca; ?>" /></td>
                </tr>
                <tr>
                    <td align="right">Aula: </td>
                    <td> 
                        <?php
                        $aula = $ensalamento->getAulasByProfessor($_SESSION['loginCodigo'], $atribuicao);
                        foreach ($aula as $reg) {
                            print "<input type=\"checkbox\" name=\"aula[]\" value=\"" . $reg['horario'] . "\"> " . $reg['horario'] . "<br>";
                        }
                        ?>
                    </td>
                </tr>                
                <tr>
                    <td align="right">Motivo: </td>
                    <td><textarea maxlength="500" rows="5" cols="80" id="motivo" name="motivo" style="width: 600px; height: 60px"><?= $atividade ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td align="right">Professor que ministrar&aacute; a aula: </td>
                    <td>
                        <select name="professorSub" id="professorSub" style="width: 350px">
                            <option></option>
                            <?php
                            require CONTROLLER . "/professor.class.php";
                            $prof = new Professores();
                            $paramsProf['tipo'] = $PROFESSOR;

                            foreach ($prof->listProfessores($paramsProf) as $reg) {
                                $selected = "";
                                if ($reg['codigo'] == $professorSub)
                                    $selected = "selected";
                                print "<option $selected value='" . crip($reg['codigo']) . "'>" . $reg['nome'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr><td></td><td>
                        <input type="hidden" name="codigo" value="<?= $codigo ?>" />
                        <input type="hidden" name="opcao" value="InsertOrUpdate" />
                        <input type="submit" disabled value="Salvar" id="salvar" />
                    </td></tr>
            </table>
        </form>
    </div>
    <?php
}

if ($_GET['opcao'] == 'pendente') {
    ?>
    <br />    
    <table id="listagemTroca" border="0" align="center">
        <tr class="listagemTroca_tr">
            <th align="center" width="170">Data Solicitação</th>
            <th align='center'>Solicitante</th>
            <th align='center' width="100">Data Troca</th>
            <th align='center'>Status</th>
        </tr>
        <?php
        $i = 0;

        $params = array(':professor' => $_SESSION['loginCodigo']);
        $sqlAdicional = " WHERE at.professorSub = :professor AND tipo = 'troca' ";
        foreach ($aulaTroca->hasTrocas($params, $sqlAdicional) as $reg) {
            $i++;
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";

            if (substr($reg['professorParecer'], 0, 6) == 'Aceito') {
                $cdif = "class='parecerAceito'";
            }
            if (substr($reg['professorParecer'], 0, 6) == 'Negado') {
                $cdif = "class='parecerNegado'";
            }
            ?>
            <tr <?= $cdif ?>>
                <td><?= $reg['dataPedido'] ?></td>
                <td><?= $reg['professorNome'] ?></td>
                <td>
                    <?php
                    $dados = $reg['disciplina'] . '<br><br>Data: ' . $reg['dataTrocaFormatada'] . '<br><br>' . str_replace(',', '<br>', $reg['aula']) . '<br><br>Motivo: ' . $reg['motivo'];
                    ?>
                    <a href='#' class='show' data-placement="top" data-content='Clique na data para ver os detalhes da solicitação.' title='Detalhes da Solicita&ccedil;&atilde;o' id='<?= $dados ?>'><?= $reg['dataTrocaFormatada'] ?></a>
                </td>
                <td>
        <?php if (!$reg['professorSubAceite']) {
            ?>
                        <a href='#' onclick="parecer('professorSub', 'S', <?= $reg['codigo'] ?>, 'Aceito')">
                            <img class="botao" src='<?= ICONS ?>/accept.png'>
                        </a>
                        &nbsp;&nbsp;
                        <a href='#'onclick="parecer('professorSub', 'N', <?= $reg['codigo'] ?>, 'Negado')">
                            <img class="botao" src='<?= ICONS ?>/cancel.png'>
                        </a>
                        <?php
                    } else if ($reg['professorSubAceite'] == '1' && !$reg['coordenadorAceite']) {
                        print "Aceito. Aguardando validação do coordenador.";
                    } else {
                        print $reg['professorParecer'];
                    }
                    ?>
                </td>
            </tr>
    <?php }
    ?>
    </table>
    <?php
}

if ($_GET['opcao'] == 'listTroca') {
    ?>
    <br />    
    <table id="listagem" border="0" align="center">
        <tr class="listagem_tr">
            <th align="center" width="100">Tipo</th>
            <th align="center" width="170">Data Solicitação</th>
            <th align='center' width="140">Data Troca/Reposi&ccedil;&atilde;o</th>
            <th align='center'>Professor Substituto</th>
            <th align='center'>Parecer</th>
            <th align='center'>Coordenador</th>
            <th align='center'>Parecer</th>
        </tr>
        <?php
        $i = 0;
        $params = array(':professor' => $_SESSION['loginCodigo']);
        $sqlAdicional = ' AND professor = :professor ';
        foreach ($aulaTroca->listTrocas($params, $sqlAdicional) as $reg) {
            $i++;
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            ?>
            <tr <?= $cdif ?>>
                <td><?= $reg['tipo'] ?></td>                
                <td><?= $reg['dataPedido'] ?></td>
                <td>
                    <?php
                    $dados = $reg['disciplina'] . '<br><br>Data: ' . $reg['dataTrocaFormatada'] . '<br><br>' . str_replace(',', '<br>', $reg['aula']) . '<br><br>Motivo: ' . $reg['motivo'];
                    ?>
                    <a href='#' class='show' data-placement="top" data-content='Clique na data para ver os detalhes da solicitação.' title='Detalhes da Solicita&ccedil;&atilde;o' id='<?= $dados ?>'><?= $reg['dataTrocaFormatada'] ?></a>
                </td>                
                <td><?php if ($reg['tipo'] == 'Troca')
                        print $reg['professorSub'];
                    else
                        print '---';
                    ?></td>
                <td><?php if ($reg['tipo'] == 'Troca')
                print "<a href='#' title='" . $reg['professorSubParecer'] . "'>" . $reg['avalProfSub'] . "</a>";
            else
                print '---';
            ?></td>
                <td><?php if ($reg['professorSubAceite'] == 'N')
                print '---';
            else
                print $reg['coordenador'];
                    ?></td>
                <td><?php if ($reg['professorSubAceite'] == 'N')
            print '---';
        else
            print "<a href='#' title='" . $reg['coordenadorParecer'] . "'>" . $reg['avalCoord'] . "</a>";
        ?></td>
            </tr>
    <?php } ?>
    </table>
    <?php
}

//COORDENADOR
if ($_GET['opcao'] == 'validacao') {
    ?>
    <div id="etiqueta" align="center">
        <span>Aten&ccedil;&atilde;o coordenador, cada professor substituto j&aacute; aceitou a troca correspondente listada aqui.</span>
    </div>    
    <br />
    <table id="listagemTroca" border="0" align="center">
        <tr class="listagemTroca_tr">
            <th align="center" width="100">Tipo</th>
            <th align="center" width="170">Data Solicitação</th>
            <th align='center'>Solicitante</th>
            <th align='center'>Solicitado</th>
            <th align='center' width="100">Data Troca</th>
            <th align='center'>Status</th>
        </tr>
        <?php
        $i = 0;

        $params = array(':coord' => $_SESSION['loginCodigo']);
        $sqlAdicional = " WHERE atribuicao IN "
                . "(SELECT a.codigo FROM Cursos c, Atribuicoes a, Turmas t, Coordenadores co "
                . "WHERE a.turma = t.codigo "
                . "AND t.curso = c.codigo "
                . "AND co.curso = c.codigo "
                . "AND co.coordenador= :coord) "
                . "AND ( (professorSubAceite = 'S' AND tipo = 'troca') "
                . "     OR (tipo = 'reposicao') )";

        foreach ($aulaTroca->hasTrocas($params, $sqlAdicional) as $reg) {
            $i++;
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";

            if ($reg['coordenadorParecer'] == 'S') {
                $cdif = "class='parecerAceito'";
            }
            if ($reg['coordenadorParecer'] == 'N') {
                $cdif = "class='parecerNegado'";
            }
            ?>
            <tr <?= $cdif ?>>
                <td><?= $reg['tipo'] ?></td>                
                <td><?= $reg['dataPedido'] ?></td>
                <td><?= $reg['professorNome'] ?></td>
                <td><?= $reg['professorSubNome'] ?></td>
                <td>
        <?php
        $dados = $reg['disciplina'] . '<br><br>Data: ' . $reg['dataTrocaFormatada'] .
                '<br><br>Parecer do Substituto: ' . $reg['professorParecer'] .
                '<br><br>' . str_replace(',', '<br>', $reg['aula']) . '<br><br>Motivo: ' . $reg['motivo'];
        ?>
                    <a href='#' class='show' data-placement="top" data-content='Clique na data para ver os detalhes da solicitação.' title='Detalhes da Solicita&ccedil;&atilde;o' id='<?= $dados ?>'><?= $reg['dataTrocaFormatada'] ?></a>
                </td>
                <td>
                    <?php
                    if (($reg['tipo'] == 'Troca' && $reg['professorSubAceite'] && !$reg['coordenadorParecer']) || ($reg['tipo'] != 'Troca' && !$reg['coordenadorParecer'])) {
                        ?>
                        <a href='#' onclick="parecer('coordenador', 'S', <?= $reg['codigo'] ?>, 'Aceito')">
                            <img class="botao" src='<?= ICONS ?>/accept.png'>
                        </a>
                        &nbsp;&nbsp;
                        <a href='#' onclick="parecer('coordenador', 'N', <?= $reg['codigo'] ?>, 'Negado')">
                            <img class="botao" src='<?= ICONS ?>/cancel.png'>
                        </a>
            <?php
        } else {
            print $reg['coordenadorParecer'];
        }
        ?>
                </td>
            </tr>
    <?php }
    ?>
    </table>
    <?php
}

if (!$_GET['opcao'] && !in_array($COORD, $_SESSION["loginTipo"])) {
    ?>
    <script>
        $('#troca').load('<?= $SITE ?>?opcao=pendente');
    </script>
    <?php
}

if (in_array($COORD, $_SESSION["loginTipo"]) && !$_GET['opcao']) {
    ?>
    <script>
        $('#troca').load('<?= $SITE ?>?opcao=validacao');
    </script>
    <?php
}
?>
<script>
    $(".show").click(function () {
        var message = $(this).attr('id');
        $.Zebra_Dialog(message, {
            'title': 'Detalhes da Solicta&ccedil;&atilde;o',
            'width': 500
        });
    });

    function parecer(tipo, parecer, codigo, texto) {
        var data = $.datepicker.formatDate('yy-mm-dd', new Date());
        var dados = tipo + '=<?= $_SESSION['loginCodigo'] ?>&' + tipo + 'Data=' + data + '&codigo=' + codigo + '&' + tipo + 'Aceite=' + parecer;

        $.Zebra_Dialog('<strong>Parecer: ' + texto + '<br><br>Por favor, informe o parecer:</strong>', {
            'type': 'prompt',
            'promptInput': '<textarea rows="2" cols="30" name="Zebra_valor" maxlength="200" id="Zebra_valor"></textarea>',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption, valor) {
                if (caption == 'Sim') {
                    $('#troca').load('<?= $SITE ?>?opcao=parecer&' + dados + '&' + tipo + 'Parecer=' + encodeURIComponent(valor));
                }
            }
        });
    }

    valida();
    function valida() {
        if ($('#data').val() != "" && $('#conteudo').val() != "" && $('#quantidade').val() != "")
            $('#salvar').removeAttr('disabled');
        else
            $('#salvar').attr('disabled', 'disabled');
    }

    $(document).ready(function () {
        $('#atribuicao').change(function () {
            $('#troca').load('<?= $SITE ?>?opcao=getTroca&atribuicao=' + $('#atribuicao').val());
        });

        $('#dataTroca, #professorSub, #quantidade, #plano').hover(function () {
            valida();
        });

        $('#dataTroca, #professorSub, #quantidade, #plano').change(function () {
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
            prevText: 'Anterior'
        });
    });
</script>