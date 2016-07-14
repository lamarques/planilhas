<?php

/**
 * Class responsavel em padronizar dados dos formularios
 * @autor Cristian Cardoso
 * @atualizacao 07/03/2010
 * @hora 11:00
 * @vercao 0.2.2
 */
class TrataDados {

    private $dados;
    private $erro;
    private $msg;
    private $valida;
    private $validaCampoType;
    private $validaObrigatorio;
    private $validaCampoEspecial;
    private $validaMsg;
    private $campo;
    private $valor;
    private $tabela;
    private $moduloTabela;
    private $idTabela;
    private $erCEP = '/^\d{5}\-\d{3}$/';
    private $erCPF = '/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/';
    private $erCNPJ = '/^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$/';
    private $erHORA = '/^([0-1][0-9]|[2][0-3]):[0-5][0-9]$/';
    private $erMOEDA = '/^\d{1,3}(\.\d{3})*\,\d{2}$/';
    private $erNUMERO = '/^\d{0,}$/';
    private $erNUMEROPOSITIVO = '/^[1-9]|[1-9][0-9]+$/';
    private $erNUMEROPONTO = '/^[-]?[0-9]+([\.][0-9]+)?$/';
    private $erTELEFONE = '/^\(?\d{2}\)?\d{4}-\d{4}$/';
    private $erTELEFONE_SP = '/^\(?\d{2}\)?\d{5}-\d{4}$/';
    private $erFAX = '/^\(?\d{2}\)?\d{4}-\d{4}$/';
    private $erCELULAR = '/^\(?\d{2}\)?\d{4}-\d{4}$/';
    private $erEMAIL = '/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-zA-Z]{2,6}(?:\.[a-zA-Z]{2})?)$/';
    private $erDATA = '/^((0[1-9]|[12]\d)\/(0[1-9]|1[0-2])|30\/(0[13-9]|1[0-2])|31\/(0[13578]|1[02]))\/(19|20)?\d{4}$/';
    //private $erURL = '/^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i';
    private $erURL = '/^(http[s]?:\/\/|ftp:\/\/)?(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i';
    private $erESPECIAIS = '/^(cpf|cnpj|telefone|telefone_sp|telefone_optativo|fax|celular|cep|data|hora|moeda|numero|numeropositivo|senha|email|url)$/';
    private $erRESTRITOS = '/^(acao|form|id_form)$/';

    // private $erRESTRITOS        = array('acao','form','id_form');
    public function __construct() {
        $this->dados = array( );
        $this->erro = array( );
    }

    /**
     * Responsavel em captar todos os campos $_POST[]
     *
     * @return bool
     */
    public function pegaDados() {

        foreach ( $_POST as $this->campo => $this->valor ) {
            if(is_array($this->valor)){
                $this->valor = array_map('trim', $this->valor);
            } else {
                $this->valor = trim( $this->valor );
            }
            $this->campo = trim( $this->campo );

            //if (array_search($this->campo, $this->erRESTRITOS)) {
            if ( !preg_match( $this->erRESTRITOS, $this->campo ) ) {
                if ( !substr_count( $this->campo, '_Valida' ) ) {
                    $this->valida = (isset( $_POST[$this->campo . '_Valida'] )) ? $_POST[$this->campo . '_Valida'] : '';
                    if ( !empty( $this->valida ) ) {
                        //list ($this->validaObrigatorio, $this->validaCampoEspecial, $this->validaMsg, $this->validaCampoType) = split (',', utf8_decode($this->valida));
                        @list( $this->validaObrigatorio, $this->validaCampoEspecial, $this->validaMsg, $this->validaCampoType ) = explode( ',', $this->valida );

                        switch ( $this->validaCampoType ) {
                            case 'password' :
                                $this->verificaText();
                                break;
                            case 'hidden' :
                                $this->verificaHidden();
                                break;
                            case 'text' :
                                $this->verificaText();
                                break;
                            case 'select-one' :
                                $this->verificaSelectone();
                                break;
                            case 'select-multiple' :
                                $this->verificaSelectmultiple();
                                break;
                            case 'textarea' :
                                $this->verificaTextarea();
                                break;
                            default :
                                $this->setDados();
                                break;
                        }
                    } else {
                        $this->setDados();
                    }
                }
            } else {
                $this->{$this->campo}( $this->valor );
            }
        }
        return (empty( $this->erro )) ? true : false;
    }

