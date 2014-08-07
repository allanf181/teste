<?php
include_once "inc/config.inc.php";

require MYSQL;
require VARIAVEIS;
require FUNCOES;
require SESSAO;

?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>

<table border="0" width='100%'>
<tr><td colspan="2"><font size="4"><b>WebDi&aacute;rio</b></font></font>
<br><font size="1">Vers&atilde;o 1.<?php print $VERSAO; ?></font></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>
<?php
$user = $_SESSION["loginCodigo"];


// Mostra a foto do usuário e informações de senha
if ($user) {
    require CONTROLLER . "/pessoa.class.php";
    $pessoa = new Pessoas();
    
    // REMOVER FOTO
    if (isset($_GET['removerFoto']))
        $pessoa->removeFoto($user);
    
    // ALTERACAO DE EMAIL
    if (isset($_GET['email'])) {
        if ($_GET['email'] == 'undefined')
            $_GET['email'] = null;
        $params = array('codigo' => crip($user), 'email' => $_GET['email']);
        $pessoa->insertOrUpdate($params);
    }
    
    // ALTERACAO DO LATTES
    if (isset($_GET['lattes'])) {
        if ($_GET['lattes'] == 'undefined')
            $_GET['lattes'] = null;
        $params = array('codigo' => crip($user), 'lattes' => $_GET['lattes']);
        $pessoa->insertOrUpdate($params);
    }    
    
    $addFoto = "id='adiciona-foto' title='Alterar Foto'";
    if (!$ENVIOFOTO && in_array($ALUNO, $_SESSION["loginTipo"]))
            $addFoto='';
    ?>
        <a href='#' <?php print $addFoto; ?>><img alt="foto" style="width: 150px; height: 150px" src="<?php print INC; ?>/file.inc.php?type=pic&time=<?php print time(); ?>&id=<?php print crip($user); ?>" /></a>
    <?php
   
    $params = array('codigo' => $user);
    $userDados = $pessoa->listRegistros($params);
    
    if ($userDados[0]['foto'] && $addFoto) {
        ?>
        <br><img src="<?php print ICONS; ?>/remove.png" id="remover-foto" title='Remover Foto' style="width: 15px; height: 15px">
        <?php
    }

    if (!$userDados[0]['email']) {
        ?>
        <br><br>Email: <input type="text" size="60" maxlength="100" name="email" id="email" value="" />
        <img src="<?php print ICONS; ?>/accept.png" id="send-email" style="width: 20px; height: 20px">
        <?php
    } else {
        ?>
        <br><br>Email: <?php print $userDados[0]['email']; ?></a>
        &nbsp;<img src="<?php print ICONS; ?>/remove.png" id="send-email" title='Remover Email' style="width: 15px; height: 15px">
        <?php
    }
    ?>
        <br><font size="1">Mantenha seu email sempre atualizado para avisos e recupera&ccedil;&atilde;o de senha.</font>
    <?php
    
    // INFOS DE SENHA
    $res = $pessoa->infoPassword($user);
    if ($res['dataSenha']) {
        ?>
        <br><br><a href="javascript:$('#index').load('<?=VIEW?>/senha.php?opcao=alterar'); void(0);" title='Clique aqui para alterar sua senha!'>&Uacute;ltima altera&ccedil;&atilde;o da senha: <?php print formata($res['dataSenha']); ?></a>
        <?php
    }

    if ($res['dias']) {
        if (($res['data'] >= $res['dias'])) {
            ?>
            <br><br><p>Aten&ccedil;&atilde;o, sua sua est&aacute; expirada. <a href="javascript:$('#index').load('<?=VIEW?>/senha.php?opcao=alterar'); void(0);">Clique aqui</a> e efetue a troca.
            <?php
        } else {
            $diaAlteracao = $res['dias'] - $res['data'];
            if ($diaAlteracao <= 5)
                $diaAlteracao = "<span class='texto_alerta'>$diaAlteracao</span>";
            ?>
            <br><br><p>Voc&ecirc; ter&aacute; que mudar a senha em: <?php print $diaAlteracao; ?> dia(s).</p><br />
            <?php
        }
    }
}

// Verifica se o aluno preencheu o sócioEconômico
if (in_array($ALUNO, $_SESSION["loginTipo"])) {
    require CONTROLLER . "/aluno.class.php";
    $aluno = new Alunos();
    if ($nome = $aluno->hasSocioEconomico($user)) {
        ?>
        <br><br><font size="2" color="red">Ol&aacute; <?php print $nome; ?>, seu question&aacute;rio Socioecon&ocirc;mico est&aacute; incompleto.</font>
        <br><a href="javascript:$('#index').load('<?php print VIEW; ?>/aluno/socioEconomico.php'); void(0);" title='Socioencon&ocirc;mico'>Clique aqui para responder</a>
        <?php
    }
}

