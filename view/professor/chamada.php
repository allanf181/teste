<?php
//Esse arquivo é fixo para o professor.
//Permite a impressão da lista de chamada.
//Link visível no menu: PADRÃO NÃO, pois este arquivo tem uma visualização diferente, ele aparece como ícone.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require VARIAVEIS;
require FUNCOES;
require MENSAGENS;
require PERMISSAO;
require SESSAO;

?>
<script src="<?= VIEW ?>/js/tooltip.js" type="text/javascript"></script>
<h2><?= $TITLE_DESCRICAO ?><?= $TITLE ?></h2>
<br />
<a class='nav professores_item' target='_blank' href='<?php print VIEW; ?>/secretaria/relatorios/inc/chamada.php?atribuicao=<?= $_GET['atribuicao'] ?>'>
    <img src='<?=ICONS?>/files/pdf.png'><h3>Imprimir no formato PDF</h3>
</a>
<br />
<a class='nav professores_item' target='_blank' href='<?php print VIEW; ?>/secretaria/relatorios/inc/chamadaPlanilha.php?atribuicao=<?= $_GET['atribuicao'] ?>'>
    <img src='<?=ICONS?>/files/xls.png'><h3>Imprimir no formato de planilha eletr&ocirc;nica</h3>
</a>