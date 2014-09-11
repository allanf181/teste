<?php
//Esse arquivo é fixo para o professor.
//Permite a inserção de links e arquivos para download pelos alunos.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

if ($_POST['pagina'])
    $_GET['pagina'] = $_POST['pagina'];
if (!$_GET['pagina'])
    $_GET['pagina'] = "inserir";

// MOSTRA AS PESSOAS QUE BAIXARAM O ARQUIVO
if ($_GET['pagina'] == 'down') {
    require CONTROLLER . "/arquivoLog.class.php";
    $arqLogs = new ArquivosLogs();

    $down = $arqLogs->listArquivosLogs(dcrip($_GET["codigo"]));
    print "<br>";
    $n=1;
    foreach ($down as $d)
        print $n++.": ".$d['nome'] . '<br>';
    die;
}

require CONTROLLER . "/arquivo.class.php";
$arq = new Arquivos();

if ($_POST['atribuicao'])
    $_GET['atribuicao'] = $_POST['atribuicao'];
$atribuicao = $_GET["atribuicao"];

$local = $_SESSION['loginCodigo'] . '/' . dcrip($atribuicao) . '/';

?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?=$TITLE_DESCRICAO?><?=$TITLE?></h2>
<link rel="stylesheet" type="text/css" href="<?= VIEW; ?>/css/aba.css" media="screen" />

<ul class="tabs">
    <li><a href="javascript:$('#professor').load('<?= $SITE . "?atribuicao=$atribuicao"; ?>&pagina=inserir'); void(0);">Inserir Material</a></li>
    <li><a href="javascript:$('#professor').load('<?= $SITE . "?atribuicao=$atribuicao"; ?>&pagina=copiar'); void(0);">Copiar Material</a></li>
