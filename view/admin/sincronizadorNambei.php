<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Realiza a sincronização da base de dados utilizada pelo WebDiário com relação à base de dados do Nambei.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require PERMISSAO;
require SESSAO;

require CONTROLLER . "/atualizacao.class.php";
$atualizacao = new Atualizacoes();

require CONTROLLER . "/log.class.php";
$log = new Logs();

require CONTROLLER . "/notaFinal.class.php";
$notas = new NotasFinais();
?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>

<script>
    $(document).ready(function() {
        $("input[type='button']").click(function() {
            $('#' + this.id + 'Retorno').load('db2/' + this.id + '.php');
        });
    });
</script>

<form id="form" method="post" >
    <table align="center" border="0" class="sincronizador" width="100%">
        <?php
        $res = $atualizacao->listAtualizacoes();
        foreach ($res as $reg) {
            ?>
            <tr>
                <td align="right" valign="top"><?= $reg['title'] ?>: </td>
                <td valign="top">
                    <input type="button" id="<?= $reg['file'] ?>" value="<?= $reg['rotulo'] ?>" />
                </td>
                <td>
                    <div id='<?= $reg['file'] ?>Retorno'><?= $reg['situacao'] ?></div>
                    
                    <?php
                    // DISCIPLINAS QUE AGUARDAM O RODA
                    if ($reg['title']=="Consulta Roda"){
                        echo "<div style='border: 2px solid red'>";
                            echo "<div id='aguardandoRoda'>";
                            echo "<p style='background: red; color: white; text-align: center'>Docentes aguardando o Roda:</p>";
                            foreach ($notas->getDisciplinasRoda() as $atribuicao){
                                echo "<p><a  title='Clique para consultar o Roda para esta disciplina' href=\"javascript:$('#".$reg['file']."Retorno').load('db2/db2ConsultaDisciplinas.php?atribuicao=".$atribuicao['codigo']."');void(0);\">".$atribuicao['professor']." [".$atribuicao['disciplina']."] ".$atribuicao['turma']." ".$atribuicao['curso']." </a></p>";
                                echo "<div id='resultadoRoda".$atribuicao['codigo']."'></div>";
                            }
                            echo "</div>";
                        echo "</div>";
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <td colspan="3">
                <hr>
            </td>
        </tr>
        <tr>
            <td colspan="3" align="center">
                <a id="atualizar" href="javascript:$('#index').load('<?= $SITE ?>');void(0);" title="Atualizar Resumos">
                    <img class="botao" src="<?= ICONS ?>/sync.png" />
                </a>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <b>RESUMO</b>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <?php
                $sqlAdicional .= " AND l.origem = 'CRON' ORDER BY l.codigo DESC, l.data DESC, l.url ";
                foreach ($log->listLogs($params, $sqlAdicional, 1, 13) as $reg) {
                    print $reg['data'] . " - " . $reg['url'] . "<br>";
                }
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="3"><hr></td>
        </tr>
        <tr>
            <td colspan="3">
                <b>ERROS OCORRIDOS</b>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <?php
                $sqlAdicional = " AND l.origem = 'CRON_ERRO' "
                        . "AND l.data BETWEEN DATE_SUB(NOW(), INTERVAL 5 DAY) AND NOW() "
                        . "GROUP BY l.url "
                        . "ORDER BY l.data DESC, l.origem ASC, l.url ASC, l.codigo DESC ";
                foreach ($log->listLogs($params, $sqlAdicional, 1, null) as $reg) {
                    print $reg['data'] . " - " . utf8_decode($reg['url']) . "<br>";
                }
                ?>
            </td>
        </tr>
    </table>
</form>