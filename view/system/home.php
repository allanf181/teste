<?php
include_once "../../inc/config.inc.php";

require VARIAVEIS;
require FUNCOES;
require SESSAO;

$user = $_SESSION["loginCodigo"];

// verifica se não está sendo chamado diretamente.
if (strpos($_SERVER["HTTP_REFERER"], LOCATION) == false) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . LOCATION);
}

//FUNCAO PARA RENOVAR O TEMPO DO SITE.
if ($_GET['time_over']) {
    print "Renovado o tempo...";
    die;
}

//FUNCAO PARA TROCAR O ANO/SEMESTRE SEM RELOAD.
if ($_GET['ano'] && $_GET['semestre']) {
    print "Ano/Semestre alterado...";
    die;
}

require CONTROLLER . "/atribuicao.class.php";
require CONTROLLER . "/planoEnsino.class.php";
require CONTROLLER . "/pessoa.class.php";
require CONTROLLER . "/professor.class.php";
require CONTROLLER . "/aulaTroca.class.php";
require CONTROLLER . "/aluno.class.php";
require CONTROLLER . "/tdDado.class.php";
require CONTROLLER . "/aviso.class.php";
require CONTROLLER . "/atendimento.class.php";
require CONTROLLER . "/coordenador.class.php";
require CONTROLLER . "/ocorrencia.class.php";
require CONTROLLER . "/chat.class.php";
require CONTROLLER . "/log.class.php";
require CONTROLLER . "/bolsa.class.php";
?>
<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>

<table border="0" width='100%'>
    <tr>
        <td colspan="3">
            <table border="0" width='100%'>
                <tr>
                    <td>
                        <font size="4"><b>WebDi&aacute;rio</b></font>
                        <br><a class="link" title='O que h&aacute; de novo...' href="javascript:$('#index').load('<?= VIEW ?>/system/creditos.php');void(0);">
                            <font size="1">Vers&atilde;o 1.<?= $VERSAO ?></font>
                        </a>
                    </td>
                    <td width='162'>
                        <?php
                        showLastAccess();
                        ?>
                    </td>
                    <?php
                    if (in_array($PROFESSOR, $_SESSION["loginTipo"]) || in_array($ALUNO, $_SESSION["loginTipo"])) {
                        ?>
                        <td width='50'>&nbsp;</td>
                        <td width='20'>
                            <?php
                            bolsa();
                            ?>
                        </td>
                        <td width='20'>&nbsp;</td>
                        <td width='20'>
                            <?php
                            chat();
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                    <td width='20'>&nbsp;</td>
                    <td width='20'>
                        <?php
                        // Informações de Senha
                        senhaInfo();
                        ?>
                    </td>
                    <td width='20'>&nbsp;</td>                  
                    <td width='20'>
                        <a title='Clique aqui para enviar sugest&otilde;es ou reportar problemas' href="javascript:$('#index').load('<?= VIEW ?>/system/email.php');void(0);">
                            <img style='width: 40px' src="<?= ICONS ?>/bug.png">
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" valign="top">
            <?php
            // Mostra e altera a foto do usuário
            defineFoto();
            // Mostra e altera o Email
            defineEmail();

            // Verifica se o aluno preencheu o sócioEconômico
            if (in_array($ALUNO, $_SESSION["loginTipo"])) {
                socioEconomico();
            }

            // Verifica a Versão do Sistema
            if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($SEC, $_SESSION["loginTipo"])) {
                checaSistema();
            }

            // INFORMES PARA SECRETARIA/GED/ADM
            if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($SEC, $_SESSION["loginTipo"]) || in_array($GED, $_SESSION["loginTipo"])) {
                // Verifica se o coordenador tem trocas para validar
                checkCoordHasCursoArea(0);
                checkBloqueioFoto();
                checkPapeis();
            }

            if (in_array($PROFESSOR, $_SESSION["loginTipo"])) {
                // Mostra e define o lattes
                defineLattes();
                // Verifica se tem troca de aula para aceitar
                checaTrocaAula();
                // Verifica se há correções na FPA, PIT e RIT
                checaTD();
                // Verifica se há correções no Plano de Ensino
                checaPlanoEnsino();
                // Verifica se o professor digitou o horário de atendimento
                checaAtendimentoAluno();
            }
            ?>
        </td>
        <?php
        // Mostra avisos para usuários
        avisos();
        ?>
    </tr>
</table>

<?php
if (in_array($PROFESSOR, $_SESSION["loginTipo"]) || in_array($ALUNO, $_SESSION["loginTipo"])) {
// Listra Trocas de Aulas validadas para os Alunos e Professores
    listaTrocaAula();
}

