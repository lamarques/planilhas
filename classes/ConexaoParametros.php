<?php

/**
 * Classe responsável por organizar as conexoes dos clientes.
 *
 * @author tiago
 */
class ConexaoParametros {

    public $arrParametro = array();

    function planilhas() {
        $this->arrParametro['name'] = 'datumplanilhas';
        $this->arrParametro['user_db'] = 'postgres';
        $this->arrParametro['pass'] = 'root';
        return $this->arrParametro;
    }
    
}
