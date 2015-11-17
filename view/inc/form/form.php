<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="<?= VIEW ?>/js/bootstrap/css/bootstrap.min.css" />

<!-- Optional theme -->
<link rel="stylesheet" href="<?= VIEW ?>/js/bootstrap/css/bootstrap-theme.min.css" />

<!-- Latest compiled and minified JavaScript -->
<script src="<?= VIEW ?>/js/bootstrap/js/bootstrap.min.js"></script>        

<script src="<?= VIEW ?>/js/bootstrap/bootstrap-table/dist/bootstrap-table.js" type="text/javascript"></script>
<script src="<?= VIEW ?>/js/bootstrap/bootstrap-table/dist/locale/bootstrap-table-pt-BR.js" type="text/javascript"></script>

<script>
$(document).ready(function () {
    $(".item-excluir").click(function () {
        $.Zebra_Dialog('<strong>Deseja continuar com a exclus&atilde;o?</strong>', {
            'type': 'question',
            'title': '<?= $TITLE ?>',
            'buttons': ['Sim', 'Não'],
            'onClose': function (caption) {
                if (caption == 'Sim') {
                    var selected = [];
                    $('input:checkbox:checked').each(function () {
                        selected.push($(this).val());
                    });

                    $('#index').load('<?= $SITE ?>?opcao=delete&codigo=' + selected + '&item=<?= $item ?>');
                }
            }
        });
    });

    $(".item-alterar").click(function () {
        var codigo = $(this).attr('id');
        $('#index').load('<?= $SITE ?>?codigo=' + codigo);
    });

    $('#select-all').click(function (event) {
        if (this.checked) {
            $(':checkbox').each(function () {
                this.checked = true;
            });
        } else {
            $(':checkbox').each(function () {
                this.checked = false;
            });
        }
    });
});
</script>