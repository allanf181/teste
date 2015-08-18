<?php

/**
 * @Entity
 * @Table(name="Atribuicoes")
 */
class Atribuicao {

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer", name="codigo")
     */
    private $codigo;
    /**
     * @ManyToOne(targetEntity="Disciplina", inversedBy="atribuicoes")
     * @JoinColumn(name="disciplina", referencedColumnName="codigo")
     */
    private $disciplina;
    
    /**
     * @ManyToOne(targetEntity="Turma", inversedBy="atribuicoes")
     * @JoinColumn(name="turma", referencedColumnName="codigo")
     */
    private $turma;
    
    /** @Column */
    private $bimestre;
    
    /** @Column */
    private $ementa;
    
    /** @Column */
    private $dataInicio;
    
    /** @Column */
    private $dataFim;
    
    /** @Column */
    private $aulaPrevista;
    
    /** @Column */
    private $observacoes;
    
    /** @Column */
    private $status;
    
    /** @Column */
    private $periodo;
    
    /** @Column */
    private $prazo;
    
    /** @Column */
    private $competencias;
    
    /** @Column */
    private $calculo;
    
    /** @Column */
    private $formula;
    
    /** @Column */
    private $subturma;
    
    /** @Column */
    private $eventod;
    
    /**
     * @OneToMany(targetEntity="Matricula", mappedBy="atribuicao")
     **/
    private $matriculas;
    
    
    
    function getCodigo() {
        return $this->codigo;
    }

    /** @return Disciplina */
    function getDisciplina() {
        return $this->disciplina;
    }

    function getTurma() {
        return $this->turma;
    }

    function getBimestre() {
        return $this->bimestre;
    }

    function getEmenta() {
        return $this->ementa;
    }

    function getDataInicio() {
        return $this->dataInicio;
    }

    function getDataFim() {
        return $this->dataFim;
    }

    function getAulaPrevista() {
        return $this->aulaPrevista;
    }

    function getObservacoes() {
        return $this->observacoes;
    }

    function getStatus() {
        return $this->status;
    }

    function getPeriodo() {
        return $this->periodo;
    }

    function getPrazo() {
        return $this->prazo;
    }

    function getCompetencias() {
        return $this->competencias;
    }

    function getCalculo() {
        return $this->calculo;
    }

    function getFormula() {
        return $this->formula;
    }

    function getSubturma() {
        return $this->subturma;
    }

    function getEventod() {
        return $this->eventod;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setDisciplina($disciplina) {
        $this->disciplina = $disciplina;
    }

    function setTurma($turma) {
        $this->turma = $turma;
    }

    function setBimestre($bimestre) {
        $this->bimestre = $bimestre;
    }

    function setEmenta($ementa) {
        $this->ementa = $ementa;
    }

    function setDataInicio($dataInicio) {
        $this->dataInicio = $dataInicio;
    }

    function setDataFim($dataFim) {
        $this->dataFim = $dataFim;
    }

    function setAulaPrevista($aulaPrevista) {
        $this->aulaPrevista = $aulaPrevista;
    }

    function setObservacoes($observacoes) {
        $this->observacoes = $observacoes;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setPeriodo($periodo) {
        $this->periodo = $periodo;
    }

    function setPrazo($prazo) {
        $this->prazo = $prazo;
    }

    function setCompetencias($competencias) {
        $this->competencias = $competencias;
    }

    function setCalculo($calculo) {
        $this->calculo = $calculo;
    }

    function setFormula($formula) {
        $this->formula = $formula;
    }

    function setSubturma($subturma) {
        $this->subturma = $subturma;
    }

    function setEventod($eventod) {
        $this->eventod = $eventod;
    }

    /** @return Matricula */
    function getMatriculas() {
        return $this->matriculas;
    }

    function setMatriculas($matriculas) {
        $this->matriculas = $matriculas;
    }


    

}
