<?php
include_once "inc/config.inc.php";

require MYSQL;
require VARIAVEIS;
require FUNCOES;


?>
<table border="0" width='100%'>
<tr><td colspan="2"><font size="4"><b>WebDi&aacute;rio</b></font></font>
<br><font size="1">Vers&atilde;o 1.7</font></td></tr>
<tr><td>
<?php
$user = $_SESSION["loginCodigo"];


// Mostra a foto do usuário e informações de senha
if ($user) {
    require CONTROLLER . "/pessoa.class.php";
    $pessoa = new Pessoa();
    
    if (isset($_GET['removerFoto']))
        $pessoa->removeFoto($user);
    
    ?>
    <img alt="foto" style="width: 150px; height: 150px" src="<?php print INC; ?>/file.inc.php?type=pic&time=<?php print time(); ?>&id=<?php print crip($user); ?>" />
    <br><a href="#" id="adiciona-foto">Alterar Foto</a>
    <?php
   
    if ($pessoa->hasPicture($user)) {
        ?>
        &nbsp;<img src="<?php print ICONS; ?>/remove.png" id="remover-foto" title='Remover Foto' style="width: 15px; height: 15px">
        <?php
    }
    
    $res = $pessoa->infoPassword($user);
    if ($res['dataSenha']) {
        ?>
        <br><br>&Uacute;ltima altera&ccedil;&atilde;o da senha: <?php print formata($res['dataSenha']); ?>
        <?php
    }

    if ($res['dias']) {
        if (($res['data'] >= $res['dias'])) {
            ?>
            <br><br><p>Aten&ccedil;&atilde;o, sua sua est&aacute; expirada. <a href="javascript:$('#index').load('inc/senha.php?opcao=alterar'); void(0);">Clique aqui</a> e efetue a troca.
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
    $aluno = new Aluno();
    if ($nome = $aluno->hasSocioEconomico($user)) {
        ?>
        <br><br><font size="2" color="red">Ol&aacute; <?php print $nome; ?>, seu question&aacute;rio Socioecon&ocirc;mico est&aacute; incompleto.</font>
        <br><a href="javascript:$('#index').load('<?php print VIEW; ?>/aluno/socioEconomico.php'); void(0);" title='Socioencon&ocirc;mico'>Clique aqui para responder</a>
        <?php
    }
}

if (in_array($ADM, $_SESSION["loginTipo"]) || in_array($SEC, $_SESSION["loginTipo"])) {
    
    // Verifica se o CRON está sendo executado.
    require CONTROLLER . "/log.class.php";
    $log = new Log();
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
    if (isset($_GET['lattes'])) {
        if ($_GET['lattes'] == 'undefined')
            $_GET['lattes'] = null;
        $pessoa->updateLattes($user, $_GET['lattes']);
    }

    $lattes = $pessoa->showLattes($user);
    if (!$lattes) {
        ?>
        <br><br>Lattes: <input type="text" size="60" maxlength="200" name="campoLattes" id="campoLattes" value="" />
        <img src="<?php print ICONS; ?>/accept.png" id="lattes" style="width: 20px; height: 20px">
        <?php
    } else {
        ?>
        <br><br>Lattes: <a href="<?php print $lattes; ?>" target="_blank"><?php print $lattes; ?></a>
        &nbsp;<img src="<?php print ICONS; ?>/remove.png" id="lattes" title='Remover Lattes' style="width: 15px; height: 15px">
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
    require CONTROLLER . "/plano.class.php";
    $plano = new Plano();
    $res = $plano->hasChangePlano($user);

    if ($res) {
        foreach($res as $reg) {
            ?>
            <br><br><font size="2" color="red">Aten&ccedil;&atilde;o: <?php print $reg['PlanoSolicitante']; ?>, solicitou corre&ccedil;&atilde;o em seu Plano de Ensino de <?php print $reg['Disc']; ?>: <br><?php print $reg['PlanoSolicitacao']; ?></font>
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
$aviso = new Aviso();
$res = $aviso->hasAviso($user);
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
$res = $pessoa->listProfOutOfLimitAddAula($user, $ANO, $SEMESTRE);
if ($res) {
    ?>
    <br><table id="listagem" align="center">
    <caption><font color="red">Lista de professores sem registro de aulas nos &uacute;ltimos 7 dias.</font></caption>
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
?>

<script>
    $(document).ready(function() {
        $('#lattes').click(function(event) {
            var lattes = encodeURIComponent($('#campoLattes').val());
            $('#index').load('home.php?lattes=' + lattes);
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