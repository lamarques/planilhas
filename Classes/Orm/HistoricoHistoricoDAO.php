<?php

namespace Classes\Orm;

/**
 * Classe DAO para a tabela historico.historico
 *
 * Data Access Object (DAO) - Responsavel em fazer operações básicas na tabela como: Inserir, Editar e Excluir
 *
 * Versão: 3.0
 * Criado Por: Rogério Lamarques
 * Data Criação: 18/07/2016 18:39:13
 */
class HistoricoHistoricoDAO { 
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
     * @param objeto $historicoDTO Objeto DTO contendo informações a serem inseridas
     */
    public function inserir(HistoricoHistoricoDTO $historicoDTO) {
        // Define a propriedade idTipoAcao como Inserir
        $this->idTipoAcao = 1;
        // Define a nota para a ação
        $this->nota = 'Cadastro inserido';
        // Instancia classe SqlInserir
        $sql = new \Classes\SqlInserir();
        // Seta a tabela
        $sql->setTabela('historico.historico');
        // Seta o(s) campo(s)
        if ( $historicoDTO->getIdTipoAcao() !== false )
        {
            $sql->setCampo('id_tipo_acao', $historicoDTO->getIdTipoAcao());
        }
        if ( $historicoDTO->getHistorico() !== false )
        {
            $sql->setCampo('historico', $historicoDTO->getHistorico());
        }
        if ( $historicoDTO->getNomeTabela() !== false )
        {
            $sql->setCampo('nome_tabela', $historicoDTO->getNomeTabela());
        }
        if ( $historicoDTO->getIdRegistro() !== false )
        {
            $sql->setCampo('id_registro', $historicoDTO->getIdRegistro());
        }
        if ( $historicoDTO->getIdChaveEstrangeira() !== false )
        {
            $sql->setCampo('id_chave_estrangeira', $historicoDTO->getIdChaveEstrangeira());
        }
        if ( $historicoDTO->getDataCracao() !== false )
        {
            $sql->setCampo('data_cracao', $historicoDTO->getDataCracao());
        }
        if ( $historicoDTO->getNota() !== false )
        {
            $sql->setCampo('nota', $historicoDTO->getNota());
        }
        // Prepara sql
        $consulta = $this->pdo->prepare($sql->getInstrucao());
        // Executa sql
        $consulta->execute();
        // Seta o ultimo id inserido a propriedade idHistorico
        $historicoDTO->setIdHistorico($this->pdo->lastInsertId('historico.'));
        // Seta o sql executado a propriedade sql
        $this->sql = $sql->getInstrucao();
    }

