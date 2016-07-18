<?php

namespace Classes;
/**
 * classe SqlFiltro
 * Esta classe provê uma interface para definição de filtros de seleção
 */
class SqlFiltro extends SqlExpressao
{
    
    private $variavel; // variável
    private $operador; // operador
    private $valor; // valor
    

    /**
     * instancia um novo filtro
     *
     * @param string $variavel variável
     * @param char $operador operador (>,<)
     * @param string $valor valor a ser comparado
     */
    public function __construct( $variavel, $operador, $valor )
    {
        // armazena as propriedades
        $this->variavel = $variavel;
        $this->operador = $operador;
        
        // transforma o valor de acordo com certas regras
        // antes de atribuir à propriedade $this->valor
        $this->valor = $this->transform($valor);
    }

    /**
     * recebe um valor e faz as modificações necessárias
     * para ele ser interpretado pelo banco de dados
     * podendo ser um integer/string/boolean ou array.
     *
     * @param integer|string|bool|array $valor
     * @return array|bool|int|string
     */
    private function transform( $valor )
    {
        // caso seja um array
        if ( is_array($valor) )
        {
            // percorre os valores
            foreach ( $valor as $x )
            {
                // se for um inteiro
                if ( is_integer($x) )
                {
                    $foo[] = $x;
                }
                else if ( is_string($x) )
                {
                    // se for string, adiciona aspas
                    $foo[] = "'$x'";
                }
            }
            // converte o array em string separada por ","
            $result = '(' . implode(',', $foo) . ')';
        }
        // caso seja uma string
        else if ( is_string($valor) )
        {
            // adiciona aspas
            $valor = addslashes($valor);
            $result = "'$valor'";
        }
        // caso seja valor nullo
        else if ( is_null($valor) )
        {
            // armazena NULL
            $result = 'NULL';
        }
        // caso seja booleano
        else if ( is_bool($valor) )
        {
            // armazena TRUE ou FALSE
            $result = $valor ? 'TRUE' : 'FALSE';
        }
        else
        {
            $result = $valor;
        }
        // retorna o valor
        return $result;
    }
    
    /**
     * retorna o filtro em forma de expressão
     *
     * @return string
     */
    public function dump()
    {
        // concatena a expressão
        return "{$this->variavel} {$this->operador} {$this->valor}";
    }
}
