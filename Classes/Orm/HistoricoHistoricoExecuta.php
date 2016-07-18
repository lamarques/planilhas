<?php

namespace Classes\Orm;

/**
 * Classe responsavel em utilizar Data Access Object para historico.historico
 *
 * Versão: 3.0
 * Criado Por: Rogério Lamarques
 * Data Criação: 18/07/2016 18:39:13
 */
class HistoricoHistoricoExecuta { 
    // Define as propriedades
    private $pdo;
    private $criaHistorico;
    private $sql;
    private $ultimoId;

    /**
     * Método Construtor
     * @access public
     */
    public function __construct(\PDO $pdo, $historico = false) {
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
     * Método Responsavel em inserir
     * @access public
     * @param array $dadosHistorico Dados contendo o nome dos campos e seus valores
     */
    public function inserir($dadosHistorico) {
        if(!isset($dadosHistorico['id_chave_estrangeira'])){
            $dadosHistorico['id_chave_estrangeira'] = "";
        }
        $sql = "INSERT INTO historico.historico (id_tipo_acao, id_funcionarios, historico, nome_tabela, id_registro, id_chave_estrangeira, data_cracao, nota)
                VALUES (:id_tipo_acao, :id_funcionarios, :historico, :nome_tabela, :id_registro, :id_chave_estrangeira, CURRENT_TIMESTAMP, :nota)";
        try{
            $consulta = $this->pdo->prepare($sql);
            $consulta->bindParam(':id_tipo_acao', $dadosHistorico['id_tipo_acao'], \PDO::PARAM_INT);
            $consulta->bindParam(':id_funcionarios', $dadosHistorico['id_funcionarios'], \PDO::PARAM_INT);
            $consulta->bindParam(':historico', $dadosHistorico['historico'], \PDO::PARAM_STR);
            $consulta->bindParam(':nome_tabela', $dadosHistorico['nome_tabela'], \PDO::PARAM_STR);
            $consulta->bindParam(':id_registro', $dadosHistorico['id_registro'], \PDO::PARAM_INT);
            $consulta->bindParam(':id_chave_estrangeira', $dadosHistorico['id_chave_estrangeira'], \PDO::PARAM_STR);
            $consulta->bindParam(':nota', $dadosHistorico['nota'], \PDO::PARAM_STR);
            $consulta->execute();
        } catch (\PDOException $e){
            print_r($e);
        }
    }
}
