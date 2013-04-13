<?php
//Esse arquivo é fixo para o aluno. Não entra em permissões
//
//O número abaixo indica se o arquivo deve entrar nas permissões (respeitar a ordem da linha)
//1

require '../../inc/config.inc.php';
require MYSQL;
require VARIAVEIS;
require MENSAGENS;
require FUNCOES;
require SESSAO;

?>
<script src="<?php print VIEW; ?>/js/tooltip.js" type="text/javascript"></script>
<?php

if (in_array($PROFESSOR, $_SESSION["loginTipo"])) {
    
    $codigo = dcrip($_GET['turma']);
    $subturma = dcrip($_GET['subturma']);

    if ($codigo) {
        $tipo = 'turma';
    }

    if (!$codigo) {
        $tipo = 'professor';
        $codigo = $_SESSION["loginCodigo"];
    }
    
    require CONTROLLER . "/ensalamento.class.php";
    $ensalamento = new Ensalamentos();
    $res = $ensalamento->getEnsalamento($codigo, $tipo, $subturma);

    foreach ($res as $reg) {
        preg_match('#\[(.*?)\]#', $reg['horario'], $match);
        $turno[$res['horCodigo']] = $match[1];
        $reg['horario'] = str_ireplace("[$match[1]]", "", $reg['horario']);
        $horas[$reg['diaSemana']][$res['inicio']][$reg['atribuicao']][$reg['horCodigo']] = $reg['inicio'] . " - " . $reg['fim'];
        $disciplinas[$reg['atribuicao']] = 'SALA:'.$reg['sala'] . ' - LOCAL:' . $reg['localizacao'] . " - DISC:" . $reg['disciplina'] . " - PROF:" . $reg['professor'];
        $siglas[$reg['atribuicao']] = $reg['discNumero'];
        $aulas[$reg['horCodigo']] = $reg['horario'];
        $discNome[$reg['atribuicao']] = $reg['disciplina'];
        $turmaNome = $reg['turma'];
    }

    require CONTROLLER . "/turno.class.php";
    $t = new Turnos();
    $res = $t->listRegistros();
    foreach ($res as $reg)
        $turnos[$reg['sigla']] = $reg['nome'];

    if ($tipo == 'professor')
        $MOSTRA = 'HOR&Aacute;RIO INDIVIDUAL';
    else
        $MOSTRA = "Turma $turmaNome [$subturma]";
    
    if ($atribuicao) $MOSTRA = $discNome[$atribuicao];
    print "<h2><font color=\"white\">$MOSTRA</h2>\n";

    print "<center><table width=\"80%\" border=\"0\" summary=\"Calendário\" id=\"tabela_boletim\">\n";
    print "<thead>\n";
    print "<tr>\n";
    foreach (diasDaSemana() as $dCodigo => $dNome) {
        print "<th abbr=\"Domingo\" title=\"$dNome\"><span style='font-weight: bold; color: white'>$dNome</span></th>\n";
    }
    
    print "</tr>\n";
    print "</thead>\n";
    print "<tr align=\"center\">\n";
    for ($i = 1; $i <= 7; $i++) {
        $turnoAnterior = '';
        print "<td style='width: 10%' valign=\"top\">";
        if (isset($horas[$i]))
            foreach ($horas[$i] as $disc) {
                foreach ($disc as $dNum => $dHor) {
                    foreach ($dHor as $cHor => $dSala) {
                        if (isset($turno[$cHor]) && $turno[$cHor] != $turnoAnterior)
                            print "<br>" . strtoupper($turnos[$turno[$cHor]]) . "<hr><br>\n";
                        print "<a href='#' title='$disciplinas[$dNum]'>$siglas[$dNum] - $aulas[$cHor]</a><br>$dSala<br>";
                        if (count($horas[$i]) > 1)
                            print "<hr>";
                        
                        if (isset($turno[$cHor]))
                            $turnoAnterior = $turno[$cHor];
                    }
                }
            }
        print "</td>";
    }
    print "</tr>\n";
    print "</table></center>\n";
}
?>