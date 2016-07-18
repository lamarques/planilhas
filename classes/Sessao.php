<?php

namespace Classes;

/**
 * classe Sessao
 * Gerencia uma sessão com o usuário
 */
class Sessao {

    /**
     * Inicializa uma sessão
     */
    function __construct($name = null) {
        if(empty($name)){
            session_start();
        } else {
            session_start();
            session_name($name);
        }
    }

    /**
     * Armazena uma variável na sessão
     *
     * @param string $var Nome da variável
     * @param string $valor	Valor da variavel
     */
    public function setValor($var, $valor) {
        $_SESSION[$var] = $valor;
    }

    /**
     * Retorna uma variável da sessão
     *
     * @param string $var Nome da variável
     * @return string
     */
    public function getValor($var) {
        if (isset($_SESSION[$var])) {
            return $_SESSION[$var];
        }
        return false;
    }

    /**
     * Destrói os dados de uma sessão
     */
    public function limpaSessao() {
        $_SESSION = array();
        session_destroy();
    }

    /**
     * Responsável em verificar se usuário tem permissão
     *
     * @return bool
     */
    public function verificaSessao() {
        if (($_SESSION['cliente'] != $this->getClienteUrl() ) || (empty($_SESSION["sessao_usuario"])) || (empty($_SESSION["sessao_id_funcionarios"])) || ($_SESSION["sessao_ativa"] != 'sistema_planilhas_6511')) {
            return false;
        }
        return true;
    }
    
    /**
     * Responsável em verificar se usuário está logado no modulo de venda online
     *
     * @return bool
     */
    public function verificaSessaoVendaOnline() {
        if (($_SESSION['cliente'] != $this->getClienteUrl() ) || (empty($_SESSION["sessao_usuario_venda_online"])) || (empty($_SESSION["sessao_nome_venda_online"])) || (empty($_SESSION["sessao_id_usuario_venda_online"])) || ($_SESSION["sessao_ativa_venda_online"] != 'sistema_sige_789_venda_online')) {
            return false;
        }
        return true;
    }

    /**
     * Responsável em verificar se usuário está logado no modulo de centro de eventos
     *
     * @return bool
     */
    public function verificaSessaoCentroEventos() {
        if ($this->getValor('sessao_ativa') === 'sistema_sige_organizador' ) {
            return true;
        }
        return false;
    }

    /**
     * Lista as variáveis em sessão
     *
     * @return array
     */
    public function listarSessao() {
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
    }

    public function limpaValoresSessao() {
        $_SESSION = array();
    }

    public function setCliete() {
        $_SESSION['cliente'] = $this->getClienteUrl();
    }

    public function getClienteUrl() {
        $variavel = explode(".", $_SERVER['SERVER_NAME']);
        return $variavel[0];
    }

    public function getPastaCliente() {
        if (!empty($_SESSION['cliente'])) {
            return $_SESSION['cliente'] . "/";
        } else {
            return "";
        }
    }
    
    public function getSessao(){
        return $_SESSION;
    }
    
    public function removeItemSessao($item){
        unset($_SESSION[$item]);
    }
}
