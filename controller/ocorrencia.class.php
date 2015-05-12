<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class Ocorrencias extends Generic {

    public function __construct() {
        //
    }
    
    public function insertOrUpdateOcorrencias($POST) {
        $params['codigo'] = $POST['codigo'];
        $params['descricao'] = $POST['descricao'];
        $params['data'] = $POST['data'];
        $params['registroPor'] = $POST['registroPor'];
        $to = explode(',', $POST['to']);

        $res = 0;
        foreach ($to as $dest) {
            $params['aluno'] = 'NULL';

            if (substr($dest, 0, 2) == 'P:')
                $params['aluno'] = crip(substr($dest, 2));

            if ($this->insertOrUpdate($params))
                $res++;
        }

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    public function listOcorrencias($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT o.codigo, p.nome as aluno, p1.nome as registroPor, p.codigo as codAluno,
                        date_format(o.data, '%d/%m/%Y %H:%i') as dataFormat,o.descricao,
                        (SELECT COUNT(*) FROM OcorrenciasInteracoes i WHERE i.ocorrencia = o.codigo) as interacao
                        FROM Ocorrencias o, Pessoas p, Pessoas p1
                        WHERE o.aluno = p.codigo
                        AND o.registroPor = p1.codigo";

        $sql .= " $sqlAdicional ";

        $sql .= "$nav";
        
        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        else
            return false;
    }
    
    public function countOcorrencias($aluno) {
        $bd = new database();
        $sql = "SELECT COUNT(*) as total
                        FROM Ocorrencias o, Pessoas p
                        WHERE o.aluno = p.codigo
                        AND p.codigo = :aluno";

        $params = array('aluno' => $aluno);
        $res = $bd->selectDB($sql, $params);

        if ($res[0]['total'])
            return $res[0]['total'];
        else
            return false;
    }
}

?>