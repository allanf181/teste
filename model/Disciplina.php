<?php

/**
 *
 * @Entity
 * @Table(name="Disciplinas")
 */
class Disciplina
{
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer", name="codigo")
     */
    private $codigo;
 
    /** @Column  */
    private $numero;
    
    /** @Column  */
    private $modulo;
    
    /** @Column  */
    private $nome;
    
    /** @Column  */
    private $ch;
    
    /**
     * @ManyToOne(targetEntity="Curso", inversedBy="disciplinas")
     * @JoinColumn(name="curso", referencedColumnName="codigo")
     */
    private $curso;
    
    /**
     * @OneToMany(targetEntity="Atribuicao", mappedBy="disciplina")
     **/
    private $atribuicoes;    
    
    public function __construct() {
        $this->atribuicoes = new Doctrine\Common\Collections\ArrayCollection();
    }    
    
    function getCodigo() {
        return $this->codigo;
    }

    function getNumero() {
        return $this->numero;
    }

    function getModulo() {
        return $this->modulo;
    }

    function getNome() {
        return $this->nome;
    }

    function getCh() {
        return $this->ch;
    }

    function getCurso() {
        return $this->curso;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setNumero($numero) {
        $this->numero = $numero;
    }

    function setModulo($modulo) {
        $this->modulo = $modulo;
    }

    function setNome($nome) {
        $this->nome = $nome;
    }

    function setCh($ch) {
        $this->ch = $ch;
    }

    function setCurso($curso) {
        $this->curso = $curso;
    }

    function getAtribuicoes() {
        return $this->atribuicoes;
    }

    function setAtribuicoes($atribuicoes) {
        $this->atribuicoes = $atribuicoes;
    }



    
}

?>