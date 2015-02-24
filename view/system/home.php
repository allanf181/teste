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
require CONTROLLER . "/calendario.class.php";
require CONTROLLER . "/questionario.class.php";

?>
<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>

<script type="text/javascript" src="<?= VIEW ?>/js/scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
<link type="text/css" href="<?= VIEW ?>/js/scrollbar/jquery.mCustomScrollbar.min.css" rel="stylesheet" media="screen" />

<table border="0" width='100%'>
    <tr>
        <td colspan="3">
            <table border="0" width='100%'>
                <tr>
                    <td>
                        <font size="4"><b>WebDi&aacute;rio</b></font>
                        <br><a class="link" data-content="Clique para conhecer o Grupo de Trabalho do WebDi&aacute;rio e as modifica&ccedil;&otilde;es a cada nova vers&atilde;o." title='O que h&aacute; de novo...' href="javascript:$('#index').load('<?= VIEW ?>/system/creditos.php');void(0);">
                            <font size="1">Vers&atilde;o 1.<?= $VERSAO ?></font>
                        </a>
                    </td>
                    <td width='162'>
                        <?php
                        showLastAccess();
                        ?>
                    </td>
                    <?php
                    calendario();
                    if (in_array($PROFESSOR, $_SESSION["loginTipo"]) || in_array($ALUNO, $_SESSION["loginTipo"])) {
                        bolsa();
                        chat();
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
                        <a title='Colabore com o Grupo de Trabalho' data-content='Clique aqui para enviar sugest&otilde;es ou reportar problemas' href="javascript:$('#index').load('<?= VIEW ?>/system/email.php');void(0);">
                            <img style='width: 40px' src="<?= ICONS ?>/bug.png">
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" valign="top" align="left">
            <?php
            // Mostra e altera a foto do usuário
            defineFoto();
            // Mostra e altera o Email
            defineEmail();

            // Verifica a Versão do Sistema
            if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($SEC, $_SESSION["loginTipo"])) {
                checkSistema();
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
                checkTrocaAula();
                // Verifica se há correções na FPA, PIT e RIT
                checkTD();
                // Verifica se há correções no Plano de Ensino
                checkPlanoEnsino();
                // Verifica se o professor digitou o horário de atendimento
                checkAtendimentoAluno();
            }
            ?>
        </td>
        <td width="450" valign="top" align="right">
            <?php
            // Verifica se há questionário para ser preenchido.
            questionario();

            // Mostra avisos para usuários
            avisos();
            ?>
        </td>
    </tr>
</table>

<?php
showChecks();

if (in_array($PROFESSOR, $_SESSION["loginTipo"]) || in_array($ALUNO, $_SESSION["loginTipo"])) {
// Listra Trocas de Aulas validadas para os Alunos e Professores
    listTrocaAula();
}

if (!in_array($ALUNO, $_SESSION["loginTipo"])) {
    listOcorrencia();
}

// INFORMES PARA COORDENADORES
if (in_array($COORD, $_SESSION["loginTipo"])) {
// Verifica se o coordenador tem trocas para validar
    listTrocaCoord();
// Verificar se há diários para liberar
    listLibDiario();
// Verifica se há Planos de Ensino para validar
    listLibPlanoEnsino();
// Verifica se há FPA, PIT e RIT para validar.
    listLibTD();
}

///////////////////////// FUNCOES ////////////////////////////////////
function showLastAccess() {
    global $user;

    $log = new Logs();
    ?>
    <font size="1"><b><?= $log->getLastAccess($user) ?></b><font>
    <?php
}

function chat() {
    global $ANO;

    $chat = new Chat();
    $message = $chat->listMessage($_SESSION['loginProntuario'], $ANO);
    ?>
    <td width='20'>&nbsp;</td>
    <td width='20'>            
        <a <?= ($message) ? "data-trigger='start'" : ""; ?> title='CHAT' data-content='<?= ($message) ? $message : "Voc&ecirc; n&atilde;o tem mensagens novas."; ?>' data-trigger='click' data-closeable='true'>
            <img style='width: 40px' src='<?= INC ?>/file.inc.php?type=chat' />
        </a>
    </td>
    <?php
}

