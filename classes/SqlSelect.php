<?php

namespace Classes;
/**
 * classe SqlSelect
 * Esta classe provê meios para manipulação de uma instrução de SELECT no banco de dados
 */
final class SqlSelect extends SqlInstrucao {

    private $colunas;	// array de colunas a serem retornadas.
    /**
     * método addColumn
     * adiciona uma coluna a ser retornada pelo SELECT
     * @param $column = coluna da tabela
     */
    public function addColuna($coluna) {
        // adiciona a coluna no array
        $this->colunas[] = $coluna;
    }

    /**
     * método getInstrucao()
     * retorna a instrução de SELECT em forma de string.
     */
    public function getInstrucao() {
        // monta a instrução de SELECT
        $this->sql = 'SELECT ';

        // monta string com os nomes de colunas
        $this->sql .= implode(', ', $this->colunas);

        // adiciona na cláusula FROM o nome da tabela
        $this->sql .= ' FROM ' . $this->tabela;

        // obtém a cláusula WHERE do objeto criteria.
        if ($this->criterio) {
            $expression = $this->criterio->dump();
            if ($expression) {
                $this->sql .= ' WHERE ' . $expression;
            }
            // obtém as propriedades do critério
            $order = $this->criterio->getPropriedade('order');
            $limit = $this->criterio->getPropriedade('limit');
            $offset= $this->criterio->getPropriedade('offset');

            // obtém a ordenação do SELECT
            if ($order) {
                $this->sql .= ' ORDER BY ' . $order;
            }
            if ($limit) {
                $this->sql .= ' LIMIT ' . $limit;
            }
            if ($offset) {
                $this->sql .= ' OFFSET ' . $offset;
            }
        }
        return $this->sql . ';';
    }
}