    /**
     * Método Responsavel em editar
     * @access public
     * @param objeto $historicoDTO Objeto DTO contendo informações a serem editadas
     */
    public function editar(HistoricoHistoricoDTO $historicoDTO) { 
        // Define criterio para executar busca
        $criterioListar = array();
        $criterioListar []= array('id_historico', '=', $historicoDTO->getIdHistorico());
        // Executa a busca
        $consulta = $this->listar(null, $criterioListar);
        // Verifica se a busca retornou o registro
        if ( $consulta->rowCount() == 1 )
        {
            // Define a propriedade idTipoAcao como Editar
            $this->idTipoAcao = 2;
            // Instancia classe SqlEditar
            $sql = new \Classes\SqlEditar();
            // Seta a tabela
            $sql->setTabela('historico.historico');
            // Passa resultado da consulta a variavel $linha
            $linha = $consulta->fetch(\PDO::FETCH_ASSOC);
            $this->nota = '';
            // Faz verificação sobre cada campo da tabela para setar na consulta e criar nota
            if ( $historicoDTO->getIdTipoAcao() !== false )
            {
                if ( $linha['id_tipo_acao'] != $historicoDTO->getIdTipoAcao() )
                {
                    // Instancia Classe HistoricoTipoAcao0Executa 
                    $tipoAcao0 = new HistoricoTipoAcao0Executa($this->pdo);
                    // Define campo para busca
                    $campoTipoAcao0 = array('tipo_acao');
                    // Define criterio para busca valor antigo
                    $criterioTipoAcao0Antigo = array();
                    $criterioTipoAcao0Antigo []= array('id_tipo_acao0', '=', $linha['id_tipo_acao']);
                    // Executa a busca
                    $consultaAntigo = $tipoAcao0->listar($campoTipoAcao0, $criterioTipoAcao0Antigo);
                    $linhaAntigo = $consultaAntigo->fetch(\PDO::FETCH_ASSOC);
                    // Define criterio para busca valor atual
                    $criterioTipoAcao0Atual = array();
                    $criterioTipoAcao0Atual []= array('id_tipo_acao0', '=', $historicoDTO->getIdTipoAcao());
                    $consultaAtual = $tipoAcao0->listar($campoTipoAcao0, $criterioTipoAcao0Atual);
                    $linhaAtual = $consultaAtual->fetch(\PDO::FETCH_ASSOC);
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "id_tipo_acao|><|<>|><|{$linhaAntigo['tipo_acao']}|><|<>|><|{$linhaAtual['tipo_acao']}";
                    $sql->setCampo('id_tipo_acao', $historicoDTO->getIdTipoAcao());
                }
            }
            if ( $historicoDTO->getIdFuncionarios() !== false )
            {
                if ( $linha['id_funcionarios'] != $historicoDTO->getIdFuncionarios() )
                {
                    // Instancia Classe CadastrosFuncionariosExecuta 
                    $funcionarios = new CadastrosFuncionariosExecuta($this->pdo);
                    // Define campo para busca
                    $campoFuncionarios = array('nome');
                    // Define criterio para busca valor antigo
                    $criterioFuncionariosAntigo = array();
                    $criterioFuncionariosAntigo []= array('id_funcionarios', '=', $linha['id_funcionarios']);
                    // Executa a busca
                    $consultaAntigo = $funcionarios->listar($campoFuncionarios, $criterioFuncionariosAntigo);
                    $linhaAntigo = $consultaAntigo->fetch(\PDO::FETCH_ASSOC);
                    // Define criterio para busca valor atual
                    $criterioFuncionariosAtual = array();
                    $criterioFuncionariosAtual []= array('id_funcionarios', '=', $historicoDTO->getIdFuncionarios());
                    $consultaAtual = $funcionarios->listar($campoFuncionarios, $criterioFuncionariosAtual);
                    $linhaAtual = $consultaAtual->fetch(\PDO::FETCH_ASSOC);
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "id_funcionarios|><|<>|><|{$linhaAntigo['nome']}|><|<>|><|{$linhaAtual['nome']}";
                    $sql->setCampo('id_funcionarios', $historicoDTO->getIdFuncionarios());
                }
            }
            if ( $historicoDTO->getHistorico() !== false )
            {
                if ( $linha['historico'] != $historicoDTO->getHistorico() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "historico|><|<>|><|{$linha['historico']}|><|<>|><|{$historicoDTO->getHistorico()}";
                    $sql->setCampo('historico', $historicoDTO->getHistorico());
                }
            }
            if ( $historicoDTO->getNomeTabela() !== false )
            {
                if ( $linha['nome_tabela'] != $historicoDTO->getNomeTabela() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "nome_tabela|><|<>|><|{$linha['nome_tabela']}|><|<>|><|{$historicoDTO->getNomeTabela()}";
                    $sql->setCampo('nome_tabela', $historicoDTO->getNomeTabela());
                }
            }
            if ( $historicoDTO->getIdRegistro() !== false )
            {
                if ( $linha['id_registro'] != $historicoDTO->getIdRegistro() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "id_registro|><|<>|><|{$linha['id_registro']}|><|<>|><|{$historicoDTO->getIdRegistro()}";
                    $sql->setCampo('id_registro', $historicoDTO->getIdRegistro());
                }
            }
            if ( $historicoDTO->getIdChaveEstrangeira() !== false )
            {
                if ( $linha['id_chave_estrangeira'] != $historicoDTO->getIdChaveEstrangeira() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "id_chave_estrangeira|><|<>|><|{$linha['id_chave_estrangeira']}|><|<>|><|{$historicoDTO->getIdChaveEstrangeira()}";
                    $sql->setCampo('id_chave_estrangeira', $historicoDTO->getIdChaveEstrangeira());
                }
            }
            if ( $historicoDTO->getDataCracao() !== false )
            {
                if ( $linha['data_cracao'] != $historicoDTO->getDataCracao() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "data_cracao|><|<>|><|{$linha['data_cracao']}|><|<>|><|{$historicoDTO->getDataCracao()}";
                    $sql->setCampo('data_cracao', $historicoDTO->getDataCracao());
                }
            }
            if ( $historicoDTO->getNota() !== false )
            {
                if ( $linha['nota'] != $historicoDTO->getNota() )
                {
                    $this->nota .= empty($this->nota) ? '' : "\r\n|><|<->|><|";
                    $this->nota .= "nota|><|<>|><|{$linha['nota']}|><|<>|><|{$historicoDTO->getNota()}";
                    $sql->setCampo('nota', $historicoDTO->getNota());
                }
            }
            // Verifica se deve editar ou nao
            if ( !empty($this->nota) )
            {
                // Instancia classe SqlCriterio
                $criterio = new \Classes\SqlCriterio();
                // Define criterio de edição
                $criterio->add(new \Classes\SqlFiltro('id_historico', '=', $historicoDTO->getIdHistorico()));
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
                    $this->historico($historicoDTO);
                }
            }
        }
    }

    /**
     * Método Responsavel em excluir
     * @access public
     * @param objeto $historicoDTO Objeto DTO contendo informações a serem editadas
     */
    public function excluir(HistoricoHistoricoDTO $historicoDTO) { 
        // Define a propriedade idTipoAcao como Excluir
        $this->idTipoAcao = 3;
        // Define a nota para a ação
        $this->nota = 'Cadastro excluido';
        // Instancia classe SqlExcluir
        $sql = new \Classes\SqlExcluir();
        // Seta a tabela
        $sql->setTabela('historico.historico');
        // Instancia classe SqlCriterio
        $criterio = new \Classes\SqlCriterio();
        // Define criterio de exclusão
        $criterio->add(new \Classes\SqlFiltro('id_historico', '=', $historicoDTO->getIdHistorico()));
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
                $this->historico($historicoDTO);
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
        $sql = new \Classes\SqlSelect();
        // Seta a tabela
        $sql->setTabela('historico.historico');
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
        $criterio = new \Classes\SqlCriterio();
        // Seta criterios
        if ( $criterios )
        {
            foreach( $criterios AS $valor )
            {
                $operador = isset($valor[3]) ? $valor[3] . ' ' : 'AND ';
                $criterio->add(new \Classes\SqlFiltro($valor[0], $valor[1], $valor[2]), $operador);
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
    private function historico(HistoricoHistoricoDTO $historicoDTO) { 
        // Cria Array para guardar dados do historico
        $dadosHistorico = array();
        // Seta valores
        $dadosHistorico['id_usuarios'] = (isset($_SESSION['sessao_id_usuarios'])) ? $_SESSION['sessao_id_usuarios'] : NULL;
        $dadosHistorico['id_tipo_acao'] = $this->idTipoAcao;
        $dadosHistorico['historico'] = $this->getSql();
        $dadosHistorico['nome_tabela'] = 'historico.historico';
        $dadosHistorico['id_registro'] = $historicoDTO->getIdHistorico();
        $dadosHistorico['nota'] = $this->nota;
        // Instancia classe HistoricoHistoricoExecuta
        $historico = new HistoricoHistoricoExecuta($this->pdo);
        $historico->inserir($dadosHistorico);
    }

}
