<?php
require "inc/config.inc.php";

class Academico extends Ruckusing_Migration_Base
{
    public function up()
    {
        // ADD COLUMN ON ATRIBUICOES
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '".MY_DB."'
                                    AND TABLE_NAME = 'Atribuicoes' 
                                    AND COLUMN_NAME = 'formula'");
        if (!$result) {
            $this->execute("ALTER TABLE  `Atribuicoes` ADD  `formula` VARCHAR( 50 ) NULL AFTER  `calculo`");
            printf("TABLE COLUMN ADD: (formula)\n\n");
        }
        
        // ADD COLUMN ON INSTITUICOES
        $result = $this->select_all("SELECT * 
                                    FROM information_schema.COLUMNS 
                                    WHERE 
                                        TABLE_SCHEMA = '".MY_DB."' 
                                    AND TABLE_NAME = 'Instituicoes' 
                                    AND COLUMN_NAME = 'campiDigitaNotas '");
        if (!$result) {
            $this->execute("ALTER TABLE  `Instituicoes` ADD  `campiDigitaNotas` VARCHAR( 5 ) NULL AFTER  `bloqueioFoto`");
            printf("TABLE COLUMN ADD: (campiDigitaNotas)\n\n");
        }
        
        // ATUALIZAÇÃO DE PERMISSOES
        $result = $this->select_all("SELECT prof,aluno FROM Instituicoes");
    
        // ALUNO
        if ($result[0]['aluno']) {
            $permissao = 'view/aluno/aluno.php,view/aluno/aula.php,view/aluno/avaliacao.php,view/aluno/aviso.php,view/aluno/boletim.php,view/aluno/ensalamento.php,view/aluno/planoEnsino.php,view/aluno/socioEconomico.php';
            $nome = ',Aulas,Avalia&ccedil;&otilde;es,Avisos,Boletim,Ensalamento,Plano de Ensino,Socioecon&ocirc;mico';
            $menu = ',,,,,view/aluno/ensalamento.php,,view/aluno/socioEconomico.php';
            
            $this->execute("UPDATE Permissoes SET menu='$menu',permissao='$permissao',nome='$nome' WHERE tipo = ".$result[0]['aluno']);
            printf("ALUNO converted\n");
            $aluno = $result[0]['aluno'];
        }

        // PROFESSOR
        if ($result[0]['prof']) {
            $permissao = 'view/professor/aula.php,view/professor/avaliacao.php,view/professor/aviso.php,view/professor/ensalamento.php,view/professor/frequencia.php,view/professor/ftd.php,view/professor/nota.php,view/professor/plano.php,view/professor/professor.php';
            $nome = 'Aulas,Avalia&ccedil;&otilde;es,Avisos,Ensalamento,Frequ&ecirc;ncias,FTD,Notas,Planos de Ensino,';
            $menu = ',,,view/professor/ensalamento.php,,view/professor/ftd.php,,,';
            
            $this->execute("UPDATE Permissoes SET menu='$menu',permissao='$permissao',nome='$nome' WHERE tipo = ".$result[0]['prof']);
            printf("PROFESSOR converted\n");
            $prof = $result[0]['prof'];
        }        
        

        // DEMAIS TIPOS

        $result = $this->select_all("SELECT tipo,codigo,nome,menu,permissao FROM Permissoes WHERE tipo NOT IN ($prof,$aluno)");
        if($result) {
            $abono = 'view/secretaria/abono.php';
            $atribuicao = 'view/secretaria/atribuicao.php';
            $aviso = 'view/secretaria/aviso.php';
            $boletim = 'view/aluno/boletim.php';
            $calendario = 'view/secretaria/calendario.php';
            $cidade = 'view/secretaria/cidade.php';
            $estado = 'view/secretaria/estado.php';
            $ftd = 'view/secretaria/ftd.php';
            $pessoa = 'view/secretaria/pessoa.php';
            $plano = 'view/secretaria/plano.php';
            $socioEconomico = 'view/secretaria/socioEconomico.php';
            $atendimento = 'view/secretaria/atendimento.php';

            $boletimTurma = 'view/secretaria/relatorios/boletimTurma.php';
            $frequenciaLista = 'view/secretaria/relatorios/frequencias.php';
            $relatorio = 'view/secretaria/relatorios/listagem.php';

            $coordenador = 'view/secretaria/cursos/coordenador.php';
            $curso = 'view/secretaria/cursos/curso.php';
            $diario = 'view/secretaria/prazos/diario.php';
            $disciplina = 'view/secretaria/cursos/disciplina.php';
            $matricula = 'view/secretaria/cursos/matricula.php';
            $modalidade = 'view/secretaria/cursos/modalidade.php';
            $professorAtribuicao = 'view/secretaria/cursos/professorAtribuicao.php';
            $situacao = 'view/secretaria/cursos/situacao.php';
            $tipoAvaliacao = 'view/secretaria/cursos/tipoAvaliacao.php';
            $turma = 'view/secretaria/cursos/turma.php';
            $turno = 'view/secretaria/cursos/turno.php';
            $notasFinais = 'view/secretaria/cursos/notasFinais.php';
            
            $ensalamento = 'view/secretaria/ensalamento/ensalamento.php';
            $horario = 'view/secretaria/ensalamento/horario.php';
            $sala = 'view/secretaria/ensalamento/sala.php';

            $instituicao = 'view/admin/instituicao.php';
            $logs = 'view/admin/logs.php';
            $migracao = 'view/admin/migracao.php';
            $permissao = 'view/admin/permissao.php';
            $sincronizadorNambei = 'view/admin/sincronizadorNambei.php';
            $tipo = 'view/admin/tipo.php';
            $usoSistema = 'view/admin/usoSistema.php';
            
            $prazoAula = 'view/secretaria/prazos/aula.php';
            $prazoDiario = 'view/secretaria/prazos/diario.php';
            
            foreach($result as $P) {
                $P['permissao'] = explode(",", $P['permissao']);
                $P['menu'] = explode(",", $P['menu']);
                $P['nome'] = explode(",", $P['nome']);
             
                $i=0;
                $novo['permissao'] = array();
                $novo['menu'] = array();
                $novo['nome'] = array();
                foreach ($P['permissao'] as $perm) {
                    if ($perm != 'atualizacaoSistema.php' && 
                        $perm != 'nota.php' &&
                        $perm != 'aula.php' &&
                        $perm != 'professor.php' &&
                        $perm != 'avaliacao.php' &&
                        $perm != 'aluno.php' &&
                        $perm != 'frequencia.php') {
                        
                        $nome = $P['nome'][$i];

                        $perm = str_replace(".php", '', $perm);
                        $menu = str_replace(".php", '', $P['menu'][$i]);

                        $novo['permissao'][] = $$perm;
                        $novo['menu'][] = $$menu;
                        $novo['nome'][] = $nome;
                    }
                    $i++;
                }
                $P1 = implode(",", $novo['permissao']);
                $M1 = implode(",", $novo['menu']);
                $N1 = implode(",", $novo['nome']);
                printf("TIPO (%s) converted...\n", $P['tipo']);

                $this->execute("UPDATE Permissoes SET menu='$M1',permissao='$P1',nome='$N1' WHERE codigo = ". $P['codigo']);
            }
        }
    }//up()

    public function down()
    {
    }//down()
}
