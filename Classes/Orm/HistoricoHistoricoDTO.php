<?php

namespace Classes\ORM;

/**
 * Classe DTO para a tabela historico.historico
 *
 * Data Transfer Object (DTO) - Utilizado apenas como estrutura para transporte de dados
 *
 * Contém a mesma estrutura da tabela, os campos da tabela com os seus Get's and Set's
 *
 * Versão: 3.0
 * Criado Por: Rogério Lamarques
 * Data Criação: 18/07/2016 18:39:13
 */
class HistoricoHistoricoDTO { 
    // Define as propriedades
    private $idHistorico = false;
    private $idTipoAcao = false;
    private $idFuncionarios = false;
    private $historico = false;
    private $nomeTabela = false;
    private $idRegistro = false;
    private $idChaveEstrangeira = false;
    private $dataCracao = false;
    private $nota = false;

    /**
     * Seta um valor à propriedade idHistorico
     * @access public
     * @param int4 $idHistorico
     */
    public function setIdHistorico($idHistorico)
    {
        $this->idHistorico = intval($idHistorico);
    }

    /**
     * Retorna o valor da propriedade idHistorico
     * @access public
     * @return int4
     */
    public function getIdHistorico()
    {
        return $this->idHistorico;
    }

    /**
     * Seta um valor à propriedade idTipoAcao
     * @access public
     * @param int4 $idTipoAcao
     */
    public function setIdTipoAcao($idTipoAcao)
    {
        $this->idTipoAcao = intval($idTipoAcao);
    }

    /**
     * Retorna o valor da propriedade idTipoAcao
     * @access public
     * @return int4
     */
    public function getIdTipoAcao()
    {
        return $this->idTipoAcao;
    }

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
     * Seta um valor à propriedade historico
     * @access public
     * @param text $historico
     */
    public function setHistorico($historico)
    {
        $this->historico = strlen($historico) > 0 ? $historico : '';
    }

    /**
     * Retorna o valor da propriedade historico
     * @access public
     * @return text
     */
    public function getHistorico()
    {
        return $this->historico;
    }

    /**
     * Seta um valor à propriedade nomeTabela
     * @access public
     * @param varchar $nomeTabela
     */
    public function setNomeTabela($nomeTabela)
    {
        $this->nomeTabela = strlen($nomeTabela) > 0 ? substr($nomeTabela,0,200) : '';
    }

    /**
     * Retorna o valor da propriedade nomeTabela
     * @access public
     * @return varchar
     */
    public function getNomeTabela()
    {
        return $this->nomeTabela;
    }

    /**
     * Seta um valor à propriedade idRegistro
     * @access public
     * @param int4 $idRegistro
     */
    public function setIdRegistro($idRegistro)
    {
        $this->idRegistro = strlen($idRegistro) > 0 ? intval($idRegistro) : '';
    }

    /**
     * Retorna o valor da propriedade idRegistro
     * @access public
     * @return int4
     */
    public function getIdRegistro()
    {
        return $this->idRegistro;
    }

    /**
     * Seta um valor à propriedade idChaveEstrangeira
     * @access public
     * @param text $idChaveEstrangeira
     */
    public function setIdChaveEstrangeira($idChaveEstrangeira)
    {
        $this->idChaveEstrangeira = strlen($idChaveEstrangeira) > 0 ? $idChaveEstrangeira : '';
    }

    /**
     * Retorna o valor da propriedade idChaveEstrangeira
     * @access public
     * @return text
     */
    public function getIdChaveEstrangeira()
    {
        return $this->idChaveEstrangeira;
    }

    /**
     * Seta um valor à propriedade dataCracao
     * @access public
     * @param timestamp $dataCracao
     */
    public function setDataCracao($dataCracao)
    {
        $this->dataCracao = empty($dataCracao) ? date('Y-m-d H:i:s') : $dataCracao;
    }

    /**
     * Retorna o valor da propriedade dataCracao
     * @access public
     * @return timestamp
     */
    public function getDataCracao()
    {
        return $this->dataCracao;
    }

    /**
     * Seta um valor à propriedade nota
     * @access public
     * @param text $nota
     */
    public function setNota($nota)
    {
        $this->nota = strlen($nota) > 0 ? $nota : '';
    }

    /**
     * Retorna o valor da propriedade nota
     * @access public
     * @return text
     */
    public function getNota()
    {
        return $this->nota;
    }

}