function calendario() {
    global $user, $ANO;

    $calendario = new Calendarios();

    if ($eventos = $calendario->getEventos($user, $ANO)) {
        ?>
        <td width='20'>&nbsp;</td>
        <td width='20'>
            <a title='Eventos recentes' data-content='<?= $eventos ?>'>
                <img style='width: 40px' src='<?= IMAGES ?>/horario.png' />
            </a>
        </td>
        <?php
    }
}

function bolsa() {
    global $ALUNO, $PROFESSOR, $ANO;

    $bolsa = new Bolsas();

    if (in_array($ALUNO, $_SESSION["loginTipo"]))
        $tipo = 'aluno';
    if (in_array($PROFESSOR, $_SESSION["loginTipo"]))
        $tipo = 'professor';

    if ($message = $bolsa->checkBolsas($_SESSION['loginCodigo'], $tipo, $ANO)) {
        ?>
        <td width='20'>&nbsp;</td>
        <td width='20'>
            <a title='Bolsas' data-content='<?= $message ?>' href="javascript:$('#index').load('<?= VIEW."/$tipo" ?>/bolsa.php');void(0);">
                <img style='width: 40px' src='<?= IMAGES ?>/bolsa.png' />
            </a>
        </td>
        <?php
    }
}

function senhaInfo() {
    global $user;

    $pessoa = new Pessoas();
    // INFOS DE SENHA
    $res = $pessoa->infoPassword($user);

    if (($res['dias']) && ($res['data'] >= $res['dias'])) {
        ?>
        <a data-placement="top-right" data-trigger='start' href="javascript:$('#index').load('<?= VIEW ?>/system/senha.php?opcao=alterar');void(0);" title='Aten&ccedil;&atilde;o, sua sua est&aacute; expirada.' data-content='Clique para alterar sua senha!' data-closeable='true'>
            <img style='width: 40px' src='<?= IMAGES ?>/senha.png' />
        </a>
        <?php
    } else if ($res['dias'] && ( ($res['dias'] - $res['data']) <= 5)) {
        $diaAlteracao = ($res['dias'] - $res['data'])
        ?>
        <a data-placement="top-right" data-trigger='start' href="javascript:$('#index').load('<?= VIEW ?>/system/senha.php?opcao=alterar');void(0);" title='Voc&ecirc; ter&aacute; que mudar a senha em <?= $diaAlteracao ?> dia(s).' data-content='Clique para alterar sua senha!'>
            <img style='width: 40px' src='<?= IMAGES ?>/senha.png' />
        </a>
        <?php
    } else {
        ?>
        <a href="javascript:$('#index').load('<?= VIEW ?>/system/senha.php?opcao=alterar');void(0);" title='&Uacute;ltima altera&ccedil;&atilde;o da senha: <?= $res['dataSenha'] ?>' data-content='Clique para alterar sua senha!'>
            <img style='width: 40px' src='<?= IMAGES ?>/senha.png' />
        </a>
        <?php
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
                <a href='#' data-content='Coloque apenas foto de rosto e individual, demais fotos serão descartadas!' data-placement="right" data-trigger='hover' <?= $addFoto ?>>
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
        print "- " . $reg['curso'] . " [".$reg['codCurso']."]<br>";

    checkCoordHasCursoArea(1);
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
        <br>E-mail: <input data-title='E-mail utilizado para avisos e recuperação de senha.' data-content='Mantenha sempre atualizado.' class='newTooltip' data-placement="top-right" data-trigger='hover' type="text" size="40" maxlength="100" name="email" id="email" value="" />
        <img src="<?= ICONS ?>/accept.png" id="send-email" style="width: 20px; height: 20px">
        <?php
    } else {
        ?>
        <br>E-mail: <?= $userDados[0]['email'] ?></a>
        <a title='Remover Email' href='#'>
            <img src="<?= ICONS ?>/remove.png" id="send-email" style="width: 15px; height: 15px">
        </a>
        <?php
    }
    ?>
    <?php
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
        <br><br>Lattes: <input title='Plataforma Lattes' data-content='Informe aos alunos seu currículo Lattes' class='newTooltip' data-placement="top-right" data-trigger='click' type="text" size="40" maxlength="200" name="lattes" id="lattes" value="" />
        <img src="<?= ICONS ?>/accept.png" id="send-lattes" style="width: 20px; height: 20px">
        <?php
    } else {
        ?>
        <br><br>Lattes: <a href="<?= $userDados[0]['lattes'] ?>" target="_blank"><?= $userDados[0]['lattes'] ?></a>
        <a title='Remover Lattes' href='#'>
            <img src="<?= ICONS ?>/remove.png" id="send-lattes" style="width: 15px; height: 15px">
        </a>
        <?php
    }
}