if (!in_array($ALUNO, $_SESSION["loginTipo"])) {
    checkOcorrencia();
}

// INFORMES PARA COORDENADORES
if (in_array($COORD, $_SESSION["loginTipo"])) {
// Verifica se o coordenador tem trocas para validar
    listaTrocaCoord();
// Verificar se há diários para liberar
    checaLibDiario();
// Verifica se há Planos de Ensino para validar
    checaLibPlanoEnsino();
// Verifica se há FPA, PIT e RIT para validar.
    checaLibTD();
}

///////////////////////// FUNCOES ////////////////////////////////////
function bolsa() {
    global $ALUNO, $PROFESSOR;

    $bolsa = new Bolsas();

    if (in_array($ALUNO, $_SESSION["loginTipo"]))
        $tipo = 'aluno';
    if (in_array($PROFESSOR, $_SESSION["loginTipo"]))
        $tipo = 'professor';

    if ($message = $bolsa->checkBolsas($_SESSION['loginCodigo'], $tipo)) {
        ?>
        <a title='<?= $message ?>' href="javascript:$('#index').load('<?= VIEW ?>/secretaria/bolsas/bolsa.php');void(0);">
            <img style='width: 40px' src='<?= IMAGES ?>/bolsa.png' />
        </a>
        <?php
    }
}

function chat() {
    $chat = new Chat();
    $params = array(':prontuario' => $_SESSION['loginProntuario']);
    $message = $chat->listMessage($params);
    ?>
    <a title='<?= $message ?>'>
        <img style='width: 40px' src='<?= INC ?>/file.inc.php?type=chat' />
    </a>
    <?php
}

function senhaInfo() {
    global $user;

    $pessoa = new Pessoas();
    // INFOS DE SENHA
    $res = $pessoa->infoPassword($user);
    if ($res['dataSenha']) {
        ?>
        <a href="javascript:$('#index').load('<?= VIEW ?>/system/senha.php?opcao=alterar');void(0);" title='&Uacute;ltima altera&ccedil;&atilde;o da senha: <?= formata($res['dataSenha']) ?><br>Clique aqui para alterar sua senha!'>
            <img style='width: 40px' src='<?= IMAGES ?>/senha.png' />
        </a>
        <?php
    }

    if ($res['dias']) {
        if (($res['data'] >= $res['dias'])) {
            ?>
            <br><br><p>Aten&ccedil;&atilde;o, sua sua est&aacute; expirada. <a href="javascript:$('#index').load('<?= VIEW ?>/system/senha.php?opcao=alterar');void(0);">Clique aqui</a> e efetue a troca.
                <?php
            } else {
                $diaAlteracao = $res['dias'] - $res['data'];
                if ($diaAlteracao <= 5)
                    $diaAlteracao = "<span class='texto_alerta'>$diaAlteracao</span>";
                ?>
            <br><br><p>Voc&ecirc; ter&aacute; que mudar a senha em: <?= $diaAlteracao ?> dia(s).</p><br />
            <?php
        }
    }
}

function defineFoto() {
    global $_GET, $ALUNO, $user, $_SESSION, $ENVIOFOTO, $COORD;

    $pessoa = new Pessoas();

    // REMOVER FOTO
    if (isset($_GET['removerFoto']))
        $pessoa->removeFoto($user);

    $addFoto = "id='adiciona-foto' title='Alterar Foto'";
    if (!$ENVIOFOTO && in_array($ALUNO, $_SESSION["loginTipo"]))
        $addFoto = '';
    ?>
    <table border='0'>
        <tr>
            <td>
                <a href='#' <?= $addFoto ?>>
                    <img alt="foto" style="width: 150px; height: 150px" src="<?= INC ?>/file.inc.php?type=pic&time=<?= time() ?>&id=<?= crip($user) ?>" />
                </a>
                <?php
                $params = array('codigo' => $user);
                $userDados = $pessoa->listRegistros($params);
                if ($userDados[0]['foto'] && $addFoto) {
                    ?>
                    <br><img src="<?= ICONS ?>/remove.png" id="remover-foto" title='Remover Foto' style="width: 15px; height: 15px">
                    <?php
                }
                ?>
            </td>
            <td width="20px">&nbsp;</td>
            <td valign="top">
                <?php
                if (in_array($COORD, $_SESSION["loginTipo"])) {
                    showCoordCurso();
                }
                ?>
            </td>
            <td width="20px">&nbsp;</td>            
        </tr>
    </table>
    <?php
}

