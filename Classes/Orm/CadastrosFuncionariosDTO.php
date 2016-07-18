<?php

namespace Classes\ORM;

/**
 * Classe DTO para a tabela cadastros.funcionarios
 *
 * Data Transfer Object (DTO) - Utilizado apenas como estrutura para transporte de dados
 *
 * Contém a mesma estrutura da tabela, os campos da tabela com os seus Get's and Set's
 *
 * Versão: 3.0
 * Criado Por: Rogério Lamarques
 * Data Criação: 15/07/2016 19:31:49
 */
class CadastrosFuncionariosDTO { 
    // Define as propriedades
    private $idFuncionarios = false;
    private $idClientes = false;
    private $matricula = false;
    private $nome = false;
    private $siglanomemeio = false;
    private $sobrenome = false;
    private $usuario = false;
    private $senha = false;
    private $email = false;
    private $permissao = false;
    private $ativo = false;

    /**
     * Seta um valor à propriedade idFuncionarios
     * @access public
     * @param int4 $idFuncionarios
     */
    public function setIdFuncionarios($idFuncionarios)
    {
        $this->idFuncionarios = intval($idFuncionarios);
    }

    /**
     * Retorna o valor da propriedade idFuncionarios
     * @access public
     * @return int4
     */
    public function getIdFuncionarios()
    {
        return $this->idFuncionarios;
    }

    /**
     * Seta um valor à propriedade idClientes
     * @access public
     * @param int4 $idClientes
     */
    public function setIdClientes($idClientes)
    {
        $this->idClientes = intval($idClientes);
    }

    /**
     * Retorna o valor da propriedade idClientes
     * @access public
     * @return int4
     */
    public function getIdClientes()
    {
        return $this->idClientes;
    }

    /**
     * Seta um valor à propriedade matricula
     * @access public
     * @param int4 $matricula
     */
    public function setMatricula($matricula)
    {
        $this->matricula = strlen($matricula) > 0 ? intval($matricula) : '';
    }

    /**
     * Retorna o valor da propriedade matricula
     * @access public
     * @return int4
     */
    public function getMatricula()
    {
        return $this->matricula;
    }

    /**
     * Seta um valor à propriedade nome
     * @access public
     * @param varchar $nome
     */
    public function setNome($nome)
    {
        $this->nome = strlen($nome) > 0 ? substr($nome,0,255) : '';
    }

    /**
     * Retorna o valor da propriedade nome
     * @access public
     * @return varchar
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Seta um valor à propriedade siglanomemeio
     * @access public
     * @param varchar $siglanomemeio
     */
    public function setSiglanomemeio($siglanomemeio)
    {
        $this->siglanomemeio = strlen($siglanomemeio) > 0 ? substr($siglanomemeio,0,20) : '';
    }

    /**
     * Retorna o valor da propriedade siglanomemeio
     * @access public
     * @return varchar
     */
    public function getSiglanomemeio()
    {
        return $this->siglanomemeio;
    }

    /**
     * Seta um valor à propriedade sobrenome
     * @access public
     * @param varchar $sobrenome
     */
    public function setSobrenome($sobrenome)
    {
        $this->sobrenome = strlen($sobrenome) > 0 ? substr($sobrenome,0,255) : '';
    }

    /**
     * Retorna o valor da propriedade sobrenome
     * @access public
     * @return varchar
     */
    public function getSobrenome()
    {
        return $this->sobrenome;
    }

    /**
     * Seta um valor à propriedade usuario
     * @access public
     * @param varchar $usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = strlen($usuario) > 0 ? substr($usuario,0,255) : '';
    }

    /**
     * Retorna o valor da propriedade usuario
     * @access public
     * @return varchar
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Seta um valor à propriedade senha
     * @access public
     * @param varchar $senha
     */
    public function setSenha($senha)
    {
        $this->senha = strlen($senha) > 0 ? substr($senha,0,255) : '';
    }

    /**
     * Retorna o valor da propriedade senha
     * @access public
     * @return varchar
     */
    public function getSenha()
    {
        return $this->senha;
    }

    /**
     * Seta um valor à propriedade email
     * @access public
     * @param varchar $email
     */
    public function setEmail($email)
    {
        $this->email = strlen($email) > 0 ? substr($email,0,255) : '';
    }

    /**
     * Retorna o valor da propriedade email
     * @access public
     * @return varchar
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Seta um valor à propriedade permissao
     * @access public
     * @param int4 $permissao
     */
    public function setPermissao($permissao)
    {
        $this->permissao = strlen($permissao) > 0 ? intval($permissao) : '';
    }

    /**
     * Retorna o valor da propriedade permissao
     * @access public
     * @return int4
     */
    public function getPermissao()
    {
        return $this->permissao;
    }

    /**
     * Seta um valor à propriedade ativo
     * @access public
     * @param bool $ativo
     */
    public function setAtivo($ativo)
    {
        $this->ativo = $ativo;
    }

    /**
     * Retorna o valor da propriedade ativo
     * @access public
     * @return bool
     */
    public function getAtivo()
    {
        return $this->ativo;
    }

}
