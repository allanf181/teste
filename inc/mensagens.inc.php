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

//DIARIO
$QUESTION_DIARIO1 = "Sr. Professor<br />";
$QUESTION_DIARIO1.= "A data final do per&iacute;odo letivo foi atingida.";
$QUESTION_DIARIO1.=" Sendo assim, o seu di&aacute;rio foi bloqueado e somente o coordenador poder&aacute; desfazer esta opera&ccedil;&atilde;o.";
$QUESTION_DIARIO1.="<br /><br />Deseja finalizar seu di&aacute;rio, caso j&aacute; tenha conclu&iacute;do a digita&ccedil;&atilde;o de aulas a notas?";
$QUESTION_DIARIO1.="<br /><br />Aten&ccedil;&atilde;o: o di&aacute;rio ser&aacute; finalizado somente se todas as avalia&ccedil;&otilde;es forem aplicadas e todas as notas digitadas.";

$QUESTION_DIARIO2 = "Sr. Professor<br />";
$QUESTION_DIARIO2.= "O n&uacute;mero de aulas do di&aacute;rio est&aacute; completo e avalia&ccedil;&otilde;es foram aplicadas. <br /> <br />";
$QUESTION_DIARIO2.="<br>Deseja finalizar a digita&ccedil;&atilde;o do di&aacute;rio e efetuar a entrega &agrave; secretaria? <br /> <br />";
$QUESTION_DIARIO2.="Atenção: o seu diário será bloqueado e somente o coordenador poderá desfazer esta operação.";
$QUESTION_DIARIO2.="<br /><br />Aten&ccedil;&atilde;o: o di&aacute;rio ser&aacute; finalizado somente se todas as avalia&ccedil;&otilde;es forem aplicadas e todas as notas digitadas.";

$QUESTION_DIARIO3 ="<br>Deseja finalizar a digita&ccedil;&atilde;o do di&aacute;rio e efetuar a entrega &agrave; secretaria? <br /> <br />";
$QUESTION_DIARIO3.="Atenção: o seu diário será bloqueado e somente o coordenador poderá desfazer esta operação.";
$QUESTION_DIARIO3.="<br /><br />Aten&ccedil;&atilde;o: o di&aacute;rio ser&aacute; finalizado somente se todas as avalia&ccedil;&otilde;es forem aplicadas e todas as notas digitadas.";

