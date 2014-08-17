<?php
// AVALIAÇÕES
$SUB_MENOR_NOTA = "Substitui a menor nota";
$SUB_MEDIA = "Substitui a m&eacute;dia das avalia&ccedil;&otilde;es";
$ADD_MENOR_NOTA = "Adiciona o valor da recupera&ccedil;&atilde;o no valor da menor nota";
$ADD_MEDIA = "Adicionar o valor da recupera&ccedil;&atilde;o na m&eacute;dia";

$MEDIA = 'M&eacute;dia';
$SOMA = 'Soma';
$FORMULA = 'F&oacute;rmula';
$PESO = 'Peso';

$AVALIACAO = 'Avalia&ccedil;&atilde;o';
$RECUPERACAO = 'Recupera&ccedil;&atilde;o';
$PONTOEXTRA = 'Ponto Extra';
$SUBSTITUTIVA = 'Substitutiva';

function mensagem($TIPO, $MSG, $OPT=null) {

        $OK_DELETE 	= 'Registros deletados: '.$OPT;
	$ERRO_DELETE 	= 'Problema ao apagar registro.';
	
	$OK_INSERT 	= 'Registro inserido com sucesso.';
	$ERRO_INSERT 	= 'Problema ao inserir registro.';
	
	$OK_UPDATE 	= 'Registros alterados: '.$OPT;
	$ERRO_UPDATE 	= 'Problema ao alterar registro.';

        // AVISOS ANTIGOS, SERAO REMOVIDOS
	$OK_TRUE_DELETE 	= 'Registro deletado com sucesso.';
	$NOK_FALSE_DELETE 	= 'Problema ao apagar registro.';
	
	$OK_TRUE_INSERT 	= 'Registro inserido com sucesso.';
	$NOK_FALSE_INSERT 	= 'Problema ao inserir registro.';
	
	$OK_TRUE_UPDATE 	= 'Registro alterado com sucesso';
	$NOK_FALSE_UPDATE 	= 'Problema ao alterar registro.';
        
      	$INFO_UPDATE 	= 'Esse registro já está cadastrado no banco de dados.';
     
        $INFO_PRAZO_DIARIO = 'O seu di&aacute;rio foi reaberto at&eacute: '.$OPT;
        
	$INFO_DELETE = 'Problema ao excluir registro! Esse registro possui depend&ecirc;ncias.';
        
        $INFO_FALSE_DELETE_DEP = 'Problema ao excluir registro! Esse registro possui depend&ecirc;ncias.';
	
	$PRONTUARIO_EXISTE = 'Esse prontu&aacute;rio j&aacute; est&aacute; cadastrado.';

	$FALSE_INSERT_NULL_FIELD = 'Problema ao inserir registro, preencha todos os campos.';
	$FALSE_UPDATE_NULL_FIELD = 'Problema ao atualizar registro, preencha todos os campos.';

	$OK_TRUE_COPY_PLANO_ENSINO = 'Plano de ensino copiado com sucesso.';
	$NOK_FALSE_COPY_PLANO_ENSINO = 'Problema ao copiar plano de ensino.';

	$OK_TRUE_COPY_PLANO_AULA = 'Plano de aula copiado com sucesso.';
	$NOK_FALSE_COPY_PLANO_AULA = 'Problema ao copiar plano de aula.';
        
        $INFO_EMPTY_PLANO_ENSINO = 'Necess&aacute;rio cadastrar o Plano de Ensino primeiro.';
        $ERRO_SOLICITACAO_PLANO = $OPT[0].", solicitou corre&ccedil;&atilde;o em seu Plano: <br>".$OPT[1];
        $OK_PLANO_VALIDO = $OPT." validou seu plano!";
    
	$OK_TRUE_COPY_MODALIDADE = 'Modalidade copiada com sucesso.';
	$NOK_FALSE_COPY_MODALIDADE = 'Problema ao copiar a modalidade.';

        $OK_TRUE_COPY_PERMISSAO = 'Permiss&atilde;o copiada com sucesso.';
	$NOK_FALSE_COPY_PERMISSAO = 'Problema ao copiar a Permiss&atilde;o.';
        
	$FALSE_CLOSE_CLASS_REGISTRY = 'O di&aacute;rio n&atilde;o pode ser finalizado. Verifique se h&aacute; algum aluno sem nota ou se algum instrumento de avalia&ccedil;&atilde;o deixou de ser aplicado.';
	
	$NOT_SELECT = "Nenhum registro selecionado.";
	
        $OK_VALID_FTD = $OPT.' validou sua FTD.';
        $INFO_INVALID_FTD = $OPT[0].', solicitou corre&ccedil;&atilde;o em seu FTD: <br>'.$OPT[1];
	$OK_NOT_SAVE_FTD = "Ol&aacute; Professor, como ainda n&atilde;o salvou sua FTD, o sistema tentou buscar seus hor&aacute;rios para facilitar.";
	
        $ERRO_ARQUIVO = $OPT;
        $INFO_ARQUIVO = $OPT;
        
        // LOGIN
        $ERRO_MANY_TRY = "Tentativas restantes: ".$OPT;
        $ERRO_USER_OR_PASS_INVALID = "Usu&aacute;rio ou senha inv&aacute;lidos.";
        $ERRO_CAPTCHA = "Caracetes inv&aacute;lidos. Tente novamente.</font>";
        $OK_EMAIL_ENVIADO = "As instru&ccedil;&otilde;es para recupera&ccedil;&atilde;o de senha foram enviadas para seu e-mail!";
    	$ERRO_EMAIL_NAO_CADASTRADO = "E-mail n&atilde;o cadastrado ou prontu&aacute;rio n&atilde;o localizado.";
	$INFO_PRONTUARIO_VAZIO = "Por favor, digite o prontu&aacute;rio!";

        // CODIGOS DE ERROS - PDO/MYSQL
        if ($OPT) {
            if ($OPT == 23000 && ($MSG=='UPDATE' || $MSG=='INSERT')) {
                $MSG = 'UPDATE';
                $TIPO = 'INFO';
            }
            if ($OPT == 23000 && $MSG=='DELETE') {
                $MSG = 'DELETE';
                $TIPO = 'INFO';
            }            
        }
       
        $MSG = $TIPO.'_'.$MSG;

        if ($TIPO == 'ERRO')
            return print "<div class=\"flash error\" id=\"flash_error\">".$$MSG."</div>\n";

        if ($TIPO == 'INFO')
            return print "<div class=\"flash info\" id=\"flash_info\">".$$MSG."</div>\n";
                
	if ($TIPO == 'OK')
            return print "<div class=\"flash notice\" id=\"flash_notice\">".$$MSG."</div>\n";

	if ($TIPO == 'C_NOK')
            return print "<div class=\"flash error\" id=\"flash_error\">".$MSG."</div>\n";

	if ($TIPO == 'C_OK')
            return print "<div class=\"flash notice\" id=\"flash_notice\">".$MSG."</div>\n";
}
?>