</ul>
<div class="tab_container" id="form">
    <?php
    if ($_GET['pagina'] == "copiar") {
        ?>
        <p>Permite copiar materiais cadastrados para disciplinas equivalentes no semestre atual ou outros semestres.</p>
        <?php
        if ($_GET["codigoCopy"]) {
            $ret = $arq->copyArquivo($_SESSION['loginCodigo'], dcrip($atribuicao), dcrip($_GET["codigoCopy"]));
            $atribuicao = dcrip($_GET["atribuicao"]);
            mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
        }
        ?>

        <script>
            $('#form_padrao').html5form({
                method: 'POST',
                action: '<?= $SITE; ?>',
                responseDiv: '#professor',
                colorOn: '#000',
                colorOff: '#999',
                messages: 'br'
            })
        </script>

        <div id="html5form" class="main">
            <form id="form_padrao">
                <table border="0" width="100%">
                    <tr><td align="left" style="width: 160px">Disciplinas equivalentes: </td><td>
                            <select name="campoDisciplina" id="campoDisciplina" value="<?php echo $disciplina; ?>" >
                                <option></option>
                                <?php
                                $res = $arq->getArquivoEquivalente(dcrip($_GET["atribuicao"]));
                                foreach ($res as $reg) {
                                    if (!$reg['subturma'])
                                        $reg['subturma'] = $reg['eventod'];
                                    ?>
                                    <option value='<?= crip($reg['codigo']) ?>'><?= $reg['nome'] . ' [' . $reg['semestre'] . '/' . $reg['ano'] . '] [' . $reg['numero'] . '] [' . $reg['subturma'] ?>]</option>
                                    <?php
                                }
                                ?>
                            </select>
                        </td></tr>
                </table>
                <br />
                <table width="100%"><tr><td>
                            <a class="nav" id='item-copiar' href="#" title="Copiar Plano de Ensino"><img src='<?= ICONS ?>/copiar.gif' width="30" /></a>
                        </td></tr></table> 
            </form>
        </div>
    </div>
    <?php
}
if ($_GET['pagina'] == "inserir") {
    // INSERT E UPDATE
    if ($_POST["opcao"] == 'InsertOrUpdate') {
        extract(array_map("htmlspecialchars", $_POST), EXTR_OVERWRITE);
        unset($_POST['opcao']);
        unset($_POST['pagina']);
        $tipo = $_GET['pagina'];
       
        $ret = $arq->insertOrUpdateArquivo($_POST, $local);
        mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
        if ($_POST['codigo'])
            $_GET["codigo"] = $_POST['codigo'];
        else
            $_GET["codigo"] = crip($ret['RESULTADO']);
    }

    // DELETE
    if ($_GET["opcao"] == 'delete') {
        $ret = $arq->deleteArquivo($_GET["codigo"], $local);

        mensagem($ret['STATUS'], $ret['TIPO'], $ret['RESULTADO']);
        $_GET["codigo"] = null;
    }

    // LISTAGEM
    if (!empty($_GET["codigo"])) { // se o parâmetro não estiver vazio
        // consulta no banco
        $params = array('codigo' => dcrip($_GET["codigo"]));
        $res = $arq->listRegistros($params);
        extract(array_map("htmlspecialchars", $res[0]), EXTR_OVERWRITE);
        $atribuicao = crip($atribuicao);
        $codigo = crip($codigo);
    }

    $max_file = ini_get('upload_max_filesize') * 1024 * 1024;
    print "Tamanho m&aacute;ximo do arquivo: " . ini_get('upload_max_filesize') . "<br>";
    ?>

    <script type="text/javascript" src="js/jquery.form.min.js"></script>
    <script type="text/javascript">
            $(document).ready(function() {

                $('#salvar').change(function() {
                    $('#MyUploadForm').submit();
                });

                var options = {
                    target: '#professor', // target element(s) to be updated with server response 
                    beforeSubmit: beforeSubmit, // pre-submit callback 
                    success: afterSuccess, // post-submit callback 
                    resetForm: true        // reset the form after successful submit 
                };

                $('#MyUploadForm').submit(function() {
                    $(this).ajaxSubmit(options);
                    // always return false to prevent standard browser submit and page navigation 
                    return false;
                });
            });

            function afterSuccess()
            {
                $('#submit-btn').show(); //hide submit button
                $('#loading-img').hide(); //hide submit button
            }

            function beforeSubmit() {
                //check whether browser fully supports all File API
                if (window.File && window.FileReader && window.FileList && window.Blob)
                {
                    if (!$('#descricao').val())
                        return false;
                    if ($('#arquivo').val()) {
                        var fsize = $('#arquivo')[0].files[0].size; //get file size

                        if (fsize ><?php print $max_file; ?>)
                        {
                            $("#retorno").html("Arquivo muito grande, tamanho limite: <b> " + bytesToSize(fsize) + "</b>");
                            return false
                        }
                    }
                    $('#submit-btn').hide(); //hide submit button
                    $('#loading-img').show(); //hide submit button
                    $("#retorno").html("");
                }
                else
                {
                    //Output error to older unsupported browsers that doesn't support HTML5 File API
                    $("#retorno").html("Por favor, atualize seu browser para suportar essa função!");
                    return false;
                }
            }

            //function to format bites bit.ly/19yoIPO
            function bytesToSize(bytes) {
                var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                if (bytes == 0)
                    return '0 Bytes';
                var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
                return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
            }

    </script>
    <div align="center">
        <form action="<?= VIEW; ?>/professor/arquivo.php" method="post" enctype="multipart/form-data" id="MyUploadForm" style="text-align: left">
            <input type="hidden" name="pagina" value="inserir" />
            <input type="hidden" name="opcao" value="InsertOrUpdate" />
            <input type="hidden" name="codigo" value="<?= $codigo; ?>" />
            <input type="hidden" name="atribuicao" value="<?= $atribuicao; ?>" />
            <input type="hidden" name="pessoa" value="<?= crip($_SESSION['loginCodigo']); ?>" />
            <table id="form" border="0" width="100%">
                <tr>
                    <td align="left">Descri&ccedil;&atilde;o: </td>
                    <td><textarea maxlength='200' cols="60" id='descricao' name='descricao'><?= $descricao; ?></textarea></td>
                </tr>
                <tr>
                    <td align="left">Link: </td>
                    <td><input type="text" size="60" id="link" name="link" maxlength="255" value="<?= $link; ?>"/></td>
                </tr>
                <tr>
                    <td align="left">Arquivo: </td>
                    <td>
                        <?php if (!$codigo) {
                            ?>
                            <input name="arquivo" id="arquivo" type="file" accept="*" />
                            <?php
                        } else {
                            ?>
                            <a href="<?php print INC; ?>/file.inc.php?type=arquivo&id=<?= $codigo; ?>" target="_blank"><?= $arquivo ?></a>
                            <?php
                        }
                        ?>
                            <div id='retorno'></div>
                    </td>
                </tr>

            </table>
            <table width="100%"><tr><td><input type="submit" id="salvar" value="Salvar" /></td>
                    <td><a href="javascript:$('#professor').load('<?= "$SITE?pagina=inserir&atribuicao=$atribuicao"; ?>'); void(0);">Novo/Limpar</a></td>
                </tr></table>	
            <br>
        </form>
    </div>

    <?php
    // Verifica no diretório se há algo que
    // não está no banco e sincroniza.
    $arq->syncFolder($local, dcrip($atribuicao));

    $params = array('codigo' => $atribuicao);
    $res = $arq->listArquivos($params, $item, $itensPorPagina);
    ?>
    <br />

    <div align="center">
    <table id="listagem" border="0" align="center">
        <tr>
            <th align="center" width="30">#</th>
            <th align="center" width="200">Descri&ccedil;&atilde;o</th>
            <th align="left" width="255">Link</th>
            <th align="left" width="255">Arquivo</th>
            <th align="left" width="150">Data</th>
            <th align="center" width="50">&nbsp;&nbsp;<input type="checkbox" id="select-all" value="">
                <a href="#" class='item-excluir'><img class='botao' src='<?php print ICONS; ?>/delete.png' /></a>
            </th>
        </tr>
        <?php
        // efetuando a consulta para listagem
        $i = 1;
        foreach ($res as $reg) {
            $i % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
            if ($reg['arquivo'])
                $path_parts = pathinfo($reg['arquivo']);
            ?>
            <tr <?= $cdif ?>><td align='left'><?= $i++ ?></td>
                <td><a href="#" title="<?=$reg['descricao']?>"><?= abreviar(nl2br(mostraTexto($reg['descricao'])),25) ?></a></td>
                <td>
                    <?php if ($reg['link']) {
                        ?>
                        <img class='botao' src='<?= ICONS . 'files/internet.png' ?>' />
                        <?php
                    }
                    ?>                    
                    <a title="<?=$reg['link']?>" href="<?= $reg['link'] ?>" target="_blank"><?= abreviar($reg['link'],25) ?></a>
                </td>
                <td>
                    <?php
                    if ($reg['arquivo']) {
                        if (strlen($reg['arquivo']) > 25)
                            $fileName = abreviar($reg['arquivo'],25).$path_parts['extension'];
                        else
                            $fileName = $reg['arquivo'];
                        ?>
                        <a title="Clique aqui para abrir o arquivo." href="<?= INC ?>/file.inc.php?type=arquivo&id=<?= crip($reg['codigo']) ?>" target="_blank">
                            <?php
                            if (is_file(PATH . ICONS . 'files/' . $path_parts['extension'] . '.png')) {
                                $fileIcon = 'files/' . $path_parts['extension'] . '.png';
                            } else {
                                $fileIcon = 'files/file.png';
                            }
                            ?>
                            <img class='botao' src='<?= ICONS . $fileIcon ?>' /></a>
                            <a title='Clique aqui para visualizar quem fez download desse arquivo.' href="#" class='item-down' id='<?= crip($reg['codigo']) ?>'><?= $fileName ?></a>                            
                        <?php
                    }
                    ?>
                </td>
                <td><?= $reg['data'] ?></td>
                <td align='center'><input type='checkbox' id='deletar' name='deletar[]' value='<?= crip($reg['codigo']) ?>' />
                    <a href='#' title='Alterar' class='item-alterar' id='<?= crip($reg['codigo']) ?>'>
                        <img class='botao' src='<?= ICONS ?>/config.png' /></a>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    </div>
    <br /><br /><br />
    <?php
}

$atribuicao = $_GET['atribuicao'];
?>
<script>

        $(document).ready(function() {
            $('#descricao').maxlength({
                events: [], // Array of events to be triggerd    
                maxCharacters: 200, // Characters limit   
                status: true, // True to show status indicator bewlow the element    
                statusClass: "status", // The class on the status div  
                statusText: "caracteres restando", // The status text  
                notificationClass: "notification", // Will be added when maxlength is reached  
                showAlert: false, // True to show a regular alert message    
                alertText: "Limite de caracteres excedido!", // Text in alert message   
                slider: true // True Use counter slider    
            });

            $(".item-excluir").click(function() {
                $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>', {
                    'type': 'question',
                    'title': '<?php print $TITLE; ?>',
                    'buttons': ['Sim', 'Não'],
                    'onClose': function(caption) {
                        if (caption == 'Sim') {
                            var selected = [];
                            $('input:checkbox:checked').each(function() {
                                selected.push($(this).val());
                            });
                            $('#professor').load('<?= "$SITE?atribuicao=$atribuicao"; ?>&pagina=inserir&opcao=delete&codigo=' + selected + '&item=<?= $item; ?>');
                        }
                    }
                });
            });

            $(".item-alterar").click(function() {
                var codigo = $(this).attr('id');
                $('#professor').load('<?= "$SITE?atribuicao=$atribuicao"; ?>&pagina=inserir&codigo=' + codigo);
            });

            $("#item-copiar").click(function() {
                var codigo = $('#campoDisciplina').val();
                $.Zebra_Dialog('<strong>Aten&ccedil;&atilde;o, o material da disciplina escolhida ser&aacute; adicionado nessa disciplina. Deseja continuar?', {
                    'type': 'question',
                    'title': '<?php print $TITLE; ?>',
                    'buttons': ['Sim', 'Não'],
                    'onClose': function(caption) {
                        if (caption == 'Sim') {
                            $('#professor').load('<?= "$SITE?atribuicao=$atribuicao"; ?>&pagina=copiar&codigoCopy=' + codigo);
                        }
                    }
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

            $('.item-down').click(function(event) {
                var codigo = $(this).attr('id');
                new $.Zebra_Dialog('<strong>Pessoas que fizeram download do arquivo:</strong>', {
                    source: {'iframe': {
                            'src': '<?= VIEW ?>/professor/arquivo.php?pagina=down&codigo=' + codigo,
                            'height': 350
                        }
                    },
                    width: 500,
                    title: 'Visualização de Downloads'
                });
            });
        });
</script>