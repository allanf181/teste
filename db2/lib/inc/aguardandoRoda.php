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

$descricao = "Curso Superior: Esta listagem apresenta as disciplinas e nomes dos docentes que já exportaram notas e aguardam o Roda.<br /><br />"
        . "Curso Modular e Integrado: Esta listagem apresenta a turma e curso cujos professores já exportaram as notas e aguardam Roda. "
        . "Neste último caso, só serão apresentadas nesta listagem as turmas em que todos os docentes exportaram notas.";

echo "<p style='background: red; color: white; text-align: center'>Docentes/Turmas aguardando o Roda:<a  title='$descricao'><img class='resultado botao' src='".ICONS."/info.png' /></a></p>";
echo "<p style='background: red; color: white; text-align: center; padding-bottom: 5px'>Filtrar: <input type='text' id='filtro' /></p>";
foreach ($notas->getDisciplinasRoda() as $atribuicao) {
    $codigoAtribuicao = ($DEBUG)?"[". $atribuicao['codigo'] ."] ":"";
    echo "<p class='resultado'><a class='consulta-roda' id='".$atribuicao['codigo']."' "
            . "title='Clique para consultar o Roda para esta disciplina'>" 
            .$codigoAtribuicao. $atribuicao['professor'] 
            . " [" . $atribuicao['disciplina'] . "] " . $atribuicao['turma'] . " " . $atribuicao['curso'] . " </a></p>";
    echo "<div id='resultadoRoda" . $atribuicao['codigo'] . "'></div>";
}

foreach ($notas->getTurmasRoda($ANO, $SEMESTRE) as $atribuicao) {
    $codigoAtribuicao = ($DEBUG)?"[". $atribuicao['codigo'] ."] ":"";
    echo "<p class='resultado'><a class='consulta-roda-turma' id='".$atribuicao['codigo']."' "
            . "title='Clique para consultar o Roda para esta turma'>" 
            .$codigoAtribuicao."Turma ". $atribuicao['turma'] 
            ." ". $atribuicao['curso'] ." [".$atribuicao['modalidade'] . "] </a></p>";
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
        
        $('.consulta-roda').click(function(){
//            alert($(this).attr('id'));
            var req = $.get('db2/db2ConsultaDisciplinas.php?atribuicao='+$(this).attr('id'), function(data) {
                $('#<?=$reg['file']?>Retorno').html(data);
            });        
            $.when(req).then(function(){
                $('#index').load('<?= $SITE ?>');
            });
        });
        
        $('.consulta-roda-turma').click(function(){
            var req = $.get('db2/db2ConsultaDisciplinas.php?turma='+$(this).attr('id'), function(data) {
                $('#<?=$reg['file']?>Retorno').html(data);
            });        
            $.when(req).then(function(){
                $('#index').load('<?= $SITE ?>');
            });
        });
    });    
</script>