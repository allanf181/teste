<?php
if(!class_exists('Generic'))
    require_once CONTROLLER.'/generic.class.php';

class AtvAcadItens extends Generic {

    public function __construct() {
        //
    }
    
    // LISTA OS ITENS DAS ATIVIDADES ACADÊMICAS CADASTRADAS
    // USADO POR: CURSOS/ATIVIDADES_ACADEMICAS/ITENS.PHP
    public function listItens($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT aa.nome, ai.codigo, ai.atividade, ai.comprovacao, "
                . "ai.CH, ai.CHLimite, ai.tipo "
                . "FROM AtvAcadItens ai, AtvAcademicas aa "
                . "WHERE aa.codigo = ai.atvAcademica ";

        $sql .= " $sqlAdicional ";

        $sql .= " ORDER BY aa.nome, ai.tipo, ai.atividade ";

        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    // IMPORTAÇÃO DE ITENS PRÉ-CADASTRADOS
    public function import($codigo) {

        $reg = array();
        $reg[0]['tipo'] = 'Acadêmica';
        $reg[0]['atividade'] = 'Representação estudantil (Colegiado, Diretório Acadêmico, Comissão de Recepção de Alunos etc.).';
        $reg[0]['comprovacao'] = 'Atas de nomeação e término do mandato, emitidas pelo órgão colegiado competente.';
        $reg[0]['CH'] = '10 horas por semestre.';
        $reg[0]['CHLimite'] = '40';
        $reg[1]['tipo'] = 'Acadêmica';
        $reg[1]['atividade'] = 'Disciplina de nível superior cursada em outra Instituição (desde que não utilizada como equivalência de disciplina no IFSP).';
        $reg[1]['comprovacao'] = 'Histórico escolar.';
        $reg[1]['CH'] = '20 horas por disciplina, ou carga horária especificada no histórico.';
        $reg[1]['CHLimite'] = '80';
        $reg[2]['tipo'] = 'Acadêmica';
        $reg[2]['atividade'] = 'Atividades de monitoria acadêmica (incluindo PET) no IFSP ou em outras Instituições de Ensino Superior.';
        $reg[2]['comprovacao'] = 'Documento emitido pela coordenação do curso ao qual se aplicam as atividades.';
        $reg[2]['CH'] = '30 horas por semestre.';
        $reg[2]['CHLimite'] = '60';
        $reg[3]['tipo'] = 'Acadêmica';
        $reg[3]['atividade'] = 'Estudos de meio e visitas monitoradas, vinculadas à licenciatura, desde que ocupem horário alheio ao das aulas regulares.';
        $reg[3]['comprovacao'] = 'Declaração do professor responsável pela atividade, com breve descrição do trabalho e registro da carga horária atribuída.';
        $reg[3]['CH'] = 'Carga horária especificada na declaração.';
        $reg[3]['CHLimite'] = '60';
        $reg[4]['tipo'] = 'Acadêmica';
        $reg[4]['atividade'] = 'Acompanhamento de palestras do interesse da licenciatura.';
        $reg[4]['comprovacao'] = 'Certificado emitido pela organização do evento.';
        $reg[4]['CH'] = '2 horas por palestra, ou a carga horária especificada no certificado.';
        $reg[4]['CHLimite'] = '30';
        $reg[5]['tipo'] = 'Acadêmica';
        $reg[5]['atividade'] = 'Participação como ouvinte em defesas de teses (doutorado) e dissertações (mestrado).';
        $reg[5]['comprovacao'] = 'Relato escrito da defesa, com título da tese, nome do candidato e dos membros da banca examinadora, além de comentários sobre o tema do trabalho e sobre o evento da defesa.';
        $reg[5]['CH'] = '4 horas por evento.';
        $reg[5]['CHLimite'] = '40';
        $reg[6]['tipo'] = 'Acadêmica';
        $reg[6]['atividade'] = 'Trabalho voluntário na área de educação científica, incluindo modalidade de Ensino de Jovens e Adultos.';
        $reg[6]['comprovacao'] = 'Declaração do órgão ou entidade na qual se desenvolvem as atividades, constando o período de dedicação do aluno.';
        $reg[6]['CH'] = 'Carga horária especificada na declaração.';
        $reg[6]['CHLimite'] = '60';
        $reg[7]['tipo'] = 'Acadêmica';
        $reg[7]['atividade'] = 'Cursos de idiomas ou informática.';
        $reg[7]['comprovacao'] = 'Declaração da instituição ofertadora do curso.';
        $reg[7]['CH'] = 'Carga horária especificada na declaração.';
        $reg[7]['CHLimite'] = '60';
        $reg[8]['tipo'] = 'Científica';
        $reg[8]['atividade'] = 'Estágio em projeto de extensão, iniciação científica ou iniciação à docência, com ou sem bolsa (PIBID, PIBIC, FAPESP, Capes, CNPq etc.).';
        $reg[8]['comprovacao'] = 'Documento emitido pelo orientador da atividade, com breve descrição do trabalho e atestando seu status em desenvolvimento ou concluído.';
        $reg[8]['CH'] = '50 horas por semestre.';
        $reg[8]['CHLimite'] = '60';
        $reg[9]['tipo'] = 'Científica';
        $reg[9]['atividade'] = 'Participação em comissões para organização de eventos científicos (semanas acadêmicas, encontros de área etc.).';
        $reg[9]['comprovacao'] = 'Certificado de colaboração emitido pelos responsáveis pelo evento.';
        $reg[9]['CH'] = '10 horas por evento, ou carga horária especificada no certificado.';
        $reg[9]['CHLimite'] = '30';
        $reg[10]['tipo'] = 'Científica';
        $reg[10]['atividade'] = 'Participação como ministrante em mini-cursos, colóquios, cursos de extensão, oficinas e afins, em eventos com emissão de certificados.';
        $reg[10]['comprovacao'] = 'Certificado de colaboração emitido pelos responsáveis pelo evento.';
        $reg[10]['CH'] = '20 horas por evento, ou carga horária especificada no certificado.';
        $reg[10]['CHLimite'] = '60';
        $reg[11]['tipo'] = 'Científica';
        $reg[11]['atividade'] = 'Participação em mini-cursos, colóquios, cursos de extensão, oficinas e afins.';
        $reg[11]['comprovacao'] = 'Certificado de participação constando a carga horária do evento.';
        $reg[11]['CH'] = '8 horas por atividade, ou a carga horária especificada no certificado.';
        $reg[11]['CHLimite'] = '50';
        $reg[12]['tipo'] = 'Científica';
        $reg[12]['atividade'] = 'Participação como ouvinte em congressos ou encontros científicos.';
        $reg[12]['comprovacao'] = 'Certificado de participação emitido pela organização do evento.';
        $reg[12]['CH'] = '5 horas por evento.';
        $reg[12]['CHLimite'] = '30';
        $reg[13]['tipo'] = 'Científica';
        $reg[13]['atividade'] = 'Apresentação de pôsteres em eventos científicos (Semanas acadêmicas, congressos, encontros etc.).';
        $reg[13]['comprovacao'] = 'Certificado de apresentação emitido pela organização do evento.';
        $reg[13]['CH'] = '10 horas por pôster apresentado (5 horas para reapresentações do mesmo trabalho).';
        $reg[13]['CHLimite'] = '60';
        $reg[14]['tipo'] = 'Científica';
        $reg[14]['atividade'] = 'Comunicações orais em eventos científicos (Semanas acadêmicas, congressos, encontros etc.).';
        $reg[14]['comprovacao'] = 'Certificado de apresentação emitido pela organização do evento.';
        $reg[14]['CH'] = '20 horas por comunicação, ou carga horária constante no certificado.';
        $reg[14]['CHLimite'] = '60';
        $reg[15]['tipo'] = 'Científica';
        $reg[15]['atividade'] = 'Publicação de artigo completo em periódico das áreas abrangidas na licenciatura.';
        $reg[15]['comprovacao'] = 'Cópia do artigo publicado ou do termo de aceitação do periódico, constando o nome dos autores do artigo.';
        $reg[15]['CH'] = '30 horas por artigo.';
        $reg[15]['CHLimite'] = '90';
        $reg[16]['tipo'] = 'Científica';
        $reg[16]['atividade'] = 'Publicação de trabalhos completos em anais de eventos científicos.';
        $reg[16]['comprovacao'] = 'Cópia do trabalho publicado, com referência completa aos anais do evento.';
        $reg[16]['CH'] = '20 horas por trabalho publicado.';
        $reg[16]['CHLimite'] = '60';
        $reg[17]['tipo'] = 'Científica';
        $reg[17]['atividade'] = 'Publicação de resumos em anais de eventos científicos.';
        $reg[17]['comprovacao'] = 'Cópia do resumo, com referência completa aos anais do evento.';
        $reg[17]['CH'] = '5 horas por resumo publicado.';
        $reg[17]['CHLimite'] = '30';
        $reg[18]['tipo'] = 'Científica';
        $reg[18]['atividade'] = 'Publicação de artigos em periódicos de divulgação científica ou de caráter não-acadêmico (jornais, revistas, etc.), com temática do interesse da licenciatura.';
        $reg[18]['comprovacao'] = 'Cópia do material publicado, com referência completa ao veículo de comunicação.';
        $reg[18]['CH'] = '10 horas por artigo publicado.';
        $reg[18]['CHLimite'] = '40';
        $reg[19]['tipo'] = 'Científica';
        $reg[19]['atividade'] = 'Participação na produção de material didático ou de divulgação científica (livros, vídeos, blogs, exposições etc.).';
        $reg[19]['comprovacao'] = 'Cópia do material produzido e declaração do coordenador do projeto.';
        $reg[19]['CH'] = '10 horas por peça produzida.';
        $reg[19]['CHLimite'] = '40';
        $reg[20]['tipo'] = 'Científica';
        $reg[20]['atividade'] = 'Trabalho junto à editoria de periódicos científicos (diagramação, revisão, tradução, produção de resenhas etc.).';
        $reg[20]['comprovacao'] = 'Certificado emitido pelo editor chefe do periódico.';
        $reg[20]['CH'] = '20 horas por semestre.';
        $reg[20]['CHLimite'] = '60';
        $reg[21]['tipo'] = 'Cultural';
        $reg[21]['atividade'] = 'Participação na produção de objetos artísticos publicados ou apresentados ao público (vídeos, artes plásticas, teatro, literatura, música etc.).';
        $reg[21]['comprovacao'] = 'A critério do colegiado de curso (cópia da obra publicada, panfletos ou material de divulgação que atestem as datas do evento ou das apresentações etc.).';
        $reg[21]['CH'] = '10 horas por produção.';
        $reg[21]['CHLimite'] = '30';
        $reg[22]['tipo'] = 'Cultural';
        $reg[22]['atividade'] = 'Participação em oficinas, cursos ou mini-cursos ligados a manifestações artísticas e culturais.';
        $reg[22]['comprovacao'] = 'Certificado de participação emitido pelos responsáveis pelo evento.';
        $reg[22]['CH'] = '10 horas por evento, ou carga horária especificada no certificado.';
        $reg[22]['CHLimite'] = '30';
        $reg[23]['tipo'] = 'Cultural';
        $reg[23]['atividade'] = 'Cinema, teatro, concertos, shows e demais apresentações artísticas. Museus, mostras e exposições.';
        $reg[23]['comprovacao'] = 'Tickets de entrada.';
        $reg[23]['CH'] = '2 horas por evento.';
        $reg[23]['CHLimite'] = '40';
        $reg[24]['tipo'] = 'Cultural';
        $reg[24]['atividade'] = 'Cursos extracurriculares (exceto os de informática e idiomas)';
        $reg[24]['comprovacao'] = 'Declaração da instituição ofertadora do curso.';
        $reg[24]['CH'] = 'Carga horária especificada na declaração.';
        $reg[24]['CHLimite'] = '40';
        $reg[25]['tipo'] = 'Cultural';
        $reg[25]['atividade'] = 'Trabalho voluntário em outras áreas, não ligadas à educação científica.';
        $reg[25]['comprovacao'] = 'Declaração do órgão ou entidade na qual se desenvolvem as atividades, constando o período de dedicação do aluno.';
        $reg[25]['CH'] = 'Carga horária especificada na declaração.';
        $reg[25]['CHLimite'] = '40';
        $reg[26]['tipo'] = 'Cultural';
        $reg[26]['atividade'] = 'Trabalho como mesário ou presidente de junta eleitoral (em eleições federais ou municipais).';
        $reg[26]['comprovacao'] = 'Declaração do Tribunal Regional Eleitoral.';
        $reg[26]['CH'] = '10 horas (mesário); 15 horas (presidente); Em caso de segundo turno, serão acrescidas 10 horas para ambas as funções.';
        $reg[26]['CHLimite'] = '50';
        $reg[27]['tipo'] = 'Cultural';
        $reg[27]['atividade'] = 'Participação em eventos de natureza artística, esportiva ou cultural em geral, mediante emissão de certificado.';
        $reg[27]['comprovacao'] = 'Certificado de participação no evento ou exposição, emitido pelos organizadores do evento.';
        $reg[27]['CH'] = '3 horas por evento.';
        $reg[27]['CHLimite'] = '15';
        $reg[28]['tipo'] = 'Cultural';
        $reg[28]['atividade'] = 'Leitura de livro.';
        $reg[28]['comprovacao'] = 'Xerox da capa do livro e de sua ficha catalográfica, além da resenha descritiva.';
        $reg[28]['CH'] = '5 horas por semestre.';
        $reg[28]['CHLimite'] = '20';
        $reg[29]['tipo'] = 'Cultural';
        $reg[29]['atividade'] = 'Acompanhamento de palestras, eventos, feiras não relacionados à licenciatura.';
        $reg[29]['comprovacao'] = 'Certificado emitido pela organização do evento.';
        $reg[29]['CH'] = '2 horas por palestra, ou a carga horária especificada no certificado.';
        $reg[29]['CHLimite'] = '20';

        foreach($reg as $r) {
            $r['atvAcademica'] = $codigo;
            $res = $this->insertOrUpdate($r);
        }

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }     
}

?>