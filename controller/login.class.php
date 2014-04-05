<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

if (!class_exists('PessoasTipos'))
    require_once CONTROLLER . '/pessoaTipo.class.php';

if (!class_exists('Instituicoes'))
    require_once CONTROLLER . '/instituicao.class.php';

class login extends Generic {

    public function __construct() {
        
    }

    // MÉTODO PARA AUTENTICAÇÃO
    // USADO POR: VIEW/LOGIN.PHP
    public function autentica($prontuario, $senha, $LDAP_ATIVADO) {
        $bd = new database();

        $rs = null;
        if ($LDAP_ATIVADO) {
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
        }

        if (!$rs) { // SE NAO AUTENTICOU PELO LDAP, TENTA PELO BANCO.
            // SE ADMIN QUE ESTA USANDO OUTRO LOGIN
            if (strpos($prontuario, '#ADMIN') !== false) {
                $pront = explode('#', $prontuario);
                $sql = "SELECT codigo, nome, prontuario, email, dataSenha, senha"
                        . " FROM Pessoas"
                        . " WHERE prontuario=:pront1"
                        . " AND 'admin' = ( SELECT p1.prontuario FROM Pessoas p1 "
                        . "WHERE p1.prontuario = :pront2 AND senha = PASSWORD(:senha) )";
                $params = array(':pront1' => $pront[0], ':pront2' => $pront[1], ':senha' => $senha);
            } else {
                $sql = "SELECT codigo, nome, prontuario, email, dataSenha, senha"
                        . " FROM Pessoas"
                        . " WHERE prontuario=:prontuario"
                        . " AND senha=PASSWORD(:senha)";
                $params = array(':prontuario' => $prontuario, ':senha' => $senha);
            }
        } else { // SE AUTENTICOU PELO LDAP, PEGA OS DADOS PARA A SESSAO.
            $sql = "SELECT codigo, nome, prontuario, email, dataSenha, senha"
                    . " FROM Pessoas"
                    . " WHERE prontuario=:prontuario";
            $params = array(':prontuario' => $prontuario);
        }
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            $pessoa = new PessoasTipos();

            $_SESSION["loginCodigo"] = $res[0]['codigo'];
            $_SESSION["loginNome"] = $res[0]['nome'];
            $_SESSION["loginTipo"] = $pessoa->getTipoPessoa($res[0]['codigo']);
            $_SESSION["loginProntuario"] = $res[0]['prontuario'];
            $_SESSION["loginPassword"] = crip($res[0]['senha']);
            $_SESSION["loginEmail"] = $res[0]['email'];
            $_SESSION["loginDataSenha"] = $res[0]['dataSenha'];
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
            $chv = 'https://' . $_SERVER['HTTP_HOST'] . '/academico/index.php?est=' . $chave . '&p=' . $prontuario;

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

                $instituicao = new Instituicoes();
                if ($instituicao->sendEmail($res[0]['email'], $assunto, $mensagem, $headers)) {
                    return true;
                }
            }
        }
        return false;
    }

    // MÉTODO PARA ALTERAÇÃO DE SENHA
    // USADO POR: VIEW/SENHA.PHP
    public function alteraSenha($prontuario, $senha, $senhaNova, $chave) {
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
                return true;
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

        if ($res[0]) {
            return true;
        } else {
            return false;
        }
    }

}

?>