function showCoordCurso() {
    global $user;

    $coordenador = new Coordenadores();

    print "<b>Olá Coordenador</b>,<br>Verifique abaixo os cursos que coordena: <br><br>";
    $params['pessoa'] = $user;
    $sqlAdicional = ' AND p.codigo = :pessoa ';
    $res = $coordenador->listCoordenadores($params, $sqlAdicional);
    foreach ($res as $reg)
        print "- " . $reg['curso'] . "<br>";

    checkCoordHasCursoArea(1);
}

function checkCoordHasCursoArea($coord) {
    global $user;

    if ($coord) {
        $params['pessoa'] = $user;
        $sqlAdicional = " WHERE curso IN (SELECT curso FROM Coordenadores WHERE coordenador = :pessoa) ";
        $resp1 = "Aten&ccedil;&atilde;o Coordenador, sua &aacute;rea de atua&ccedil;&atilde;o n&atilde;o foi definida. Solicitar ao respons&aacute;vel o cadastro para acesso a FPA, PIT e RIT de seus professores.";
        $resp2 = "Aten&ccedil;&atilde;o Coordenador, seu curso de atua&ccedil;&atilde;o n&atilde;o foi definido. Solicitar ao respons&aacute;vel o cadastro para acesso aos Di&aacute;rios, Planos de Ensino/Aula, FPA, PIT e RIT de seus professores.";
    } else {
        $link .= "<br><a href=\"javascript:$('#index').load('" . VIEW . "/secretaria/cursos/coordenador.php');void(0);\">Clique aqui para definir</a>";
        $resp1 = "Aten&ccedil;&atilde;o, as &aacute;reas de atua&ccedil;&atilde;o dos coordenadores n&atilde;o foram definidas. $link";
        $resp2 = "<br>Aten&ccedil;&atilde;o, os cursos de atua&ccedil;&atilde;o dos coordenadores n&atilde;o foram definidos. $link";
        print "<br />";
    }

    $coordenador = new Coordenadores();
    $res = $coordenador->checkIfCoordHasAreaCurso($params, $sqlAdicional);

    if ($res['area']) {
        ?>
        <br><font size="2" color="red"><?= $resp1 ?></font>
        <?php
    }
    if ($res['curso']) {
        ?>
        <br><font size="2" color="red"><?= $resp2 ?></font>
        <?php
    }
}

function checkBloqueioFoto() {
    $pessoa = new Pessoas();
    $res = $pessoa->countBloqPic();
    if ($res) {
        ?>
        <br><br><font size="2" color="red">Aten&ccedil;&atilde;o, h&aacute; fotos de alunos bloqueadas aguardando valida&ccedil;&atilde;o.</font>
        <br><a href="javascript:$('#index').load('<?= VIEW ?>/secretaria/pessoa.php?opcao=validacao');void(0);">Clique aqui para validar</a>
        <?php
    }

    $coordenador = new Coordenadores();
    $res = $coordenador->checkIfCoordHasAreaCurso($params, $sqlAdicional);

    if ($res['area']) {
        ?>
        <br><font size="2" color="red"><?= $resp1 ?></font>
        <?php
    }
    if ($res['curso']) {
        ?>
        <br><font size="2" color="red"><?= $resp2 ?></font>
        <?php
    }
}

function checkPapeis() {
    global $PAPEIS, $GED, $ADM, $SEC, $COORD, $ALUNO, $PROFESSOR, $SSP;

    foreach ($PAPEIS as $p => $n) {
        if (!$$p) {
            ?>
            <font size="2" color="red">Aten&ccedil;&atilde;o, o papel de <?= $n ?> n&atilde;o foi definido.</font>
            <br><a href="javascript:$('#index').load('<?= VIEW ?>/admin/instituicao.php');void(0);">Clique aqui para definir</a>
            <?php
        }
    }
}

function defineEmail() {
    global $user, $_GET;

    $pessoa = new Pessoas();
    // ALTERACAO DE EMAIL
    if (isset($_GET['email']) && $user) {
        if ($_GET['email'] == 'undefined')
            $_GET['email'] = null;

        $params = array('codigo' => crip($user), 'email' => $_GET['email']);
        $pessoa->insertOrUpdate($params);
    }

    $params = array('codigo' => $user);
    $userDados = $pessoa->listRegistros($params);

    if (!$userDados[0]['email']) {
        ?>
        <br>E-mail: <input type="text" size="40" maxlength="100" name="email" id="email" value="" />
        <img src="<?= ICONS ?>/accept.png" id="send-email" style="width: 20px; height: 20px">
        <?php
    } else {
        ?>
        <br>E-mail: <?= $userDados[0]['email'] ?></a>
        &nbsp;<img src="<?= ICONS ?>/remove.png" id="send-email" title='Remover Email' style="width: 15px; height: 15px">
        <?php
    }
    ?>
    <br><font size="1">E-mail utilizado para avisos e recupera&ccedil;&atilde;o de senha.</font>
    <?php
}

