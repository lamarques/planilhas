<?php
/**
 * Created by PhpStorm.
 * User: rogerio.lamarques
 * Date: 18/07/2016
 * Time: 14:12
 */

namespace Classes;

    /**
     * classe SqlInserir
     * Esta classe provê meios para manipulação de uma instrução de INSERT no banco de dados
     */
final class SqlInserir extends SqlInstrucao
{

    /**
     * Atribui valores à determinadas colunas no banco de dados que serão inseridas
     *
     * @param string $coluna coluna da tabela
     * @param string $valor valor a ser armazenado
     */
    public function setCampo($coluna, $valor)
    {
        // verifica se é um dado escalar (string, inteiro, ...)
        if (is_scalar($valor)) {
            if (is_string($valor) and (!empty($valor))) {
                // caso seja uma string

                $valor = addslashes($valor);
                $valor = str_replace("'", "''", $valor);

                $this->colunaValores[$coluna] = "'$valor'";
            } else if (is_bool($valor)) {
                // caso seja um boolean
                $this->colunaValores[$coluna] = $valor ? 'TRUE' : 'FALSE';
            } else if ($valor !== '') {
                // caso seja outro tipo de dado
                $this->colunaValores[$coluna] = $valor;
            } else {
                // caso seja NULL
                $this->colunaValores[$coluna] = "NULL";
            }
        }
    }

    /**
     * retorna a instrução de INSERT em forma de string.
     *
     * @return string
     */
    public function getInstrucao()
    {


        $this->sql = "INSERT INTO {$this->tabela} (";

        // monta uma string contendo os nomes de colunas
        $colunas = implode(', ', array_keys($this->colunaValores));

        // monta uma string contendo os valores
        $valores = implode(', ', array_values($this->colunaValores));
        $this->sql .= $colunas . ')';
        $this->sql .= " VALUES ({$valores});";
        //print $this->sql;

        return $this->sql;
    }
}
