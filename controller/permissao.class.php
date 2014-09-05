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

        $sql = "SELECT codigo,nome,menu,permissao FROM Permissoes WHERE tipo IN ($param)";
        $res = $bd->selectDB($sql, $params);

        // Concatenando todas as permissões do usuário
        $P['nome'] = null;
        $P['menu'] = null;
        $P['permissao'] = null;
        $vir = null;
        $j = 0;

        $P['nome'] = null;
        $P['menu'] = null;
        $P['permissao'] = null;

        foreach ($res as $reg) {
            if ($j)
                $vir = ',';
            $P['nome'] .= $vir . $reg['nome'];
            $P['menu'] .= $vir . $reg['menu'];
            $P['permissao'] .= $vir . $reg['permissao'];
            $j++;
        }

        // Tranformando em Array
        $P['nome'] = explode(",", $P['nome']);
        $P['menu'] = explode(",", $P['menu']);
        $P['permissao'] = explode(",", $P['permissao']);

        // Pegando o codigo em caso de alteracao de alguma permissao
        if ($res)
            $P['codigo'] = $res[0]['codigo'];

        if ($tipo == 'permissao')
            return $P;

        // BUSCANDO A BASE DOS ARQUIVOS;
        $i = 0;
        $tree = array();
        $menus = array();
        foreach ($P['menu'] as $menu) {
            if (!array_key_exists($menu, $menus)) {
                $menus[$menu] = $P['nome'][$i];

                $pathParts = explode('/', $menu);
                $pathParts[count($pathParts) - 1] = $menu;
                $subTree = array(array_pop($pathParts));
                foreach (array_reverse($pathParts) as $dir) {
                    $subTree = array($dir => $subTree);
                }
                $tree = array_merge_recursive($tree, $subTree);
            }
            $i++;            
        }

        if ($tipo == 'menu')
            return $menus;
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

        $sql = "SELECT nome,menu,permissao,(SELECT codigo FROM Permissoes WHERE tipo = :codigo) as codigo"
                . " FROM Permissoes WHERE tipo = :tipo";

        $res = $bd->selectDB($sql, $params);

        $params1['codigo'] = $res[0]['codigo'];
        $params1['permissao'] = $res[0]['permissao'];
        $params1['nome'] = $res[0]['nome'];
        $params1['menu'] = $res[0]['menu'];
        $params1['tipo'] = $params['codigo'];

        if ($res[0])
            $res = $this->insertOrUpdate($params1);
    }

}

?>