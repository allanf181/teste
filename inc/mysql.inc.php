<?php

Class database {
    /* Método construtor do banco de dados */

    public function __construct() {
        
    }

    ///*Evita que a classe seja clonada*/
    private function __clone() {
        
    }

    /* Método que destroi a conexão com banco de dados e remove da memória todas as variáveis setadas */

    public function __destruct() {
        $this->disconnect();
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    private static $dbtype = "mysql";
    private static $host = MY_HOST;
    private static $port = MY_PORT;
    private static $user = MY_USER;
    private static $password = MY_PASS;
    private static $db = MY_DB;

    /* Metodos que trazem o conteudo da variavel desejada
      @return   $xxx = conteudo da variavel solicitada */

    private function getDBType() {
        return self::$dbtype;
    }

    private function getHost() {
        return self::$host;
    }

    private function getPort() {
        return self::$port;
    }

    private function getUser() {
        return self::$user;
    }

    private function getPassword() {
        return self::$password;
    }

    private function getDB() {
        return self::$db;
    }

    private function connect() {
        try {
            $this->conexao = new PDO($this->getDBType() .
                    ":host=" . $this->getHost() .
                    ";port=" . $this->getPort() .
                    ";dbname=" . $this->getDB(), $this->getUser(), $this->getPassword(), array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ));
        } catch (PDOException $i) {
            //se houver exceção, exibe
            die("Erro: <code>" . $i->getMessage() . "</code>");
        }

        return ($this->conexao);
    }

    private function disconnect() {
        $this->conexao = null;
    }

    /* Método select que retorna um VO ou um array de objetos */

    public function selectDB($sql, $params = null, $class = null) {
        try {
            $query = $this->connect()->prepare($sql);
            $query->execute($params);

            if (isset($class)) {
                $rs = $query->fetchAll(PDO::FETCH_CLASS, $class) or die(print_r($query->errorInfo(), true));
            } else {
                $rs = $query->fetchAll();
            }
            self::__destruct();
            return $rs;
        } catch (PDOException $Exception) {
            print $Exception->getMessage();
            return false;
        }
    }

    /* Método insert que insere valores no banco de dados e retorna o último id inserido */

    public function insertDB($sql, $params = null) {
        // CONTROLE - MENSAGENS
        $rs = array();
        $rs['TIPO'] = 'INSERT';

        try {
            $conexao = $this->connect();
            $query = $conexao->prepare($sql);
            $query->execute($params);
            $rs['RESULTADO'] = $conexao->lastInsertId();
            $rs['STATUS'] = 'OK';
            self::__destruct();
            return $rs;
        } catch (PDOException $Exception) {
            $rs['STATUS'] = 'ERRO';
            $rs['RESULTADO'] = $Exception->getCode();
            return $rs;
        }
    }

    /* Método update que altera valores do banco de dados e retorna o número de linhas afetadas */

    public function updateDB($sql, $params = null) {
        // CONTROLE - MENSAGENS
        $rs = array();
        $rs['TIPO'] = 'UPDATE';
        try {
            $query = $this->connect()->prepare($sql);
            $query->execute($params);
            $rs['RESULTADO'] = $query->rowCount();
            self::__destruct();

            if ($rs['RESULTADO'])
                $rs['STATUS'] = 'OK';
            else
                $rs['STATUS'] = 'INFO';

            return $rs;
        } catch (PDOException $Exception) {
            $rs['STATUS'] = 'ERRO';
            $rs['RESULTADO'] = $Exception->getCode();
            return $rs;
        }
    }

    /* Método delete que excluí valores do banco de dados retorna o número de linhas afetadas */

    public function deleteDB($sql, $params = null) {
        // CONTROLE - MENSAGENS
        $rs = array();
        $rs['TIPO'] = 'DELETE';

        try {
            $query = $this->connect()->prepare($sql);
            $query->execute($params);
            $rs['RESULTADO'] = $query->rowCount();
            $rs['STATUS'] = 'OK';
            self::__destruct();
            return $rs;
        } catch (PDOException $Exception) {
            $rs['STATUS'] = 'ERRO';
            $rs['RESULTADO'] = $Exception->getCode();
            return $rs;
        }
    }

    public function backup($db) {
        $conexao = $this->connect();

        $conexao->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_NATURAL);

        header("Content-disposition: filename=banco".$db . date('YMdHi') . ".sql.gz");
        header("Content-type: gzip");
        header("Pragma: no-cache");
        header("Expires: 0");

        $dados= "set @@foreign_key_checks=0; \n\n";
        
        $numtypes = array('tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'float', 'double', 'decimal', 'real');

        $pstm1 = $conexao->query('SHOW TABLES');
        while ($row = $pstm1->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            $result = $conexao->query("SELECT * FROM $table");
            $num_fields = $result->columnCount();
            $num_rows = $result->rowCount();

            $return = "";

            $pstm2 = $conexao->query("SHOW CREATE TABLE $table");
            $row2 = $pstm2->fetch(PDO::FETCH_NUM);
            $ifnotexists = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $row2[1]);
            $return.= "\n\n" . $ifnotexists . ";\n\n";

            $dados.=$return;

            $return = "";

            if ($num_rows) {
                $return = 'INSERT INTO `' . "$table" . "` (";
                $pstm3 = $conexao->query("SHOW COLUMNS FROM $table");
                $count = 0;
                $type = array();

                while ($rows = $pstm3->fetch(PDO::FETCH_NUM)) {

                    if (stripos($rows[1], '(')) {
                        $type[$table][] = stristr($rows[1], '(', true);
                    } else
                        $type[$table][] = $rows[1];

                    $return.= "`" . $rows[0] . "`";
                    $count++;
                    if ($count < ($pstm3->rowCount())) {
                        $return.= ", ";
                    }
                }

                $return.= ")" . ' VALUES';

                $dados.=$return;
                $return = "";
            }
            $count = 0;
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $return = "\n\t(";

                for ($j = 0; $j < $num_fields; $j++) {


                    if (isset($row[$j])) {

                        if ((in_array($type[$table][$j], $numtypes)) && (!empty($row[$j])))
                            $return.= $row[$j];
                        else
                            $return.= $conexao->quote($row[$j]);
                    } else {
                        $return.= 'NULL';
                    }
                    if ($j < ($num_fields - 1)) {
                        $return.= ',';
                    }
                }
                $count++;
                if ($count < ($result->rowCount())) {
                    $return.= "),";
                } else {
                    $return.= ");";
                }

                $dados.=$return;
                $return = "";
            }
            $return = "\n\n-- ------------------------------------------------ \n\n";

            $dados.=$return;
            $return = "";
        }

        $error1 = $pstm2->errorInfo();
        $error2 = $pstm3->errorInfo();
        $error3 = $result->errorInfo();
        $dados.= $error1[2];
        $dados.= $error2[2];
        $dados.= $error3[2];
        
        // COMPACTANDO DADOS DE BACKUP
        print gzencode($dados,9);
        
        die;
    }

}

?>