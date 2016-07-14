<?php
/**
 * classe SqlInstrucao
 * Esta classe provê os métodos em comum entre todas instruções
 * SQL (SELECT, INSERT, DELETE e UPDATE)
 */
abstract class SqlInstrucao {

    protected $sql;         // armazena a instrução SQL
    protected $criterio;    // armazena o objeto critério

    /**
     * define o nome da entidade (tabela) manipulada pela instrução SQL
     *
     * @param string $tabela Nome da tabela
     */
    final public function setTabela($tabela) {
        $this->tabela = $tabela;
    }

    /**
     * retorna o nome da entidade (tabela)
     *
     * @return string
     */
    final public function getTabela() {
        return $this->tabela;
    }

    /**
     * Define um critério de seleção dos dados através da composição de um objeto
     * do tipo SqlCriterio, que oferece uma interface para definição de critérios
     *
     * @param objeto $criteria objeto do tipo SqlCriterio
     */
    public function setCriterio(SqlCriterio $criterio) {
        $this->criterio = $criterio;
    }

    /**
     * declarando-o como <abstract> obrigamos sua declaração nas classes filhas,
     * uma vez que seu comportamento será distinto em cada uma delas, configurando polimorfismo.
     */
    abstract function getInstrucao();
}
