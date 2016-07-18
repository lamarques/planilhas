<?php

namespace Classes\Orm;
use Classes\SqlCriterio;
use Classes\SqlInserir;
use Classes\SqlSelect;
use Classes\SqlFiltro;

/**
 * Classe DAO para a tabela cadastros.funcionarios
 *
 * Data Access Object (DAO) - Responsavel em fazer operações básicas na tabela como: Inserir, Editar e Excluir
 *
 * Versão: 3.0
 * Criado Por: Rogério Lamarques
 * Data Criação: 15/07/2016 19:31:49
 */
class CadastrosFuncionariosDAO { 
    // Define as propriedades
    private $pdo;
    private $sql;
    private $idTipoAcao;
    private $nota;
    private $criaHistorico;

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
     * Método Responsavel em inserir
     * @access public
     * @param objeto $funcionariosDTO Objeto DTO contendo informações a serem inseridas
     */
    public function inserir(CadastrosFuncionariosDTO $funcionariosDTO) {
        // Define a propriedade idTipoAcao como Inserir
        $this->idTipoAcao = 1;
        // Define a nota para a ação
        $this->nota = 'Cadastro inserido';
        // Instancia classe SqlInserir
        $sql = new SqlInserir();
        // Seta a tabela
        $sql->setTabela('cadastros.funcionarios');
        // Seta o(s) campo(s)
        if ( $funcionariosDTO->getIdClientes() !== false )
        {
            $sql->setCampo('id_clientes', $funcionariosDTO->getIdClientes());
        }
        if ( $funcionariosDTO->getMatricula() !== false )
        {
            $sql->setCampo('matricula', $funcionariosDTO->getMatricula());
        }
        if ( $funcionariosDTO->getNome() !== false )
        {
            $sql->setCampo('nome', $funcionariosDTO->getNome());
        }
        if ( $funcionariosDTO->getSiglanomemeio() !== false )
        {
            $sql->setCampo('siglanomemeio', $funcionariosDTO->getSiglanomemeio());
        }
        if ( $funcionariosDTO->getSobrenome() !== false )
        {
            $sql->setCampo('sobrenome', $funcionariosDTO->getSobrenome());
        }
        if ( $funcionariosDTO->getUsuario() !== false )
        {
            $sql->setCampo('usuario', $funcionariosDTO->getUsuario());
        }
        if ( $funcionariosDTO->getSenha() !== false )
        {
            $sql->setCampo('senha', $funcionariosDTO->getSenha());
        }
        if ( $funcionariosDTO->getEmail() !== false )
        {
            $sql->setCampo('email', $funcionariosDTO->getEmail());
        }
        if ( $funcionariosDTO->getPermissao() !== false )
        {
            $sql->setCampo('permissao', $funcionariosDTO->getPermissao());
        }
        if ( $funcionariosDTO->getAtivo() !== false )
        {
            $sql->setCampo('ativo', $funcionariosDTO->getAtivo());
        }
        // Prepara sql
        $consulta = $this->pdo->prepare($sql->getInstrucao());
        // Executa sql
        $consulta->execute();
        // Seta o ultimo id inserido a propriedade idFuncionarios
        $funcionariosDTO->setIdFuncionarios($this->pdo->lastInsertId('cadastros.funcionarios_id_funcionarios_seq'));
        // Seta o sql executado a propriedade sql
        $this->sql = $sql->getInstrucao();
        // Inseri o historico
        if ( $this->criaHistorico )
        {
            $this->historico($funcionariosDTO);
        }
    }