function socioEconomico() {
    global $user;

    $aluno = new Alunos();
    if ($nome = $aluno->hasSocioEconomico($user)) {
        ?>
        <br><br><font size="2" color="red">Ol&aacute; <?= $nome ?>, seu question&aacute;rio Socioecon&ocirc;mico est&aacute; incompleto.</font>
        <br><a href="javascript:$('#index').load('<?= VIEW ?>/aluno/socioEconomico.php');void(0);" title='Socioencon&ocirc;mico'>Clique aqui para responder</a>
        <?php
    }
}

function checaSistema() {
    global $VERSAOAT, $VERSAO, $SITE_TITLE, $SITE_CIDADE, $DIGITANOTAS;
    // Checa a versão atual.
    if (!$VERSAOAT || $VERSAO < $VERSAOAT) {
        if (updateDataBase()) {
            ?>
            <br><br><font size="4" color="green">Sua vers&atilde;o foi atualizada: 1.<?= $VERSAOAT ?></font>
            <br>O sistema atualizou automaticamente o banco de dados.
            <br>Verifique se o "git pull" est&aacute; sendo executado automaticamente pelo CRON.
            <?php
        } else {
            ?>
            <br><br><font size="3" color="red">Problema para atualizar a vers&atilde;o: 1.<?= $VERSAOAT ?></font>
            <br>- Verifique as permiss&otilde;es em <?= dirname(__FILE__) ?>
            <?php
            if (getenv('APACHE_RUN_USER') != get_current_user()) {
                ?>
                <br>- Permiss&otilde;es divergentes, deveria ser: <?= getenv('APACHE_RUN_USER') ?>
                <?php
            }
            ?>
            <br>- Verifique se o "git pull" est&aacute; sendo executado automaticamente pelo CRON.
            <br>- Execute o migrate manualmente: "php lib/migration/ruckus.php db:migrate"
            <?php
        }
    }

    // Verifica se o CRON está sendo executado.
    $log = new Logs();
    if ($log->hasCronActive()) {
        ?>
        <br><br><font size="2" color="red">Aten&ccedil;&atilde;o: o script de sincroniza&ccedil;&atilde;o nunca foi executado ou n&atilde;o est&aacute; sendo executado diariamente.</font>
        <br><a href="javascript:$('#index').load('<?= VIEW ?>/admin/sincronizadorNambei.php');void(0);">Clique aqui para verificar</a>
        <?php
    }

    // Verifica se o nome e cidade no sistema estão preenchidos.
    if (!$SITE_TITLE || !$SITE_CIDADE) {
        ?>
        <br><br><font size="2" color="red">Aten&ccedil;&atilde;o: nome da institui&ccedil;&atilde;o e a cidade devem ser preenchidos.</font>
        <br><a href="javascript:$('#index').load('<?= VIEW ?>/admin/instituicao.php');void(0);">Clique aqui para preencher</a>
        <?php
    }

    // Verifica se a sigla do campus foi preenchida
    // Utilizada pela DigitaNotas
    if (!$DIGITANOTAS) {
        ?>
        <br><br><font size="2" color="red">Aten&ccedil;&atilde;o: a sigla da institui&ccedil;&atilde;o deve ser preenchida para que as notas sejam exportadas automaticamente para o DigitaNotas.</font>
        <br><a href="javascript:$('#index').load('<?= VIEW ?>/admin/instituicao.php');void(0);">Clique aqui para preencher</a>
        <?php
    }
}

function defineLattes() {
    global $_GET, $user;

    $pessoa = new Pessoas();
    // ALTERACAO DO LATTES
    if (isset($_GET['lattes'])) {
        if ($_GET['lattes'] == 'undefined')
            $_GET['lattes'] = null;
        $params = array('codigo' => crip($user), 'lattes' => $_GET['lattes']);
        $pessoa->insertOrUpdate($params);
    }

    $params = array('codigo' => $user);
    $userDados = $pessoa->listRegistros($params);
    if (!$userDados[0]['lattes']) {
        ?>
        <br><br>Lattes: <input type="text" size="40" maxlength="200" name="lattes" id="lattes" value="" />
        <img src="<?= ICONS ?>/accept.png" id="send-lattes" style="width: 20px; height: 20px">
        <?php
    } else {
        ?>
        <br><br>Lattes: <a href="<?= $userDados[0]['lattes'] ?>" target="_blank"><?= $userDados[0]['lattes'] ?></a>
        &nbsp;<img src="<?= ICONS ?>/remove.png" id="send-lattes" title='Remover Lattes' style="width: 15px; height: 15px">
        <?php
    }
}

