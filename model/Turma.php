<?php

/**
 * @Entity
 * @Table(name="Turmas")
 */
class Turma {

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer", name="codigo")
     */
    private $codigo;
    
    /**
     * @Column
     */
    private $ano;
    
    /**
     * @Column
     */
    private $semestre;
    
    /**
     * @Column
     */
    private $sequencia;
    
    /**
     * @Column
     */
    private $numero;
    
    /**
     * @Column
     */
    private $serie;
//    
    /**
     * @ManyToOne(targetEntity="Curso", inversedBy="turmas")
     * @JoinColumn(name="curso", referencedColumnName="codigo")
     */
    private $curso;
    
    /**
     * @ManyToOne(targetEntity="Turno", inversedBy="turmas")
     * @JoinColumn(name="turno", referencedColumnName="codigo")
     */
    private $turno;
    
    /**
     * @OneToMany(targetEntity="Atribuicao", mappedBy="turma")
     **/
    private $atribuicoes;    
   
    
    public function __construct() {
        $this->atribuicoes = new Doctrine\Common\Collections\ArrayCollection();
    }    

    function getCodigo() {
        return $this->codigo;
    }

    function getAno() {
        return $this->ano;
    }

    function getSemestre() {
        return $this->semestre;
    }

    function getSequencia() {
        return $this->sequencia;
    }

    function getNumero() {
        return $this->numero;
    }

    function getSerie() {
        return $this->serie;
    }

    function getCurso() {
        return $this->curso;
    }

    function getTurno() {
        return $this->turno;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setAno($ano) {
        $this->ano = $ano;
    }

    function setSemestre($semestre) {
        $this->semestre = $semestre;
    }

    function setSequencia($sequencia) {
        $this->sequencia = $sequencia;
    }

    function setNumero($numero) {
        $this->numero = $numero;
    }

    function setSerie($serie) {
        $this->serie = $serie;
    }

    function setCurso($curso) {
        $this->curso = $curso;
    }

    function setTurno($turno) {
        $this->turno = $turno;
    }
    
    /** @return Atribuicao */    
    function getAtribuicoes() {
        return $this->atribuicoes;
    }

    function setAtribuicoes($atribuicoes) {
        $this->atribuicoes = $atribuicoes;
    }







}
