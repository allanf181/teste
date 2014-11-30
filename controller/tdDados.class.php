<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

if (!class_exists('Pessoas'))
    require_once CONTROLLER . '/pessoa.class.php';

class TDDados extends Generic {

    public function __construct() {
        //
    }

    // USADO POR: PROFESSOR/FPA.PHP
    public function insertOrUpdateFPA($params) {

        // APROVEITA E ATUALIZA OS DADOS DO PROFESSOR NA TABELA PESSOAS
        $pessoa['telefone'] = $params["telefone"];
        $pessoa['celular'] = $params["celular"];
        $pessoa['email'] = $params["email"];
        $pessoa['codigo'] = $params["professor"];

        unset($params["_"]);
        unset($params["telefone"]);
        unset($params["celular"]);
        unset($params["email"]);

        //PEGANDO OS COMPONENTES
        for ($i = 1; $i <= 10; $i++) {
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
        print_r($params);
        $res1 = $this->insertOrUpdate($params);
        print_r($res1);

        //INSERINDO COMPONENTES
        $componente = new TDFPAComponente();




        if (!$params["codigo"])
            $params["codigo"] = $res['RESULTADO'];

        $p = new Pessoas();
        $p->insertOrUpdate($pessoa);

        $res['TIPO'] = 'UPDATE';
        $res['STATUS'] = 'OK';
        $res['RESULTADO'] = '1';

        return $res1;
    }

}

?>