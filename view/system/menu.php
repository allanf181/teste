<div id="cssmenu">
    <ul>
        <li><a href="javascript:$('#index').load('<?= VIEW ?>/system/home.php');void(0);"><span>Home</span></a></li>
        <?php
        // SE FOR ALUNO OU PROFESSOR, MOSTRA O MENU PERSONALIZADO COM AS SUAS ATRIBUICOES
        $menuDiferencial = 0;
        if (in_array($PROFESSOR, $_SESSION["loginTipo"])) {
            // MENU PROFESSOR
            require CONTROLLER . "/atribuicao.class.php";
            $disc = new Atribuicoes();
            $disc = $disc->getAtribuicoesFromPapel($_SESSION['loginCodigo'], 'professor', $ANO, 'menu');
            $menuDiferencial = 'professor';
        }
        if (in_array($ALUNO, $_SESSION["loginTipo"])) {
            // MENU ALUNO
            require CONTROLLER . "/atribuicao.class.php";
            $disc = new Atribuicoes();
            $disc = $disc->getAtribuicoesFromPapel($_SESSION['loginCodigo'], 'aluno', $ANO, 'menu');
            $menuDiferencial = 'aluno';
        }
        if ($menuDiferencial) {
            foreach ($disc as $ano => $reg) {
                ?><li class='active has-sub'><a href='#'><span>Disciplinas <?= $ano ?></span></a>
                    <ul>
                        <?php
                        if (isset($disc[$ano]['A']['A'])) {
                            foreach ($disc[$ano]['A']['A'] as $atribuicao => $reg) { // ANUAL
                                ?>
                                <li><a data-placement="right" data-content="<?= $reg[3] . '<br>' . $reg[4] ?>" title="<?= $reg[0] ?>" href="javascript:$('#index').load('<?= VIEW . "/" . $menuDiferencial . "/" . $menuDiferencial . ".php?atribuicao=" . crip($reg[1]); ?>');void(0);"><span><?= $reg[2] ?></span></a></li>
                                <?php
                            }
                        }
                        foreach ($disc[$ano] as $semestre => $reg) {
                            if ($semestre != 'A') { // SEMESTRAL
                                ?>
                                <li class='active has-sub'>
                                    <a href='#'><span><b><?= $semestre ?>&ordm; SEMESTRE</b></span></a>
                                    <ul>
                                        <?php
                                        if (isset($disc[$ano][$semestre]['S'])) { // DISCIPLINA SEMESTRAL
                                            foreach ($disc[$ano][$semestre]['S'] as $atribuicao => $reg) {
                                                ?>
                                                <li>
                                                    <a data-placement="right" data-content="<?= $reg[3] . '<br>' . $reg[4] ?>" title="<?= $reg[0] ?>" href="javascript:$('#index').load('<?= VIEW . "/" . $menuDiferencial . "/" . $menuDiferencial . ".php?atribuicao=" . crip($reg[1]); ?>');void(0);"><span><?= $reg[2] ?></span></a>
                                                </li>
                                                <?php
                                            }
                                        }
                                        if (isset($disc[$ano][$semestre]['B'])) { // BIMESTRE
                                            foreach ($disc[$ano][$semestre]['B'] as $bimestre => $reg) {
                                                ?>
                                                <li class='active has-sub'>
                                                    <a href='#'><span><b><?= $bimestre ?>&ordm; BIMESTRE</b></span></a>
                                                    <ul>
                                                        <?php
                                                        foreach ($disc[$ano][$semestre]['B'][$bimestre] as $atribuicao => $reg) {
                                                            ?>
                                                            <li>
                                                                <a data-placement="right" data-content="<?= $reg[3] . '<br>' . $reg[4] ?>" title="<?= $reg[0] ?>" href="javascript:$('#index').load('<?= VIEW . "/" . $menuDiferencial . "/" . $menuDiferencial . ".php?atribuicao=" . crip($reg[1]); ?>');void(0);"><span><?= $reg[2] ?></span></a>
                                                            </li>
                                                            <?php
                                                        }
                                                        ?>
                                                    </ul>
                                                </li>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </ul>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                </li>
                <?php
            } // FIM MENU PROFESSOR/ALUNO
        }
        if ($_SESSION["loginTipo"]) {
            require CONTROLLER . "/permissao.class.php";
            $permissao = new Permissoes();
            $menus = $permissao->listaPermissoes($_SESSION["loginTipo"], 'menu');

            function menuMapa($k) {
                $mapa['atribuicao_docente'] = 'atribuição docente';
                $mapa['atividade_academica'] = 'atividades acadêmicas';
                $mapa['atribuicao'] = 'atribuição';
                $mapa['relatorios'] = 'relatórios';
                if (@$mapa[$k])
                    return $mapa[$k];

                return $k;
            }

            function makeMenu($ar) {
                global $menus;
                foreach ($ar as $k => $v) {
                    if (!is_array($v)) {
                        ?>
                        <li>
                            <a href="javascript:$('#index').load('<?= $v ?>');void(0);"><span><?= $menus['nome'][$v] ?></span></a>
                        </li>
                        <?php
                    }
                    if (is_array($ar[$k])) {
                        $n = menuMapa($k);
                        ?>
                        <li class='active has-sub'><a href='#'><span><?= maiusculo($n) ?></span></a>
                            <ul>
                            <?php
                            makeMenu($ar[$k]);
                    }
                }
                ?>
                </ul>                    
                <?php
            }
            ?>
            <?php
            print makeMenu($menus['arvore']['view']);
        }
        ?>
</div>
