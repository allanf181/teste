<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

if (!class_exists('Pessoas'))
    require_once CONTROLLER . '/pessoa.class.php';

class TDDados extends Generic {

    public function __construct() {
        //
    }

    public function listFPA($params=null, $item=null, $itensPorPagina=null, $sqlAdicional=null) {
        $bd = new database();
        
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina ";
        
        $sql = "SELECT f.codigo,f.ano,f.semestre,f.apelido,f.area,f.regime,f.duracaoAula,f.dedicarEnsino,"
                . "f.subHorario,f.horario1,f.horario2,f.horario3,f.horario,p.nome,"
                . "p.prontuario,p.telefone,p.email,p.celular "
                . "FROM TDDados as f, Pessoas as p, TDVars v "
                . "WHERE f.pessoa = p.codigo "
                . "AND v.TD = f.codigo "
		. "AND f.ano = :ano "
		. "AND (f.semestre = :sem OR f.semestre = 0) ";
                
        $sql .= " $sqlAdicional ";
        $sql .= " ORDER BY p.nome ";
        $sql .= " $nav ";
        
        $res = $bd->selectDB($sql, $params);
        
        if ($res)
            return $res;
        
        return false;
    }
    
    // USADO POR: PROFESSOR/FPA.PHP
    public function insertOrUpdateFPA($params) {
        // APROVEITA E ATUALIZA OS DADOS DO PROFESSOR NA TABELA PESSOAS
        $pessoa['telefone'] = $params["telefone"];
        $pessoa['celular'] = $params["celular"];
        $pessoa['email'] = $params["email"];
        $pessoa['codigo'] = $params["pessoa"];

        $enviar = $params["enviar"];
        
        unset($params["_"]);
        unset($params["telefone"]);
        unset($params["celular"]);
        unset($params["email"]);
        unset($params["enviar"]);

        $params["horario"] = implode(',', $params["horario"]);
        if (!$params["dedicarEnsino"]) $params["dedicarEnsino"] = '0';
        if (!$params["subHorario"]) $params["subHorario"] = '0';

        for ($i = 1; $i <= 3; $i++) {
            $params['horario'.$i] = $params["Intervalo".$i].','.$params["Periodo".$i].','.$params["IniIntervalo".$i];
            unset($params["Intervalo".$i]);
            unset($params["Periodo".$i]);
            unset($params["IniIntervalo".$i]);
        }
                
        //PEGANDO OS COMPONENTES
        for ($i = 0; $i <= 9; $i++) {
            if ($params['S' . $i] && $params['N' . $i] && $params['A' . $i]) {
                $paramsC[$i]['sigla'] = $params['S' . $i];
                $paramsC[$i]['nome'] = $params['N' . $i];
                $paramsC[$i]['curso'] = $params['C' . $i];
                $paramsC[$i]['periodo'] = $params['P' . $i];
                $paramsC[$i]['aulas'] = $params['A' . $i];
            }
            unset($params['S' . $i]);
            unset($params['N' . $i]);
            unset($params['C' . $i]);
            unset($params['P' . $i]);
            unset($params['A' . $i]);
        }

        //PEGANDO AS ATIVIDADES
        for ($i = 0; $i <= 6; $i++) {
            if ($params['AtvD' . $i] && $params['AtvA' . $i]) {
                $paramsAtv[$i]['descricao'] = $params['AtvD' . $i];
                $paramsAtv[$i]['aulas'] = $params['AtvA' . $i];
            }
            unset($params['AtvD' . $i]);
            unset($params['AtvA' . $i]);
        }

        //PEGANDO AS COMPLEMENTACOES
        for ($i = 0; $i <= 6; $i++) {
            if ($params['CompD' . $i] && $params['CompA' . $i]) {
                $paramsCmp[$i]['descricao'] = $params['CompD' . $i];
                $paramsCmp[$i]['aulas'] = $params['CompA' . $i];
            }
            unset($params['CompD' . $i]);
            unset($params['CompA' . $i]);
        }

        $modelo = $params["modelo"];
        unset($params["modelo"]);

        $res1 = $this->insertOrUpdate($params);
        $codTD = ($params['codigo']) ? $params['codigo'] : $res1['RESULTADO'];

        //INSERINDO COMPONENTES
        $componente = new TDFPAComponente();
        $componente->deleteComponentes($params['codigo']);
        foreach ($paramsC as $c) {
            if ($c['sigla'] && $c['nome'] && $c['aulas']) {
                $c['TD'] = $codTD;
                $c['modelo'] = $modelo;                
                $resC = $componente->insertOrUpdate($c);
            }
        }
        
        //INSERINDO ATIVIDADES
        $atvECmt = new TDFPAAtvECmt();
        $atvECmt->deleteAtvECmt($params['codigo']);
        foreach ($paramsAtv as $c) {
            if ($c['descricao'] && $c['aulas']) {
                $c['TD'] = $codTD;
                $c['tipo'] = 'atv';
                $c['modelo'] = $modelo;
                $resAtv = $atvECmt->insertOrUpdate($c);
            }
        }        
        //INSERINDO COMPLEMENTACOES
        foreach ($paramsCmp as $c) {
            if ($c['descricao'] && $c['aulas']) {
                $c['TD'] = $codTD;
                $c['tipo'] = 'cmp';
                $c['modelo'] = $modelo;
                $c['TD'] = $codTD;
            }
        }
        
        if ($enviar) {
            $tdVars = new TDVars();
            $tdVars->deleteVars($params['codigo']);
            $e['TD'] = $codTD;
            $e['modelo'] = $modelo;
            $e['finalizado'] = date('Y:m:d H:i:s');
            $tdVars->insertOrUpdate($e);
            
            //REGISTRANDO NO LOG DE SOLICITACOES
            $l['codigo'] = $codTD;
            $l['nome'] = $modelo;
            $l['data'] = date('Y:m:d H:i:s');
            $log = new LogSolicitacoes();
            $log->updateSolicitacao($l);
        }

        $p = new Pessoas();
        $p->insertOrUpdate($pessoa);

        $res['TIPO'] = 'UPDATE';
        $res['STATUS'] = 'OK';
        $res['RESULTADO'] = '1';

        if ($resC['STATUS'] == 'OK')
            return $resC;
        if ($res1['STATUS'] == 'OK')
            return $res1;
    }

}

?>