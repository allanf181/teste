<script> 
    window.start = <?php print $TIMEOUT*60; ?>;
</script> 

<?php
// VERIFICANDO A SESSÃO DO USUÁRIO
if (isset($_SESSION['timeout']) && $_SESSION['timeout'] + $TIMEOUT * 60 < time() && $_SESSION['timeout'] != 'CRON') {
    $_SESSION['timeout'] = time();
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#index').load('<?php print VIEW; ?>/logoff.php');
        });
    </script>
    <?php
} else {
    $_SESSION['timeout'] = time();
}

?>