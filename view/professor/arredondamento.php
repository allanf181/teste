<?php
//Esse arquivo é fixo para o professor.
//Permite a inserção de arredondamentos no WebDiário.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/arredondamento.class.php";
$arredondamento = new Arredondamento();

$TITLE = "Arredondamento de Médias";
?>
<script src="<?= VIEW ?>/js/screenshot/main.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<?php
$atribuicao = dcrip($_GET["atribuicao"]);
if (isset($_GET["ifa"]))
    $ifa= dcrip($_GET["ifa"]);
if (isset($_GET["liberaDN"]))
    $liberaDN=dcrip($_GET["liberaDN"]);

$dados = $arredondamento->getMedias($atribuicao);
?> 

<script>
$('#form_padrao').html5form({
    method: 'POST',
    action: '<?= VIEW ?>/professor/avaliacao.php?opcao=controleDiario&liberaDN=<?=crip($liberaDN)?>&ifa=<?=crip($ifa)?>&atribuicao=<?= crip($atribuicao) ?>',
    responseDiv: '#professor',
    colorOn: '#000',
    colorOff: '#999',
    messages: 'br'
})
</script>

<div id="html5form" class="main">
    <form id="form_padrao">
        <table id="listagem" border="0" align="center">
            <tr>
                <th align="center" width="100" text-align="center">Prontuário</th>
                <th align="center" width="900">Nome</th>
                <th align="center" width="300">Média</th>
                <th width="400" align='center' text-align="center">Arredondamento</th>
            </tr>
            <?php
            $classe = 1;
            foreach ($dados as $indice=>$reg){
            $classe % 2 == 0 ? $cdif = "class='cdif'" : $cdif = "";
                ?>
            <tr <?= $cdif ?>>
                <td align="center"><?= $dados[$indice]['prontuario']?></td>
                <td>
                    <a href='#' rel='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($dados[$indice]['aluno']) ?>' class='screenshot' title='<?= mostraTexto($dados[$indice]['nome']) ?>'>
                    <img style='width: 20px; height: 20px' src='<?= INC ?>/file.inc.php?type=pic&id=<?= crip($dados[$indice]['aluno']) ?>' />
                    </a>
                    <?= $dados[$indice]['nome']?></td>
                <td><?= $dados[$indice]['media']?></td>
                <td>
                    <!--<input type="hidden" name="matricula[<?=$dados[$indice]['aluno']?>]" value='<?= $dados[$indice]['matricula'] ?>' />-->
                    <?php 
                    $decimal = $dados[$indice]['media'] - (int)$dados[$indice]['media'];
                    if(($decimal < 0.001) || (($decimal > 0.499) && ($decimal < 0.501))){ ?>
                        <input name='media[<?=$dados[$indice]['matricula']?>]' style='width: 40px' class="input" type='text' value='<?= $dados[$indice]['media'] ?>' size='4' maxlength='4' readonly="readonly" /></td>
                    <?php   
                    } else { 
                        $media = $dados[$indice]['media'];
                        if (isset($dados[$indice]['ncc'])){
                            $media = $dados[$indice]['ncc'];
                        }
                    ?>
                    <input name='media[<?=$dados[$indice]['matricula']?>]' style='width: 40px' class="input" type='text'  tabindex='<?= $classe ?>' size='4' maxlength='4' onKeyUp="validaTodos()" value="<?= $media ?>" /></td>
                    <?php } ?>
                    
            </tr>
                <?php
            $classe++;
            }
            ?>
            <input type="hidden" name="atribuicao" value="<?= crip($atribuicao) ?>" />
        </table>
                <center>
                    <input type="button" id="btnEnviar" value="Enviar" name="enviar" style="margin-top: 10px" disabled="disabled" />
                    <input type="submit" id="enviar" style="visibility: hidden" name="submitEnviar" />
                </center>
            
        
    </form>
</div>
<script>
    validaTodos();
    
    $('.input').focus(function(){
        $(this).select();
    });
    
    function validaItem(item) {
        item.value = item.value.replace(",", ".");
        if (item.value < 0 || item.value > 10) {
            return false;
        }
        decimal = item.value - parseInt(item.value);
        jaArredondado = (decimal < 0.01) || ((decimal > 0.49) && (decimal < 0.51));
        if (!jaArredondado){
            return false;
        }
        return true;
    }
    
    function validaTodos(){
        document.getElementById('btnEnviar').disabled=true;
        var nodes = document.querySelectorAll('#form_padrao input[type=text]');
        for (var i=0; i<nodes.length; i++){
            if (!validaItem(nodes[i]))
                return false;
        }
        document.getElementById('btnEnviar').disabled=false;
    }
    
    $("#btnEnviar").click(function () {
        $.Zebra_Dialog('<strong>Professor, as notas ser&atilde;o finalizadas e posteriormente exportadas para o DigitaNotas, ap&oacute;s essa opera&ccedil;&atilde;o as notas n&atilde;o poder&atilde;o ser alteradas. \n\
                                <br><br>Somente a secretaria poder&aacute; alterar a nota pelo Nambei.\n\
                                <br><br>Deseja continuar com a exporta&ccedil;&atilde;o?</strong>', {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption) {
                if (caption == 'Sim') {
                    $('#enviar').click();
                }
            }
        });
    });
    
</script>