    public function setValor( $param ) {
        $this->valor = $param;
    }

    /**
     * Método nao ira fazer nada, foi criado somente para nao dar erro.
     *
     * @param string $valor
     */
    private function acao( $valor ) {
        
    }

    /**
     * Responsavel em pegar o nome da tabela e verificar se tem modulo
     *
     * @param string $valor nome da tabela
     */
    private function form( $valor ) {
        $moduloTabela = explode( '.', $valor );
        if ( count( $moduloTabela ) == 1 ) {
            $this->moduloTabela = $valor;
            $this->tabela = $valor;
        } else {
            $this->moduloTabela = $valor;
            $this->tabela = $moduloTabela[1];
        }
    }

    /**
     * Responsavel em receber o id do registro q esta sendo editado ou excluido
     *
     * @param integer $valor id do registro
     */
    private function id_form( $valor ) {
        $this->idTabela = intval( $valor );
    }

    private function verificaHidden() {
        if ( empty( $this->validaCampoEspecial ) || empty( $this->valor ) ) {
            $this->setDados();
        } else {
            if ( !(preg_match( $this->erESPECIAIS, $this->validaCampoEspecial )) ) {
                $this->setDados();
            } else {
                if ( $this->{'verifica' . ucfirst( $this->validaCampoEspecial )}() ) {
                    $this->setDados();
                } else {
                    $this->setErro();
                }
            }
        }
    }

    /**
     * Responsavel em verificar capos text
     *
     */
    private function verificaText() {
        if ( $this->validaObrigatorio == 'sim' || $this->valor != '' ) {
            if ( $this->valor == '' ) {
                $this->setErro();
            } else {
                if ( !(preg_match( $this->erESPECIAIS, $this->validaCampoEspecial )) ) {
                    $this->setDados();
                } else {
                    if ( $this->{'verifica' . ucfirst( $this->validaCampoEspecial )}() ) {
                        $this->setDados();
                    } else {
                        $this->setErro();
                    }
                }
            }
        } else {
            $this->setDados();
        }
    }

    /**
     * Responsavel em verificar campos select
     */
    private function verificaSelectone() {
        if ( $this->validaObrigatorio == 'sim' && $this->valor == '0' ) {
            $this->setErro();
        } else {
            $this->setDados();
        }
    }

    /**
     * Responsavel em verificar campos select-multiple
     */
    private function verificaSelectmultiple() {
        $this->valor = empty( $this->valor ) ? '' : explode( ';', $this->valor );
        if ( $this->validaObrigatorio == 'sim' && empty( $this->valor ) ) {
            $this->setErro();
        } else {
            $this->setDados();
        }
    }

    /**
     * Responsavel em verificar campos Textarea
     */
    private function verificaTextarea() {
        if ( $this->validaObrigatorio == 'sim' && $this->valor == '' ) {
            $this->setErro();
        } else {
            //$this->dados[$this->campo] = addslashes(utf8_decode($this->valor));
            //$this->dados[$this->campo] = addslashes($this->valor); // alterado
            $this->dados[$this->campo] = $this->valor;
        }
    }

    /**
     * Responsavel em validar cpf
     *
     * @return bool
     */
    public function verificaCpf() {
        if ( !preg_match( $this->erCPF, $this->valor ) ) {
            return false;
        }
        //$cpf = ereg_replace('[^0-9]', '', $this->valor);
        $cpf = preg_replace( '/[^0-9]/', '', $this->valor );

        $nulos = array( '12345678909', '11111111111', '22222222222', '33333333333', '44444444444', '55555555555', '66666666666', '77777777777', '88888888888', '99999999999', '00000000000' );

        if ( !preg_match( '/^\d{11}$/', $cpf ) ) {
            return false;
        }
        if ( in_array( $cpf, $nulos ) ) {
            $this->setErro();
            return false;
        }
        $acum = 0;
        for ( $i = 0; $i < 9; $i++ ) {
            $acum += $cpf[$i] * (10 - $i);
        }
        $x = $acum % 11;
        $acum = ($x > 1) ? (11 - $x) : 0;
        if ( $acum != $cpf[9] ) {
            return false;
        }
        $acum = 0;
        for ( $i = 0; $i < 10; $i++ ) {
            $acum += $cpf[$i] * (11 - $i);
        }
        $x = $acum % 11;
        $acum = ($x > 1) ? (11 - $x) : 0;
        if ( $acum != $cpf[10] ) {
            return false;
        }
        return true;
    }

