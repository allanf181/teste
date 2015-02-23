<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class QuestionariosQuestoesItens extends Generic {

    private $_tagAbertura;
    private $_tagFechamento;

    public function setTag($questao, $categoria, $nome, $valor, $obrigatorio = 'undefined') {
        switch ($categoria) {
            case '1'://Escolha de uma Lista = select
                $this->_tagAbertura = "<option value = '$valor' ";
                $this->_tagFechamento = ">$valor</option>";
                break;
            case '2'://Multipla Escolha = radio
                $this->_tagAbertura = "<input type = 'radio' name = '$nome' value = '$valor' class = '$obrigatorio'";
                $this->_tagFechamento = "/> $valor";
                break;
            case '3'://Multipla resposta = checkbox
                $nome .= '[]';
                $this->_tagAbertura = "<input type = 'checkbox' name = '$nome' value = '$valor' class = '$obrigatorio'";
                $this->_tagFechamento = "/> $valor";
                break;
        }
    }

    public function getTagAbertura() {
        return $this->_tagAbertura;
    }

    public function getTagFechamento() {
        return $this->_tagFechamento;
    }

    public function listQuestoesItens($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT * FROM QuestionariosQuestoesItens qqi  
    			WHERE qqi.questao = :questao
	 	 	 $sqlAdicional
                        ORDER BY qqi.codigo ";

        $sql .= "$nav";
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

}

?>
