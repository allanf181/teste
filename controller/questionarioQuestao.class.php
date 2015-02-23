<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class QuestionariosQuestoes extends Generic {

    private $_tagAbertura;
    private $_tagFechamento;

    public function setTag($categoria, $nome, $obrigatorio = 'undefined') {
        switch ($categoria) {
            case '1'://Escolha de uma Lista = select
                $this->_tagAbertura = "<select name = '$nome' class = '$obrigatorio'>";
                $this->_tagFechamento = "</select>";
                break;
            case '2'://Multipla Escolha = radio
                $this->_tagAbertura = "";
                $this->_tagFechamento = "";
                break;
            case '3'://Multipla resposta = checkbox
                $this->_tagAbertura = "";
                $this->_tagFechamento = "";
                break;
            case '4'://Texto = text
                $this->_tagAbertura = "<input type = 'text' name = '$nome' class = '$obrigatorio'";
                $this->_tagFechamento = "/>";
                break;
            case '5'://Paragrafo = textarea
                $this->_tagAbertura = "<textarea rows = '6' cols = '25' name = '$nome' class = '$obrigatorio'>";
                $this->_tagFechamento = "</textarea>";
                break;
            case '6'://Data = text. O componente é da classe dt para utilizar no jQuery para adicionar datePicker
                $this->_tagAbertura = "<input type = 'text' name = '$nome' class = 'dt $obrigatorio'";
                $this->_tagFechamento = "/>";
                break;
            default:
                $this->_tagAbertura = "<input type = 'text' name = '$nome' class = '$obrigatorio'";
                $this->_tagFechamento = "/>";
                break;
        }
    }

    public function getTagAbertura() {
        return $this->_tagAbertura;
    }

    public function getTagFechamento() {
        return $this->_tagFechamento;
    }

    public function listQuestoes($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT qq.codigo, qc.nome as categoria, qq.nome as questaoNome,
                        qq.obrigatorio, qc.codigo as codCategoria
                        FROM QuestionariosQuestoes qq, QuestionariosCategorias qc
    			WHERE qq.categoria = qc.codigo
                        AND qq.questionario = :questionario
			$sqlAdicional ";

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
