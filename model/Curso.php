<?php

/**
 * @Entity
 * @Table(name="Cursos")
 */
class Curso {

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer", name="codigo")
     */
    private $codigo;

    /**
     * @Column(type="string")
     */
    private $nome;
    
    /**
     * @ManyToOne(targetEntity="Modalidade", inversedBy="cursos")
     * @JoinColumn(name="modalidade", referencedColumnName="codigo")
     */
    private $modalidade;
    
    /**
     * @Column(type="string")
     */
    private $fechamento;

    /**
     * @Column(type="string")
     */
    private $nomeAlternativo;

    /**
     * @OneToMany(targetEntity="Turma", mappedBy="curso")
     **/
    private $turmas;
//
//    /**
//     * @OneToMany(targetEntity="Disciplina", mappedBy="curso")
//     **/
//    private $disciplinas;    
    
    public function __construct() {
        $this->turmas = new Doctrine\Common\Collections\ArrayCollection();
        $this->disciplinas = new Doctrine\Common\Collections\ArrayCollection();
    }    

    function getCodigo() {
        return $this->codigo;
    }

    public function getNome() {
        return $this->nome;
    }

    function getModalidade() {
        return $this->modalidade;
    }

    function getFechamento() {
        return $this->fechamento;
    }

    function getNomeAlternativo() {
        return $this->nomeAlternativo;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setNome($nome) {
        $this->nome = $nome;
    }

    function setModalidade($modalidade) {
        $this->modalidade = $modalidade;
    }

    function setFechamento($fechamento) {
        $this->fechamento = $fechamento;
    }

    function setNomeAlternativo($nomeAlternativo) {
        $this->nomeAlternativo = $nomeAlternativo;
    }

    /** @return Turma */
    function getTurmas() {
        return $this->turmas;
    }

    function setTurmas($turmas) {
        $this->turmas = $turmas;
    }

    function getDisciplinas() {
        return $this->disciplinas;
    }

    function setDisciplinas($disciplinas) {
        $this->disciplinas = $disciplinas;
    }





    
}