function checaTrocaAula() {
    global $aulaTroca;

    $aulaTroca = new AulasTrocas();

    $params = array(':professor' => $_SESSION['loginCodigo']);
    $sqlAdicional = "  WHERE at.professorSub = :professor AND "
            . " professorSubAceite = '0' AND tipo = 'troca' ";
    $res = $aulaTroca->hasTrocas($params, $sqlAdicional);
    if ($res) {
        foreach ($res as $reg) {
            ?>
            <br><br><font size="2" color="red">Aten&ccedil;&atilde;o, voc&ecirc; tem uma solicita&ccedil;&atilde;o de troca de aula.</font>
            <br><a href="javascript:$('#index').load('<?= VIEW ?>/professor/aulaTroca.php');void(0);">Clique aqui para analisar</a>
            <?php
        }
    }
}

function checaTD() {
    global $user, $ANO, $SEMESTRE;

    // Verificando se há correções para a FPA, PIT e RIT
    $tdDados = new TDDados();
    $sqlAdicional = ' AND f.pessoa = :cod ';
    $params = array(':cod' => $user);
    $res = $tdDados->hasChangeTD($params, $sqlAdicional);
    if ($res) {
        foreach ($res as $reg) {
            ?>
            <br><br><font size="2" color="red">Aten&ccedil;&atilde;o: <?= $reg['solicitante'] ?>, solicitou corre&ccedil;&atilde;o em sua <?= $reg['modelo'] ?> (<?= $reg['semestre'] . '/' . $reg['ano'] ?>): <br><?= $reg['solicitacao'] ?></font>
            <br><a href="javascript:$('#index').load('<?= VIEW ?>/professor/atribuicao/<?= strtolower($reg['modelo']) ?>.php');void(0);">Clique aqui para corrigir</a>
            <?php
        }
    }
}

function checaPlanoEnsino() {
    global $user;

    $plano = new PlanosEnsino();
    // Verificando se há correções para o Plano de Ensino.
    $sqlAdicional = "AND pr.professor = :cod ";
    $params = array('cod' => $user);
    $res = $plano->hasChangePE($params, $sqlAdicional);
    if ($res) {
        foreach ($res as $reg) {
            ?>
            <br><br><font size="2" color="red">Aten&ccedil;&atilde;o: <?= $reg['solicitante'] ?>, solicitou corre&ccedil;&atilde;o em seu Plano de Ensino de <?= $reg['disciplina'] ?>: <br><?= $reg['solicitacao'] ?></font>
            <br><a href="javascript:$('#index').load('<?= VIEW ?>/professor/professor.php?atribuicao=<?= crip($reg['atribuicao']) ?>');void(0);">Clique aqui para corrigir</a>
            <?php
        }
    }
}

function checaAtendimentoAluno() {
    global $user, $ANO, $SEMESTRE;

    $atendimento = new Atendimento();
    // Verificando se o professor digitou o horário de atendimento ao aluno.
    $sqlAdicional = ' WHERE pessoa = :pessoa AND ano = :ano AND semestre = :semestre ';
    $params = array('pessoa' => $user, 'ano' => $ANO, 'semestre' => $SEMESTRE);
    $res = $atendimento->listRegistros($params, $sqlAdicional, null, null);
    if (!$res || !$res[0]['horario']) {
        ?>
        <br><br><font size="2" color="red">Aten&ccedil;&atilde;o, digite seu hor&aacute;rio de atendimento para divulga&ccedil;&atilde;o aos alunos. </font>
        <br><a href="javascript:$('#index').load('<?= VIEW ?>/professor/atribuicao/atendimento.php');void(0);">Clique aqui para digitar</a>
        <?php
    }
}

