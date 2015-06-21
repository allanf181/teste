<?php

if (!class_exists('database'))
    require_once PATH . INC . '/mysql.inc.php';

Class ldap {

    private $dominio, $user, $password, $basedn, $porta, $ldap_conn;

    public function __construct() {
        $bd = new database();
        $sql = "SELECT ldap_user, ldap_password, ldap_basedn, ldap_filter, "
                . "ldap_dominio, ldap_porta, ldap_cache "
                . "FROM Instituicoes";
        $res = $bd->selectDB($sql);

        $this->user = $res[0]['ldap_user'];
        $this->password = $res[0]['ldap_password'];
        $this->basedn = $res[0]['ldap_basedn'];
        $this->ldap_filter = $res[0]['ldap_filter'];
        $this->porta = $res[0]['ldap_porta'];
        $this->dominio = $res[0]['ldap_dominio'];
        $this->ldap_cache = $res[0]['ldap_cache'];

        $this->connect();
    }

    ///*Evita que a classe seja clonada*/
    private function __clone() {
        
    }

    public function __destruct() {
        $this->disconnect();
    }

    private function connect() {
        $this->ldap_conn = ldap_connect($this->dominio, $this->porta);

        ldap_set_option($this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap_conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->ldap_conn, LDAP_OPT_NETWORK_TIMEOUT, 4);
        ldap_set_option($this->ldap_conn, LDAP_OPT_TIMELIMIT, 4);

        if ($this->ldap_conn) {
            $ldapbind = ldap_bind($this->ldap_conn, $this->user, $this->password);
        } else {
            print "Sem conex&atilde;o com o LDAP: host/port " . ldap_error($this->ldap_conn);
        }
        return false;
    }

    private function disconnect() {
        ldap_close($this->ldap_conn);
    }

    public function changePassword($login, $user_password) {
        $user = "(" . $this->ldap_filter . "=$login)";
        $userDn = $this->getObjDN($user);

        $userdata = $this->pwd_encryption($user_password);
        $res = ldap_mod_replace($this->ldap_conn, $userDn, $userdata);

        $error = ldap_error($this->ldap_conn);

        if (preg_match('/Invalid DN syntax/', $error))
            return "Login n&atilde;o encontrado!!!";
        if (preg_match('/Server is unwilling to perform/', $error))
            return "Senha n&atilde;o atende aos requisitos do windows!!!";
        if (preg_match('/Success/', $error))
            return 1;
        else
            return $error;
    }

    private function pwd_encryption($newPassword) {
        //OPENLDAP
        if ($this->ldap_filter == 'sn' || $this->ldap_filter == 'cn') {
            return array('userpassword' => "{MD5}" . base64_encode(pack("H*", md5($newPassword))));
        }

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
        $params = array(':pront' => $user);
        $res = $bd->selectDB($sql, $params);

        if (!$res) {
            $sql = "INSERT INTO schema_ldap_cache VALUES (NULL, :pront, PASSWORD(:pass), NOW())";
        } else {
            $sql = "UPDATE schema_ldap_cache SET senha = PASSWORD(:pass), data = NOW() WHERE prontuario = :pront";
        }
        $params = array(':pront' => $user, ':pass' => $password);
        $res = $bd->insertDB($sql, $params);
    }

    public function autentica($user, $password) {
        // compare value
        $userA = "(" . $this->ldap_filter . "=$user)";

        $userDn = $this->getObjDN($userA);

        $r = ldap_bind($this->ldap_conn, $userDn, $password);

        if ($r === -1) {
            echo "Error LDAP: " . ldap_error($ds);
        } elseif ($r === true) {
            $this->setCache($user, $password);
            return true;
        } elseif ($r === false) {
            return false;
        }

        return false;
    }

    public function addUser($OU, $nome, $login) {

        $ou = "(name=$OU)";
        $ouDn = $this->getObjDN($ou);

        // INFO FOR OPENLDAP
        if ($this->ldap_filter == 'sn' || $this->ldap_filter == 'cn') {
            $info["cn"] = $nome;
            $info["sn"] = $login;
            $info["objectclass"] = "inetOrgPerson";
        } else {
            $info["instancetype"] = "4";
            $info["samaccountname"] = $login;
            $info["objectClass"] = array("top", "person", "organizationalPerson", "user");
            $info["cn"] = $nome;
            $info["useraccountcontrol"][0] = 544;
        }

        $res = ldap_add($this->ldap_conn, "CN=" . $info["cn"] . ",$ouDn", $info);
        $error = ldap_error($this->ldap_conn);

        if (preg_match('/No such object/', $error))
            return "OU nao encontrada";
        if (preg_match('/Already exists/', $error))
            return "Usuario ja existe";;
        if (preg_match('/Success/', $error))
            return 1;
        else
            return $error;
    }

    public function addOU($OU) {
        $addgroup_ad["objectClass"][0] = "top";
        $addgroup_ad["objectClass"][1] = "organizationalUnit";

        $res = ldap_add($this->ldap_conn, "OU=$OU,".$this->basedn, $addgroup_ad);
        $error = ldap_error($this->ldap_conn);

        if (preg_match('/Already exists/', $error))
            return "Essa OU ja existe";
        if (preg_match('/Success/', $error))
            return 1;
        else
            return $error;
    }

}

?>