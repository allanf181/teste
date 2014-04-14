<?php

if (!class_exists('database'))
    require_once MYSQL;

if (!class_exists('Disciplina'))
    require CONTROLLER . "/disciplina.class.php";


class permissao {

    public function __construct() {
        //
    }

    // NECESSITA REFATORAÇÃO
    public function listaPermissoes($codigo, $tipo = null) {
        $bd = new database();
        $sql = "SELECT nome,menu,permissao FROM Permissoes WHERE tipo IN (:cod)";
     
        $codigo = implode(',', $codigo);
        $params = array(':cod'=> $codigo);
        $res = $bd->selectDB($sql, $params);
        
        // Concatenando todas as permissões do usuário
        $P['nome']=null;
        $P['menu']=null;
        $P['permissao']=null;
        $vir=null;
        $j=0;
        
        foreach ($res as $reg) {
            if ($j)
                $vir = ',';
            $P['nome'] .= $vir . $res[0]['nome'];
            $P['menu'] .= $vir . $res[0]['menu'];
            $P['permissao'] .= $vir . $res[0]['permissao'];
            $j++;
        }

        // Tranformando em Array
        $P['nome'] = explode(",", $P['nome']);
        $P['menu'] = explode(",", $P['menu']);
        $P['permissao'] = explode(",", $P['permissao']);

        if ($tipo == 'permissao') return $P;

        // BUSCANDO A BASE DOS ARQUIVOS;
        $i = 0;
        $tree = array();
        foreach ($P['menu'] as $menu) {
            $menus[$menu] = $P['nome'][$i];
            $pathParts = explode('/', $menu);
            $pathParts[count($pathParts)-1] = $menu;
            $subTree = array(array_pop($pathParts));
            foreach (array_reverse($pathParts) as $dir) {
                $subTree = array($dir => $subTree);
            }
            $tree = array_merge_recursive($tree, $subTree);
            $i++;
        }
        if ($tipo == 'menu') return $menus;
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
            $arquivoNome = pathinfo($arquivo, PATHINFO_BASENAME);
            $getDescription = file($arquivo);
            $descricao = htmlentities(substr($getDescription[2], 2), ENT_COMPAT, 'UTF-8');
            return $descricao;
        } catch (Exception $erro) {
            return $erro;
        }
    }
}

?>