if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($SEC, $_SESSION["loginTipo"])) {
    // Checa a versão atual.
    if (!$VERSAOAT || $VERSAO < $VERSAOAT) {
        if (updateDataBase()) {
            ?>
            <br><br><font size="4" color="green">Sua vers&atilde;o foi atualizada: 1.<?php print $VERSAOAT; ?></font>
            <br>O sistema atualizou automaticamente o banco de dados.
            <br>Verifique se o "git pull" est&aacute; sendo executado automaticamente pelo CRON.
            <?php
        } else {
            ?>
            <br><br><font size="3" color="red">Problema para atualizar a vers&atilde;o: 1.<?php print $VERSAOAT; ?></font>
            <br>- Verifique as permiss&otilde;es em <?php print dirname(__FILE__); ?>
            <?php 
            if (getenv('APACHE_RUN_USER') != get_current_user()) {
                ?>
                <br>- Permiss&otilde;es divergentes, deveria ser: <?php print getenv('APACHE_RUN_USER'); ?>
                <?php
            }
            ?>
            <br>- Verifique se o "git pull" est&aacute; sendo executado automaticamente pelo CRON.
            <br>- Execute o migrate manualmente: "php lib/migration/ruckus.php db:migrate"
            <?php
        }
    }    
    
    // Verifica se o CRON está sendo executado.
    require CONTROLLER . "/log.class.php";
    $log = new Logs();
    if ($log->hasCronActive()) {
        ?>
        <br><br><font size="2" color="red">Aten&ccedil;&atilde;o: o script de sincroniza&ccedil;&atilde;o nunca foi executado ou n&atilde;o est&aacute; sendo executado diariamente.</font>
        <br><a href="javascript:$('#index').load('<?php print VIEW; ?>/admin/sincronizadorNambei.php'); void(0);">Clique aqui para verificar</a>
        <?php
    }

    // Verifica se o nome e cidade no sistema estão preenchidos.
    if (!$SITE_TITLE || !$SITE_CIDADE) {
        ?>
        <br><br><font size="2" color="red">Aten&ccedil;&atilde;o: nome da institui&ccedil;&atilde;o e a cidade devem ser preenchidos.</font>
        <br><a href="javascript:$('#index').load('<?php print VIEW;?>/admin/instituicao.php'); void(0);">Clique aqui para preencher</a>
        <?php
    }
}


// AVISOS PARA PROFESSOR
if (in_array($PROFESSOR, $_SESSION["loginTipo"])) {

    if (!$userDados[0]['lattes']) {
        ?>
        <br><br>Lattes: <input type="text" size="60" maxlength="200" name="lattes" id="lattes" value="" />
        <img src="<?php print ICONS; ?>/accept.png" id="send-lattes" style="width: 20px; height: 20px">
        <?php
    } else {
        ?>
        <br><br>Lattes: <a href="<?php print $userDados[0]['lattes']; ?>" target="_blank"><?php print $userDados[0]['lattes']; ?></a>
        &nbsp;<img src="<?php print ICONS; ?>/remove.png" id="send-lattes" title='Remover Lattes' style="width: 15px; height: 15px">
        <?php
    }

    // Verificando se há correções para a FTD
    require CONTROLLER . "/ftd.class.php";
    $ftd = new Ftd();
    $res = $ftd->hasChangeFtd($user, $ANO, $SEMESTRE);
    if ($res['ftdSolicitacao']) {
        ?>
        <br><br><font size="2" color="red">Aten&ccedil;&atilde;o: <?php print $res['ftdSolicitante']; ?>, solicitou corre&ccedil;&atilde;o em sua FTD: <br><?php print $res['ftdSolicitacao']; ?></font>
        <br><a href="javascript:$('#index').load('<?php print VIEW; ?>/professor/ftd.php'); void(0);">Clique aqui para corrigir</a>
        <?php
    }

    // Verificando se há correções para o Plano de Ensino.
    require CONTROLLER . "/planoEnsino.class.php";
    $plano = new PlanosEnsino();
    $res = $plano->hasChangePlano($user);

    if ($res) {
        foreach($res as $reg) {
            ?>
            <br><br><font size="2" color="red">Aten&ccedil;&atilde;o: <?php print $reg['PlanoSolicitante']; ?>, solicitou corre&ccedil;&atilde;o em seu Plano de Ensino de <?php print $reg['Disc']; ?>: <br><?php print $reg['PlanoSolicitacao']; ?></font>
            <br><a href="javascript:$('#index').load('<?php print VIEW; ?>/professor/professor.php?atribuicao=<?php print crip($reg['CodAtribuicao']); ?>'); void(0);">Clique aqui para corrigir</a>
            <?php
        }
    }
}
?>
<br><br>Escolha uma das opções do menu para começar.
</td>

