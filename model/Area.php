<?php

/**
 *
 * @Entity
 * @Table(name="Areas")
 */
class Area
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
    
    function __construct($codigo, $nome) {
        $this->codigo = $codigo;
        $this->nome = $nome;
    }

    
    public function getCodigo()
    {
        return $this->codigo;
    }
 
    public function getNome()
    {
        return $this->nome;
    }
 
    public function setNome($nome)
    {
        $this->nome = $nome;
    }
 
}

?>