// AVISOS - ALERTAS
function checkCoordHasCursoArea($coord) {
    global $user, $ANO, $textoAlerta;

    $params['ano'] = $ANO;
    if ($coord) {
        $params['pessoa'] = $user;
        $sqlAdicional = " AND curso IN (SELECT curso FROM Coordenadores WHERE coordenador = :pessoa) ";
        $resp1 = "Sua &aacute;rea de atua&ccedil;&atilde;o n&atilde;o foi definida. Solicitar ao respons&aacute;vel o cadastro para acesso a FPA, PIT e RIT de seus professores.";
        $resp2 = "Seu curso de atua&ccedil;&atilde;o n&atilde;o foi definido. Solicitar ao respons&aacute;vel o cadastro para acesso aos Di&aacute;rios, Planos de Ensino/Aula, FPA, PIT e RIT de seus professores.";
    } else {
        $link .= "<br><a href=\"javascript:$('#index').load('" . VIEW . "/secretaria/cursos/coordenador.php');void(0);\">Clique aqui para definir</a>";
        $resp1 = "As &aacute;reas de atua&ccedil;&atilde;o dos coordenadores n&atilde;o foram definidas. $link";
        $resp2 = "Os cursos de atua&ccedil;&atilde;o dos coordenadores n&atilde;o foram definidos. $link";
    }

    $coordenador = new Coordenadores();
    $res = $coordenador->checkIfCoordHasAreaCurso($params, $sqlAdicional);

    if ($res['area']) {
        $textoAlerta['Área'] = $resp1;
    }
    if ($res['curso']) {
        $textoAlerta['Curso'] = $resp2;
    }
}

function checkBloqueioFoto() {
    global $textoAlerta;

    $pessoa = new Pessoas();
    $res = $pessoa->countBloqPic();
    if ($res) {
        $resp = "H&aacute; fotos de alunos bloqueadas aguardando valida&ccedil;&atilde;o.";
        $resp .= "<br><a href=\"javascript:$('#index').load('" . VIEW . "/secretaria/pessoa.php?opcao=validacao');void(0);\">Clique aqui para validar</a>";
        $textoAlerta['Fotos'] = $resp;
    }
}

function checkPapeis() {
    global $PAPEIS, $GED, $ADM, $SEC, $COORD, $ALUNO, $PROFESSOR, $SSP, $textoAlerta;

    foreach ($PAPEIS as $p => $n) {
        if (!$$p) {
            $resp = "O papel de " . $n . " n&atilde;o foi definido.";
            $resp .= "<br><a href=\"javascript:$('#index').load('" . VIEW . "/admin/instituicao.php');void(0);\">Clique aqui para definir</a>";
            $textoAlerta['Papéis'] = $resp;
        }
    }
}