<?php
// SISTEMA DE AVISOS
require CONTROLLER . "/aviso.class.php";
$aviso = new Avisos();
$res = $aviso->getAvisoGeral($user);
if ($res) {
    ?>
    <td width="300" valign="top">
    <div style="width: 300px; height: 400px; overflow-y: scroll;">
    <table border="0" id="form" width="100%">
    <tr><td colspan="2">Avisos Gerais</td></tr>
    <?php
    foreach($res as $reg) {
        list($codigo, $nome) = @split('#', $reg['Pessoa']);
        ?>
        <tr><td colspan="2"><h2><?php print $nome; ?></h2></td></tr>
        <tr><td valign="top" width="50">
        <img alt="foto" style="width: 50px; height: 50px" src="<?php print INC; ?>/file.inc.php?type=pic&id=<?php print crip($codigo); ?>" />
        </td>
        <td valign="top"><?php print $reg['Data']; ?><br><?php print $reg['Conteudo']; ?></a>
        </td>
        </tr>
        <?php
    }
    ?>
    </table>
    </div>
    </td>
    <?php
}
?>
</tr>
</table>

<?php
// INFOMRAR AO COORDENADOR PROFESSORES QUE NÃO CADASTRAM 
// DISCIPLINAS DE ACORDO COM O LIMITE IMPOSTO EM INSTITUIÇÕES
if (in_array($COORD, $_SESSION["loginTipo"])) {
    require CONTROLLER . "/atribuicao.class.php";
    $atribuicao = new Atribuicoes();
    
    $res = $atribuicao->listProfOutOfLimitAddAula($user, $ANO, $SEMESTRE);
    if ($res) {
        ?>
        <br><table id="listagem" align="center">
        <caption>Lista de professores sem registro de aulas nos &uacute;ltimos 7 dias.</caption>
        <tr><th width='220'>Nome</th><th align='center' width='80'>&Uacute;ltimo Lan&ccedil;amento</th></tr>
        <?php
        $i = $item;
        foreach($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            print "<tr $cdif><td>".$reg['Professor']."</td><td>".$reg['Data']."</td></tr>";
            $i++;
        }
        ?>
        </table>
        <?php
    }
    
    $res = $plano->listChangePlano($user);
    if ($res) {
        ?>
        <br><br><table id="listagem">
        <caption>Lista de Professores que aguardam por valida&ccedil;&atilde;o do Plano de Ensino.</caption>
        <tr><th width='220'>Nome</th><th align='center' width='80'>Disciplina</th></tr>
        <?php
        $i = $item;
        foreach($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            print "<tr $cdif><td><a href=\"javascript:$('#index').load('".VIEW."/secretaria/plano.php?curso=".crip($reg['codCurso'])."'); void(0);\" class='plano-ensino' title='Clique aqui para validar'>".$reg['Professor']."</a></td><td>".$reg['Disciplina']."</td></tr>";
            $i++;
        }
        ?>
        </table>
        <?php
    }    
}
?>

<script>
    $(document).ready(function() {
        $('#send-lattes').click(function(event) {
            var lattes = encodeURIComponent($('#lattes').val());
            $('#index').load('home.php?lattes=' + lattes);
        });
        $('#send-email').click(function(event) {
            var email = encodeURIComponent($('#email').val());
            $('#index').load('home.php?email=' + email);
        });
        
        $('#remover-foto').click(function(event) {
            $('#index').load('home.php?removerFoto=<?php print crip($user); ?>');
    
        });

        $('#adiciona-foto').click(function(event) {
            new $.Zebra_Dialog('<strong>Recorte a foto, se desejar.</strong>', {
                source: {'iframe': {
                    'src':  '<?php print VIEW; ?>/trocaFoto.php',
                    'height': 350
                     }
                     },
                width: 500,
                title:  'Troque a Foto',
                onClose:  function() {
                            $('#index').load('home.php');
                        }
            });
        });
    });
</script>	