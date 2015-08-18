<?php

/**
 *
 * @Entity
 * @Table(name="Pessoas")
 */
class Pessoa {

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer", name="codigo")
     */
    protected $codigo;

    /** @Column */
    protected $nome;
    
    /** @Column */
    protected $prontuario;
    
    /** @Column */
    protected $senha;
    
    /** @Column */
    protected $cpf;
    
    /** @Column */
    protected $rg;
    
    /** @Column */
    protected $naturalidade;
    
    /** @Column(type="date") */
    private $nascimento;
    
    /** @Column */
    protected $endereco;
    /** @Column */
    protected $bairro;
    /** @Column */
    protected $cidade;
    /** @Column */
    protected $cep;
    /** @Column */
    protected $telefone;
    /** @Column */
    protected $celular;
    
    /**
     * @Column(type="string")
     */    
    protected $email;
    /** @Column */
    protected $observacoes;
    /** @Column */
    protected $foto;
    /** @Column */
    protected $sexo;
    /** @Column */
    protected $dataSenha;
    /** @Column */
    protected $recuperaSenha;
    /** @Column */
    protected $ano1g;
    /** @Column */
    protected $escola1g;
    /** @Column */
    protected $lattes;
    /** @Column */
    protected $bloqueioFoto;
    /** @Column(type="datetime") */
    protected $dataAlteracao;
    
    function __construct($codigo, $nome, $prontuario, $senha, $cpf, $rg, $naturalidade, $nascimento, $endereco, $bairro, $cidade, $cep, $telefone, $celular, $email, $observacoes, $foto, $sexo, $dataSenha, $recuperaSenha, $ano1g, $escola1g, $lattes, $bloqueioFoto, $dataAlteracao) {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->prontuario = $prontuario;
        $this->senha = $senha;
        $this->cpf = $cpf;
        $this->rg = $rg;
        $this->naturalidade = $naturalidade;
        $this->nascimento = $nascimento;
        $this->endereco = $endereco;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->cep = $cep;
        $this->telefone = $telefone;
        $this->celular = $celular;
        $this->email = $email;
        $this->observacoes = $observacoes;
        $this->foto = $foto;
        $this->sexo = $sexo;
        $this->dataSenha = $dataSenha;
        $this->recuperaSenha = $recuperaSenha;
        $this->ano1g = $ano1g;
        $this->escola1g = $escola1g;
        $this->lattes = $lattes;
        $this->bloqueioFoto = $bloqueioFoto;
        $this->dataAlteracao = $dataAlteracao;
    }

    function getCodigo() {
        return $this->codigo;
    }

    function getNome() {
        return $this->nome;
    }

    function getProntuario() {
        return $this->prontuario;
    }

    function getSenha() {
        return $this->senha;
    }

    function getCpf() {
        return $this->cpf;
    }

    function getRg() {
        return $this->rg;
    }

    function getNaturalidade() {
        return $this->naturalidade;
    }

    function getNascimento() {
        return $this->nascimento;
    }

    function getEndereco() {
        return $this->endereco;
    }

    function getBairro() {
        return $this->bairro;
    }

    function getCidade() {
        return $this->cidade;
    }

    function getCep() {
        return $this->cep;
    }

    function getTelefone() {
        return $this->telefone;
    }

    function getCelular() {
        return $this->celular;
    }

    function getEmail() {
        return $this->email;
    }

    function getObservacoes() {
        return $this->observacoes;
    }

    function getFoto() {
        return $this->foto;
    }

    function getSexo() {
        return $this->sexo;
    }

    function getDataSenha() {
        return $this->dataSenha;
    }

    function getRecuperaSenha() {
        return $this->recuperaSenha;
    }

    function getAno1g() {
        return $this->ano1g;
    }

    function getEscola1g() {
        return $this->escola1g;
    }

    function getLattes() {
        return $this->lattes;
    }

    function getBloqueioFoto() {
        return $this->bloqueioFoto;
    }

    function getDataAlteracao() {
        return $this->dataAlteracao;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setNome($nome) {
        $this->nome = $nome;
    }

    function setProntuario($prontuario) {
        $this->prontuario = $prontuario;
    }

    function setSenha($senha) {
        $this->senha = $senha;
    }

    function setCpf($cpf) {
        $this->cpf = $cpf;
    }

    function setRg($rg) {
        $this->rg = $rg;
    }

    function setNaturalidade($naturalidade) {
        $this->naturalidade = $naturalidade;
    }

    function setNascimento($nascimento) {
        $this->nascimento = $nascimento;
    }

    function setEndereco($endereco) {
        $this->endereco = $endereco;
    }

    function setBairro($bairro) {
        $this->bairro = $bairro;
    }

    function setCidade($cidade) {
        $this->cidade = $cidade;
    }

    function setCep($cep) {
        $this->cep = $cep;
    }

    function setTelefone($telefone) {
        $this->telefone = $telefone;
    }

    function setCelular($celular) {
        $this->celular = $celular;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setObservacoes($observacoes) {
        $this->observacoes = $observacoes;
    }

    function setFoto($foto) {
        $this->foto = $foto;
    }

    function setSexo($sexo) {
        $this->sexo = $sexo;
    }

    function setDataSenha($dataSenha) {
        $this->dataSenha = $dataSenha;
    }

    function setRecuperaSenha($recuperaSenha) {
        $this->recuperaSenha = $recuperaSenha;
    }

    function setAno1g($ano1g) {
        $this->ano1g = $ano1g;
    }

    function setEscola1g($escola1g) {
        $this->escola1g = $escola1g;
    }

    function setLattes($lattes) {
        $this->lattes = $lattes;
    }

    function setBloqueioFoto($bloqueioFoto) {
        $this->bloqueioFoto = $bloqueioFoto;
    }

    function setDataAlteracao($dataAlteracao) {
        $this->dataAlteracao = $dataAlteracao;
    }



}

?>