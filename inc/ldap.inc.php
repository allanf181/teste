<?php
if(!class_exists('database'))
    require_once CONTROLLER.'/mysql.class.php';

Class ldap {

    private $dominio, $user, $password, $basedn, $porta, $ldap_conn;

    public function __construct() {
        $bd = new database();
        $sql = "SELECT ldap_user, ldap_password, ldap_basedn, "
                . "ldap_dominio, ldap_porta, ldap_cache "
                . "FROM Instituicoes";
        $res = $bd->selectDB($sql);
    
        $this->user = $res[0]['ldap_user'];
        $this->password = $res[0]['ldap_password'];;
        $this->basedn = $res[0]['ldap_basedn'];
        $this->porta = $res[0]['ldap_porta'];
        $this->dominio = $res[0]['ldap_dominio'];
        $this->ldap_cache = $res[0]['ldap_cache'];

        $this->connect();
    }

    ///*Evita que a classe seja clonada*/
    private function __clone() {}

    public function __destruct() {
        $this->disconnect();
    }

    private function connect() {
        $this->ldap_conn = ldap_connect($this->dominio, $this->porta);

        ldap_set_option($this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap_conn, LDAP_OPT_REFERRALS, 0);

        if ($this->ldap_conn) {
            $ldapbind = ldap_bind($this->ldap_conn, $this->user, $this->password);
        } else {
            print "Sem conexao com o LDAP: host/port " . ldap_error($this->ldap_conn);
        }

        if ($ldapbind) {
            return $this->ldap_conn;
        } else {
            print "Sem conexao com o LDAP: bind " . ldap_error($this->ldap_conn);
        }
        return false;
    }

    private function disconnect() {
        ldap_close($this->ldap_conn);
    }

    public function changePassword($login, $user_password) {
        $user = "(sAMAccountName=$login)";
        $ldap_conn = create_ldap_conn_ssl();
        $userDn = get_obj_dn($ldap_conn, $user);
        $userdata = $this->pwd_encryption($user_password);

        $res = @ldap_mod_replace($ldap_conn, $userDn, $userdata);
        $error = ldap_error($ldap_conn);
        ldap_close($ldap_conn);

        if (preg_match('/Invalid DN syntax/', $error))
            return "Login nao encontrado";
        if (preg_match('/Server is unwilling to perform/', $error))
            return "Senha nao atende aos requisitos do windows";
        if (preg_match('/Success/', $error))
            return 1;
        else
            return $error;
    }

    private function pwd_encryption($newPassword) {
        $newPassword = "\"" . $newPassword . "\"";
        $len = strlen($newPassword);
        $newPassw = "";
        for ($i = 0; $i < $len; $i++) {
            $newPassw .= "{$newPassword{$i}}\000";
        }
        $userdata["unicodePwd"] = $newPassw;
        return $userdata;
    }

    private function getObjDN($obj) {
        $searchResults = ldap_search($this->ldap_conn, $this->basedn, $obj);
        if (!is_resource($searchResults))
            return 0;

        $entry = ldap_first_entry($this->ldap_conn, $searchResults);
        if (!is_resource($entry))
            return 0;

        $res = ldap_get_dn($this->ldap_conn, $entry);

        return $res;
    }

    private function setCache($user, $password) {
        $bd = new database();
        $sql = "SELECT codigo FROM schema_ldap_cache WHERE prontuario = :pront";
        $params = array(':pront'=> $user);
        $res = $bd->selectDB($sql, $params);
        
        if (!$res) {
            $sql = "INSERT INTO schema_ldap_cache VALUES (NULL, :pront, PASSWORD(:pass), NOW())";
        } else {
            $sql = "UPDATE schema_ldap_cache SET senha = PASSWORD(:pass), data = NOW() WHERE prontuario = :pront";
        }
        $params = array(':pront'=> $user,':pass'=> $password);
        $res = $bd->insertDB($sql, $params);
    }
    
    public function autentica($user, $password) {
        // compare value
        $userA = "(sAMAccountName=$user)";

        $userDn = $this->getObjDN($userA);
        
        $r = ldap_bind($this->ldap_conn, $userDn, $password);

        if ($r === -1) {
            echo "Error: " . ldap_error($ds);
        } elseif ($r === true) {
            $this->setCache($user, $password);
            return true;
        } elseif ($r === false) {
            return false;
        }

        return false;
    }

}

?>