function avisos() {
    global $user;

    // SISTEMA DE AVISOS
    $aviso = new Avisos();
    $res = $aviso->getAvisoGeral($user);
    ?>
    <td width="300" valign="top">
        <?php
        if ($res) {
            ?>
            <div style="width: 400px; height: 400px; overflow-y: scroll;">
                <table border="0" id="form" width="100%">
                    <tr>
                        <td colspan="2">Avisos Gerais</td>
                    </tr>
                    <?php
                    foreach ($res as $reg) {
                        list($codigo, $nome) = @explode('#', $reg['Pessoa']);
                        $disc = ($reg['disciplina']) ? " - " . $reg['disciplina'] : "";
                        ?>
                        <tr>
                            <td colspan="2"><h2><?= $nome ?></h2></td>
                        </tr>
                        <tr>
                            <td valign="top" width="50">
                                <img alt="foto" style="width: 50px; height: 50px" src="<?= INC ?>/file.inc.php?type=pic&id=<?= crip($codigo) ?>" />
                            </td>
                            <td valign="top">
                                <font size='1'><?= $reg['Data'] . $disc ?></font><br><?= $reg['Conteudo'] ?></a>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div>
            <?php
        }
        ?>
    </td>
    <?php
}

function listaTrocaAula() {
    global $PROFESSOR, $ALUNO, $_SESSION;

    if (in_array($PROFESSOR, $_SESSION["loginTipo"])) {
        // Verificando se o Professor tem aulas trocadas vigentes
        $params = array('professor' => $_SESSION['loginCodigo']);
        $sqlAdicional = " AND (professor = :professor OR professorSub = :professor) "
                . " AND coordenadorAceite = 'S' "
                . " AND dataTroca >= NOW() ";
    }

    if (in_array($ALUNO, $_SESSION["loginTipo"])) {
        // Verificando se o Aluno tem aulas trocadas vigentes
        $params = array('aluno' => $_SESSION['loginCodigo']);
        $sqlAdicional = " AND atribuicao IN (SELECT atribuicao FROM Matriculas WHERE aluno = :aluno) "
                . " AND coordenadorAceite = 'S' "
                . " AND dataTroca >= NOW() ";
    }

    $aulaTroca = new AulasTrocas();
    $res = $aulaTroca->listTrocas($params, $sqlAdicional);
    if ($res) {
        ?>
        <br><br><table id="listagem">
            <caption>Trocas de Aulas/Reposi&ccedil;&otilde;es Previstas - Validadas e Vigentes</caption>
            <tr>
                <th width='80'>Tipo</th>                
                <th width='150'>Professor</th>
                <th align='center' width='80'>Disciplina</th>
                <th align='center' width='80'>Professor Substituto</th>
                <th align='center' width='80'>Data da Troca</th>
            </tr>
            <?php
            $i = 0;
            foreach ($res as $reg) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                ?>
                <tr <?= $cdif ?>>
                    <td width='80'><?= $reg['tipo'] ?></td>                    
                    <td width='120'><?= $reg['professor'] ?></td>
                    <td width='120'><a href='#' title='<?= $reg['curso'] ?>'><?= $reg['disciplina'] ?></a></td>
                    <td width='120'><?= $reg['professorSub'] ?></td>
                    <td><?= $reg['dataTrocaFormatada'] ?></td>
                </tr>
                <?php
                $i++;
            }
            ?>
        </table>
        <?php
    }
}

function listaTrocaCoord() {
    global $_SESSION;

    $_SESSION['regAnterior'] = null;

    // Verificando Troca de Aulas
    $aulaTroca = new AulasTrocas();

    $params = array('coord' => $_SESSION['loginCodigo']);
    $sqlAdicional = " AND c.codigo IN (SELECT curso FROM Coordenadores WHERE coordenador= :coord) "
            . "AND ( (professorSubAceite = 'S' AND coordenadorAceite = '0' AND tipo = 'troca' ) "
            . "     OR (  coordenadorAceite = '0' AND tipo = 'reposicao' ) )";
    $res = $aulaTroca->listTrocas($params, $sqlAdicional);
    if ($res) {
        ?>
        <br><br><table id="listagem">
            <caption>Solicita&ccedil;&otilde;es de Troca de Aula/Reposi&ccedil;&otilde;es</caption>
            <tr>
                <th width='50'>Tipo</th>
                <th width='150'>Professor</th>
                <th align='center' width='150'>Disciplina</th>
                <th align='center' width='80'>Motivo</th>
                <th align='center' width='80'>Data da Solicita&ccedil;&atilde;o</th>
                <th align='center' width='80'>Data da Troca</th>
            </tr>
            <?php
            $i = 0;
            foreach ($res as $reg) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                $title = $reg['motivo'];
                if (strlen($reg['motivo']) > 70)
                    $reg['motivo'] = abreviar($reg['motivo'], 70);
                ?>
                <tr <?= $cdif ?>>
                    <td width='120'><?= $reg['tipo'] ?></td>                    
                    <td width='120'><?= $reg['professor'] ?></td>
                    <td width='120'><a href='#' title='<?= $reg['curso'] ?>'><?= $reg['disciplina'] ?></a></td>
                    <td><a href='#' title='<?= $title ?>'><?= $reg['motivo'] ?></a></td>
                    <td><?= $reg['dataPedido'] ?></td>
                    <td><a href="javascript:$('#index').load('<?= VIEW ?>/professor/aulaTroca.php');void(0);" title='Clique aqui para analisar'>
                            <?= $reg['dataTrocaFormatada'] ?></a>
                    </td>                    
                </tr>
                <?php
                $i++;
            }
            ?>
        </table>
        <?php
    }
}

