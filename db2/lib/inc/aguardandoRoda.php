<?php
//A descrição abaixo é utilizada em Permissões para indicar o que o arquivo faz (respeitar a ordem da linha)
//Lista as atribuições que já tem nota lançada e aguardam a execução do Roda.
//Link visível, quando ativo, mostra o nome definido no menu do sistema.
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require PERMISSAO;

require CONTROLLER . "/notaFinal.class.php";
$notas = new NotasFinais();

echo "<p style='background: red; color: white; text-align: center'>Docentes aguardando o Roda:</p>";
echo "<p style='background: red; color: white; text-align: center; padding-bottom: 5px'>Filtrar: <input type='text' id='filtro' /></p>";
foreach ($notas->getDisciplinasRoda() as $atribuicao) {
    echo "<p class='resultado'><a  title='Clique para consultar o Roda para esta disciplina' href=\"javascript:$('#" . $reg['file'] . "Retorno').load('db2/db2ConsultaDisciplinas.php?atribuicao=" . $atribuicao['codigo'] . "');void(0);\">" . $atribuicao['professor'] . " [" . $atribuicao['disciplina'] . "] " . $atribuicao['turma'] . " " . $atribuicao['curso'] . " </a></p>";
    echo "<div id='resultadoRoda" . $atribuicao['codigo'] . "'></div>";
}
?>
<script>
    $(document).ready(function () {
        $('#filtro').keyup(function(){
            $('.resultado').each(function(){
                if ($(this).html().toUpperCase().indexOf($('#filtro').val().toUpperCase())>=0){
                    $(this).show();
                }
                else{
                    $(this).hide();
                }
            });
        });
    });    
</script>