function mensagem($TIPO, $MSG, $OPT = null) {

    $OK_DELETE = 'Registros deletados: ' . $OPT;
    $ERRO_DELETE = 'Problema ao apagar registro.';

    $OK_INSERT = 'Registro inserido com sucesso.';
    $ERRO_INSERT = 'Problema ao inserir registro.';

    $OK_UPDATE = 'Registros alterados: ' . $OPT;
    $ERRO_UPDATE = 'Problema ao alterar registro.';

    // AVISOS ANTIGOS, SERAO REMOVIDOS
    $OK_TRUE_DELETE = 'Registro deletado com sucesso.';
    $NOK_FALSE_DELETE = 'Problema ao apagar registro.';

    $OK_TRUE_INSERT = 'Registro inserido com sucesso.';
    $NOK_FALSE_INSERT = 'Problema ao inserir registro.';

    $OK_TRUE_UPDATE = 'Registro alterado com sucesso';
    $NOK_FALSE_UPDATE = 'Problema ao alterar registro.';

    $INFO_UPDATE = 'Esse registro já está cadastrado no banco de dados ou nenhum valor foi alterado.';

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
    $ERRO_SOLICITACAO_PLANO = $OPT[0] . ", solicitou corre&ccedil;&atilde;o em seu Plano: <br>" . $OPT[1];
    $OK_PLANO_VALIDO = $OPT . " validou seu plano!";

    $OK_TRUE_COPY_MODALIDADE = 'Modalidade copiada com sucesso.';
    $NOK_FALSE_COPY_MODALIDADE = 'Problema ao copiar a modalidade.';

    $OK_TRUE_COPY_PERMISSAO = 'Permiss&atilde;o copiada com sucesso.';
    $NOK_FALSE_COPY_PERMISSAO = 'Problema ao copiar a Permiss&atilde;o.';

    $NOK_FALSE_CLOSE_CLASS_REGISTRY = 'O di&aacute;rio n&atilde;o pode ser finalizado. '
            . 'Verifique se h&aacute; algum aluno sem nota ou se algum instrumento de avalia&ccedil;&atilde;o deixou de ser aplicado. '
            . '<br>O Sistema n&atilde;o ser&aacute; finalizado caso algum aluno esteja com situa&ccedil;&atilde;o pendente em Avalia&ccedil;&otilde;es!';

    $NOT_SELECT = "Nenhum registro selecionado.";

    $OK_VALID_FTD = $OPT . ' validou sua FTD.';
    $OK_FINISH_FTD = 'FTD finalizada, aguardando valiada&ccedil;&atilde;o do coordenador.';
    $INFO_INVALID_FTD = $OPT[0] . ', solicitou corre&ccedil;&atilde;o em seu FTD: <br>' . $OPT[1];
    $OK_NOT_SAVE_FTD = "Ol&aacute; Professor, como ainda n&atilde;o salvou sua FTD, o sistema tentou buscar seus hor&aacute;rios para facilitar.";

    $ERRO_ARQUIVO = $OPT;
    $INFO_ARQUIVO = $OPT;

    // LOGIN
    $ERRO_MANY_TRY = "Tentativas restantes: " . $OPT;
    $ERRO_USER_OR_PASS_INVALID = "Usu&aacute;rio ou senha inv&aacute;lidos.";
    $ERRO_CAPTCHA = "Caracetes inv&aacute;lidos. Tente novamente.</font>";
    $OK_EMAIL_ENVIADO = "As instru&ccedil;&otilde;es para recupera&ccedil;&atilde;o de senha foram enviadas para seu e-mail!";
    $ERRO_EMAIL_NAO_CADASTRADO = "E-mail n&atilde;o cadastrado ou prontu&aacute;rio n&atilde;o localizado.";
    $INFO_PRONTUARIO_VAZIO = "Por favor, digite o prontu&aacute;rio!";

    $INFO_LDAP_ATIVADO = "O autentica&ccedil;&atilde;o LDAP est&aacute; ativada, n&atilde;o &eacute; poss&iacute;vel trocar ou recuperar a senha no WebDi&aacute;rio. Troque a senha em seu sistema de origem ou procure o suporte do campus.";
    $ERRO_PASS_NOT_MATCH = 'Senha atual n&atilde;o confere';
    $ERRO_KEY_NOT_MATCH = 'Chave n&atilde;o confere ou est&aacute; inv&aacute;lida.';
    $ERRO_WRONG_CHARACTER = 'Caracteres inv&aacute;lidos. Tente novamente.';
    
    //DIARIO
    $OK_PRAZO_DIARIO = 'Registro inserido com sucesso. Aguarde a libera&ccedil;&atilde;o do coordenador.';
    $NOK_PRAZO_DIARIO = 'Problema na solicita&ccedil;&atilde;o. Tente novamente.';
    $INFO_STATUS_DIARIO_1 = "Este di&aacute;rio foi fechado pelo Coordenador!";
    $INFO_STATUS_DIARIO_2 = "Você j&aacute; finalizou este di&aacute;rio!";
    $INFO_STATUS_DIARIO_3 = "Este di&aacute;rio foi fechado pela Secretaria!";
    $INFO_STATUS_DIARIO_4 = "Este di&aacute;rio foi fechado pelo Sistema pois o prazo para finaliza&ccedil;&atilde;o do di&aacute;rio foi atingido!";
    $INFO_STATUS_DIARIO_100 = "Esse di&aacute;rio ainda n&atilde;o come&ccedil;ou!";
    $INFO_STATUS_DIARIO_101 = "Seu prazo para altera&ccedil;&atilde;o do di&aacute;rio foi estentido at&eacute; &agrave;s " . $OPT;

    $OK_EMAIL_SUGESTAO = "O e-mail foi enviado com sucesso. Obrigado por participar!!!";

    // CODIGOS DE ERROS - PDO/MYSQL
    if ($OPT) {
        if ($OPT == 23000 && ($MSG == 'UPDATE' || $MSG == 'INSERT')) {
            $MSG = 'UPDATE';
            $TIPO = 'INFO';
        }
        if ($OPT == 23000 && $MSG == 'DELETE') {
            $MSG = 'DELETE';
            $TIPO = 'INFO';
        }
    }

    $MSG = $TIPO . '_' . $MSG;

    if ($TIPO == 'ERRO')
        return print "<div class=\"flash error\" id=\"flash_error\">" . $$MSG . "</div>\n";

    if ($TIPO == 'INFO')
        return print "<div class=\"flash info\" id=\"flash_info\">" . $$MSG . "</div>\n";

    if ($TIPO == 'OK')
        return print "<div class=\"flash notice\" id=\"flash_notice\">" . $$MSG . "</div>\n";

    if ($TIPO == 'NOK')
        return print "<div class=\"flash error\" id=\"flash_error\">" . $$MSG . "</div>\n";

    if ($TIPO == 'C_NOK')
        return print "<div class=\"flash error\" id=\"flash_error\">" . $MSG . "</div>\n";

    if ($TIPO == 'C_OK')
        return print "<div class=\"flash notice\" id=\"flash_notice\">" . $MSG . "</div>\n";
}

?>