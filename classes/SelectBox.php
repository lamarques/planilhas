<?php

/**
 * Classe responsavel em criar select box
 */
class SelectBox {

    private $conexao;
    private $pdo;
    private $sql;
    private $select;
    private $id;
    private $nome;
    private $class;
    private $valida;
    private $onchange;
    private $disabled;
    private $option;
    private $valorOption;
    private $textoOption;
    private $campoSelecionado;
    private $AtributoOption;
    private $multiple;
    private $atributos;
    private $title;
    private $linguagem;
    private $selecione;
    private $agrupado;
    private $arrGrupos;
    private $Type;

    /**
     * metodo construtor inicia os atributos select e option
     */
    public function __construct() {
        $this->conexao = 'sistema';
        $this->select = "<select";
        $this->option = '<option value="0">Selecione</option>';
        $this->linguagem = "";
        $this->selecione = "Selecione";
    }

    public function setLinguagem( $linguagem ) {
        $this->linguagem = $linguagem;
        switch ( $this->linguagem ) {
            case 'ingles':
            case 'en':
                $this->selecione = "Select";
                break;
            case 'espanhol':
            case 'en':
                $this->selecione = "Seleccionar";
                break;
        }
        $this->setOpcaoZero( $this->selecione );
    }

    public function setOpcaoZero( $option ) {
        $this->option = str_replace( '<option value="0">Selecione</option>', '<option value="0">' . $option . '</option>', $this->option );
    }

    /**
     * Define se a opção zero ira ou nao aparecer
     * @param bool $zero default true
     */
    public function setMostraOpcaoZero( $zero = true ) {
        if ( !$zero ) {
            $this->option = '';
        }
    }

    /**
     * Define a conexao que devera ser utilizada
     * @param string $conexao
     */
    public function setConexao( $conexao ) {
        $this->conexao = $conexao;
    }

    /**
     * Define a conexao que devera ser utilizada
     * @param string $conexao
     */
    public function setPDO( $pdo = false ) {
        $this->pdo = $pdo;
    }

    /**
     * Define o sql que devera de ser executado.
     *
     * @param string $sql
     */
    public function setTipo( $type ) {
        $this->Type = $type;
    }

    public function setSql( $sql ) {
        $this->Type = 'sql';
        $this->Content = $sql;
    }

    /**
     * Define o array para ser executado.
     *
     * @param array();
     */
    public function setConteudo( $value ) {
        $this->Type = 'array';
        $this->Content = $value;
    }

    /**
     * Define o atributo name no select
     *
     * @param string $nome
     */
    public function setNome( $nome ) {
        $this->nome = $nome;
        $this->select .= ' name="' . $nome . '"';
    }

    /**
     * Define se terão grupos divisórios no select
     *
     * @param bool $valor
     */
    public function setAgrupado( $valor ) {
        $this->agrupado = $valor;
    }

    /**
     * Define se terão grupos divisórios no select
     *
     * @param bool $valor
     */
    public function setArrGrupos( $arrGrupos ) {
        $this->arrGrupos = $arrGrupos;
    }

    /**
     * Define o atributo multiple no select
     *
     * @param bool $multiple
     */
    public function setMultiple( $multiple = false ) {
        $this->multiple = $multiple;
        if ( $multiple ) {
            $this->select .= ' multiple="multiple" ';
        }
    }
    
    /**
     * Define o atributos no select
     *
     * @param string $nomeAtributo
     * @param string $valorAtributo
     */
    public function setAtributoSelect( $nomeAtributo, $valorAtributo ) {
        if ( $nomeAtributo ) {
            $this->select .= ' '.$nomeAtributo.'="'.$valorAtributo.'" ';
        }
    }

    /**
     * Define o atributo size no select
     *
     * @param string $multipleSize
     */
    public function setMultipleSize( $multipleSize ) {
        $this->multiple = $multipleSize;
        if ( $this->multiple )
            $this->select .= ' size="' . $multipleSize . '"';
    }

    /**
     * Define o atributo id no select
     *
     * @param string $id
     */
    public function setId( $id ) {
        $this->id = $id;
        $this->select .= ' id="' . $id . '"';
    }

    /**
     * Define o atributo valida no select
     * @param string $valida
     */
    public function setValida( $valida, $data_type = FALSE ) {
        $this->valida = $valida;
        if($data_type === FALSE) {
            $this->select .= ' valida="' . $valida . '"';
        } else {
            $this->select .= ' data-valida="' . $valida . '"';
        }
    }

    public function setStyle( $atributos ) {
        $this->atributos = $atributos;
        $this->select .= ' style="' . $atributos . '"';
    }

    /**
     * Define a o atributo class no select
     *
     * @param string $class
     */
    public function setClass( $class ) {
        $this->class = $class;
        $this->select .= ' class="' . $class . '"';
    }