function checaLibDiario() {
    global $user, $ANO, $SEMESTRE;

    $prof = new Professores();
    $att = new Atribuicoes();

    $sqlAdicional = " AND c.codigo IN (SELECT curso "
            . "FROM Coordenadores WHERE coordenador = :coordenador)";
    $params = array('coordenador' => $user, 'ano' => $ANO, 'semestre' => $SEMESTRE);
    $res = $att->listSolicitacoesDiarios($params, null, null, $sqlAdicional);
    if ($res) {
        ?>
        <br><br><table id="listagem">
            <caption>Lista de Professores que aguardam libera&ccedil;&atilde;o do di&aacute;rio.</caption>
            <tr>
                <th width='150'>Nome</th>
                <th align='center' width='80'>Disciplina</th>
                <th align='center' width='80'>Motivo</th>
                <th align='center' width='80'>Data da Solicita&ccedil;&atilde;o</th>
                <th align='center' width='10'>&nbsp;</th>
            </tr>
            <?php
            $i = 0;
            foreach ($res as $reg) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                $title = $reg['solicitacao'];
                if (strlen($reg['solicitacao']) > 30)
                    $reg['solicitacao'] = abreviar($reg['solicitacao'], 30);
                ?>
                <tr <?= $cdif ?>>
                    <td width='120'><?= $prof->getProfessor($reg['atribuicao'], 1, '<br>', 1, 1) ?></td>
                    <td width='120'><a href='#' title='<?= $reg['curso'] . '<br>' . $reg['disciplina'] ?>'><?= abreviar($reg['disciplina'], 30) ?></a></td>
                    <td><a href='#' title='<?= $title ?>'><?= $reg['solicitacao'] ?></a></td>
                    <td><?= $reg['dataSolicitacao'] ?></td>
                    <td><a href="javascript:$('#index').load('<?= VIEW ?>/secretaria/prazos/diario.php?curso=<?= crip($reg['codCurso']) ?>&turma=<?= crip($reg['codTurma']) ?>&professor=<?= crip($reg['codPessoa']) ?>');void(0);" title='Clique aqui para liberar'>
                            Liberar</a>
                    </td>                    
                </tr>
                <?php
                $i++;
            }
            ?>
        </table>
        <?php
    }
}

function checaLibPlanoEnsino() {
    global $user, $ANO, $SEMESTRE;

    $plano = new PlanosEnsino();
    $prof = new Professores();

    $sqlAdicional = "AND t.ano = :ano "
            . "AND (t.semestre=:sem OR t.semestre=0)"
            . "AND pe.finalizado <> '0000-00-00 00:00:00' "
            . "AND pe.valido = '0000-00-00 00:00:00' "
            . "AND c.codigo IN (SELECT curso FROM Coordenadores WHERE coordenador = :cod) ";
    $params = array('ano' => $ANO, 'sem' => $SEMESTRE, 'cod' => $user);
    $res = $plano->listPlanoEnsino($params, $sqlAdicional);
    if ($res) {
        ?>
        <br><br><table id="listagem">
            <caption>Lista de Professores que aguardam por valida&ccedil;&atilde;o do Plano de Ensino.</caption>
            <tr>
                <th width='120'>Nome</th>
                <th align='center' width='120'>Disciplina</th>
                <th align='center' width='10'>&nbsp;</th>
            </tr>
            <?php
            $i = 0;
            foreach ($res as $reg) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                ?>
                <tr <?= $cdif ?>>
                    <td><?= $prof->getProfessor($reg['atribuicao'], 1, '<br>', 1, 1) ?></td>
                    <td><?= $reg['disciplina'] . $reg['subturma'] ?></td>
                    <td>
                        <a href="javascript:$('#index').load('<?= VIEW ?>/secretaria/plano.php?curso=<?= crip($reg['codCurso']) ?>&turma=<?= crip($reg['codTurma']) ?>&professor=<?= crip($reg['codProfessor']) ?>');void(0);" title='Clique aqui para validar'>
                            Validar
                        </a>
                    </td>
                </tr>
                <?php
                $i++;
            }
            ?>
        </table>
        <?php
    }
}

