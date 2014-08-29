<?php

if (!class_exists('FTDHorarios'))
    require_once CONTROLLER . '/ftdHorario.class.php';

if (!class_exists('Pessoas'))
    require_once CONTROLLER . '/pessoa.class.php';

class FTDDados extends FTDHorarios {

    public function __construct() {
        //
    }

    // USADO POR: PROFESSOR/FTD.PHP
    public function insertOrUpdateFTD($params) {
        // PARA OS HORARIOS
        $dte = explode(',', $params["dte"]);
        $dts = explode(',', $params["dts"]);

        unset($params["dte"]);
        unset($params["dts"]);

        if ($params["tipo"]) {
            $params["finalizado"] = date("Y-m-d H:i:s");
            $params["solicitacao"] = '';
        }

        unset($params["tipo"]);
        unset($params["_"]);

        $res = $this->insertOrUpdate($params);

        if (!$params["codigo"])
            $params["codigo"] = $res['RESULTADO'];

        // APROVEITA E ATUALIZA OS DADOS DO PROFESSOR NA TABELA PESSOAS
        $pessoa['telefone'] = $params["telefone"];
        $pessoa['celular'] = $params["celular"];
        $pessoa['email'] = $params["email"];
        $pessoa['codigo'] = $params["professor"];

        $p = new Pessoas();
        $p->insertOrUpdate($pessoa);

        $res['TIPO'] = 'UPDATE';
        $res['STATUS'] = 'OK';
        $res['RESULTADO'] = '1';
        
        if ($params["codigo"]) {
            $params["dte"] = $dte;
            $params["dts"] = $dts;
        
            if (!$this->insertFTDHorario($params)) {
                return $res;
            } else {
                return false;
            }
        }
    }

    // USADO POR: ATENDIMENTO.PHP
    // Retorna os horário de atendimento ao aluno
    // que o professor cadastrou na FTD
    public function getAtendimentoAluno($codigo, $ano, $semestre) {
        $bd = new database();

        $sql = "SELECT horario, 
		SUBSTR( registro, 3, 1 ) AS diaSemana, 
		SUBSTR( registro, 4, 1 ) AS ES
		FROM FTDHorarios fh, FTDDados fd
		WHERE fd.codigo = fh.ftd
		AND SUBSTR( registro, 2, 1 ) = 4
		AND fd.ano = :ano 
		AND fd.semestre = :sem
		AND fd.professor = :cod";
        $params = array(':cod' => $codigo, ':ano' => $ano, ':sem' => $semestre);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            foreach ($res as $reg) {
                $new_res[$reg['diaSemana']][$reg['ES']] = $reg['horario'];
            }
            return $new_res;
        } else {
            return false;
        }
    }

    // USADO POR: HOME.PHP
    // Verifica se o usuário tem correções para FTD
    // Pode ser colocado com função no MySQL futuramente
    public function hasChangeFtd($codigo, $ano, $semestre) {
        $bd = new database();
        $sql = "SELECT (SELECT nome FROM Pessoas "
                . "WHERE codigo = fd.solicitante) as ftdSolicitante, "
                . "fd.solicitacao as ftdSolicitacao "
                . "FROM FTDDados fd "
                . "WHERE fd.ano = :ano "
                . "AND fd.semestre = :sem "
                . "AND fd.valido = '0000-00-00 00:00:00' "
                . "AND fd.professor = :cod";
        $params = array(':cod' => $codigo, ':ano' => $ano, ':sem' => $semestre);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res[0];
        } else {
            return false;
        }
    }


    // USADO POR: PROFESSOR/FTD.PHP
    public function getDadosFTD($codigo, $ano, $semestre) {
        $bd = new database();
        
        $sql = "SELECT fh.registro, fh.horario, fd.observacao,
                    fd.finalizado, fd.solicitacao, fd.telefone, fd.celular,
                    fd.email, fd.area, fd.regime, fd.codigo,
                    date_format(fd.valido, '%d/%m/%Y %H:%i') as valido,
                    (SELECT nome FROM Pessoas WHERE codigo = fd.solicitante) as solicitante
		FROM FTDDados fd, FTDHorarios fh
		WHERE fd.codigo = fh.ftd
		AND fd.ano = :ano 
		AND fd.semestre = :sem
		AND fd.professor = :cod";
                
        $params = array(':cod' => $codigo, ':ano' => $ano, ':sem' => $semestre);
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }    
}

?>