    /**
     * Método Responsavel em editar
     * @access public
     * @param objeto $funcionariosDTO Objeto DTO contendo informações a serem editadas
     */
    public function editar(CadastrosFuncionariosDTO $funcionariosDTO) { 
        // Define criterio para executar busca
        $criterioListar = array();
        $criterioListar []= array('id_funcionarios', '=', $funcionariosDTO->getIdFuncionarios());
        // Executa a busca
        $consulta = $this->listar(null, $criterioListar);
        // Verifica se a busca retornou o registro
        if ( $consulta->rowCount() == 1 )
        {
            // Define a propriedade idTipoAcao como Editar
            $this->idTipoAcao = 2;
            // Instancia classe SqlEditar
            $sql = new SqlEditar;
            // Seta a tabela
            $sql->setTabela('cadastros.funcionarios');
            // Passa resultado da consulta a variavel $linha
            $linha = $consulta->fetch(\PDO::FETCH_ASSOC);
            $this->nota = '';
            // Faz verificação sobre cada campo da tabela para setar na consulta e criar nota
            if ( $funcionariosDTO->getIdClientes() !== false )
            {
                if ( $linha['id_clientes'] != $funcionariosDTO->getIdClientes() )
                {
                    // Instancia Classe CadastrosClientesExecuta 
                    $clientes = new CadastrosClientesExecuta($this->pdo);
                    // Define campo para busca
                    $campoClientes = array('cliente');
                    // Define criterio para busca valor antigo
                    $criterioClientesAntigo = array();
                    $criterioClientesAntigo []= array('id_clientes', '=', $linha['id_clientes']);
                    // Executa a busca
                    $consultaAntigo = $clientes->listar($campoClientes, $criterioClientesAntigo);
                    $linhaAntigo = $consultaAntigo->fetch(\PDO::FETCH_ASSOC);
                    // Define criterio para busca valor atual
                    $criterioClientesAtual = array();
                    $criterioClientesAtual []= array('id_clientes', '=', $funcionariosDTO->getIdClientes());
                    $consultaAtual = $clientes->listar($campoClientes, $criterioClientesAtual);
                    $linhaAtual = $consultaAtual->fetch(\PDO::FETCH_ASSOC);
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "id_clientes|><|<>|><|{$linhaAntigo['cliente']}|><|<>|><|{$linhaAtual['cliente']}";
                    $sql->setCampo('id_clientes', $funcionariosDTO->getIdClientes());
                }
            }
            if ( $funcionariosDTO->getMatricula() !== false )
            {
                if ( $linha['matricula'] != $funcionariosDTO->getMatricula() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "matricula|><|<>|><|{$linha['matricula']}|><|<>|><|{$funcionariosDTO->getMatricula()}";
                    $sql->setCampo('matricula', $funcionariosDTO->getMatricula());
                }
            }
            if ( $funcionariosDTO->getNome() !== false )
            {
                if ( $linha['nome'] != $funcionariosDTO->getNome() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "nome|><|<>|><|{$linha['nome']}|><|<>|><|{$funcionariosDTO->getNome()}";
                    $sql->setCampo('nome', $funcionariosDTO->getNome());
                }
            }
            if ( $funcionariosDTO->getSiglanomemeio() !== false )
            {
                if ( $linha['siglanomemeio'] != $funcionariosDTO->getSiglanomemeio() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "siglanomemeio|><|<>|><|{$linha['siglanomemeio']}|><|<>|><|{$funcionariosDTO->getSiglanomemeio()}";
                    $sql->setCampo('siglanomemeio', $funcionariosDTO->getSiglanomemeio());
                }
            }
            if ( $funcionariosDTO->getSobrenome() !== false )
            {
                if ( $linha['sobrenome'] != $funcionariosDTO->getSobrenome() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "sobrenome|><|<>|><|{$linha['sobrenome']}|><|<>|><|{$funcionariosDTO->getSobrenome()}";
                    $sql->setCampo('sobrenome', $funcionariosDTO->getSobrenome());
                }
            }
            if ( $funcionariosDTO->getUsuario() !== false )
            {
                if ( $linha['usuario'] != $funcionariosDTO->getUsuario() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "usuario|><|<>|><|{$linha['usuario']}|><|<>|><|{$funcionariosDTO->getUsuario()}";
                    $sql->setCampo('usuario', $funcionariosDTO->getUsuario());
                }
            }
            if ( $funcionariosDTO->getSenha() !== false )
            {
                if ( $linha['senha'] != $funcionariosDTO->getSenha() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "senha|><|<>|><|{$linha['senha']}|><|<>|><|{$funcionariosDTO->getSenha()}";
                    $sql->setCampo('senha', $funcionariosDTO->getSenha());
                }
            }
            if ( $funcionariosDTO->getEmail() !== false )
            {
                if ( $linha['email'] != $funcionariosDTO->getEmail() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "email|><|<>|><|{$linha['email']}|><|<>|><|{$funcionariosDTO->getEmail()}";
                    $sql->setCampo('email', $funcionariosDTO->getEmail());
                }
            }
            if ( $funcionariosDTO->getPermissao() !== false )
            {
                if ( $linha['permissao'] != $funcionariosDTO->getPermissao() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "permissao|><|<>|><|{$linha['permissao']}|><|<>|><|{$funcionariosDTO->getPermissao()}";
                    $sql->setCampo('permissao', $funcionariosDTO->getPermissao());
                }
            }
            if ( $funcionariosDTO->getAtivo() !== false )
            {
                if ( $linha['ativo'] !== $funcionariosDTO->getAtivo() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "ativo|><|<>|><|{$linha['ativo']}|><|<>|><|{$funcionariosDTO->getAtivo()}";
                    $sql->setCampo('ativo', $funcionariosDTO->getAtivo());
                }
            }
            // Verifica se deve editar ou nao
            if ( !empty($this->nota) )
            {
                // Instancia classe SqlCriterio
                $criterio = new SqlCriterio;
                // Define criterio de edição
                $criterio->add(new SqlFiltro('id_funcionarios', '=', $funcionariosDTO->getIdFuncionarios()));
                $sql->setCriterio($criterio);
                // Prepara sql
                $consulta = $this->pdo->prepare($sql->getInstrucao());
                // Executa sql
                $consulta->execute();
                // Seta o sql executado a propriedade sql
                $this->sql = $sql->getInstrucao();
                // Inseri o historico
                if ( $this->criaHistorico )
                {
                    $this->historico($funcionariosDTO);
                }
            }
        }
    }

