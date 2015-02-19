<script>
    window.start = <?= $TIMEOUT * 60 ?>;

    //O restante do scrip abaixo pertence ao CHAT
    //Ele é utilizado aqui, pois caso o usuário saia
    //do CHAT é necessário detectar e parar os monitores.
    if (typeof interval != "undefined") {
        clearInterval(interval);
        clearInterval(interval1);    
    } else {
        var interval = null;
        var interval1 = null;
    }
</script> 

<?php
// VERIFICANDO A SESSÃO DO USUÁRIO
if (isset($_SESSION['timeout']) && $_SESSION['timeout'] + $TIMEOUT * 60 < time() && $_SESSION['timeout'] != 'CRON') {
    $_SESSION['timeout'] = time();
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#index').load('<?= VIEW ?>/system/logoff.php');
        });
    </script>
    <?php
} else {
    $_SESSION['timeout'] = time();
}
?>