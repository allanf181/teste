<?php

/**
 *
 * @Entity
 * @Table(name="Modalidades")
 */
class Modalidade
{
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer", name="codigo")
     */
    private $codigo;
 
    /**
     * @Column(type="string", name="nome")
     */
    private $nome;
    
    /**
     * @OneToMany(targetEntity="Curso", mappedBy="modalidade")
     **/
    private $cursos;
    
    public function __construct() {
        $this->cursos = new Doctrine\Common\Collections\ArrayCollection();
    }    
    
    function getCodigo() {
        return $this->codigo;
    }

    function getNome() {
        return $this->nome;
    }

    function getCursos() {
        return $this->cursos;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setNome($nome) {
        $this->nome = $nome;
    }

    function setCursos($cursos) {
        $this->cursos = $cursos;
    }




}

?>