function checaLibTD() {
    global $user, $ANO, $SEMESTRE;

    $tdDados = new TDDados();
    $sqlAdicional = "AND ((f.finalizado <> '0000-00-00 00:00:00') "
            . "AND (f.valido = '0000-00-00 00:00:00' OR f.valido IS NULL)) "
            . "AND f.area IN (SELECT area FROM Coordenadores WHERE coordenador = :cod) ORDER BY p.nome ";
    $params = array(':cod' => $user);
    $res = $tdDados->listTDs($params, $sqlAdicional, null, null);
    if ($res) {
        ?>
        <br><br><table id="listagem">
            <caption>Lista de Professores que aguardam por valida&ccedil;&atilde;o do FPA, PIT e RIT.</caption>
            <tr>
                <th width='120'>Nome</th>
                <th align='center' width='120'>Modelo</th>
                <th align='center' width='120'>Ano/Semestre</th>
                <th align='center' width='10'>&nbsp;</th>
            </tr>
            <?php
            $i = 0;
            foreach ($res as $reg) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                ?>
                <tr <?= $cdif ?>>
                    <td><?= $reg['nome'] ?></td>
                    <td><?= $reg['modelo'] ?></td>
                    <td><?= $reg['semestre'] . '/' . $reg['ano'] ?></td>
                    <td>
                        <a href="javascript:$('#index').load('<?= VIEW ?>/secretaria/atribuicao_docente/<?= strtolower($reg['modelo']) ?>.php?professor=<?= crip($reg['pessoa']) ?>');void(0);">
                            Validar
                        </a>
                    </td>
                </tr>
                <?php
                $i++;
            }
            ?>
        </table>
        <?php
    }
}

function checkOcorrencia() {
    global $user, $ANO, $SEMESTRE;

    $ocorrencia = new Ocorrencias();

    if (in_array($PROFESSOR, $_SESSION["loginTipo"])) {
        $sqlAdicional = "AND registroPor = :cod ORDER BY data DESC LIMIT 5";
        $params = array('cod' => $user);
    } else {
        $sqlAdicional = "ORDER BY data DESC LIMIT 5";
    }
    $res = $ocorrencia->checkOcorrencias($params, $sqlAdicional);
    if ($res) {
        ?>
        <br><br><table id="listagem">
            <caption>Lista as últimas 5 ocorrências e/ou interações.</caption>
            <tr>
                <th width='250'>Nome</th>
                <th align='center' width='140'>Data</th>
                <th align='center'>Última descrição</th>
                <th align='center' width='10'>&nbsp;</th>
            </tr>
            <?php
            $i = 0;
            foreach ($res as $reg) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                ?>
                <tr <?= $cdif ?>>
                    <td>&nbsp;<a href="#" title="<?= $reg['aluno'] ?>"><?= abreviar($reg['aluno'], 30) ?></a></td>
                    <td><?= $reg['data'] ?></td>
                    <td>&nbsp;<a href="#" title="<?= $reg['descricao'] ?>"><?= abreviar($reg['descricao'], 70) ?></a></td>
                    <td>
                        <a href="javascript:$('#index').load('<?= VIEW ?>/secretaria/ocorrencia.php?aluno=<?= crip($reg['codAluno']) ?>');void(0);" title='Clique aqui para validar'>
                            Visualizar
                        </a>
                    </td>
                </tr>
                <?php
                $i++;
            }
            ?>
        </table>
        <?php
    }
}

function showLastAccess() {
    global $user;

    $log = new Logs();
    ?>
    <font size="1"><b><?= $log->getLastAccess($user) ?></b><font>
    <?php
}
?>
<script>
    $(document).ready(function () {
        $(document).keypress(function (e) {
            if (e.which == 13) {
                if ($('#email').val())
                    $('#send-email').click();
                if ($('#lattes').val())
                    $('#send-lattes').click();
            }
        });

        $('#send-lattes').click(function () {
            var lattes = encodeURIComponent($('#lattes').val());
            $('#index').load('<?= VIEW ?>/system/home.php?lattes=' + lattes);
        });
        $('#send-email').click(function () {
            var email = encodeURIComponent($('#email').val());
            $('#index').load('<?= VIEW ?>/system/home.php?email=' + email);
        });

        $('#remover-foto').click(function () {
            $('#index').load('<?= VIEW ?>/system/home.php?removerFoto=<?= crip($user) ?>');

                    });

                    $('#adiciona-foto').click(function () {
                        new $.Zebra_Dialog('<strong>Recorte a foto, se desejar.</strong>', {
                            source: {'iframe': {
                                    'src': '<?= VIEW ?>/system/trocaFoto.php',
                                    'height': 350
                                }
                            },
                            width: 500,
                            title: 'Troque a Foto',
                            onClose: function () {
                                $('#index').load('<?= VIEW ?>/system/home.php');
                            }
                        });
                    });
                });
</script>