    public function setTitle( $title ) {
        $this->title = $title;
        $this->select .= ' title="' . $title . '"';
    }

    /**
     * Define o evento Onchange
     *
     * @param string $evento
     */
    public function setOnchange( $evento ) {
        $this->onchange = $evento;
        $this->select .= ' onchange="' . $evento . '"';
    }

    /**
     * Define o atributo disable no select
     *
     * @param bool $bool
     */
    public function setDisabled( $bool ) {
        $this->disabled = $bool;
        $this->select .= ' disabled="disabled"';
    }

    /**
     * Define o primeiro item do option
     *
     * @param string $valor Valor do value
     * @param string $texto Valor que ira aparecer nas opçoes
     */
    public function setOptionPrimeiroItem( $valor = null, $texto = null ) {
        if ( $valor || $texto ) {
            $this->option = '<option value="' . $valor . '">' . $texto . '</option>';
        } else {
            $this->option = '';
        }
    }

    /**
     * Define o nome do campo na consulta sql que ira preencher o value do option
     *
     * @param string $valor
     */
    public function setValorOption( $valor ) {
        $this->valorOption = $valor;
    }

    /**
     * Define o nome do campo na consulta sql que ira preencher o texto do option
     *
     * @param string $texto
     */
    public function setTextoOption( $texto ) {
        $this->textoOption = $texto;
    }

    /**
     * Define o valor do option que devera estar selecionado
     *
     * @param string $valor
     */
    public function setCampoSelecionado( $valor ) {
        $this->campoSelecionado = $valor;
    }

    /**
     * Define o indice do atributo
     *
     * @param string $valor
     */
    public function setAtributoOption( $indice, $campo ) {
        $this->AtributoOption[$indice] = $campo;
    }

    private function buscaConteudo() {
        switch ( $this->Type ) {
            case "sql":
                if ( !$this->pdo )
                    $this->pdo = Conexao::open( $this->conexao );
                $consulta = $this->pdo->prepare( $this->Content );
                $consulta->execute();
                $resultado = $consulta->fetchAll( PDO::FETCH_ASSOC );
                return $resultado;
                break;
            case "array":
                return $this->Content;
                break;
        }
    }

    /**
     * Monta a select e a retorna num string
     *
     * @return string
     */
    public function CriaSelect() {
        try {
            $resultado = $this->buscaConteudo();

            $this->select .= ' >' . $this->option;

            if($this->agrupado)
            {
                foreach ($resultado as $key => $value)
                {
                    $this->select .= '<optgroup label="'.$this->arrGrupos[$key].'">';
                    $this->montaOpcoes($value);
                    $this->select .= '</optgroup>';
                }
            }
            else
                $this->montaOpcoes($resultado);

            $this->select .= '</select>';
        } catch ( PDOException $e ) {
            new Excecao( $e );
        }

        return $this->select;
    }

    private function montaOpcoes($resultado){
        if ( is_array( $resultado ) ) {
            foreach ( $resultado as $key => $linha ) {
                //verifica se é um array unidimensional
                $valor = $this->valorOption ? $linha[$this->valorOption] : $key;
                $texto = $this->textoOption ? $linha[$this->textoOption] : $linha;

                $this->select .= '<option value="' . htmlspecialchars( $valor ) . '"';
                if ( $this->campoSelecionado !== null || $this->campoSelecionado === 0 ) {
                    if ( $this->multiple ) {
                        $selecionado = explode( ",", $this->campoSelecionado );
                        for ( $i = 0; $i < count( $selecionado ); $i++ ) {
                            if ( $valor == $selecionado[$i] ) {
                                $this->select .= ' selected="selected"';
                            }
                        }
                    } else {
                        if(!is_string($this->campoSelecionado)) {
                            if ( $valor == $this->campoSelecionado ) {
                                $this->select .= ' selected="selected"';
                            }
                        } else {
                            if ( strtoupper(Util::removeAcentos($valor)) == strtoupper(Util::removeAcentos($this->campoSelecionado)) ) {
                                $this->select .= ' selected="selected"';
                            }
                        }
                    }
                }

                if ( !empty( $this->AtributoOption ) ) {
                    foreach ( $this->AtributoOption as $indice => $value ) {
                        if ( isset( $linha[$value] ) ) {
                            $this->select .= $indice . '="' . $linha[$value] . '"';
                        }
                    }
                }
                $this->select .= ' >' . stripcslashes( htmlspecialchars( ucfirst( $texto ) ) ) . '</option>';
            }
        }
    }