    /**
     * Método Responsavel em excluir
     * @access public
     * @param objeto $funcionariosDTO Objeto DTO contendo informações a serem editadas
     */
    public function excluir(CadastrosFuncionariosDTO $funcionariosDTO) { 
        // Define a propriedade idTipoAcao como Excluir
        $this->idTipoAcao = 3;
        // Define a nota para a ação
        $this->nota = 'Cadastro excluido';
        // Instancia classe SqlExcluir
        $sql = new SqlExcluir;
        // Seta a tabela
        $sql->setTabela('cadastros.funcionarios');
        // Instancia classe SqlCriterio
        $criterio = new SqlCriterio;
        // Define criterio de exclusão
        $criterio->add(new SqlFiltro('id_funcionarios', '=', $funcionariosDTO->getIdFuncionarios()));
        $sql->setCriterio($criterio);
        // Prepara sql
        $consulta = $this->pdo->prepare($sql->getInstrucao());
        // Executa sql
        $consulta->execute();
        // Verifica se excluiu o registro
        if ( $consulta->rowCount() > 0 )
        {
            // Seta o sql executado a propriedade sql
            $this->sql = $sql->getInstrucao();
            // Inseri o historico
            if ( $this->criaHistorico )
            {
                $this->historico($funcionariosDTO);
            }
        }
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
        // Instancia classe SqlSelect
        $sql = new SqlSelect();
        // Seta a tabela
        $sql->setTabela('cadastros.funcionarios');
        // Seta campos
        if ( $campos )
        {
            foreach( $campos AS $campo )
            {
                $sql->addColuna($campo);
            }
        }
        else
        {
            $sql->addColuna('*');
        }
        // Instancia classe SqlCriterio
        $criterio = new SqlCriterio();
        // Seta criterios
        if ( $criterios )
        {
            foreach( $criterios AS $valor )
            {
                $operador = isset($valor[3]) ? $valor[3] . ' ' : 'AND ';
                $criterio->add(new SqlFiltro($valor[0], $valor[1], $valor[2]), $operador);
            }
        }
        // Seta propriedades
        if ( $propriedade )
        {
            foreach( $propriedade AS $valor )
            {
                $criterio->setPropriedade($valor[0], $valor[1]);
            }
        }
        // Define criterios e propriedades ao sql
        $sql->setCriterio($criterio);
        // Prepara sql
        $consulta = $this->pdo->prepare($sql->getInstrucao());
        // Executa sql
        $consulta->execute();
        // Retorna o Objeto com o resultado da consulta
        return $consulta;
    }

    /**
     * Método Responsavel inserir historico
     * @access private
     */
    private function historico(CadastrosFuncionariosDTO $funcionariosDTO) { 
        // Cria Array para guardar dados do historico
        $dadosHistorico = array();
        // Seta valores
        $dadosHistorico['id_funcionarios'] = (isset($_SESSION['sessao_funcionario']['id_funcionarios'])) ? $_SESSION['sessao_funcionario']['id_funcionarios'] : NULL;
        $dadosHistorico['id_tipo_acao'] = $this->idTipoAcao;
        $dadosHistorico['historico'] = $this->getSql();
        $dadosHistorico['nome_tabela'] = 'cadastros.funcionarios';
        $dadosHistorico['id_registro'] = $funcionariosDTO->getIdFuncionarios();
        $dadosHistorico['nota'] = $this->nota;
        // Instancia classe HistoricoHistoricoExecuta
        $historico = new HistoricoHistoricoExecuta($this->pdo);
        $historico->inserir($dadosHistorico);
    }

}
