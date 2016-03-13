<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class Permissoes extends Generic {

    public function __construct() {
        //
    }

    // NECESSITA REFATORAÇÃO
    // Entrada: Array de Permissões
    public function listaPermissoes($codigo, $tipo = null) {
        $bd = new database();

        $i = 0;
        foreach ($codigo as $value) {
            $indice = 'A' . $i;
            $new_array[$indice] = $value;
            $new_params[] = ':' . $indice;
            $i++;
        }
        $param = implode($new_params, ',');
        $params = $new_array;

        $sql = "SELECT codigo,nome,menu,permissao FROM PermissoesArquivos WHERE tipo IN ($param)";
        $res = $bd->selectDB($sql, $params);

        // Concatenando todas as permissões do usuário
        $P['codigo'] = null;
        $P['nome'] = null;
        $P['menu'] = null;
        $P['permissao'] = null;
        $vir = null;
        $j = 0;

        $P['nome'] = null;
        $P['menu'] = null;
        $P['permissao'] = null;

        // CONCATENANDO AS PERMISSOES CASO O USUARIO
        // TENHA MAIS DE UM TIPO
        foreach ($res as $reg) {
            if ($j)
                $vir = ',';
            $P['codigo'] .= $vir . $reg['codigo'];
            $P['nome'] .= $vir . $reg['nome'];
            $P['menu'] .= $vir . $reg['menu'];
            $P['permissao'] .= $vir . $reg['permissao'];
            $j++;
        }

        // Tranformando em Array
        $P['codigo'] = explode(",", $P['codigo']);
        $P['nome'] = explode(",", $P['nome']);
        $P['menu'] = explode(",", $P['menu']);
        $P['permissao'] = explode(",", $P['permissao']);

        // ARMAZENANDO O NOME DO MENU, ANTES DA ORDENAÇÃO
        // PARA NÃO PERDER OS ÍNDICES
        $i = 0;
        foreach ($P['menu'] as $menu) {
            if (!array_key_exists($menu, $menus)) {
                $menus[$menu] = $P['nome'][$i];
            }
            $i++;
        }

        // Pegando o codigo em caso de alteracao de alguma permissao
//        if ($res)
//            $P['codigo'] = $res[0]['codigo'];

        if ($tipo == 'permissao')
            return $P;

        // BUSCANDO A BASE DOS ARQUIVOS;
        // ORDERNANDO O MENU   
        $P['menu'] = array_unique($P['menu']);
        array_multisort($P['menu'], SORT_ASC, SORT_STRING, $P['menu'], SORT_NUMERIC, SORT_DESC);

        $i = 0;
        $tree = array();
        foreach ($P['menu'] as $menu) {
            $pathParts = explode('/', $menu);
            $pathParts[count($pathParts) - 1] = $menu;
            $subTree = array(array_pop($pathParts));
            foreach (array_reverse($pathParts) as $dir) {
                $subTree = array($dir => $subTree);
            }
            $tree = array_merge_recursive($tree, $subTree);
            $i++;
        }

        if ($tipo == 'menu')
            return array('arvore' => $tree, 'nome' => $menus);

        return $tree;
    }

    // CHECA SE O USUARIO TEM PERMISSAO PARA ACESSAR O ARQUIVO
    // RETORNA O TITULO DO SITE
    // USADA EM INC/PERMISSAO.PHP
    public function isAllowed($codigo, $arquivo) {
        $files = $this->listaPermissoes($codigo, 'permissao');
        if (in_array($arquivo, $files['permissao'])) {
            $siteTitulo = html_entity_decode($files['nome'][array_search($arquivo, $files['permissao'])], ENT_COMPAT, 'UTF-8');
            if ($siteTitulo)
                return $siteTitulo;
            else
                return "IFSP";
        } else {
            return false;
        }
    }

    // RETORNO A DESCRICAO DO ARQUIVO
    // UTILIZADO EM TODAS AS VIEWS
    // UTILIZADO EM ADMIN/PERMISSAO.PHP
    public function fileDescricao($arquivo) {
        try {
            $descricao['nome'] = pathinfo($arquivo, PATHINFO_FILENAME);
            $descricao['extensao'] = pathinfo($arquivo, PATHINFO_EXTENSION);
            $getDescription = file(PATH . LOCATION . "/$arquivo");
            $descricao['descricaoArquivo'] = htmlentities(substr($getDescription[2], 2), ENT_COMPAT, 'UTF-8');
            $descricao['descricaoLink'] = htmlentities(substr($getDescription[3], 2), ENT_COMPAT, 'UTF-8');
            $descricao['lista'] = trim(substr($getDescription[5], 2));
            return $descricao;
        } catch (Exception $erro) {
            return $erro;
        }
    }

    // REPLICA AS PERMISSOES DE UM TIPO PARA OUTRO
    public function copyTipo($params) {
        $bd = new database();

        $params = dcripArray($params);
        $sql = "SELECT nome,menu,permissao,(SELECT codigo FROM PermissoesArquivos WHERE tipo = :codigo) as codigo"
                . " FROM PermissoesArquivos WHERE tipo = :tipo";

        $res = $bd->selectDB($sql, $params);
        
        foreach ($res as $v) {
            $paramsInsert = array(
                            'codigo'=>0,
                            'tipo'=>$params['codigo'],
                            'permissao'=>$v['permissao'],
                            'menu'=>$v['menu'],
                            'nome'=>$v['nome']
                        );           
            $sql = "INSERT INTO PermissoesArquivos values(:codigo,:tipo,:permissao,:nome,:menu)";
            $ret = $bd->insertDB($sql, $paramsInsert);
        }
    }
    
    public function addPermissao($params){
        $bd = new database();

        $params = dcripArray($params);
        $codigos = explode(",", $params['codigo']);
        $permissoes = explode(",", $params['permissao']);
        $menus = explode(",", $params['menu']);
        $nomes = explode(",", $params['nome']);

        $sql = "DELETE FROM PermissoesArquivos WHERE tipo=:tipo";
        $bd->deleteDB($sql, array('tipo' => $params['tipo']));

        foreach ($permissoes as $i => $v) {
            $paramsInsert = array(
                            'codigo'=>$codigos[$i],
                            'tipo'=>$params['tipo'],
                            'permissao'=>$permissoes[$i],
                            'menu'=>$menus[$i],
                            'nome'=>$nomes[$i]
                        );           
            $sql = "INSERT INTO PermissoesArquivos values(:codigo,:tipo,:permissao,:nome,:menu)";
            $ret = $bd->insertDB($sql, $paramsInsert);
            $paramsInsert=null;
        }
    }

}

?>