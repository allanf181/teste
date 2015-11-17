<?php

//if (!class_exists('Doctrine'))
//    require_once DOCTRINE;

// o Autoload é responsável por carregar as classes sem necessidade de incluí-las previamente
require_once PATH.LIB."/doctrine/vendor/autoload.php";
 
// o Doctrine utiliza namespaces em sua estrutura, por isto estes uses
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

Abstract class Dao {
    
    protected $em;
    protected $repo;

    public function __construct() {
        //onde irão ficar as entidades do projeto? Defina o caminho aqui
        $entidades = array("../model/");
        $isDevMode = true;

        // configurações de conexão. Coloque aqui os seus dados
        $dbParams = array(
            'driver'   => 'pdo_mysql',
            'user'     => MY_USER,
            'password' => MY_PASS,
            'dbname'   => MY_DB,
            'charset'  => 'utf8',
        );

        //setando as configurações definidas anteriormente
        $config = Setup::createAnnotationMetadataConfiguration($entidades, $isDevMode);

        //criando o Entity Manager com base nas configurações de dev e banco de dados
        $this->em = EntityManager::create($dbParams, $config);
        
        $table = get_called_class();
//        var_dump(substr($table, 0, -3));
        $this->repo = $this->em->getRepository(substr($table, 0, -3));
//        var_dump($this->repo->findAll());

    }
    
    // MÉTODO PARA INSERÇÃO DE OBJETO
    public function insertOrUpdate($objeto) {
        try{
            
//            var_dump($objeto);
            
            // decriptografa elementos que possam
        // estar criptografados dentro do Array
//        $params = dcripArray($params);
//        foreach (array_keys($params) as $key) {
//            if ($key == 'codigo') {
//                $INS[] = 'NULL';
//                $COL[] = $key;
//            } else {
//                $COL[] = $key;
//
//                if ($key == 'senha') {
//                    $INS[] = 'PASSWORD(:' . $key . ')';
//                    $UP[] = $key . '=PASSWORD(:' . $key . ')';
//                } else if ($params[$key] == 'NULL') {
//                    $INS[] = 'NULL';
//                    $UP[] = $key . '=NULL';
//                    unset($params[$key]);
//                } else if ($params[$key] == 'NOW()') {
//                    $INS[] = 'NOW()';
//                    $UP[] = $key . '=NOW()';
//                    unset($params[$key]);
//                } else if ($params[$key] == '--') { //datas vazias
//                    $INS[] = 'NULL';
//                    $UP[] = $key . '=NULL';
//                    unset($params[$key]);
//                } else {
//                    $params[$key] = xss_clean($params[$key]);
//                    $INS[] = ':' . $key;
//                    $UP[] = $key . '=:' . $key;
//                }
//            }
//        }
//
//        $INS = implode(',', $INS);
//        $COL = implode(',', $COL);
//        $UP = implode(',', $UP);

        
            $res = array('STATUS'=>'OK','TIPO'=>'INSERT');
            if ($objeto->getId())
                $res = array('STATUS'=>'OK','TIPO'=>'UPDATE');
            $this->em->persist($objeto);
            $this->em->flush();
            return $res;
        }
        catch(Exception $e){
            return false;
        }        
    }   
    
    public function listRegistros($ordem=null, $item=null, $itensPorPagina=null){
        if ($ordem)
            $dados = $this->repo->findBy(array(), array($ordem => 'ASC')); // RETORNA OS ITENS ORDENADOS
        else
            $dados = $this->repo->findAll();
        if ($item)
            $dados = array_slice ($dados, $item, $itensPorPagina); // PAGINACAO
        return $dados;
    }
    
    public function get($codigo){
//        var_dump($codigo);
//        die;
        $dados = $this->repo->find(dcrip($codigo)); // RETORNA O ITEM ESPECÍFICO
//        var_dump($dados);
//        die;
        return $dados;
    }
    
    public function count() {
        $res = $this->repo->findAll();
        if ($res) {
            return count($res);
        } else {
            return false;
        }
    }

    public function delete($codigo) {
        $codigo = explode(",", $codigo);
        $n=0;
        foreach ($codigo as $c) {
            try{            
                $this->em->remove($this->repo->find(dcrip($c)));
                $this->em->flush();
                $n++;
            }
            catch(Exception $e){
                return array('STATUS'=>'ERRO','TIPO'=>'DELETE');
            }       
        }
        return array('STATUS'=>'OK','TIPO'=>'DELETE', 'RESULTADO'=>count($codigo));
//        return $codigo;
//        $bd = new database();
//        $table = get_called_class();
//
//        // PDO NÃO ACEITA VÁRIOS ARGUMENTOS PARA DELETE
//        // É NECESSÁRIO PREPARAR A QUERY
//        // OBS: NÃO FOI FEITO DIRETO PARA NÃO COMPROMETER
//        // A SEGURANÇA FORNECIDA PELO PDO CONTRA SQLInjection
//        $codigo = explode(',', $codigo);
//
//        if ($codigo[0] == '0')
//            unset($codigo[0]);
//
//        $i = 0;
//        foreach ($codigo as $value) {
//            $indice = 'A' . $i;
//            $new_array[$indice] = dcrip($value);
//            $new_params[] = ':' . $indice;
//            $i++;
//        }
//        $param = implode($new_params, ',');
//
//        $sql = "DELETE FROM $table WHERE codigo IN ($param)";
//
//        $params = $new_array;
//        $res = $bd->deleteDB($sql, $params);
//
//        if ($res) {
//            return $res;
//        } else {
//            return false;
//        }
    }

}

?>