function checkSistema() {
    global $VERSAOAT, $VERSAO, $SITE_TITLE, $SITE_CIDADE, $DIGITANOTAS, $textoAlerta;
    // Checa a versão atual.
    if (!$VERSAOAT || $VERSAO < $VERSAOAT) {
        if (updateDataBase()) {
            $resp = "Sua vers&atilde;o foi atualizada: 1." . $VERSAOAT;
            $resp .= "<br>O sistema atualizou automaticamente o banco de dados.";
            $resp .= "<br>Verifique se o 'git pull' est&aacute; sendo executado automaticamente pelo CRON.";
            ?>
            <div class="boxHome avisos">
                <table border="0" width="100%">
                        <tr>
                            <td colspan="2"><h2>Update</h2></td>
                        </tr>
                        <tr>
                            <td valign="top">
                                <font size='1'><?= $resp ?></font>
                            </td>
                        </tr>
                </table>
            </div>
            <?php
        } else {
            $resp = "Problema para atualizar a vers&atilde;o: 1." . $VERSAOAT;
            $resp .= "<br>- Verifique as permiss&otilde;es em " . dirname(__FILE__);

            if (getenv('APACHE_RUN_USER') != get_current_user()) {
                $resp .= "- Permiss&otilde;es divergentes, deveria ser: " . getenv('APACHE_RUN_USER');
            }
            $resp .= "- Verifique se o 'git pull' est&aacute; sendo executado automaticamente pelo CRON.";
            $resp .= "<br>- Execute o migrate manualmente: 'php lib/migration/ruckus.php db:migrate'";

            $textoAlerta['Atualização'] = $resp;
        }
    }

    // Verifica se o CRON está sendo executado.
    $log = new Logs();
    if ($log->hasCronActive()) {
        $resp = "O script de sincroniza&ccedil;&atilde;o nunca foi executado ou n&atilde;o est&aacute; sendo executado diariamente.";
        $resp .= "<br><a href=\"javascript:$('#index').load('" . VIEW . "/admin/sincronizadorNambei.php');void(0);\">Clique aqui para verificar</a>";

        $textoAlerta['Logs'] = $resp;
    }

    // Verifica se o nome e cidade no sistema estão preenchidos.
    if (!$SITE_TITLE || !$SITE_CIDADE) {
        $resp = "O nome da institui&ccedil;&atilde;o e a cidade devem ser preenchidos.";
        $resp .= "<br><a href=\"javascript:$('#index').load('" . VIEW . "/admin/instituicao.php');void(0);\">Clique aqui para preencher</a>";
        $textoAlerta['Dados'] = $resp;
    }

    // Verifica se a sigla do campus foi preenchida
    // Utilizada pela DigitaNotas
    if (!$DIGITANOTAS) {
        $resp = "A sigla da institui&ccedil;&atilde;o deve ser preenchida para que as notas sejam exportadas automaticamente para o DigitaNotas.";
        $resp .= "<br><a href=\"javascript:$('#index').load('" . VIEW . "/admin/instituicao.php');void(0);\">Clique aqui para preencher</a>";
        $textoAlerta['Digita Notas'] = $resp;
    }
}

function checkTrocaAula() {
    global $aulaTroca, $textoAlerta;

    $aulaTroca = new AulasTrocas();

    $params = array(':professor' => $_SESSION['loginCodigo']);
    $sqlAdicional = "  WHERE at.professorSub = :professor AND "
            . " professorSubAceite = '0' AND tipo = 'troca' ";
    $res = $aulaTroca->hasTrocas($params, $sqlAdicional);
    if ($res) {
        foreach ($res as $reg) {
            $resp = "Voc&ecirc; tem uma solicita&ccedil;&atilde;o de troca de aula.";
            $resp .= "<br><a href=\"javascript:$('#index').load('" . VIEW . "/professor/aulaTroca.php');void(0);\">Clique aqui para analisar</a>";
            $textoAlerta['Trocas'] = $resp;
        }
    }
}

function checkTD() {
    global $user, $ANO, $SEMESTRE, $textoAlerta;

    // Verificando se há correções para a FPA, PIT e RIT
    $tdDados = new TDDados();
    $sqlAdicional = ' AND f.pessoa = :cod ';
    $params = array(':cod' => $user);
    $res = $tdDados->hasChangeTD($params, $sqlAdicional);
    if ($res) {
        foreach ($res as $reg) {
            $resp = $reg['solicitante'] . ", solicitou corre&ccedil;&atilde;o em sua " . $reg['modelo'] . " (" . $reg['semestre'] . "/" . $reg['ano'] . "): <br>" . $reg['solicitacao'];
            $resp .= "<br><a href=\"javascript:$('#index').load('" . VIEW . "/professor/atribuicao/" . strtolower($reg['modelo']) . ".php');void(0);\">Clique aqui para corrigir</a>";
            $textoAlerta['Atribuição'] = $resp;
        }
    }
}

function checkPlanoEnsino() {
    global $user, $textoAlerta;

    $plano = new PlanosEnsino();
    // Verificando se há correções para o Plano de Ensino.
    $sqlAdicional = "AND pr.professor = :cod ";
    $params = array('cod' => $user);
    $res = $plano->hasChangePE($params, $sqlAdicional);
    if ($res) {
        foreach ($res as $reg) {
            $resp = $reg['solicitante'] . ", solicitou corre&ccedil;&atilde;o em seu Plano de Ensino de " . $reg['disciplina'] . ": <br>" . $reg['solicitacao'];
            $resp .= "<br><a href=\"javascript:$('#index').load('" . VIEW . "/professor/professor.php?atribuicao=" . crip($reg['atribuicao']) . "');void(0);\">Clique aqui para corrigir</a>";
            $textoAlerta['Planos'] = $resp;
        }
    }
}

