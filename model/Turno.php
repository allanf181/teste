<?php

/**
* @Entity
* @Table(name="Turnos")
*/
class Turno {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer", name="codigo")
     */
    protected $codigo;
    
    /**
     * @Column(type="string", name="nome")
     */
    protected $nome;
    
    /**
     * @Column(type="string", name="sigla")
     */
    protected $sigla;
    
    /**
     * @OneToMany(targetEntity="Turma", mappedBy="turno")
     **/
    private $turmas;    
   
    public function getCodigo() {
        return $this->codigo;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getSigla() {
        return $this->sigla;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setSigla($sigla) {
        $this->sigla = $sigla;
    }

    function getTurmas() {
        return $this->turmas;
    }

    function setTurmas($turmas) {
        $this->turmas = $turmas;
    }


    
}
