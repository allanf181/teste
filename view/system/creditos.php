<?php
include_once "../../inc/config.inc.php";
require VARIAVEIS;
require SESSAO;

// verifica se não está sendo chamado diretamente.
if (strpos($_SERVER["HTTP_REFERER"], LOCATION) == false) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . LOCATION);
}
?>

<h2>Equipe WebDi&aacute;rio</h2>

<br />
<center><img style="width: 400px" src="<?= IMAGES ?>/pic.png"/>


    <table border="0" id="form" width="100%">
        <tr>
            <td colspan="4"><h2>Pró-reitoria de Desenvolvimento Institucional</h2></td>
        </tr>
        <tr>
            <td colspan="4" valign="top" align="center">Eduardo Leal (Assessor)
                <br>Brunno Alves (Diretor de Sistemas de Informação)
            </td>
        </tr>

        <tr><td>&nbsp;</td></tr>

        <tr>
            <td colspan="4"><h2>Gerente de Projeto</h2></td>
        </tr>
        <tr>
            <td colspan="4" valign="top" align="center">João Paulo Lemos Escola (Barretos)
            </td>
        </tr>

        <tr><td>&nbsp;</td></tr>

        <tr>
            <td width="300"><h2>Desenvolvedores</h2></td>
            <td width="300"><h2>Testes de Software e Suporte</h2></td>
            <td width="300"><h2>Segurança de Software</h2></td>
        </tr>
        <tr>
            <td valign="top" align="center">
                João Paulo Lemos Escola (Barretos)
                <br>Kerolláine Lauro Oliveira (Araraquara) 
            </td>
            <td valign="top" align="center">
                Ricardo Takazu (Reitoria)
            </td>
            <td valign="top" align="center">
                Lucas de Araujo Oliveira (Barretos)
            </td>
        </tr>
        <tr>
            <td colspan="3"><h2>Colaboraram com o projeto</h2></td>
        </tr>
        <tr>
            <td colspan="3" valign="top" align="center">
                Anne Domenici (Sert&atilde;ozinho)
                <br>Carlos Eduardo Alves da Silva (Votuporanga)
                <br>Fernando Parreira (Votuporanga)
                <br>Josiane Rosa de Oliveira Gaia (Hortol&acirc;ndia)
                <br>Lourenço Alves (Araraquara)
                <br>Marcelo Fernandes (Campos do Jordão)
                <br>Naylor Garcia Bachiega (Birigui)
                <br>Ricardo Crivelli (Avaré) 
                <br>Rodolfo Esteves (Hortolândia)
            </td>
        </tr>

    </table>

</center>

<p style="text-align: center; margin: 20px auto 0 auto; width: 80%; color: green;">
    O WebDiário é desenvolvido de forma distribuída, com auxílio direto dos servidores dos campi e indireto por meio da contribuição dos diversos servidores
    espalhados pelos campi do IFSP. 
</p>
<p style="text-align: center; margin: 20px auto 0 auto; width: 80%; color: green;">
    É servidor do IFSP e quer ajudar? Venha fazer parte da equipe!
</p>

<br /><b>Vers&atilde;o 1.445</b>
<br> - Início implantação do Hudson;
<br> - Link do boletim no nome do aluno na tela de ocorrências;
<br> - Inclusão do campo SMTP Auth na tela de cadastro do email da Instituição.

