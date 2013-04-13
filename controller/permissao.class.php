<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Permissoes extends Generic {

    public function __construct() {
        //
    }

    // NECESSITA REFATORAÇÃO
    public function listaPermissoes($codigo, $tipo = null) {
        $bd = new database();

        $i=0;
        foreach ($codigo as $value) {
            $indice = 'A'.$i;
            $new_array[$indice] = $value;
            $new_params[] = ':'.$indice;
            $i++;
        }
        $param = implode($new_params, ',');
        $params = $new_array;

        $sql = "SELECT nome,menu,permissao FROM Permissoes WHERE tipo IN ($param)";
        $res = $bd->selectDB($sql, $params);
        
        // Concatenando todas as permissões do usuário
        $P['nome']=null;
        $P['menu']=null;
        $P['permissao']=null;
        $vir=null;
        $j=0;
        
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