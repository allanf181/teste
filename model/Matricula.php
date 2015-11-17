<?php

/**
 *
 * @Entity
 * @Table(name="Matriculas")
 */
class Matricula
{
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer", name="codigo")
     */
    private $codigo;
 
    /**
     * @ManyToOne(targetEntity="Pessoa", inversedBy="matriculas")
     * @JoinColumn(name="aluno", referencedColumnName="codigo")
     */
    private $aluno;
    
    /**
     * @ManyToOne(targetEntity="Atribuicao", inversedBy="matriculas")
     * @JoinColumn(name="atribuicao", referencedColumnName="codigo")
     */
    private $atribuicao;
    
    function getCodigo() {
        return $this->codigo;
    }

    /** @return Pessoa */
    function getAluno() {
        return $this->aluno;
    }

    function getAtribuicao() {
        return $this->atribuicao;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setAluno($aluno) {
        $this->aluno = $aluno;
    }

    function setAtribuicao($atribuicao) {
        $this->atribuicao = $atribuicao;
    }


    
    
    
}

?>