<?php

namespace Classes;

class ConexaoParametros {

    public $arrParametro = array();

    function planilhas() {
        $this->arrParametro['name'] = 'datumplanilhas';
        $this->arrParametro['user_db'] = 'postgres';
        $this->arrParametro['pass'] = 'root';
        return $this->arrParametro;
    }
    
}