function checkAtendimentoAluno() {
    global $user, $ANO, $SEMESTRE, $textoAlerta;

    $atendimento = new Atendimento();
    // Verificando se o professor digitou o horário de atendimento ao aluno.
    $sqlAdicional = ' WHERE pessoa = :pessoa AND ano = :ano AND semestre = :semestre ';
    $params = array('pessoa' => $user, 'ano' => $ANO, 'semestre' => $SEMESTRE);
    $res = $atendimento->listRegistros($params, $sqlAdicional, null, null);
    if (!$res || !$res[0]['horario']) {
        $resp = "Digite seu hor&aacute;rio de atendimento para divulga&ccedil;&atilde;o aos alunos.";
        $resp .= "<br><a href=\"javascript:$('#index').load('" . VIEW . "/professor/atribuicao/atendimento.php');void(0);\">Clique aqui para digitar</a>";
        $textoAlerta['Atendimento'] = $resp;
    }
}

function showChecks() {
    global $textoAlerta;
    
    if (count($textoAlerta) >= 1) {
    ?>
        <div class="boxHome avisos"">
        <table border="0" width="100%">
            <?php
            foreach ($textoAlerta as $k => $alerta) {
                ?>
                <tr>
                    <td colspan="2"><h2 style="background-color: #CD0000; color: white"><?= $k ?></h2></td>
                </tr>
                <tr>
                    <td valign="top">
                        <font size='1'><?= $alerta ?></font>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>
    <?php
}
}

// SISTEMA DE AVISOS
function avisos() {
    global $user, $ANO;

    // SISTEMA DE AVISOS
    $aviso = new Avisos();
    $res = $aviso->getAvisoGeral($user, $ANO);
    ?>
    <?php
    if ($res) {
        ?>
        <div class="boxHome avisos">
            <table border="0" width="100%">
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
                            <font size='1'><?= $reg['Data'] . $disc ?></font>
                            <br><a href="#" data-placement="top" title="<?= $nome ?>" data-content="<?= $reg['Conteudo'] ?>"><?= abreviar($reg['Conteudo'], 40) ?></a>
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
    <?php
}

function questionario() {
    global $user;

    //Verifica se hÃ¡ questionÃ¡rios para responder
    $questionario = new Questionarios();
    $sqlAdicional = " AND
			  (q.dataFechamento IS NULL OR q.dataFechamento >= CURDATE() or q.dataFechamento = '0000-00-00 00:00:00')
		    AND
			  (qp.finalizado IS NULL)			  
	 	    AND q.situacao <> 0
                    AND q.codigo NOT IN (SELECT q1.questionario 
                                            FROM QuestionariosQuestoes q1, QuestionariosRespostas r1 
                                            WHERE r1.questao = q1.codigo
                                            AND q1.questionario = q.codigo
                                            AND r1.pessoa = :cod )";
    $res = $questionario->getAvisoquestionarios($user, $sqlAdicional);

    //PERMISSAO PARA VER O QUESTIONARIO
    $_SESSION['QUEST_VIEW'] = 0;
    if ($res) {
        $_SESSION['QUEST_VIEW'] = 1;
        ?>
        <div class="boxHome avisos">
            <table border="0" width="100%">
                <tr>
                    <td colspan="2"><h2>Questionários não respondidos:</h2></td>
                </tr>
                <?php
                foreach ($res as $reg) {
                    $prazo = null;
                    if (isset($reg['prazoDiff'])) {
                        if ($reg['prazoDiff'] > 0)
                            $prazo = ' (' . $reg['prazoDiff'] . ' dia(s) para finalizar)';
                        else
                            $prazo = ' (finaliza hoje)';
                    }
                    ?>
                    <tr>
                        <td valign="top" width="50">
                            <a data-placement="top" title="<?= $reg['nome'] ?>" data-content="Clique para responder" href = "javascript:$('#index').load('<?= VIEW ?>/common/questionario/questionarioVisualiza.php?questionario=<?= crip($reg['codigo']) ?>'); void(0);">
                                <?= $reg['dataCriacao'] ?> - <?= $reg['nome'] . $prazo ?>
                            </a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
    }
}

// TABELAS DE DADOS
function listTrocaAula() {
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
                    <td width='120'><a href='#' data-placement="top" data-content='<?= $reg['curso'] ?>' title='Curso'><?= $reg['disciplina'] ?></a></td>
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

function listTrocaCoord() {
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
                    <td width='120'><a href='#' data-placement="top" data-content='<?= $reg['curso'] ?>' title='Curso'><?= $reg['disciplina'] ?></a></td>
                    <td><a href='#' data-placement="top" data-content='<?= $reg['motivo'] ?>' title='Motivo'><?= $reg['motivo'] ?></a></td>
                    <td><?= $reg['dataPedido'] ?></td>
                    <td><a href="javascript:$('#index').load('<?= VIEW ?>/professor/aulaTroca.php');void(0);" data-placement="top" title='Clique aqui para analisar'>
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

function listLibDiario() {
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
                <th align='center' width='120'>Motivo</th>
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
                    <td width='120'><a href='#' data-placement="top" data-content='<?= $reg['curso'] ?>' title='<?= $reg['disciplina'] ?>'><?= abreviar($reg['disciplina'], 30) ?></a></td>
                    <td><a href='#' data-placement="top" title='Motivo' data-content='<?= $title ?>'><?= $reg['solicitacao'] ?></a></td>
                    <td><?= $reg['dataSolicitacao'] ?></td>
                    <td><a href="javascript:$('#index').load('<?= VIEW ?>/secretaria/prazos/diario.php?curso=<?= crip($reg['codCurso']) ?>&turma=<?= crip($reg['codTurma']) ?>&professor=<?= crip($reg['codPessoa']) ?>');void(0);" data-placement="top" title='Clique aqui para liberar'>
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

function listLibPlanoEnsino() {
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
                        <a href="javascript:$('#index').load('<?= VIEW ?>/secretaria/plano.php?curso=<?= crip($reg['codCurso']) ?>&turma=<?= crip($reg['codTurma']) ?>&professor=<?= crip($reg['codProfessor']) ?>');void(0);" data-placement="top" title='Clique aqui para validar'>
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

function listLibTD() {
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
                        <a href="javascript:$('#index').load('<?= VIEW ?>/secretaria/atribuicao_docente/<?= strtolower($reg['modelo']) ?>.php?professor=<?= crip($reg['pessoa']) ?>');void(0);" data-placement="top" title="Clique aqui para validar">
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

function listOcorrencia() {
    global $user, $ANO, $SEMESTRE;

    $ocorrencia = new Ocorrencias();

    if (in_array($PROFESSOR, $_SESSION["loginTipo"])) {
        $sqlAdicional = "AND registroPor = :cod ORDER BY data DESC LIMIT 5";
        $params = array('cod' => $user);
    } else {
        $sqlAdicional = "ORDER BY data DESC LIMIT 5";
    }
    $res = $ocorrencia->listOcorrencias($params, $sqlAdicional);
    if ($res) {
        ?>
        <br><br><table id="listagem">
            <caption>Lista as &uacute;ltimas 5 ocorr&ecirc;ncias e/ou intera&ccedil;&otilde;es.</caption>
            <tr>
                <th width='250'>Nome</th>
                <th align='center' width='140'>Data</th>
                <th align='center'>&Uacute;ltima descri&ccedil;&atilde;o</th>
                <th align='center' width='10'>&nbsp;</th>
            </tr>
            <?php
            $i = 0;
            foreach ($res as $reg) {
                $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                ?>
                <tr <?= $cdif ?>>
                    <td>&nbsp;<a href="#" data-placement="top" data-content='<?= $reg['aluno'] ?>' title="Nome"><?= abreviar($reg['aluno'], 30) ?></a></td>
                    <td><?= $reg['data'] ?></td>
                    <td>&nbsp;<a href="#" data-placement="top" data-content="<?= $reg['descricao'] ?>" title="&Uacute;ltima descri&ccedil;&atilde;o"><?= abreviar($reg['descricao'], 70) ?></a></td>
                    <td>
                        <a data-placement="top" href="javascript:$('#index').load('<?= VIEW ?>/secretaria/ocorrencia.php?aluno=<?= crip($reg['codAluno']) ?>');void(0);" title='Clique aqui para visualizar'>
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
?>

<script>
    $(document).ready(function () {
        $('.avisos').mCustomScrollbar({
            scrollButtons: {enable: true},
            theme: "dark",
            scrollbarPosition: "outside"
        });


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