    public function setConteudoEstado() {
        $i = 0;
        $arrEstado = array();

        $arrEstado[$i]['estado'] = 'Acre';
        $arrEstado[$i]['sigla'] = 'AC';
        $arrEstado[$i++]['id_estado'] = 1;

        $arrEstado[$i]['estado'] = 'Alagoas';
        $arrEstado[$i]['sigla'] = 'AL';
        $arrEstado[$i++]['id_estado'] = 2;

        $arrEstado[$i]['estado'] = 'Amapá';
        $arrEstado[$i]['sigla'] = 'AP';
        $arrEstado[$i++]['id_estado'] = 4;

        $arrEstado[$i]['estado'] = 'Amazonas';
        $arrEstado[$i]['sigla'] = 'AM';
        $arrEstado[$i++]['id_estado'] = 3;

        $arrEstado[$i]['estado'] = 'Bahia';
        $arrEstado[$i]['sigla'] = 'BA';
        $arrEstado[$i++]['id_estado'] = 5;

        $arrEstado[$i]['estado'] = 'Ceará';
        $arrEstado[$i]['sigla'] = 'CE';
        $arrEstado[$i++]['id_estado'] = 6;

        $arrEstado[$i]['estado'] = 'Distrito Federal';
        $arrEstado[$i]['sigla'] = 'DF';
        $arrEstado[$i++]['id_estado'] = 7;

        $arrEstado[$i]['estado'] = 'Espírito Santo';
        $arrEstado[$i]['sigla'] = 'ES';
        $arrEstado[$i++]['id_estado'] = 8;

        $arrEstado[$i]['estado'] = 'Goiás';
        $arrEstado[$i]['sigla'] = 'GO';
        $arrEstado[$i++]['id_estado'] = 9;

        $arrEstado[$i]['estado'] = 'Maranhão';
        $arrEstado[$i]['sigla'] = 'MA';
        $arrEstado[$i++]['id_estado'] = 10;

        $arrEstado[$i]['estado'] = 'Mato Grasso do Sul';
        $arrEstado[$i]['sigla'] = 'MS';
        $arrEstado[$i++]['id_estado'] = 13;

        $arrEstado[$i]['estado'] = 'Mato Grasso';
        $arrEstado[$i]['sigla'] = 'MT';
        $arrEstado[$i++]['id_estado'] = 12;

        $arrEstado[$i]['estado'] = 'Minas Gerais';
        $arrEstado[$i]['sigla'] = 'MG';
        $arrEstado[$i++]['id_estado'] = 11;

        $arrEstado[$i]['estado'] = 'Paraná';
        $arrEstado[$i]['sigla'] = 'PR';
        $arrEstado[$i++]['id_estado'] = 18;

        $arrEstado[$i]['estado'] = 'Paraíba';
        $arrEstado[$i]['sigla'] = 'PB';
        $arrEstado[$i++]['id_estado'] = 15;

        $arrEstado[$i]['estado'] = 'Pará';
        $arrEstado[$i]['sigla'] = 'PA';
        $arrEstado[$i++]['id_estado'] = 14;

        $arrEstado[$i]['estado'] = 'Pernambuco';
        $arrEstado[$i]['sigla'] = 'PE';
        $arrEstado[$i++]['id_estado'] = 16;

        $arrEstado[$i]['estado'] = 'Piauí';
        $arrEstado[$i]['sigla'] = 'PI';
        $arrEstado[$i++]['id_estado'] = 17;

        $arrEstado[$i]['estado'] = 'Rio Grande do Norte';
        $arrEstado[$i]['sigla'] = 'RN';
        $arrEstado[$i++]['id_estado'] = 20;

        $arrEstado[$i]['estado'] = 'Rio Grande do Sul';
        $arrEstado[$i]['sigla'] = 'RS';
        $arrEstado[$i++]['id_estado'] = 23;

        $arrEstado[$i]['estado'] = 'Rio de Janeiro';
        $arrEstado[$i]['sigla'] = 'RJ';
        $arrEstado[$i++]['id_estado'] = 19;

        $arrEstado[$i]['estado'] = 'Rondônia';
        $arrEstado[$i]['sigla'] = 'RO';
        $arrEstado[$i++]['id_estado'] = 21;

        $arrEstado[$i]['estado'] = 'Roraima';
        $arrEstado[$i]['sigla'] = 'RR';
        $arrEstado[$i++]['id_estado'] = 22;

        $arrEstado[$i]['estado'] = 'Santa Catarina';
        $arrEstado[$i]['sigla'] = 'SC';
        $arrEstado[$i++]['id_estado'] = 24;

        $arrEstado[$i]['estado'] = 'São Paulo';
        $arrEstado[$i]['sigla'] = 'SP';
        $arrEstado[$i++]['id_estado'] = 26;

        $arrEstado[$i]['estado'] = 'Sergipe';
        $arrEstado[$i]['sigla'] = 'SE';
        $arrEstado[$i++]['id_estado'] = 25;

        $arrEstado[$i]['estado'] = 'Tocantins';
        $arrEstado[$i]['sigla'] = 'TO';
        $arrEstado[$i++]['id_estado'] = 27;

        $this->Type = 'array';
        $this->Content = $arrEstado;
    }

}
?>