<br /><b>Vers&atilde;o 1.444</b>
<br> - Correção da paginação na tela de abonos;
<br> - Exclusão no bd da permissão de item de menu de questionários;
<br> - Correção do aviso de ano/semestre alterado.
<br />
<b>Vers&atilde;o 1.443</b>
<br> - Funcionalidade de backup online.
<br />
<b>Vers&atilde;o 1.442</b>
<br> - Ajustes e correções nos formulários FPA, PIT e RIT: recurso autocompletar no campo de curso e disciplinas;
<br> - Importação dos dados de FPA para PIT e de PIT para RIT;
<br> - Melhorias no sincronizador Nambei em relação ao Consulta Roda: atualiza lista automaticamente ao clicar no botão;
<br> - Funcionalidade de fixar ano/semestre;
<br> - Todos os tipos de usuário com permissão de alterar ano/semestre por padrão (admin pode alterar);
<br />
<br />
<b>Vers&atilde;o 1.441</b>
<br> - Retirada dos Question&aacute;rios do Perfil Professor/Aluno.
<br> - Integração com RodaWeb.
<br> - Correções e ajustes na integração com RodaWeb.
<br> - Listagem de disciplinas aguardando Roda na interface do sincronizador Nambei.
<br> - Inclusão de filtro na listagem de disciplinas aguardando Roda.
<br> - Nomes das avaliações no campo "observações" do diário.
<br> - Campo "Aguardando Roda" na listagem de Atribuições;
<br> - Controle de alteração e reenvio de notas quando há liberação pela CRE;
<br />
<br />
<b>Vers&atilde;o 1.440</b>
<br> - Inclus&atilde;o do WS para verifica&ccedil;&atilde;o de IFA/Recupera&ccedil;&atilde;o/Reavalia&ccedil;&atilde;o.
<br> - Retirada data de altera&ccedil;&atilde;o de matr&iacute;cula.
<br />
<br />
<b>Vers&atilde;o 1.439</b>
<br> - Corre&ccedil;&atilde;o do LDAP.
<br> - Diversas corre&ccedil;&otilde;es solicitadas por e-mail.
<br />
<br />
<b>Vers&atilde;o 1.438</b>
<br> - Melhorias no relat&oacute;rio de Frequ&ecirc;ncias.
<br> - Corre&ccedil;&otilde;es de Seguran&ccedil;a.
<br> - Corre&ccedil;&atilde;o do #ADMIN para funcionar com LDAP.
<br> - Adi&ccedil;&atilde;o de fun&ccedil;&atilde;o que permite que o WebDi&aacute;rio adicione os usu&aacute;rios do banco no LDAP.
<br> - Adi&ccedil;&atilde;o de fun&ccedil;&atilde;o que permite que senhas alteradas no WebD&aacute;rio sejam replicadas no LDAP.
<br />
<br />
<b>Vers&atilde;o 1.437</b>
<br> - Corre&ccedil;&atilde;o da FPA, PIT e RIT.
<br> - Corre&ccedil;&atilde;o da impress&atilde;o do verso do di&aacute;rio.
<br> - Mudança no tamanho do campo de nome da disciplina e pessoa.
<br> - Liberada para GED e SEC a visualiza&ccedil;&atilde;o de Trocas/Reposi&ccedil;&otilde;es.
<br />
<br />
<b>Vers&atilde;o 1.436</b>
<br> - Corre&ccedil;&atilde;o do relacionamento entre alunos e matr&iacute;culas.
<br />
<br />
<b>Vers&atilde;o 1.435</b>
<br> - Corre&ccedil;&atilde;o das permiss&otilde;es de todos os tipos de pap&eacute;is.
<br />
<br />
<b>Vers&atilde;o 1.434</b>
<br> - Adi&ccedil;&atilde;o de Sistema de Question&aacute;rios
<br> - Desvincula&ccedil;&atilde;o do Socioecon&ocirc;mico de Pessoa, registros transportados para Socioecon&ocirc;mico em Question&aacute;rios.
<br> - Altera&ccedil;&atilde;o do ToolTip.
<br> - Informe na HOME dos &uacute;ltimos eventos do calend&aacute;rio.
<br> - Adi&ccedil;&atilde;o de campo em ADMIN-&gt;TIPOS mostrando a quantidade de usu&aacute;rios utilizando cada tipo.
<br> - Corre&ccedil;&atilde;o do chamado: Defeito #271
<br> - Adi&ccedil;&atilde;o de funcionalidade de busca por Tipo em Pessoas. Funcionalidade #273
<br />
<br />
<b>Vers&atilde;o 1.433</b>
<br> - Adi&ccedil;&atilde;o de Link Externo para recupera&ccedil;&atilde;o de senha (ADMIN --> INSTITUI&Ccedil;&Otilde;ES).
<br> - Upgrade e adapta&ccedil;&atilde;o do site para JQUERY 2.1.3
<br> - Organiza&ccedil;&atilde;o do INDEX e reorganiza&ccedil;&atilde;o de pastas.
<br> - Cria&ccedil;&atilde;o do novo tipo de papel Sociopedag&oacute;gico.
<br> - Aviso no HOME para informar caso algum papel n&atilde;o esteja cadastrado.
<br> - Adicionado Sistema de Bolsas para controle de atividades.
<br> - Aviso no HOME para informar o aluno ou professor participante de bolsa.
<br />
<br />
<b>Vers&atilde;o 1.432</b>
<br> - Adi&ccedil;&atilde;o do CHAT para atendimento e suporte aos alunos.
<br> - Diversas corre&ccedil;&otilde;es solicitadas por e-mail.
<br />
<br />
<b>Vers&atilde;o 1.431</b>
<br> - Adi&ccedil;&atilde;o do registro de Ocorr&ecirc;ncias.
<br> - Diversas corre&ccedil;&otilde;es solicitadas por e-mail.
<br />
<br />
<b>Vers&atilde;o 1.430</b>
<br> - Alterado o Cadastro de Tipos para permitir altera&ccedil;&atilde;o do Ano/Semestre para determinados tipos de pessoas.
<br />
<br />
<b>Vers&atilde;o 1.429</b>
<br> - Adi&ccedil;&atilde;o do registro de Atividades Acad&ecirc;micas para coordenadores.
<br> - Adi&ccedil;&atilde;o da tela de visualiza&ccedil;&atilde;o de ativiadades para os alunos.
<br> - Adi&ccedil;&atilde;o do relat&oacute;rio de atividades acad&ecirc;micas.
<br />
<br />
<b>Vers&atilde;o 1.428</b>
<br> - Diversas corre&ccedil;&otilde;es solicitadas por e-mail.
<br> - Adi&ccedil;&atilde;o de Gr&aacute;ficos em Relat&oacute;rios (Atribui&ccedil;&atilde;o Docente, Lan&ccedil;amento de Aulas, Totaliza&ccedil;&atilde;o de Matr&iacute;culas e Relat&oacute;rio de Frequ&ecirc;ncias).
<br> - Altera&ccedil;&atilde;o do Di&aacute;rio para mostrar situa&ccedil;&atilde;o de acordo com data.
<br> - Adi&ccedil;&atilde;o de data e hist&oacute;rico em Matr&iacute;culas.
<br> - Adicionada visualiza&ccedil;&atilde;o de acessos por IP e &uacute;ltimo acesso em Home.
<br />
<br />
<b>Vers&atilde;o 1.427</b>
<br> - Diversas corre&ccedil;&otilde;es solicitadas por e-mail.
<br> - Adi&ccedil;&atilde;o de aviso para coordenadores que n&atilde;o est&atilde;o relacionados com cursos e/ou &aacute;reas.
<br />
<br />
<b>Vers&atilde;o 1.426</b>
<br> - Adi&ccedil;&atilde;o de fotos em Ensalamentos.
<br> - Organiza&ccedil;&atilde;o de Arquivos (Boletim do Aluno).
<br> - Adi&ccedil;&atilde;o da visualiza&ccedil;&atilde;o do Boletim do Aluno em Relat&oacute;rios.
<br />
<br />
<b>Vers&atilde;o 1.425</b>
<br> - Corre&ccedil;&otilde;es na impress&atilde;o do di&aacute;rio.
<br> - Corre&ccedil;&otilde;es no boletim do aluno.
<br />
<br />
<b>Vers&atilde;o 1.424</b>
<br> - Diversas corre&ccedil;&otilde;es.
<br> - Altera&ccedil;&atilde;o na funcionalidade adicionada de acordo com o chamado - #258
<br> - Corre&ccedil;&atilde;o do chamado - #261
<br />
<br />
<b>Vers&atilde;o 1.423</b>
<br> - Adicionado Formul&aacute;rio de Atendimento ao Aluno, pois na PIT e FPA n&atilde;o &eacute; poss&iacute;vel informar.
<br> - Corre&ccedil;&atilde;o da Renova&ccedil;&atilde;o de Tempo.
<br> - Altera&ccedil;&atilde;o no modo de troca de semestre, evitando reload da página.
<br> - Funcionalidade adicionada de acordo com o chamado - #258
<br />
<br />
<b>Vers&atilde;o 1.422</b>
<br> - Adicionado FPA (Formul&aacute;rio de Prefer&ecirc;ncia de Atividades)
<br> - Adicionado PIT (Plano Individual de Trabalho)
<br> - Adicionado RIT (Relat&oacute;rio Individual de Trabalho)
<br> - Adicionada função que envia e-mail quando ocorre ações nos Planos de Ensino, FPA, RIT, PIT e Diário (chamado #208)
<br> - Corre&ccedil;&atilde;o do chamado - #240
<br> - Corre&ccedil;&atilde;o do chamado - #251
<br> - Corre&ccedil;&atilde;o do chamado - #252
<br> - Funcionalidade adicionada de acordo com o chamado - #254
<br />
<br />
<b>Vers&atilde;o 1.421</b>
<br> - Corre&ccedil;&atilde;o do chamado - #250
<br> - Funcionalidade adicionada de acordo com o chamado - #248
<br> - Funcionalidade adicionada de acordo com o chamado - #247
<br> - Corre&ccedil;&atilde;o do chamado - #244
<br> - Corre&ccedil;&atilde;o do chamado - #245
<br> - Corre&ccedil;&atilde;o do chamado - #246
<br> - Corre&ccedil;&atilde;o do chamado - #240
<br />
<br />
<b>Vers&atilde;o 1.420</b>
<br> - Corre&ccedil;&atilde;o no sistema de Notas
<br />
<br />
<b>Vers&atilde;o 1.419</b>
<br>
<br> - Corre&ccedil;&atilde;o do chamado - #241
<br> - Adicionada impress&atilde;o XLS em Relat&oacute;rios -&gt; Alunos
<br> - Corre&ccedil;&atilde;o do chamado - #234
<br> - Corre&ccedil;&atilde;o do chamado - #231
<br> - Altera&ccedil;&atilde;o no modo de autentica&ccedil;&atilde;o do LDAP
<br />
<br />
<b>Vers&atilde;o 1.418</b>
<br>
<br> - Corre&ccedil;&atilde;o do chamado - #229
<br> - Alterações na funcionalidade de acordo com o chamado - #223
<br> - Alterações na funcionalidade de acordo com o chamado - #228
<br> - Alterações na funcionalidade de acordo com o chamado - #213
<br> - Alterações na funcionalidade de acordo com o chamado - #209
<br> - Melhorias no relat&oacute;rio de Lan&ccedil;amento de Aulas
<br> - Altera&ccedil;&atilde;o no DigitaNotas para enviar um array de alunos
<br />
<br />
<b>Vers&atilde;o 1.417</b>
<br>
<br> - Funcionalidade adicionada de acordo com o chamado - #219 (Ordenação por data, nome)
<br> - Funcionalidade adicionada de acordo com o chamado - #216
<br> - Funcionalidade adicionada de acordo com o chamado - #212 (Catanduva)
<br> - Funcionalidade adicionada de acordo com o chamado - #209 (Novo relat&oacute;rio adicionado)
<br> - Corre&ccedil;&atilde;o do chamado - #156
<br />
<br />
<b>Vers&atilde;o 1.416</b>
<br>
<br> - Funcionalidade adicionada de acordo com o chamado - #205
<br> - Corre&ccedil;&atilde;o do chamado - #197
<br />
<br />
<b>Vers&atilde;o 1.415</b>
<br>
<br> - Corre&ccedil;&atilde;o do chamado - #204
<br> - Funcionalidade adicionada de acordo com o chamado - #199
<br> - Corre&ccedil;&atilde;o do chamado - #197
<br />
<br />
<b>Vers&atilde;o 1.414</b>
<br>
<br> - Corre&ccedil;&atilde;o do chamado - #196
<br> - Corre&ccedil;&atilde;o do chamado - #195
<br> - Corre&ccedil;&atilde;o do chamado - #194
<br> - Corre&ccedil;&atilde;o do chamado - #193
<br> - Corre&ccedil;&atilde;o do chamado - #190
<br> - Corre&ccedil;&atilde;o do chamado - #189
<br> - Corre&ccedil;&atilde;o do chamado - #179
<br> - Adicionado aviso para bibliotecas não instaladas do PHP.
<hr>
<br>
<b>Vers&atilde;o 1.413</b>
<br>
<br> - Adicionado recurso conforme chamado - #154
<br> - Adicionado link de contato no HOME para permitir envio de sugest&atilde;o e reportar erros.
<hr>
<br>
<b>Vers&atilde;o 1.412</b>
<br>
<br> - Corre&ccedil;&atilde;o do chamado - #185
<br> - Corre&ccedil;&atilde;o do chamado - #146
<br> - Corre&ccedil;&atilde;o do chamado - #164
<br> - Adicionada a op&ccedil;&atilde;o de impress&atilde;o em formato de planilha eletr&ocirc;nica da lista de chamada no menu do Professor.
<hr>
<br>
<b>Vers&atilde;o 1.411</b>
<br>
<br> - Adicionado WebServices de Atualiza&ccedil;&atilde;o de Vers&atilde;o
<br> - Corre&ccedil;&atilde;o do chamado - #184
<hr>
<br>
<b>Vers&atilde;o 1.410</b>
<br>
<br> - Corre&ccedil;&atilde;o do chamado - #174
<br> - Corre&ccedil;&atilde;o do chamado - #175
<br> - Corre&ccedil;&atilde;o do chamado - #176
<br> - Corre&ccedil;&atilde;o do chamado - #177
<br> - Corre&ccedil;&atilde;o do chamado - #178
<br> - Corre&ccedil;&atilde;o do chamado - #179
<br> - Corre&ccedil;&atilde;o do chamado - #180
<br> - Corre&ccedil;&atilde;o do chamado - #181
<hr>
<br>
<b>Vers&atilde;o 1.409</b>
<br>
<br> - Corre&ccedil;&atilde;o da listagem de matr&iacute;culas - #168
<br> - Aviso sobre o término da sess&atilde;o - #166
<br> - Reestrutura&ccedil;&atilde;o de c&oacute;digo - RELAT&Oacute;RIOS
<hr>
<br>
<b>Vers&atilde;o 1.408</b>
<br>
<br> - Organizar ordem dos planos de aula - chamado #160
<br> - Travamento da digitação de Instrumento Final de Avaliação e Reavaliação Final - #162
<br> - Reestrutura&ccedil;&atilde;o de c&oacute;digo - Admin
<hr>
<br>
<b>Vers&atilde;o 1.407</b>
<br>
<br> - Problemas com notas/avalia&ccedil;&otilde;es - chamado #155
<br> - Problema de sincroniza&ccedil;&atilde;o com o Nambei - #151
<br> - Reestrutura&ccedil;&atilde;o de c&oacute;digo - Planos de Ensino
<br> - Reestrutura&ccedil;&atilde;o de Secretaria/Socioecon&ocirc;mico
<br> - Reestrutura&ccedil;&atilde;o de Limites em Institui&ccedil;&otilde;es.
<br> - Atendimento do Professor realocado para o menu Relat&oacute;rios.
<br> - Corrigido problema do chamado #157
<hr>
<br>
<b>Vers&atilde;o 1.406</b>
<br>
<br> - Unifica&ccedil;&atilde;o dos Prazos de Aulas e Di&aacute;rios - chamado #117
<br> - Altera&ccedil;&atilde;o dos Prazos conforme chamado #117
<br> - Corre&ccedil;&atilde;o do chamado #153
<br> - Aviso referente ao chamado #152
<br> - Adi&ccedil;&atilde;o do período referente ao chamado #150
<br> - Adicionado bot&atilde;o de Home na Atribui&ccedil;&atilde;o no Professor
<br> - Altera&ccedil;&atilde;o do Link Di&aacute;rio do Professor
<br> - Corre&ccedil;&atilde;o da Tela de Avisos do Professor
<hr>
<br>
<b>Vers&atilde;o 1.405</b>
<br>
<br>Reestrutura&ccedil;&atilde;o de c&oacute;digo:
<br> - Avalia&ccedil;&atilde;o
<br> - Permitido alterar o campo sigla - chamado #149
<br> - Notas
<br> - Aulas
<br> - Frequ&ecirc;ncias
<br> - FTD Professor
<br> - Cadastro de Cidades
<br> - Cadastro de Pessoas
<br> - C&aacute;lculos do Sistema de Notas
<br> - Cria&ccedil;&atilde;o de visualiza&ccedil;&atilde;o comum.
<br> - Padroniza&ccedil;&atilde;o do Calend&aacute;rio.
<br>
<br>Inserido:
<br> - Hor&aacute;rio de atendimento do professor na tela do aluno.</li>
<br>
<br>
<a href="javascript:$('#index').load('<?= VIEW ?>/system/home.php');void(0);">VOLTAR</a>