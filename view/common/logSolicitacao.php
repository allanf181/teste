<?php
// verifica se não está sendo chamado diretamente.
if (strpos($_SERVER["HTTP_REFERER"], LOCATION) == false) {
    print "<p>Who are you? <br />There's nothing here. <br /><br />;P</p>\n";
    die;
}

$params['codigoTabela'] = dcrip($_GET['codigo']);
$params['nomeTabela'] = $_GET['tabela'];
?>
<table border="1" style="border-collapse:collapse; font-family: verdana; font-size: 10px;" align="center" width="100%">
    <tr style="background-color: #ccc">
        <th align="center" width="40">Data Solicitação</th>
        <th>Solicitante</th>
        <th>Solicitação</th>
        <th width="40">Data da Entrega</th>
    </tr>
    <?php
    $sqlAdicional = ' ORDER BY l.codigo DESC ';
    foreach ($log->listSolicitacoes($params, $sqlAdicional) as $l) {
        ?>
        <tr>
            <th><?= $l['dataSolicitacao'] ?></th>
            <th><?= $l['solicitante'] ?></th>
            <th><?= $l['solicitacao'] ?></th>
            <th><?= $l['dataConcessao'] ?></th>
        </tr>
        <?php
    }
    ?>
</table>