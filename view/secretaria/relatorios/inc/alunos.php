<?php
require $_SESSION['CONFIG'] ;
require MYSQL;
require VARIAVEIS;

// lista os alunos cadastrados

if (isset($_GET["curso"]) && isset($_GET["turma"])) {
    
//    echo "<br>endereco: ".($_GET["endereco"])==true;

    $ano = $_SESSION["ano"];
    $semestre = $_SESSION["semestre"];
    
    if (!empty($_GET["curso"]))
        $curso = dcrip($_GET["curso"]);
    if (!empty($_GET["turma"]))
        $turma = dcrip($_GET["turma"]);
    
    // valores fixos
    $titulosColunas[]="Prontuário";
    $titulosColunas[]="Nome";
    $largura[]=14;
    $largura[]=60;
    //12,60,20,15,40,20,20,20,20,30, 0);
    if (($_GET["rg"])=='true'){
        $rg = ", a.rg";
        $titulosColunas[]="RG";
        $largura[]=16;
    }
    if (($_GET["cpf"])=='true'){
        $cpf = ", a.cpf";
        $titulosColunas[]="CPF";
        $largura[]=18;
    }
    if (($_GET["nasc"])=='true'){
        $nasc = ", date_format(a.nascimento, '%d/%m/%Y') nascimento ";
        $titulosColunas[]="Nasc";
        $largura[]=14;
    }
    if (($_GET["endereco"])=='true'){
        $endereco = ", a.endereco";
        $titulosColunas[]="Endereço";
        $largura[]=60;
    }
    if (($_GET["bairro"])=='true'){
        $bairro = ", a.bairro";
        $titulosColunas[]="Bairro";
        $largura[]=25;
    }
    if (($_GET["cidade"])=='true'){
        $cidade = ", c.nome";
        $titulosColunas[]="Cidade";
    }
    if (($_GET["telefone"])=='true'){
        $telefone = ", a.telefone";
        $titulosColunas[]="Telefone";
        $largura[]=18;
    }
    if (($_GET["celular"])=='true'){
        $celular = ", a.celular";
        $titulosColunas[]="Celular";
        $largura[]=18;
        $largura[]=18;
    }
    if (($_GET["email"])=='true'){
        $email = ", a.email";
        $titulosColunas[]="Email";
        $largura[]=40;
    }
    if (($_GET["obs"])=='true'){
        $obs = ", ''";
        $titulosColunas[]="Observação";
        $largura[]=40;
    }
    $restricao = ""; // padrão é sem restrição
    
    // restrições
    if (!empty($curso) && empty($turma)){
        $sql = "select c.nome from Cursos c where c.codigo=$curso";
        $result = mysql_query($sql);
        $linha = mysql_fetch_row($result);
        $nomeCurso = $linha[0];
        
        $restricao.= " and t.curso=$curso";
    }
    else
        $campoCurso = ", c2.nome";
    
    if (!empty($turma)){
        $sql = "select c.nome, t.numero from Cursos c, Turmas t where c.codigo=$curso and t.codigo=$turma";
        //echo $sql;
        $result = mysql_query($sql);
        $linha = mysql_fetch_row($result);
        $nomeCurso = $linha[0];
        $nomeTurma = $linha[1];

        $restricao.= " and t.codigo=$turma";
    }
    else
        $campoTurma = ", t.numero";
    
    
    $sql = "select a.prontuario, upper(a.nome)
        $rg $cpf $nasc $endereco $bairro $cidade $telefone $celular $email $obs  $campoCurso $campoTurma
        from Tipos ti, PessoasTipos pt, Pessoas a, Cidades c, Matriculas m, Turmas t, Cursos c2, Atribuicoes at
        where pt.tipo = ti.codigo
        and a.codigo = pt.pessoa
        and a.cidade=c.codigo 
        and m.aluno=a.codigo 
        and m.atribuicao=at.codigo
        and at.turma=t.codigo 
        and t.curso=c2.codigo 
        $restricao
        and ti.codigo=$ALUNO 
        group by a.nome
        order by a.nome";

    //echo $sql;

    $titulo = "Relação de Alunos";
    $titulo2 = "";
    if (empty($campoCurso)){
        $titulo = $nomeCurso;
    }
    else if (empty($campoTurma)){ 
        $titulo = $nomeCurso;
        $titulo2 = $nomeTurma;
    }
    else{
        $titulosColunas[]="Curso";
        $largura[]=30;
        $titulosColunas[]="Turma";
        $largura[]=15;
    }
    $rodape = $SITE_TITLE;
    $fonte = 'Times';
    $tamanho = 7;
    $alturaLinha = 5;
    $orientacao = "L"; //Landscape 
    //$orientacao = "P"; //Portrait 
    $papel = "A4";
    
    // gera o relatório em PDF
    include(PATH.LIB.'/relatorio_banco.php');
}
?>