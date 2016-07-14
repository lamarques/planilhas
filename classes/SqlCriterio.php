<?php
/**
 * classe SqlCriterio
 * Esta classe provê uma interface utilizada para definição de critérios
 */
class SqlCriterio extends SqlExpressao
{
    
    private $expressao; // armazena a lista de expressões
    private $operadores; // armazena a lista de operadores
    private $propriedades; // propriedades do critério
    

    /**
     * Método Construtor
     * Inicia variaveis
     */
    function __construct()
    {
        $this->expressao = array();
        $this->operadores = array();
    }
    
    /**
     * Adiciona uma expressão ao critério
     *
     * @param objeto $expressao expressão (objeto SqlExpressao)
     * @param constante $operador operador lógico de comparação
     */
    public function add( SqlExpressao $expressao, $operador = self::AND_OPERATOR )
    {
        // na primeira vez, não precisamos de operador lógico para concatenar
        if ( empty($this->expressao) )
        {
            $operador = NULL;
        }
        // agrega o resultado da expressão à lista de expressões
        $this->expressao[] = $expressao;
        $this->operadores[] = $operador;
    }
    
    /**
     * Retorna a expressão final
     * @return string
     */
    public function dump()
    {
        // concatena a lista de expressões
        if ( is_array($this->expressao) )
        {
            if ( count($this->expressao) > 0 )
            {
                $result = '';
                foreach ( $this->expressao as $i => $expressao )
                {
                    $operador = $this->operadores[$i];
                    // concatena o operador com a respectiva expressão
                    $result .= $operador . $expressao->dump() . ' ';
                }
                $result = trim($result);
                return "({$result})";
            }
        }
    }
    
    /**
     * Define o valor de uma propriedade
     *
     * @param string $propriedade Nome da propriedade
     * @param string $valor valor da propriedade
     */
    public function setPropriedade( $propriedade, $valor )
    {
        if ( isset($valor) )
        {
            $this->propriedades[$propriedade] = $valor;
        }
        else
        {
            $this->propriedades[$propriedade] = NULL;
        }
    }
    
    /**
     * Retorna o valor de uma propriedade
     *
     * @param string $propriedade Propriedade
     * @return string
     */
    public function getPropriedade( $propriedade )
    {
        if ( isset($this->propriedades[$propriedade]) )
        {
            return $this->propriedades[$propriedade];
        }
    }
}
?>