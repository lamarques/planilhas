<?php

namespace Classes\Orm;

use Classes\Orm\CadastrosFuncionariosDAO;
use Classes\Orm\CadastrosFuncionariosDTO;

/**
 * Classe responsavel em utilizar Data Access Object para cadastros.funcionarios
 *
 * Versão: 3.0
 * Criado Por: Rogério Lamarques
 * Data Criação: 15/07/2016 19:31:49
 */
class CadastrosFuncionariosExecuta { 
    // Define as propriedades
    private $pdo;
    private $criaHistorico;
    private $sql;
    private $ultimoId;

    /**
     * Método Construtor
     * @access public
     */
    public function __construct(\PDO $pdo, $historico = true) { 
        $this->pdo = $pdo;
        $this->criaHistorico = $historico;
    }

    /**
     * Retorna o valor da propriedade sql
     * @access public
     */
    public function getSql() { 
        return $this->sql;
    }

    /**
     * Retorna o valor da propriedade ultimoId
     * @access public
     */
    public function getUltimoId() { 
        return $this->ultimoId;
    }

    /**
     * Método Responsavel em salvar
     * @access public
     * @param array $dadosFuncionarios Dado contendo o nome do campo chave primaria e seu valor
     */
    public function salvar($dadosFuncionarios) { 
        if ( !empty($dadosFuncionarios['id_funcionarios']) )
        {
            $this->editar($dadosFuncionarios);
        }
        else
        {
            $this->inserir($dadosFuncionarios);
        }
    }

    /**
     * Método Responsavel em inserir
     * @access public
     * @param array $dadosFuncionarios Dados contendo o nome dos campos e seus valores
     */
    public function inserir($dadosFuncionarios) { 
        // Instancia classe Data Transfer Object (DTO) para cadastros.funcionarios
        $funcionariosDTO = new CadastrosFuncionariosDTO;
        // Seta valor as propriedades
        if ( !empty($dadosFuncionarios['id_clientes']) )
        {
            $funcionariosDTO->setIdClientes($dadosFuncionarios['id_clientes']);
        }
        else
        {
            throw new \PDOException("Chave estrangeira id_clientes nao definida");
        }
        if ( isset($dadosFuncionarios['matricula']) )
        {
            $funcionariosDTO->setMatricula($dadosFuncionarios['matricula']);
        }
        if ( isset($dadosFuncionarios['nome']) )
        {
            $funcionariosDTO->setNome($dadosFuncionarios['nome']);
        }
        if ( isset($dadosFuncionarios['siglanomemeio']) )
        {
            $funcionariosDTO->setSiglanomemeio($dadosFuncionarios['siglanomemeio']);
        }
        if ( isset($dadosFuncionarios['sobrenome']) )
        {
            $funcionariosDTO->setSobrenome($dadosFuncionarios['sobrenome']);
        }
        if ( isset($dadosFuncionarios['usuario']) )
        {
            $funcionariosDTO->setUsuario($dadosFuncionarios['usuario']);
        }
        if ( isset($dadosFuncionarios['senha']) )
        {
            $funcionariosDTO->setSenha($dadosFuncionarios['senha']);
        }
        if ( isset($dadosFuncionarios['email']) )
        {
            $funcionariosDTO->setEmail($dadosFuncionarios['email']);
        }
        if ( isset($dadosFuncionarios['permissao']) )
        {
            $funcionariosDTO->setPermissao($dadosFuncionarios['permissao']);
        }
        if ( isset($dadosFuncionarios['ativo']) )
        {
            $funcionariosDTO->setAtivo($dadosFuncionarios['ativo']);
        }
        // Instancia classe Data Access Object (DAO) para cadastros.funcionarios
        $funcionariosDAO = new CadastrosFuncionariosDAO($this->pdo, $this->criaHistorico);
        // Chama método responsável em Inserir
        $funcionariosDAO->inserir($funcionariosDTO);
        // Seta o ultimo id inserido a propriedade idFuncionarios
        $this->ultimoId = $funcionariosDTO->getIdFuncionarios();
        // Seta o sql executado a propriedade sql
        $this->sql = $funcionariosDAO->getSql();
    }