    /**
     * Responsavel em validar cnpj
     *
     * @return bool
     */
    public function verificaCnpj() {
        if ( !preg_match( $this->erCNPJ, $this->valor ) ) {
            return false;
        }
        $array = array( '.', '-', '/' );
        $cnpj = str_replace( $array, '', $this->valor );

        if ( strlen( $cnpj ) != 14 ) {
            return false;
        }
        $soma = 0;
        $soma += ($cnpj[0] * 5);
        $soma += ($cnpj[1] * 4);
        $soma += ($cnpj[2] * 3);
        $soma += ($cnpj[3] * 2);
        $soma += ($cnpj[4] * 9);
        $soma += ($cnpj[5] * 8);
        $soma += ($cnpj[6] * 7);
        $soma += ($cnpj[7] * 6);
        $soma += ($cnpj[8] * 5);
        $soma += ($cnpj[9] * 4);
        $soma += ($cnpj[10] * 3);
        $soma += ($cnpj[11] * 2);
        $d1 = $soma % 11;
        $d1 = $d1 < 2 ? 0 : 11 - $d1;
        $soma = 0;
        $soma += ($cnpj[0] * 6);
        $soma += ($cnpj[1] * 5);
        $soma += ($cnpj[2] * 4);
        $soma += ($cnpj[3] * 3);
        $soma += ($cnpj[4] * 2);
        $soma += ($cnpj[5] * 9);
        $soma += ($cnpj[6] * 8);
        $soma += ($cnpj[7] * 7);
        $soma += ($cnpj[8] * 6);
        $soma += ($cnpj[9] * 5);
        $soma += ($cnpj[10] * 4);
        $soma += ($cnpj[11] * 3);
        $soma += ($cnpj[12] * 2);
        $d2 = $soma % 11;
        $d2 = $d2 < 2 ? 0 : 11 - $d2;
        if ( $cnpj[12] == $d1 && $cnpj[13] == $d2 ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Responsavel em validar email
     * pode validar mais de um email os mesmos devem estar separados por ', '
     *
     * @return bool retornara false caso email esteja errado
     */
    private function verificaEmail() {
        $email = explode( ', ', $this->valor );
        for ( $i = 0; $i < sizeof( $email ); $i++ ) {
            if ( !preg_match( $this->erEMAIL, $email[$i] ) ) {
                return false;
            }
        }
        return true;
    }


    private function verificaTelefone_sp() {

        if (strlen($this->valor) > 13 ) {
            return $this->verificaTelefone_sp2();
        } else {
            return $this->verificaTelefone2();
        }
    }

    private function verificaTelefone_optativo() {

        if (strlen($this->valor) > 13 ) {
            return $this->verificaTelefone_sp2();
        } else {
            return $this->verificaTelefone2();
        }
    }

    /**
     * Responsavel em validar telefone
     */
    private function verificaTelefone() {

        if (strlen($this->valor) > 13 ) {
            return $this->verificaTelefone_sp2();
        } else {
            return $this->verificaTelefone2();
        }
    }

    private function verificaTelefone2(){
        return (!preg_match( $this->erTELEFONE, $this->valor )) ? false : true;
    }

    private function verificaTelefone_sp2(){
        return (!preg_match( $this->erTELEFONE_SP, $this->valor )) ? false : true;
    }

    /**
     * Responsavel em validar fax
     */
    private function verificaFax() {
        return (!preg_match( $this->erFAX, $this->valor )) ? false : true;
    }

    /**
     * Responsavel em validar celular
     */
    private function verificaCelular() {
        return (!preg_match( $this->erCELULAR, $this->valor )) ? false : true;
    }

    /**
     * Responsavel em validar cep
     */
    private function verificaCep() {
        return (!preg_match( $this->erCEP, $this->valor )) ? false : true;
    }

    /**
     * Responsavel em validar data
     */
    public function verificaData() {
        if ( !preg_match( $this->erDATA, $this->valor ) ) {
            return false;
        } else {
            list( $dia, $mes, $ano ) = explode( '/', $this->valor );
            if ( checkdate( $mes, $dia, $ano ) ) {
                $this->valor = "{$ano}-{$mes}-{$dia}";
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Responsavel em validar hora
     */
    private function verificaHora() {
        return (!preg_match( $this->erHORA, $this->valor )) ? false : true;
    }

    /**
     * Responsavel em validar campos monetario
     */
    private function verificaMoeda() {
        $valor = str_replace( '.', '', $this->valor );
        $valor = str_replace( ',', '.', $valor );

        if ( !preg_match( $this->erNUMEROPONTO, $valor ) ) {
            return false;
        } else {
            $this->valor = floatval( $valor );
            return true;
        }
    }

    /**
     * Responsavel em validar url
     */
    private function verificaUrl() {
        return (!preg_match( $this->erURL, $this->valor )) ? false : true;
    }

    /**
     * Responsavel em validar senhas
     */
    private function verificaSenha() {
        if ( strlen( $this->valor ) >= 6 ) {
            $this->valor = md5( $this->valor );
            return true;
        } else {
            return false;
        }
    }

    /**
     * Responsavel em validar numeros inteiros
     */
    private function verificaNumero() {
        if ( !preg_match( $this->erNUMERO, $this->valor ) ) {
            return false;
        } else {
            $this->valor = $this->valor;
            return true;
        }
    }
    
    /**
     * Responsavel em validar numeros inteiros
     */
    private function verificaNumeroPositivo() {
        if ( !preg_match( $this->erNUMEROPOSITIVO, $this->valor ) ) {
            return false;
        } else {
            $this->valor = $this->valor;
            return true;
        }
    }

    /**
     * Responsavel em validar numero decimais
     */
    private function verificaNumeroponto() {
        if ( !preg_match( $this->erNUMEROPONTO, $this->valor ) ) {
            return false;
        } else {
            $this->valor = str_replace( '.', '', $this->valor );
            $this->valor = str_replace( ',', '.', $this->valor );
            $this->valor = floatval( $this->valor );
            return true;
        }
    }

    /**
     * Responsavel em adicionar todos os campos que estiverem com erro
     */
    private function setErro() {
        //$this->erro[$this->campo] = utf8_encode($this->validaMsg);
        $this->erro[$this->campo] = $this->validaMsg;
    }

    /**
     * Responsavel em adicionar todos campos q estiverem corretos
     */
    private function setDados() {
        //$this->dados[$this->campo]  = addslashes(utf8_decode($this->valor));
        //$this->dados[$this->campo]  = addslashes($this->valor); // alterado
        $this->dados[$this->campo] = $this->valor;
        $this->setMsg();
    }

    private function setMsg() {
        $this->msg[$this->campo] = $this->validaMsg;
    }

    public function getMsg( $nome_campo = NULL ) {
        if ( $nome_campo ) {
            return $this->msg[$nome_campo];
        } else {
            return $this->msg;
        }
    }

    /**
     * responsavel em retornar todos campos q estiverm com erro
     *
     * @return array
     */
    public function getErro() {
        return $this->erro;
    }

    /**
     * responsavel em retornar os dados que estiverem ok
     *
     * @return array Ira retornar um array com o nome do campo e seu valor
     */
    public function getDados() {
        return $this->dados;
    }

    /**
     * responsavel em retornar nome da tabela sem o modulo
     *
     * @return string
     */
    public function getTabela() {
        return $this->tabela;
    }

    /**
     * responsavel em retornar nome do modulo junto com a tabela ou somente a tabela
     *
     * @return string
     */
    public function getModuloTabela() {
        return $this->moduloTabela;
    }

    /**
     * responsavel em retornar o id do registro
     *
     * @return integer
     */
    public function getIdTabela() {
        return $this->idTabela;
    }

}

?>