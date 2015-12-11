<?php

class WebDiario67 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("alter table Pessoas add column ocultarAvisos char(1);");
        $this->execute("insert into TiposAvaliacoes (codigo,nome,tipo,modalidade,calculo,arredondar,notaMaior,notaMenor,sigla,final,notaUltimBimestre,qdeMinima,notaMaxima)
                        select 0,'Reavaliação Final', 'recuperacao',codigo, 'sub_media', 0,4,6,'REF',0,6,1,10 from Modalidades where codigo>=2000 AND codigo<3000;");

        printf("\nPatch WebDiario 67: OK\n\n");
        
    }//up()

    public function down()
    {
    }//down()
    
}