    /**
     * Método Responsavel em editar
     * @access public
     * @param array $dadosFuncionarios Dados contendo o nome dos campos e seus valores
     */
    public function editar($dadosFuncionarios) { 
        // Instancia classe Data Transfer Object (DTO) para cadastros.funcionarios
        $funcionariosDTO = new CadastrosFuncionariosDTO;
        // Seta valor as propriedades
        if ( !empty($dadosFuncionarios['id_funcionarios']) )
        {
            $funcionariosDTO->setIdFuncionarios($dadosFuncionarios['id_funcionarios']);
        }
        else
        {
            throw new \PDOException("Chave primaria id_funcionarios nao definida");
        }
        if ( !empty($dadosFuncionarios['id_clientes']) )
        {
            $funcionariosDTO->setIdClientes($dadosFuncionarios['id_clientes']);
        }
        if ( isset($dadosFuncionarios['matricula']) )
        {
            $funcionariosDTO->setMatricula($dadosFuncionarios['matricula']);
        }
        if ( isset($dadosFuncionarios['nome']) )
        {
            $funcionariosDTO->setNome($dadosFuncionarios['nome']);
        }
        if ( isset($dadosFuncionarios['siglanomemeio']) )
        {
            $funcionariosDTO->setSiglanomemeio($dadosFuncionarios['siglanomemeio']);
        }
        if ( isset($dadosFuncionarios['sobrenome']) )
        {
            $funcionariosDTO->setSobrenome($dadosFuncionarios['sobrenome']);
        }
        if ( isset($dadosFuncionarios['usuario']) )
        {
            $funcionariosDTO->setUsuario($dadosFuncionarios['usuario']);
        }
        if ( isset($dadosFuncionarios['senha']) )
        {
            $funcionariosDTO->setSenha($dadosFuncionarios['senha']);
        }
        if ( isset($dadosFuncionarios['email']) )
        {
            $funcionariosDTO->setEmail($dadosFuncionarios['email']);
        }
        if ( isset($dadosFuncionarios['permissao']) )
        {
            $funcionariosDTO->setPermissao($dadosFuncionarios['permissao']);
        }
        if ( isset($dadosFuncionarios['ativo']) )
        {
            $funcionariosDTO->setAtivo($dadosFuncionarios['ativo']);
        }
        // Instancia classe Data Access Object (DAO) para cadastros.funcionarios
        $funcionariosDAO = new CadastrosFuncionariosDAO($this->pdo, $this->criaHistorico);
        // Chama método responsável em editar
        $funcionariosDAO->editar($funcionariosDTO);
        // Seta o sql executado a propriedade sql
        $this->sql = $funcionariosDAO->getSql();
    }

    /**
     * Método Responsavel em excluir
     * @access public
     * @param array $dadosFuncionarios Dado contendo o nome do campo chave primaria e seu valor
     */
    public function excluir($dadosFuncionarios) { 
        // Instancia classe Data Transfer Object (DTO) para cadastros.funcionarios
        $funcionariosDTO = new CadastrosFuncionariosDTO;
        // Seta valor a propriedade chave primaria
        if ( !empty($dadosFuncionarios['id_funcionarios']) )
        {
            $funcionariosDTO->setIdFuncionarios($dadosFuncionarios['id_funcionarios']);
        }
        else
        {
            throw new \PDOException("Chave primaria id_funcionarios nao definida");
        }
        // Instancia classe Data Access Object (DAO) para cadastros.funcionarios
        $funcionariosDAO = new CadastrosFuncionariosDAO($this->pdo, $this->criaHistorico);
        // Chama método responsável em excluir
        $funcionariosDAO->excluir($funcionariosDTO);
        // Seta o sql executado a propriedade sql
        $this->sql = $funcionariosDAO->getSql();
    }

    /**
     * Método Responsavel em listar
     * @access public
     * @param array $campos Array contendo o nome dos campos
     * @param array $criterios Array contendo o(s) criterio(s) para listagem
     * @param array $propriedade Array contendo o nome das propriedades 
     * @return objeto Retorna o Objeto com o resultado da consulta
     */
    public function listar($campos = null, $criterios = null, $propriedade = null) { 
        // Instancia classe Data Access Object (DAO) para cadastros.funcionarios
        $funcionariosDAO = new CadastrosFuncionariosDAO($this->pdo, $this->criaHistorico);
        // Chama método responsável em listar
        return $funcionariosDAO->listar($campos, $criterios, $propriedade);
    }

}
