<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

if (!class_exists('Pessoas'))
    require_once CONTROLLER . '/pessoa.class.php';

class TDDados extends Generic {

    public function __construct() {
        //
    }

    // USADO POR: HOME.PHP
    // Verifica se o usuário tem correções para FPA,PIT e RIT
    public function hasChangeTD($params, $sqlAdicional=null) {
        $bd = new database();
        $sql = "SELECT (SELECT nome FROM Pessoas "
                . "WHERE codigo = l.solicitante) as solicitante, "
                . "l.solicitacao,f.modelo "
                . "FROM TDDados f, LogSolicitacoes l "
                . "WHERE l.nometabela = f.modelo "
                . "AND l.codigoTabela = f.codigo "
                . "AND f.semestre = :sem "
                . "AND f.ano = :ano "
                . "AND (f.valido = '0000-00-00 00:00:00' OR f.valido IS NULL) "
                . "AND (f.finalizado = '0000-00-00 00:00:00' OR f.finalizado IS NULL) "
                . "AND (l.dataConcessao = '0000-00-00 00:00:00' OR l.dataConcessao IS NULL) ";

        $sql .= $sqlAdicional;
        
        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    public function listTDs($params = null, $item = null, $itensPorPagina = null, $sqlAdicional = null) {
        $bd = new database();

        $nav = null;
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina ";

        $sql = "SELECT f.codigo,f.ano,f.semestre,f.apelido,f.area,f.regime,f.duracaoAula,f.dedicarEnsino,"
                . "f.subHorario,f.horario1,f.horario2,f.horario3,f.horario,p.nome,f.modelo,"
                . "p.prontuario,p.telefone,p.email,p.celular,f.pessoa,date_format(f.finalizado, '%d/%m/%Y %H:%i') as finalizado, "
                . "date_format(f.valido, '%d/%m/%Y %H:%i') as valido, "
                . "(SELECT l.solicitacao FROM LogSolicitacoes l "
                . "         WHERE l.codigoTabela = f.codigo "
                . "         AND l.nomeTabela = f.modelo "
                . "         AND l.dataConcessao IS NULL) as solicitacao, "
                . "(SELECT p.nome FROM LogSolicitacoes l, Pessoas p "
                . "         WHERE p.codigo = l.solicitante "
                . "         AND l.codigoTabela = f.codigo "
                . "         AND l.nomeTabela = f.modelo "
                . "         AND l.dataConcessao IS NULL) as solicitante "
                . "FROM TDDados as f, Pessoas as p "
                . "WHERE f.pessoa = p.codigo "
                . "AND f.ano = :ano "
                . "AND (f.semestre = :semestre OR f.semestre = 0) ";

        $sql .= " $sqlAdicional ";
        $sql .= " ORDER BY p.nome ";
        $sql .= " $nav ";

        $res = $bd->selectDB($sql, $params);

        if ($res)
            return $res;

        return false;
    }
    
    public function listModelo($params = null, $item = null, $itensPorPagina = null, $sqlAdicional = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina ";

        $sql = "SELECT f.codigo,f.ano,f.semestre,f.apelido,f.regime,f.duracaoAula,f.dedicarEnsino,"
                . "f.subHorario,f.horario1,f.horario2,f.horario3,f.horario,p.nome,"
                . "p.prontuario,p.telefone,p.email,p.celular,f.pessoa,f.finalizado,f.valido,"
                . "a.nome as area, a.codigo as codArea "
                . "FROM TDDados as f, Pessoas as p, Areas a "
                . "WHERE f.pessoa = p.codigo "
                . "AND a.codigo = f.area "
                . "AND f.ano = :ano "
                . "AND (f.semestre = :semestre OR f.semestre = 0) ";

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
        $modelo = $params["modelo"];
        
        if ($enviar)
            $params['finalizado'] = date('Y:m:d H:i:s');
        
        unset($params["_"]);
        unset($params["telefone"]);
        unset($params["celular"]);
        unset($params["email"]);
        unset($params["enviar"]);

        if ($modelo != 'RIT')
            $params["horario"] = implode(',', $params["horario"]);
        
        if (!$params["dedicarEnsino"])
            $params["dedicarEnsino"] = '0';
        if (!$params["subHorario"])
            $params["subHorario"] = '0';

        if ($modelo == 'FPA') {
            for ($i = 1; $i <= 3; $i++) {
                $params['horario' . $i] = $params["Intervalo" . $i] . ',' . $params["Periodo" . $i] . ',' . $params["IniIntervalo" . $i];
                unset($params["Intervalo" . $i]);
                unset($params["Periodo" . $i]);
                unset($params["IniIntervalo" . $i]);
            }
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

        $res1 = $this->insertOrUpdate($params);
        $codTD = ($params['codigo']) ? $params['codigo'] : $res1['RESULTADO'];

        //INSERINDO COMPONENTES
        $componente = new TDComponente();
        $componente->deleteComponentes($params['codigo']);
        foreach ($paramsC as $c) {
            if ($c['sigla'] && $c['nome'] && $c['aulas']) {
                $c['TD'] = $codTD;
                $c['modelo'] = $modelo;
                $resC = $componente->insertOrUpdate($c);
            }
        }

        //INSERINDO ATIVIDADES
        $atvECmt = new TDAtvECmt();
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
                $resAtv = $atvECmt->insertOrUpdate($c);
            }
        }

        if ($enviar) {
            //REGISTRANDO NO LOG DE SOLICITACOES
            $l['codigo'] = $codTD;
            $l['nome'] = $modelo;
            $l['data'] = date('Y:m:d H:i:s');
            $log = new LogSolicitacoes();
            $log->updateSolicitacao($l);
        }

        if ($modelo == 'FPA') {
            $p = new Pessoas();
            $p->insertOrUpdate($pessoa);
        }

        if ($resC['STATUS'] == 'OK')
            return $resC;
        if ($res1['STATUS'] == 'OK')
            return $res1;
    }

}

?>