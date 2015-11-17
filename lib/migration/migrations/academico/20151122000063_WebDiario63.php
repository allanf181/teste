<?php

class WebDiario63 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("update TiposAvaliacoes set arredondar=0");
        $this->execute("update Permissoes set permissao=CONCAT(permissao,',view/professor/arredondamento.php') where tipo=2");
        $this->execute("alter table NotasFinais modify column retorno varchar(255);");
        $this->execute("insert into TiposAvaliacoes (codigo,nome,tipo,modalidade,calculo,arredondar,notaMaior,notaMenor,sigla,final,notaUltimBimestre,qdeMinima,notaMaxima)
                        select 0,'Reavaliação Final', 'recuperacao',codigo, 'sub_media', 0,4,6,'REF',0,6,1,10 from Modalidades where codigo<10;");
        $this->execute("alter table NotasFinais add column reavaliacao float after falta;");
        $this->execute("create table PermissoesArquivos(
                            codigo int primary key auto_increment,
                            tipo int,
                            permissao varchar(500),
                            nome varchar(500),
                            menu varchar(500),
                            foreign key (tipo) references Tipos(codigo)
                        );");

        $this->execute("CREATE PROCEDURE doiterate()
                        BEGIN
                          SET @n=1;
                          set @t=0;
                          set @nTipos=(select count(*) from Permissoes);


                          label1: LOOP
                                set @tipo1=(select tipo from Permissoes where tipo>@t limit 1);
                            if @tipo = null then
                                        leave label1;
                            end if;

                                set @p1=(select (LENGTH(permissao) - LENGTH(REPLACE(permissao, ',', ''))) / LENGTH(',') from Permissoes where tipo=@tipo1)+1;

                            set @d1=(select substring_index(substring_index(permissao,',',@n),',',-1) from Permissoes where tipo=@tipo1);
                            set @d2=(select substring_index(substring_index(nome,',',@n),',',-1) from Permissoes where tipo=@tipo1);
                            set @d3=(select substring_index(substring_index(menu,',',@n),',',-1) from Permissoes where tipo=@tipo1);

                            if @d1 = @d1a then
                                        set @d1='';
                            end if;
                            if @d2 = @d2a then
                                        set @d2='';
                            end if;
                            if @d3 = @d3a then
                                        set @d3='';
                            end if;

                                insert into PermissoesArquivos (codigo,tipo,permissao,nome,menu)
                                select 
                                0,
                            tipo,
                            @d1 permissao,
                            @d2 nome,
                            @d3 menu
                            from Permissoes where tipo=@tipo1;

                            if @d1 != '' then
                                        set @d1a = @d1;
                            end if;
                            if @d2 != '' then
                                        set @d2a = @d2;
                            end if;
                            if @d3 != '' then
                                        set @d3a = @d3;
                            end if;

                            SET @n = @n + 1;
                            IF @n <= @p1 THEN
                              ITERATE label1;
                                elseif @n > @p1 then
                                set @t=@tipo1;
                                set @n=1;
                                ITERATE label1;
                            END IF;
                            LEAVE label1;
                          END LOOP label1;
                          SET @x = @p1;
                        END;
        ");
        $this->execute("call doiterate()");
        

        // ATUALIZAR VERSAO ATUAL
        $this->execute("UPDATE Instituicoes SET versao='446', versaoAtual='446'");
        printf("\nPatch WebDiario 63: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
