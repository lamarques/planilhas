<?php

include_once 'ConexaoParametros.php';
include_once 'Conexao.php';

class CriaDO {

    private $nomeClasse;
    private $tabelaPropriedade;
    private $tabelaMetodo;
    private $chavePrimaria;
    private $chavePrimariaSequence;
    private $modulo;
    private $tabela;
    private $colunas;
    private $quantidade = 0;
    private $versao = '2.0';
    private $criadoPor = 'Rogério Lamarques';
    private $dataCriacao;
    private $caminhoDestino = 'teste';

    function __construct() {
        $this->dataCriacao = date("d/m/Y H:i:s");
        $this->caminhoDestino = (isset($_GET['pasta']) && !empty($_GET['pasta'])) ? $_GET['pasta'] : $this->caminhoDestino;
    }

    public function cria($modulo, $tabela) {
        $this->tabela = $tabela;
        $this->modulo = $modulo;
        $this->quantidade++;
        try {
            $pdo = Conexao::open('sistema');

            $sqlColunas = "
                SELECT  column_name AS nome_coluna,
                        data_type,
                        udt_name,
                        character_maximum_length,
                        is_nullable,
                        column_default
                FROM information_schema.COLUMNS
                WHERE table_name = :tabela AND table_schema = :modulo
                ORDER BY ordinal_position ASC
                ;";
            $consulta = $pdo->prepare($sqlColunas);
            $consulta->bindParam(':tabela', $this->tabela, PDO::PARAM_STR);
            $consulta->bindParam(':modulo', $this->modulo, PDO::PARAM_STR);
            $consulta->execute();
            if ($consulta->rowCount()) {
                $retornoConsulta = $consulta->fetchAll(PDO::FETCH_ASSOC);

                $campos = array();
                foreach ($retornoConsulta as $linha) {
                    $campos[$linha['nome_coluna']]['nome_coluna'] = $linha['nome_coluna'];
                    $campos[$linha['nome_coluna']]['data_type'] = $linha['data_type'];
                    $campos[$linha['nome_coluna']]['udt_name'] = $linha['udt_name'];
                    $campos[$linha['nome_coluna']]['character_maximum_length'] = $linha['character_maximum_length'];
                    $campos[$linha['nome_coluna']]['nulo'] = ($linha['is_nullable'] == 'YES') ? 'sim' : 'nao';

                    if(!empty($linha['column_default']) && preg_match('/^nextval/', $linha['column_default'])) {
                        $auxExplode = explode("nextval('" . $this->modulo . ".", $linha['column_default']);
                        $auxExplode2 = explode("'::regclass)", $auxExplode[1]);
                        $this->chavePrimariaSequence = $auxExplode2[0];
                    }
                }

                $sqlReferencia = "
                    SELECT  n.nspname AS esquema,
                            cl.relname AS tabela,
                            a.attname AS coluna,
                            ct.conname AS chave,
                            nf.nspname AS esquema_ref,
                            clf.relname AS tabela_ref,
                            af.attname AS coluna_ref
                    FROM pg_catalog.pg_attribute a
                    JOIN pg_catalog.pg_class cl ON (a.attrelid = cl.oid AND cl.relkind = 'r')
                    JOIN pg_catalog.pg_namespace n ON (n.oid = cl.relnamespace)
                    JOIN pg_catalog.pg_constraint ct ON (a.attrelid = ct.conrelid AND ct.confrelid != 0 AND ct.conkey[1] = a.attnum)
                    JOIN pg_catalog.pg_class clf ON (ct.confrelid = clf.oid AND clf.relkind = 'r')
                    JOIN pg_catalog.pg_namespace nf ON (nf.oid = clf.relnamespace)
                    JOIN pg_catalog.pg_attribute af ON (af.attrelid = ct.confrelid AND af.attnum = ct.confkey[1])
                    WHERE cl.relname = :tabela AND n.nspname = :modulo
                    ;";
                $consultaReferencia = $pdo->prepare($sqlReferencia);
                $consultaReferencia->bindParam(':tabela', $this->tabela, PDO::PARAM_STR);
                $consultaReferencia->bindParam(':modulo', $this->modulo, PDO::PARAM_STR);
                $consultaReferencia->execute();
                $retornoConsultaReferencia = $consultaReferencia->fetchAll(PDO::FETCH_ASSOC);

                foreach ($retornoConsultaReferencia as $linha) {
                    $campos[$linha['coluna']]['foreign_key'] = 'sim';
                    $campos[$linha['coluna']]['esquema_ref'] = $linha['esquema_ref'];
                    $campos[$linha['coluna']]['tabela_ref'] = $linha['tabela_ref'];
                    $campos[$linha['coluna']]['coluna_ref'] = $linha['coluna_ref'];

                    $consulta = $pdo->prepare($sqlColunas);
                    $consulta->bindParam(':tabela', $linha['tabela_ref'], PDO::PARAM_STR);
                    $consulta->bindParam(':modulo', $linha['esquema_ref'], PDO::PARAM_STR);
                    $consulta->execute();
                    $retornoConsultaReferencia = $consulta->fetchAll(PDO::FETCH_ASSOC);

                    $tem = false;
                    foreach ($retornoConsultaReferencia as $linhaReferencia) {
                        if ($linhaReferencia['udt_name'] != "int4" && (!$tem)) {
                            $campos[$linha['coluna']]['nome_coluna_ref'] = $linhaReferencia['nome_coluna'];
                            $tem = true;
                        }
                    }
                }

                $sqlChavePrimaria = "
                    SELECT column_name AS nome_coluna, ordinal_position
                    FROM information_schema.table_constraints AS tc
                    JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
                    WHERE tc.constraint_schema = :modulo
                        AND tc.table_name = :tabela
                        AND constraint_type = 'PRIMARY KEY'
                        AND kcu.table_schema = :modulo
                    ;";
                $consultaChavePrimaria = $pdo->prepare($sqlChavePrimaria);
                $consultaChavePrimaria->bindParam(':tabela', $this->tabela, PDO::PARAM_STR);
                $consultaChavePrimaria->bindParam(':modulo', $this->modulo, PDO::PARAM_STR);
                $consultaChavePrimaria->execute();
                $retornoChavePrimaria = $consultaChavePrimaria->fetchAll(PDO::FETCH_ASSOC);

                $this->chavePrimaria = false;

                foreach ($retornoChavePrimaria as $linha) {
                    if ($linha['ordinal_position'] == 1) {
                        if ($linha['nome_coluna'] == "id_{$this->tabela}") {
                            $this->chavePrimaria = true;
                        }
                    }
                    $campos[$linha['nome_coluna']]['primary_key'] = 'sim';
                }

                $this->colunas = $campos;

                $tabelaEmPartes = explode("_", $this->tabela);
                $this->nomeClasse = str_replace(' ', '', ucwords($this->modulo . ' ' . implode(" ", $tabelaEmPartes)));
                $this->tabelaPropriedade = $this->getNomePropriedade($this->tabela);
                $this->tabelaMetodo = $this->getNomeMetodo($this->tabela);

                // *****************************************************************
                // ************************** CLASS DTO ****************************
                // *****************************************************************
                $this->criaDTO();

                // *****************************************************************
                // ************************** CLASS DAO ****************************
                // *****************************************************************
                $this->criaDAO();

                // *****************************************************************
                // ************************* CLASS EXECUTA *************************
                // *****************************************************************
                $this->criaExcuta();
                echo "{$this->quantidade} - Pronto a tabela {$this->modulo}.{$this->tabela}<br />";
            } else {
                echo "Módulo ou tabela inválido(a)<br/>Verifique: <b>{$this->modulo}.{$this->tabela}</b><br/>";
            }
            unset($pdo);
        } catch (PDOException $e) {
            echo "{$this->quantidade} - Erro a tabela {$this->modulo}.{$this->tabela}<br />";
        }
    }

    private function criaDTO() {
        @unlink("{$this->caminhoDestino}/{$this->nomeClasse}DTO.php");

        $fp = fopen("{$this->caminhoDestino}/{$this->nomeClasse}DTO.php", "a");
        fwrite($fp, "<?php\r\n");

        fwrite($fp, "/**\r\n");
        fwrite($fp, " * Classe DTO para a tabela {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, " *\r\n");
        fwrite($fp, " * Data Transfer Object (DTO) - Utilizado apenas como estrutura para transporte de dados\r\n");
        fwrite($fp, " *\r\n");
        fwrite($fp, " * Contém a mesma estrutura da tabela, os campos da tabela com os seus Get's and Set's\r\n");
        fwrite($fp, " *\r\n");
        fwrite($fp, " * Versão: {$this->versao}\r\n");
        fwrite($fp, " * Criado Por: {$this->criadoPor}\r\n");
        fwrite($fp, " * Data Criação: {$this->dataCriacao}\r\n");
        fwrite($fp, " */\r\n");
        fwrite($fp, "class {$this->nomeClasse}DTO { \r\n");
        fwrite($fp, "    // Define as propriedades\r\n");

        foreach ($this->colunas as $linha) {
            $nomePropriedade = $this->getNomePropriedade($linha['nome_coluna']);
            fwrite($fp, "    private \${$nomePropriedade} = false;\r\n");
        }

        fwrite($fp, "\r\n");

        foreach ($this->colunas as $linha) {

            $nomePropriedade = $this->getNomePropriedade($linha['nome_coluna']);
            $nomeMetodo = $this->getNomeMetodo($linha['nome_coluna']);

            // ************************** METODO *******************************
            fwrite($fp, "    /**\r\n");
            fwrite($fp, "     * Seta um valor à propriedade {$nomePropriedade}\r\n");
            fwrite($fp, "     * @access public\r\n");
            fwrite($fp, "     * @param {$linha['udt_name']} \${$nomePropriedade}\r\n");
            fwrite($fp, "     */\r\n");
            fwrite($fp, "    public function set{$nomeMetodo}(\${$nomePropriedade})\r\n");
            fwrite($fp, "    {\r\n");
            switch ($linha['udt_name']) {
                case 'int4' :
                    if ((isset($linha['primary_key']) || isset($linha['foreign_key'])) && ($linha['nulo'] == 'nao')) {
                        fwrite($fp, "        \$this->{$nomePropriedade} = intval(\${$nomePropriedade});\r\n");
                    } else {
                        if (isset($linha['primary_key']) || isset($linha['foreign_key'])) {
                            fwrite($fp, "        \$this->{$nomePropriedade} = empty(\${$nomePropriedade}) ? '' : intval(\${$nomePropriedade});\r\n");
                        } else {
                            fwrite($fp, "        \$this->{$nomePropriedade} = strlen(\${$nomePropriedade}) > 0 ? intval(\${$nomePropriedade}) : '';\r\n");
                        }
                    }
                    break;
                case 'numeric' :
                    fwrite($fp, "        \$this->{$nomePropriedade} = strlen(\${$nomePropriedade}) > 0 ? floatval(\${$nomePropriedade}) : '';\r\n");
                    break;
                case 'varchar' :
                    fwrite($fp, "        \$this->{$nomePropriedade} = strlen(\${$nomePropriedade}) > 0 ? substr(\${$nomePropriedade},0,{$linha['character_maximum_length']}) : '';\r\n");
                    break;
                case 'timestamp' :
                    fwrite($fp, "        \$this->{$nomePropriedade} = empty(\${$nomePropriedade}) ? date('Y-m-d H:i:s') : \${$nomePropriedade};\r\n");
                    break;
                case 'bool' :
                    fwrite($fp, "        \$this->{$nomePropriedade} = \${$nomePropriedade};\r\n");
                    break;
                default :
                    fwrite($fp, "        \$this->{$nomePropriedade} = strlen(\${$nomePropriedade}) > 0 ? \${$nomePropriedade} : '';\r\n");
                    break;
            }
            fwrite($fp, "    }\r\n\r\n");

            // ************************** METODO *******************************
            fwrite($fp, "    /**\r\n");
            fwrite($fp, "     * Retorna o valor da propriedade {$nomePropriedade}\r\n");
            fwrite($fp, "     * @access public\r\n");
            fwrite($fp, "     * @return {$linha['udt_name']}\r\n");
            fwrite($fp, "     */\r\n");
            fwrite($fp, "    public function get{$nomeMetodo}()\r\n");
            fwrite($fp, "    {\r\n");
            fwrite($fp, "        return \$this->{$nomePropriedade};\r\n");
            fwrite($fp, "    }\r\n\r\n");
        }

        fwrite($fp, "}\r\n");
        fclose($fp);
    }

    private function criaDAO() {
        @unlink("{$this->caminhoDestino}/{$this->nomeClasse}DAO.php");

        $fp = fopen("{$this->caminhoDestino}/{$this->nomeClasse}DAO.php", "a");
        fwrite($fp, "<?php\r\n");

        fwrite($fp, "/**\r\n");
        fwrite($fp, " * Classe DAO para a tabela {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, " *\r\n");
        fwrite($fp, " * Data Access Object (DAO) - Responsavel em fazer operações básicas na tabela como: Inserir, Editar e Excluir\r\n");
        fwrite($fp, " *\r\n");
        fwrite($fp, " * Versão: {$this->versao}\r\n");
        fwrite($fp, " * Criado Por: {$this->criadoPor}\r\n");
        fwrite($fp, " * Data Criação: {$this->dataCriacao}\r\n");
        fwrite($fp, " */\r\n");
        fwrite($fp, "class {$this->nomeClasse}DAO { \r\n");
        fwrite($fp, "    // Define as propriedades\r\n");
        fwrite($fp, "    private \$pdo;\r\n");
        fwrite($fp, "    private \$sql;\r\n");
        fwrite($fp, "    private \$idTipoAcao;\r\n");
        fwrite($fp, "    private \$nota;\r\n");
        fwrite($fp, "    private \$criaHistorico;\r\n\r\n");

        // ************************** METODO *******************************
        // cria metodo construtor
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Construtor\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function __construct(PDO \$pdo, \$historico = true) { \r\n");
        fwrite($fp, "        \$this->pdo = \$pdo;\r\n");
        fwrite($fp, "        \$this->criaHistorico = \$historico;\r\n");
        fwrite($fp, "    }\r\n\r\n");

        // ************************** METODO *******************************
        // cria metodo para retornar o sql
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Retorna o valor da propriedade sql\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function getSql() {\r\n");
        fwrite($fp, "        return \$this->sql;\r\n");
        fwrite($fp, "    }\r\n\r\n");

        // ************************** METODO *******************************
        // cria metodo para inserir
        $this->daoInserir($fp);

        // ************************** METODO *******************************
        // cria metodo para editar
        if ($this->chavePrimaria) {
            $this->daoEditarId($fp);
        } else {
            $this->daoEditar($fp);
        }

        // ************************** METODO *******************************
        // cria metodo para excluir
        if ($this->chavePrimaria) {
            $this->daoExcluirId($fp);
        } else {
            $this->daoExcluir($fp);
        }

        // ************************** METODO *******************************
        // cria metodo para listar
        $this->daoListar($fp);

        // ************************** METODO *******************************
        // cria metodo para criar nota
        //$this->daoCriaNota($fp);
        // ************************** METODO *******************************
        // cria metodo para historico
        if ($this->chavePrimaria) {
            $this->daoHistoricoId($fp);
        } else {
            $this->daoHistorico($fp);
        }

        fwrite($fp, "}\r\n");
        fclose($fp);
    }

    private function daoInserir($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em inserir\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param objeto \${$this->tabelaPropriedade}DTO Objeto DTO contendo informações a serem inseridas\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function inserir({$this->nomeClasse}DTO \${$this->tabelaPropriedade}DTO) {\r\n");
        fwrite($fp, "        // Define a propriedade idTipoAcao como Inserir\r\n");
        fwrite($fp, "        \$this->idTipoAcao = 1;\r\n");
        fwrite($fp, "        // Define a nota para a ação\r\n");
        fwrite($fp, "        \$this->nota = 'Cadastro inserido';\r\n");
        fwrite($fp, "        // Instancia classe SqlInserir\r\n");
        fwrite($fp, "        \$sql = new SqlInserir;\r\n");
        fwrite($fp, "        // Seta a tabela\r\n");
        fwrite($fp, "        \$sql->setTabela('{$this->modulo}.{$this->tabela}');\r\n");
        fwrite($fp, "        // Seta o(s) campo(s)\r\n");
        $i = 0;
        foreach ($this->colunas as $linha) {
            $nomeMetodo = $this->getNomeMetodo($linha['nome_coluna']);
            if ($this->chavePrimaria) {
                if ($i != 0) {
                    fwrite($fp, "        if ( \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}() !== false )\r\n");
                    fwrite($fp, "        {\r\n");
                    fwrite($fp, "            \$sql->setCampo('{$linha['nome_coluna']}', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}());\r\n");
                    fwrite($fp, "        }\r\n");
                }
            } else {
                fwrite($fp, "        if ( \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}() !== false )\r\n");
                fwrite($fp, "        {\r\n");
                fwrite($fp, "            \$sql->setCampo('{$linha['nome_coluna']}', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}());\r\n");
                fwrite($fp, "        }\r\n");
            }
            $i++;
        }
        fwrite($fp, "        // Prepara sql\r\n");
        fwrite($fp, "        \$consulta = \$this->pdo->prepare(\$sql->getInstrucao());\r\n");
        fwrite($fp, "        // Executa sql\r\n");
        fwrite($fp, "        \$consulta->execute();\r\n");
        if ($this->chavePrimaria) {
            fwrite($fp, "        // Seta o ultimo id inserido a propriedade id{$this->tabelaMetodo}\r\n");
            fwrite($fp, "        \${$this->tabelaPropriedade}DTO->setId{$this->tabelaMetodo}(\$this->pdo->lastInsertId('{$this->modulo}.{$this->chavePrimariaSequence}'));\r\n");
        }
        fwrite($fp, "        // Seta o sql executado a propriedade sql\r\n");
        fwrite($fp, "        \$this->sql = \$sql->getInstrucao();\r\n");
        fwrite($fp, "        // Inseri o historico\r\n");
        fwrite($fp, "        if ( \$this->criaHistorico )\r\n");
        fwrite($fp, "        {\r\n");
        fwrite($fp, "            \$this->historico(\${$this->tabelaPropriedade}DTO);\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function daoEditarId($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em editar\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param objeto \${$this->tabelaPropriedade}DTO Objeto DTO contendo informações a serem editadas\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function editar({$this->nomeClasse}DTO \${$this->tabelaPropriedade}DTO) { \r\n");
        fwrite($fp, "        // Define criterio para executar busca\r\n");
        fwrite($fp, "        \$criterioListar = array();\r\n");
        $nomeMetodo = $this->getNomeMetodo("id_{$this->tabela}");
        fwrite($fp, "        \$criterioListar []= array('id_{$this->tabela}', '=', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}());\r\n");
        fwrite($fp, "        // Executa a busca\r\n");
        fwrite($fp, "        \$consulta = \$this->listar(null, \$criterioListar);\r\n");
        fwrite($fp, "        // Verifica se a busca retornou o registro\r\n");
        fwrite($fp, "        if ( \$consulta->rowCount() == 1 )\r\n");
        fwrite($fp, "        {\r\n");
        fwrite($fp, "            // Define a propriedade idTipoAcao como Editar\r\n");
        fwrite($fp, "            \$this->idTipoAcao = 2;\r\n");
        fwrite($fp, "            // Instancia classe SqlEditar\r\n");
        fwrite($fp, "            \$sql = new SqlEditar;\r\n");
        fwrite($fp, "            // Seta a tabela\r\n");
        fwrite($fp, "            \$sql->setTabela('{$this->modulo}.{$this->tabela}');\r\n");
        fwrite($fp, "            // Passa resultado da consulta a variavel \$linha\r\n");
        fwrite($fp, "            \$linha = \$consulta->fetch(PDO::FETCH_ASSOC);\r\n");
        fwrite($fp, "            \$this->nota = '';\r\n");
        fwrite($fp, "            // Faz verificação sobre cada campo da tabela para setar na consulta e criar nota\r\n");
        $i = 0;
        foreach ($this->colunas as $linha) {
            if ($i != 0) {
                $nomeMetodo = $this->getNomeMetodo($linha['nome_coluna']);
                fwrite($fp, "            if ( \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}() !== false )\r\n");
                fwrite($fp, "            {\r\n");
                if ($linha['udt_name'] == 'bool') {
                    fwrite($fp, "                if ( \$linha['{$linha['nome_coluna']}'] !== \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}() )\r\n");
                } else {
                    fwrite($fp, "                if ( \$linha['{$linha['nome_coluna']}'] != \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}() )\r\n");
                }

                fwrite($fp, "                {\r\n");

                if (isset($linha['esquema_ref'])) {
                    $tabelaRefMetodo = $this->getNomeMetodo($linha['tabela_ref']);
                    $tabelaRefPropriedade = $this->getNomePropriedade($linha['tabela_ref']);

                    $tabelaRefEmPartes = explode("_", $linha['tabela_ref']);
                    $nomeClasseRef = str_replace(' ', '', ucwords($linha['esquema_ref'] . ' ' . implode(" ", $tabelaRefEmPartes)));
                    fwrite($fp, "                    // Instancia Classe {$nomeClasseRef}Executa \r\n");
                    fwrite($fp, "                    \${$tabelaRefPropriedade} = new {$nomeClasseRef}Executa(\$this->pdo);\r\n");
                    fwrite($fp, "                    // Define campo para busca\r\n");
                    fwrite($fp, "                    \$campo{$tabelaRefMetodo} = array('{$linha['nome_coluna_ref']}');\r\n");
                    fwrite($fp, "                    // Define criterio para busca valor antigo\r\n");
                    fwrite($fp, "                    \$criterio{$tabelaRefMetodo}Antigo = array();\r\n");
                    fwrite($fp, "                    \$criterio{$tabelaRefMetodo}Antigo []= array('id_{$linha['tabela_ref']}', '=', \$linha['{$linha['nome_coluna']}']);\r\n");
                    fwrite($fp, "                    // Executa a busca\r\n");
                    fwrite($fp, "                    \$consultaAntigo = \${$tabelaRefPropriedade}->listar(\$campo{$tabelaRefMetodo}, \$criterio{$tabelaRefMetodo}Antigo);\r\n");
                    fwrite($fp, "                    \$linhaAntigo = \$consultaAntigo->fetch(PDO::FETCH_ASSOC);\r\n");
                    if ($linha['nulo'] == 'nao') {
                        fwrite($fp, "                    // Define criterio para busca valor atual\r\n");
                        fwrite($fp, "                    \$criterio{$tabelaRefMetodo}Atual = array();\r\n");
                        fwrite($fp, "                    \$criterio{$tabelaRefMetodo}Atual []= array('id_{$linha['tabela_ref']}', '=', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}());\r\n");
                        fwrite($fp, "                    \$consultaAtual = \${$tabelaRefPropriedade}->listar(\$campo{$tabelaRefMetodo}, \$criterio{$tabelaRefMetodo}Atual);\r\n");
                        fwrite($fp, "                    \$linhaAtual = \$consultaAtual->fetch(PDO::FETCH_ASSOC);\r\n");
                    } else {
                        fwrite($fp, "                    if ( \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}() !== '' )\r\n");
                        fwrite($fp, "                    {\r\n");
                        fwrite($fp, "                        // Define criterio para busca valor atual\r\n");
                        fwrite($fp, "                        \$criterio{$tabelaRefMetodo}Atual = array();\r\n");
                        fwrite($fp, "                        \$criterio{$tabelaRefMetodo}Atual []= array('id_{$linha['tabela_ref']}', '=', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}());\r\n");
                        fwrite($fp, "                        \$consultaAtual = \${$tabelaRefPropriedade}->listar(\$campo{$tabelaRefMetodo}, \$criterio{$tabelaRefMetodo}Atual);\r\n");
                        fwrite($fp, "                        \$linhaAtual = \$consultaAtual->fetch(PDO::FETCH_ASSOC);\r\n");
                        fwrite($fp, "                    }\r\n");
                        fwrite($fp, "                    else\r\n");
                        fwrite($fp, "                    {\r\n");
                        fwrite($fp, "                        \$linhaAtual['{$linha['nome_coluna_ref']}'] = '';\r\n");
                        fwrite($fp, "                    }\r\n");
                    }

                    fwrite($fp, "                    \$this->nota .= empty(\$this->nota) ? '' : \"\\r\\n|><|<->|><|\";\r\n");
                    fwrite($fp, "                    \$this->nota .= \"{$linha['nome_coluna']}|><|<>|><|{\$linhaAntigo['{$linha['nome_coluna_ref']}']}|><|<>|><|{\$linhaAtual['{$linha['nome_coluna_ref']}']}\";\r\n");
                } else {
                    fwrite($fp, "                    \$this->nota .= empty(\$this->nota) ? '' : \"\\r\\n|><|<->|><|\";\r\n");
                    fwrite($fp, "                    \$this->nota .= \"{$linha['nome_coluna']}|><|<>|><|{\$linha['{$linha['nome_coluna']}']}|><|<>|><|{\${$this->tabelaPropriedade}DTO->get{$nomeMetodo}()}\";\r\n");
                }

                fwrite($fp, "                    \$sql->setCampo('{$linha['nome_coluna']}', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}());\r\n");
                fwrite($fp, "                }\r\n");
                fwrite($fp, "            }\r\n");
            }
            $i++;
        }

        fwrite($fp, "            // Verifica se deve editar ou nao\r\n");
        fwrite($fp, "            if ( !empty(\$this->nota) )\r\n");
        fwrite($fp, "            {\r\n");
        fwrite($fp, "                // Instancia classe SqlCriterio\r\n");
        fwrite($fp, "                \$criterio = new SqlCriterio;\r\n");
        fwrite($fp, "                // Define criterio de edição\r\n");
        $nomeMetodo = $this->getNomeMetodo("id_{$this->tabela}");
        fwrite($fp, "                \$criterio->add(new SqlFiltro('id_{$this->tabela}', '=', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}()));\r\n");
        fwrite($fp, "                \$sql->setCriterio(\$criterio);\r\n");
        fwrite($fp, "                // Prepara sql\r\n");
        fwrite($fp, "                \$consulta = \$this->pdo->prepare(\$sql->getInstrucao());\r\n");
        fwrite($fp, "                // Executa sql\r\n");
        fwrite($fp, "                \$consulta->execute();\r\n");
        fwrite($fp, "                // Seta o sql executado a propriedade sql\r\n");
        fwrite($fp, "                \$this->sql = \$sql->getInstrucao();\r\n");
        fwrite($fp, "                // Inseri o historico\r\n");
        fwrite($fp, "                if ( \$this->criaHistorico )\r\n");
        fwrite($fp, "                {\r\n");
        fwrite($fp, "                    \$this->historico(\${$this->tabelaPropriedade}DTO);\r\n");
        fwrite($fp, "                }\r\n");
        fwrite($fp, "            }\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function daoEditar($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em editar\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param objeto \${$this->tabelaPropriedade}DTO Objeto DTO contendo informações a serem editadas\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function editar({$this->nomeClasse}DTO \${$this->tabelaPropriedade}DTO) { \r\n");
        fwrite($fp, "        // Define criterio para executar busca\r\n");
        fwrite($fp, "        \$criterioListar = array();\r\n");

        foreach ($this->colunas as $linha) {
            if (isset($linha['primary_key'])) {
                $nomeMetodo = $this->getNomeMetodo($linha['nome_coluna']);
                fwrite($fp, "        \$criterioListar []= array('{$linha['nome_coluna']}', '=', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}());\r\n");
            }
        }

        fwrite($fp, "        // Executa a busca\r\n");
        fwrite($fp, "        \$consulta = \$this->listar(null, \$criterioListar);\r\n");
        fwrite($fp, "        // Verifica se a busca retornou o registro\r\n");
        fwrite($fp, "        if ( \$consulta->rowCount() == 1 )\r\n");
        fwrite($fp, "        {\r\n");
        fwrite($fp, "            // Define a propriedade idTipoAcao como Editar\r\n");
        fwrite($fp, "            \$this->idTipoAcao = 2;\r\n");
        fwrite($fp, "            // Instancia classe SqlEditar\r\n");
        fwrite($fp, "            \$sql = new SqlEditar;\r\n");
        fwrite($fp, "            // Seta a tabela\r\n");
        fwrite($fp, "            \$sql->setTabela('{$this->modulo}.{$this->tabela}');\r\n");
        fwrite($fp, "            // Passa resultado da consulta a variavel \$linha\r\n");
        fwrite($fp, "            \$linha = \$consulta->fetch(PDO::FETCH_ASSOC);\r\n");
        fwrite($fp, "            \$this->nota = '';\r\n");
        fwrite($fp, "            // Faz verificação sobre cada campo da tabela para setar na consulta e criar nota\r\n");

        foreach ($this->colunas as $linha) {
            if (!isset($linha['primary_key'])) {
                $nomeMetodo = $this->getNomeMetodo($linha['nome_coluna']);
                fwrite($fp, "            if ( \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}() !== false )\r\n");
                fwrite($fp, "            {\r\n");
                if ($linha['udt_name'] == 'bool') {
                    fwrite($fp, "                if ( \$linha['{$linha['nome_coluna']}'] !== \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}() )\r\n");
                } else {
                    fwrite($fp, "                if ( \$linha['{$linha['nome_coluna']}'] != \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}() )\r\n");
                }

                fwrite($fp, "                {\r\n");

                if (isset($linha['esquema_ref'])) {
                    $tabelaRefMetodo = $this->getNomeMetodo($linha['tabela_ref']);
                    $tabelaRefPropriedade = $this->getNomePropriedade($linha['tabela_ref']);

                    $tabelaRefEmPartes = explode("_", $linha['tabela_ref']);
                    $nomeClasseRef = str_replace(' ', '', ucwords($linha['esquema_ref'] . ' ' . implode(" ", $tabelaRefEmPartes)));
                    fwrite($fp, "                    // Instancia Classe {$nomeClasseRef}Executa \r\n");
                    fwrite($fp, "                    \${$tabelaRefPropriedade} = new {$nomeClasseRef}Executa(\$this->pdo);\r\n");
                    fwrite($fp, "                    // Define campo para busca\r\n");
                    fwrite($fp, "                    \$campo{$tabelaRefMetodo} = array('{$linha['nome_coluna_ref']}');\r\n");
                    fwrite($fp, "                    // Define criterio para busca valor antigo\r\n");
                    fwrite($fp, "                    \$criterio{$tabelaRefMetodo}Antigo = array();\r\n");
                    fwrite($fp, "                    \$criterio{$tabelaRefMetodo}Antigo []= array('id_{$linha['tabela_ref']}', '=', \$linha['{$linha['nome_coluna']}']);\r\n");
                    fwrite($fp, "                    // Executa a busca\r\n");
                    fwrite($fp, "                    \$consultaAntigo = \${$tabelaRefPropriedade}->listar(\$campo{$tabelaRefMetodo}, \$criterio{$tabelaRefMetodo}Antigo);\r\n");
                    fwrite($fp, "                    \$linhaAntigo = \$consultaAntigo->fetch(PDO::FETCH_ASSOC);\r\n");
                    if ($linha['nulo'] == 'nao') {
                        fwrite($fp, "                    // Define criterio para busca valor atual\r\n");
                        fwrite($fp, "                    \$criterio{$tabelaRefMetodo}Atual = array();\r\n");
                        fwrite($fp, "                    \$criterio{$tabelaRefMetodo}Atual []= array('id_{$linha['tabela_ref']}', '=', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}());\r\n");
                        fwrite($fp, "                    \$consultaAtual = \${$tabelaRefPropriedade}->listar(\$campo{$tabelaRefMetodo}, \$criterio{$tabelaRefMetodo}Atual);\r\n");
                        fwrite($fp, "                    \$linhaAtual = \$consultaAtual->fetch(PDO::FETCH_ASSOC);\r\n");
                    } else {
                        fwrite($fp, "                    if ( \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}() !== '' )\r\n");
                        fwrite($fp, "                    {\r\n");
                        fwrite($fp, "                        // Define criterio para busca valor atual\r\n");
                        fwrite($fp, "                        \$criterio{$tabelaRefMetodo}Atual = array();\r\n");
                        fwrite($fp, "                        \$criterio{$tabelaRefMetodo}Atual []= array('id_{$linha['tabela_ref']}', '=', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}());\r\n");
                        fwrite($fp, "                        \$consultaAtual = \${$tabelaRefPropriedade}->listar(\$campo{$tabelaRefMetodo}, \$criterio{$tabelaRefMetodo}Atual);\r\n");
                        fwrite($fp, "                        \$linhaAtual = \$consultaAtual->fetch(PDO::FETCH_ASSOC);\r\n");
                        fwrite($fp, "                    }\r\n");
                        fwrite($fp, "                    else\r\n");
                        fwrite($fp, "                    {\r\n");
                        fwrite($fp, "                        \$linhaAtual['{$linha['nome_coluna_ref']}'] = '';\r\n");
                        fwrite($fp, "                    }\r\n");
                    }

                    fwrite($fp, "                    \$this->nota .= empty(\$this->nota) ? '' : \"\\r\\n|><|<->|><|\";\r\n");
                    fwrite($fp, "                    \$this->nota .= \"{$linha['nome_coluna']}|><|<>|><|{\$linhaAntigo['{$linha['nome_coluna_ref']}']}|><|<>|><|{\$linhaAtual['{$linha['nome_coluna_ref']}']}\";\r\n");
                } else {
                    fwrite($fp, "                    \$this->nota .= empty(\$this->nota) ? '' : \"\\r\\n|><|<->|><|\";\r\n");
                    fwrite($fp, "                    \$this->nota .= \"{$linha['nome_coluna']}|><|<>|><|{\$linha['{$linha['nome_coluna']}']}|><|<>|><|{\${$this->tabelaPropriedade}DTO->get{$nomeMetodo}()}\";\r\n");
                }

                fwrite($fp, "                    \$sql->setCampo('{$linha['nome_coluna']}', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}());\r\n");
                fwrite($fp, "                }\r\n");
                fwrite($fp, "            }\r\n");
            }
        }

        fwrite($fp, "            // Verifica se deve editar ou nao\r\n");
        fwrite($fp, "            if ( !empty(\$this->nota) )\r\n");
        fwrite($fp, "            {\r\n");
        fwrite($fp, "                // Instancia classe SqlCriterio\r\n");
        fwrite($fp, "                \$criterio = new SqlCriterio;\r\n");
        fwrite($fp, "                // Define criterio de edição\r\n");
        foreach ($this->colunas as $linha) {
            if (isset($linha['primary_key'])) {
                $nomeMetodo = $this->getNomeMetodo($linha['nome_coluna']);
                fwrite($fp, "                \$criterio->add(new SqlFiltro('{$linha['nome_coluna']}', '=', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}()));\r\n");
            }
        }
        fwrite($fp, "                \$sql->setCriterio(\$criterio);\r\n");
        fwrite($fp, "                // Prepara sql\r\n");
        fwrite($fp, "                \$consulta = \$this->pdo->prepare(\$sql->getInstrucao());\r\n");
        fwrite($fp, "                // Executa sql\r\n");
        fwrite($fp, "                \$consulta->execute();\r\n");
        fwrite($fp, "                // Seta o sql executado a propriedade sql\r\n");
        fwrite($fp, "                \$this->sql = \$sql->getInstrucao();\r\n");
        fwrite($fp, "                // Inseri o historico\r\n");
        fwrite($fp, "                if ( \$this->criaHistorico )\r\n");
        fwrite($fp, "                {\r\n");
        fwrite($fp, "                    \$this->historico(\${$this->tabelaPropriedade}DTO);\r\n");
        fwrite($fp, "                }\r\n");
        fwrite($fp, "            }\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function daoExcluirId($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em excluir\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param objeto \${$this->tabelaPropriedade}DTO Objeto DTO contendo informações a serem editadas\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function excluir({$this->nomeClasse}DTO \${$this->tabelaPropriedade}DTO) { \r\n");
        fwrite($fp, "        // Define a propriedade idTipoAcao como Excluir\r\n");
        fwrite($fp, "        \$this->idTipoAcao = 3;\r\n");
        fwrite($fp, "        // Define a nota para a ação\r\n");
        fwrite($fp, "        \$this->nota = 'Cadastro excluido';\r\n");
        fwrite($fp, "        // Instancia classe SqlExcluir\r\n");
        fwrite($fp, "        \$sql = new SqlExcluir;\r\n");
        fwrite($fp, "        // Seta a tabela\r\n");
        fwrite($fp, "        \$sql->setTabela('{$this->modulo}.{$this->tabela}');\r\n");
        fwrite($fp, "        // Instancia classe SqlCriterio\r\n");
        fwrite($fp, "        \$criterio = new SqlCriterio;\r\n");
        fwrite($fp, "        // Define criterio de exclusão\r\n");
        $nomeMetodo = $this->getNomeMetodo("id_{$this->tabela}");
        fwrite($fp, "        \$criterio->add(new SqlFiltro('id_{$this->tabela}', '=', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}()));\r\n");
        fwrite($fp, "        \$sql->setCriterio(\$criterio);\r\n");
        fwrite($fp, "        // Prepara sql\r\n");
        fwrite($fp, "        \$consulta = \$this->pdo->prepare(\$sql->getInstrucao());\r\n");
        fwrite($fp, "        // Executa sql\r\n");
        fwrite($fp, "        \$consulta->execute();\r\n");
        fwrite($fp, "        // Verifica se excluiu o registro\r\n");
        fwrite($fp, "        if ( \$consulta->rowCount() > 0 )\r\n");
        fwrite($fp, "        {\r\n");
        fwrite($fp, "            // Seta o sql executado a propriedade sql\r\n");
        fwrite($fp, "            \$this->sql = \$sql->getInstrucao();\r\n");
        fwrite($fp, "            // Inseri o historico\r\n");
        fwrite($fp, "            if ( \$this->criaHistorico )\r\n");
        fwrite($fp, "            {\r\n");
        fwrite($fp, "                \$this->historico(\${$this->tabelaPropriedade}DTO);\r\n");
        fwrite($fp, "            }\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function daoExcluir($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em excluir\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param objeto \${$this->tabelaPropriedade}DTO Objeto DTO contendo informações a serem editadas\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function excluir({$this->nomeClasse}DTO \${$this->tabelaPropriedade}DTO) { \r\n");
        fwrite($fp, "        // Define a propriedade idTipoAcao como Excluir\r\n");
        fwrite($fp, "        \$this->idTipoAcao = 3;\r\n");
        fwrite($fp, "        // Define a nota para a ação\r\n");
        fwrite($fp, "        \$this->nota = 'Cadastro excluido';\r\n");
        fwrite($fp, "        // Instancia classe SqlExcluir\r\n");
        fwrite($fp, "        \$sql = new SqlExcluir;\r\n");
        fwrite($fp, "        // Seta a tabela\r\n");
        fwrite($fp, "        \$sql->setTabela('{$this->modulo}.{$this->tabela}');\r\n");
        fwrite($fp, "        // Instancia classe SqlCriterio\r\n");
        fwrite($fp, "        \$criterio = new SqlCriterio;\r\n");
        fwrite($fp, "        // Define criterio de exclusão\r\n");
        foreach ($this->colunas as $linha) {
            if (isset($linha['primary_key'])) {
                $nomeMetodo = $this->getNomeMetodo($linha['nome_coluna']);
                fwrite($fp, "        \$criterio->add(new SqlFiltro('{$linha['nome_coluna']}', '=', \${$this->tabelaPropriedade}DTO->get{$nomeMetodo}()));\r\n");
            }
        }
        fwrite($fp, "        \$sql->setCriterio(\$criterio);\r\n");
        fwrite($fp, "        // Prepara sql\r\n");
        fwrite($fp, "        \$consulta = \$this->pdo->prepare(\$sql->getInstrucao());\r\n");
        fwrite($fp, "        // Executa sql\r\n");
        fwrite($fp, "        \$consulta->execute();\r\n");
        fwrite($fp, "        // Verifica se excluiu o registro\r\n");
        fwrite($fp, "        if ( \$consulta->rowCount() > 0 )\r\n");
        fwrite($fp, "        {\r\n");
        fwrite($fp, "            // Seta o sql executado a propriedade sql\r\n");
        fwrite($fp, "            \$this->sql = \$sql->getInstrucao();\r\n");
        fwrite($fp, "            // Inseri o historico\r\n");
        fwrite($fp, "            if ( \$this->criaHistorico )\r\n");
        fwrite($fp, "            {\r\n");
        fwrite($fp, "                \$this->historico(\${$this->tabelaPropriedade}DTO);\r\n");
        fwrite($fp, "            }\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function daoListar($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em listar\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param array \$campos Array contendo o nome dos campos\r\n");
        fwrite($fp, "     * @param array \$criterios Array contendo o(s) criterio(s) para listagem\r\n");
        fwrite($fp, "     * @param array \$propriedade Array contendo o nome das propriedades \r\n");
        fwrite($fp, "     * @return objeto Retorna o Objeto com o resultado da consulta\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function listar(\$campos = null, \$criterios = null, \$propriedade = null) { \r\n");
        fwrite($fp, "        // Instancia classe SqlSelect\r\n");
        fwrite($fp, "        \$sql = new SqlSelect;\r\n");
        fwrite($fp, "        // Seta a tabela\r\n");
        fwrite($fp, "        \$sql->setTabela('{$this->modulo}.{$this->tabela}');\r\n");
        fwrite($fp, "        // Seta campos\r\n");
        fwrite($fp, "        if ( \$campos )\r\n");
        fwrite($fp, "        {\r\n");
        fwrite($fp, "            foreach( \$campos AS \$campo )\r\n");
        fwrite($fp, "            {\r\n");
        fwrite($fp, "                \$sql->addColuna(\$campo);\r\n");
        fwrite($fp, "            }\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "        else\r\n");
        fwrite($fp, "        {\r\n");
        fwrite($fp, "            \$sql->addColuna('*');\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "        // Instancia classe SqlCriterio\r\n");
        fwrite($fp, "        \$criterio = new SqlCriterio;\r\n");
        fwrite($fp, "        // Seta criterios\r\n");
        fwrite($fp, "        if ( \$criterios )\r\n");
        fwrite($fp, "        {\r\n");
        fwrite($fp, "            foreach( \$criterios AS \$valor )\r\n");
        fwrite($fp, "            {\r\n");
        fwrite($fp, "                \$operador = isset(\$valor[3]) ? \$valor[3] . ' ' : 'AND ';\r\n");
        fwrite($fp, "                \$criterio->add(new SqlFiltro(\$valor[0], \$valor[1], \$valor[2]), \$operador);\r\n");
        fwrite($fp, "            }\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "        // Seta propriedades\r\n");
        fwrite($fp, "        if ( \$propriedade )\r\n");
        fwrite($fp, "        {\r\n");
        fwrite($fp, "            foreach( \$propriedade AS \$valor )\r\n");
        fwrite($fp, "            {\r\n");
        fwrite($fp, "                \$criterio->setPropriedade(\$valor[0], \$valor[1]);\r\n");
        fwrite($fp, "            }\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "        // Define criterios e propriedades ao sql\r\n");
        fwrite($fp, "        \$sql->setCriterio(\$criterio);\r\n");
        fwrite($fp, "        // Prepara sql\r\n");
        fwrite($fp, "        \$consulta = \$this->pdo->prepare(\$sql->getInstrucao());\r\n");
        fwrite($fp, "        // Executa sql\r\n");
        fwrite($fp, "        \$consulta->execute();\r\n");
        fwrite($fp, "        // Retorna o Objeto com o resultado da consulta\r\n");
        fwrite($fp, "        return \$consulta;\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function daoHistoricoId($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel inserir historico\r\n");
        fwrite($fp, "     * @access private\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    private function historico({$this->nomeClasse}DTO \${$this->tabelaPropriedade}DTO) { \r\n");
        fwrite($fp, "        // Cria Array para guardar dados do historico\r\n");
        fwrite($fp, "        \$dadosHistorico = array();\r\n");
        fwrite($fp, "        // Seta valores\r\n");
        fwrite($fp, "        \$dadosHistorico['id_usuarios'] = (isset(\$_SESSION['sessao_id_usuarios'])) ? \$_SESSION['sessao_id_usuarios'] : NULL;\r\n");
        fwrite($fp, "        \$dadosHistorico['id_tipo_acao'] = \$this->idTipoAcao;\r\n");
        fwrite($fp, "        \$dadosHistorico['historico'] = \$this->getSql();\r\n");
        fwrite($fp, "        \$dadosHistorico['nome_tabela'] = '{$this->modulo}.{$this->tabela}';\r\n");
        fwrite($fp, "        \$dadosHistorico['id_registro'] = \${$this->tabelaPropriedade}DTO->getId{$this->tabelaMetodo}();\r\n");
        fwrite($fp, "        \$dadosHistorico['nota'] = \$this->nota;\r\n");
        fwrite($fp, "        // Instancia classe HistoricoHistoricoExecuta\r\n");
        fwrite($fp, "        \$historico = new HistoricoHistoricoExecuta(\$this->pdo);\r\n");
        fwrite($fp, "        \$historico->inserir(\$dadosHistorico);\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function daoHistorico($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel inserir historico\r\n");
        fwrite($fp, "     * @access private\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    private function historico({$this->nomeClasse}DTO \${$this->tabelaPropriedade}DTO) { \r\n");
        fwrite($fp, "        // Cria Array para guardar dados do historico\r\n");
        fwrite($fp, "        \$dadosHistorico = array();\r\n");
        fwrite($fp, "        // Seta valores\r\n");
        fwrite($fp, "        \$dadosHistorico['id_usuarios'] = isset(\$_SESSION['sessao_id_usuarios']) ? \$_SESSION['sessao_id_usuarios'] : NULL;\r\n");
        fwrite($fp, "        \$dadosHistorico['id_tipo_acao'] = \$this->idTipoAcao;\r\n");
        fwrite($fp, "        \$dadosHistorico['historico'] = \$this->getSql();\r\n");
        fwrite($fp, "        \$dadosHistorico['nome_tabela'] = '{$this->modulo}.{$this->tabela}';\r\n");
        fwrite($fp, "        \$id = '';\r\n");
        foreach ($this->colunas as $linha) {
            if (isset($linha['primary_key'])) {
                $nomeMetodo = $this->getNomeMetodo($linha['nome_coluna']);
                fwrite($fp, "        \$id .= \"{$linha['nome_coluna']}, {\${$this->tabelaPropriedade}DTO->get{$nomeMetodo}()};\";\r\n");
            }
        }

        fwrite($fp, "        \$dadosHistorico['id_chave_estrangeira'] = \$id;\r\n");
        fwrite($fp, "        \$dadosHistorico['nota'] = \$this->nota;\r\n");
        fwrite($fp, "        // Instancia classe HistoricoHistoricoExecuta\r\n");
        fwrite($fp, "        \$historico = new HistoricoHistoricoExecuta(\$this->pdo);\r\n");
        fwrite($fp, "        \$historico->inserir(\$dadosHistorico);\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function criaExcuta() {
        @unlink("{$this->caminhoDestino}/{$this->nomeClasse}Executa.php");

        $fp = fopen("{$this->caminhoDestino}/{$this->nomeClasse}Executa.php", "a");
        fwrite($fp, "<?php\r\n");
        fwrite($fp, "/**\r\n");
        fwrite($fp, " * Classe responsavel em utilizar Data Access Object para {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, " *\r\n");
        fwrite($fp, " * Versão: {$this->versao}\r\n");
        fwrite($fp, " * Criado Por: {$this->criadoPor}\r\n");
        fwrite($fp, " * Data Criação: {$this->dataCriacao}\r\n");
        fwrite($fp, " */\r\n");
        fwrite($fp, "class {$this->nomeClasse}Executa { \r\n");
        fwrite($fp, "    // Define as propriedades\r\n");
        fwrite($fp, "    private \$pdo;\r\n");
        fwrite($fp, "    private \$criaHistorico;\r\n");
        fwrite($fp, "    private \$sql;\r\n");
        if ($this->chavePrimaria) {
            fwrite($fp, "    private \$ultimoId;\r\n");
        }
        fwrite($fp, "\r\n");

        // ************************** METODO *******************************
        // cria metodo construtor
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Construtor\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function __construct(PDO \$pdo, \$historico = true) { \r\n");
        fwrite($fp, "        \$this->pdo = \$pdo;\r\n");
        fwrite($fp, "        \$this->criaHistorico = \$historico;\r\n");
        fwrite($fp, "    }\r\n\r\n");

        // ************************** METODO *******************************
        // cria metodo para retornar o sql
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Retorna o valor da propriedade sql\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function getSql() { \r\n");
        fwrite($fp, "        return \$this->sql;\r\n");
        fwrite($fp, "    }\r\n\r\n");

        if ($this->chavePrimaria) {
            // ************************** METODO *******************************
            // cria metodo para retornar o ultimo ID
            fwrite($fp, "    /**\r\n");
            fwrite($fp, "     * Retorna o valor da propriedade ultimoId\r\n");
            fwrite($fp, "     * @access public\r\n");
            fwrite($fp, "     */\r\n");
            fwrite($fp, "    public function getUltimoId() { \r\n");
            fwrite($fp, "        return \$this->ultimoId;\r\n");
            fwrite($fp, "    }\r\n\r\n");
        }

        // ************************** METODO *******************************
        // cria metodo para inserir
        $this->executaSalvar($fp);

        // ************************** METODO *******************************
        // cria metodo para inserir
        if ($this->chavePrimaria) {
            $this->executaInserirId($fp);
        } else {
            $this->executaInserir($fp);
        }

        // ************************** METODO *******************************
        // cria metodo para editar
        $this->executaEditarId($fp);

        // ************************** METODO *******************************
        // cria metodo para excluir
        if ($this->chavePrimaria) {
            $this->executaExcluirId($fp);
        } else {
            $this->executaExcluir($fp);
        }

        // ************************** METODO *******************************
        // cria metodo para listar
        $this->executaListar($fp);

        fwrite($fp, "}\r\n");
        fclose($fp);
    }

    private function executaSalvar($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em salvar\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param array \$dados{$this->tabelaMetodo} Dado contendo o nome do campo chave primaria e seu valor\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function salvar(\$dados{$this->tabelaMetodo}) { \r\n");
        fwrite($fp, "        if ( !empty(\$dados{$this->tabelaMetodo}['id_{$this->tabela}']) )\r\n");
        fwrite($fp, "        {\r\n");
        fwrite($fp, "            \$this->editar(\$dados{$this->tabelaMetodo});\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "        else\r\n");
        fwrite($fp, "        {\r\n");
        fwrite($fp, "            \$this->inserir(\$dados{$this->tabelaMetodo});\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function executaInserirId($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em inserir\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param array \$dados{$this->tabelaMetodo} Dados contendo o nome dos campos e seus valores\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function inserir(\$dados{$this->tabelaMetodo}) { \r\n");
        fwrite($fp, "        // Instancia classe Data Transfer Object (DTO) para {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DTO = new {$this->nomeClasse}DTO;\r\n");
        fwrite($fp, "        // Seta valor as propriedades\r\n");
        $i = 0;
        foreach ($this->colunas as $linha) {
            if ($i != 0) {
                $nomeMetodo = $this->getNomeMetodo($linha['nome_coluna']);
                if (isset($linha['primary_key']) || isset($linha['foreign_key'])) {
                    if ($linha['nulo'] == 'nao') {
                        fwrite($fp, "        if ( !empty(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']) )\r\n");
                        fwrite($fp, "        {\r\n");
                        fwrite($fp, "            \${$this->tabelaPropriedade}DTO->set{$nomeMetodo}(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']);\r\n");
                        fwrite($fp, "        }\r\n");

                        if (isset($linha['primary_key'])) {
                            fwrite($fp, "        else\r\n");
                            fwrite($fp, "        {\r\n");
                            fwrite($fp, "            throw new PDOException(\"Chave primaria {$linha['nome_coluna']} nao definida\");\r\n");
                            fwrite($fp, "        }\r\n");
                        } else if (isset($linha['foreign_key'])) {
                            fwrite($fp, "        else\r\n");
                            fwrite($fp, "        {\r\n");
                            fwrite($fp, "            throw new PDOException(\"Chave estrangeira {$linha['nome_coluna']} nao definida\");\r\n");
                            fwrite($fp, "        }\r\n");
                        }
                    } else {
                        fwrite($fp, "        if ( isset(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']) )\r\n");
                        fwrite($fp, "        {\r\n");
                        fwrite($fp, "            \${$this->tabelaPropriedade}DTO->set{$nomeMetodo}(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']);\r\n");
                        fwrite($fp, "        }\r\n");
                    }
                } else {
                    fwrite($fp, "        if ( isset(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']) )\r\n");
                    fwrite($fp, "        {\r\n");
                    fwrite($fp, "            \${$this->tabelaPropriedade}DTO->set{$nomeMetodo}(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']);\r\n");
                    fwrite($fp, "        }\r\n");
                }
            }
            $i++;
        }
        fwrite($fp, "        // Instancia classe Data Access Object (DAO) para {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DAO = new {$this->nomeClasse}DAO(\$this->pdo, \$this->criaHistorico);\r\n");
        fwrite($fp, "        // Chama método responsável em Inserir\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DAO->inserir(\${$this->tabelaPropriedade}DTO);\r\n");
        fwrite($fp, "        // Seta o ultimo id inserido a propriedade id{$this->tabelaMetodo}\r\n");
        fwrite($fp, "        \$this->ultimoId = \${$this->tabelaPropriedade}DTO->getId{$this->tabelaMetodo}();\r\n");
        fwrite($fp, "        // Seta o sql executado a propriedade sql\r\n");
        fwrite($fp, "        \$this->sql = \${$this->tabelaPropriedade}DAO->getSql();\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function executaInserir($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em inserir\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param array \$dados{$this->tabelaMetodo} Dados contendo o nome dos campos e seus valores\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function inserir(\$dados{$this->tabelaMetodo}) { \r\n");
        fwrite($fp, "        // Instancia classe Data Transfer Object (DTO) para {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DTO = new {$this->nomeClasse}DTO;\r\n");
        fwrite($fp, "        // Seta valor as propriedades\r\n");
        foreach ($this->colunas as $linha) {
            $nomeMetodo = $this->getNomeMetodo($linha['nome_coluna']);
            if (isset($linha['primary_key']) || isset($linha['foreign_key'])) {
                if ($linha['nulo'] == 'nao') {
                    fwrite($fp, "        if ( !empty(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']) )\r\n");
                    fwrite($fp, "        {\r\n");
                    fwrite($fp, "            \${$this->tabelaPropriedade}DTO->set{$nomeMetodo}(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']);\r\n");
                    fwrite($fp, "        }\r\n");

                    if (isset($linha['primary_key'])) {
                        fwrite($fp, "        else\r\n");
                        fwrite($fp, "        {\r\n");
                        fwrite($fp, "            throw new PDOException(\"Chave primaria {$linha['nome_coluna']} nao definida\");\r\n");
                        fwrite($fp, "        }\r\n");
                    } else if (isset($linha['foreign_key'])) {
                        fwrite($fp, "        else\r\n");
                        fwrite($fp, "        {\r\n");
                        fwrite($fp, "            throw new PDOException(\"Chave estrangeira {$linha['nome_coluna']} nao definida\");\r\n");
                        fwrite($fp, "        }\r\n");
                    }
                } else {
                    fwrite($fp, "        if ( isset(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']) )\r\n");
                    fwrite($fp, "        {\r\n");
                    fwrite($fp, "            \${$this->tabelaPropriedade}DTO->set{$nomeMetodo}(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']);\r\n");
                    fwrite($fp, "        }\r\n");
                }
            } else {
                fwrite($fp, "        if ( isset(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']) )\r\n");
                fwrite($fp, "        {\r\n");
                fwrite($fp, "            \${$this->tabelaPropriedade}DTO->set{$nomeMetodo}(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']);\r\n");
                fwrite($fp, "        }\r\n");
            }
        }
        fwrite($fp, "        // Instancia classe Data Access Object (DAO) para {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DAO = new {$this->nomeClasse}DAO(\$this->pdo, \$this->criaHistorico);\r\n");
        fwrite($fp, "        // Chama método responsável em Inserir\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DAO->inserir(\${$this->tabelaPropriedade}DTO);\r\n");
        fwrite($fp, "        // Seta o sql executado a propriedade sql\r\n");
        fwrite($fp, "        \$this->sql = \${$this->tabelaPropriedade}DAO->getSql();\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function executaEditarId($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em editar\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param array \$dados{$this->tabelaMetodo} Dados contendo o nome dos campos e seus valores\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function editar(\$dados{$this->tabelaMetodo}) { \r\n");
        fwrite($fp, "        // Instancia classe Data Transfer Object (DTO) para {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DTO = new {$this->nomeClasse}DTO;\r\n");
        fwrite($fp, "        // Seta valor as propriedades\r\n");
        foreach ($this->colunas as $linha) {
            $nomeMetodo = $this->getNomeMetodo($linha['nome_coluna']);

            if ((isset($linha['primary_key']) || isset($linha['foreign_key'])) && ($linha['nulo'] == 'nao')) {
                fwrite($fp, "        if ( !empty(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']) )\r\n");
                fwrite($fp, "        {\r\n");
                fwrite($fp, "            \${$this->tabelaPropriedade}DTO->set{$nomeMetodo}(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']);\r\n");
                fwrite($fp, "        }\r\n");
            } else {
                fwrite($fp, "        if ( isset(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']) )\r\n");
                fwrite($fp, "        {\r\n");
                fwrite($fp, "            \${$this->tabelaPropriedade}DTO->set{$nomeMetodo}(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']);\r\n");
                fwrite($fp, "        }\r\n");
            }
            if (isset($linha['primary_key'])) {
                fwrite($fp, "        else\r\n");
                fwrite($fp, "        {\r\n");
                fwrite($fp, "            throw new PDOException(\"Chave primaria {$linha['nome_coluna']} nao definida\");\r\n");
                fwrite($fp, "        }\r\n");
            }
        }
        fwrite($fp, "        // Instancia classe Data Access Object (DAO) para {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DAO = new {$this->nomeClasse}DAO(\$this->pdo, \$this->criaHistorico);\r\n");
        fwrite($fp, "        // Chama método responsável em editar\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DAO->editar(\${$this->tabelaPropriedade}DTO);\r\n");
        fwrite($fp, "        // Seta o sql executado a propriedade sql\r\n");
        fwrite($fp, "        \$this->sql = \${$this->tabelaPropriedade}DAO->getSql();\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function executaExcluirId($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em excluir\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param array \$dados{$this->tabelaMetodo} Dado contendo o nome do campo chave primaria e seu valor\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function excluir(\$dados{$this->tabelaMetodo}) { \r\n");
        fwrite($fp, "        // Instancia classe Data Transfer Object (DTO) para {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DTO = new {$this->nomeClasse}DTO;\r\n");
        fwrite($fp, "        // Seta valor a propriedade chave primaria\r\n");
        fwrite($fp, "        if ( !empty(\$dados{$this->tabelaMetodo}['id_{$this->tabela}']) )\r\n");
        fwrite($fp, "        {\r\n");
        $nomeMetodo = $this->getNomeMetodo("id_{$this->tabela}");
        fwrite($fp, "            \${$this->tabelaPropriedade}DTO->set{$nomeMetodo}(\$dados{$this->tabelaMetodo}['id_{$this->tabela}']);\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "        else\r\n");
        fwrite($fp, "        {\r\n");
        fwrite($fp, "            throw new PDOException(\"Chave primaria id_{$this->tabela} nao definida\");\r\n");
        fwrite($fp, "        }\r\n");
        fwrite($fp, "        // Instancia classe Data Access Object (DAO) para {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DAO = new {$this->nomeClasse}DAO(\$this->pdo, \$this->criaHistorico);\r\n");
        fwrite($fp, "        // Chama método responsável em excluir\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DAO->excluir(\${$this->tabelaPropriedade}DTO);\r\n");
        fwrite($fp, "        // Seta o sql executado a propriedade sql\r\n");
        fwrite($fp, "        \$this->sql = \${$this->tabelaPropriedade}DAO->getSql();\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function executaExcluir($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em excluir\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param array \$dados{$this->tabelaMetodo} Dado contendo o nome do campo chave primaria e seu valor\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function excluir(\$dados{$this->tabelaMetodo}) { \r\n");
        fwrite($fp, "        // Instancia classe Data Transfer Object (DTO) para {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DTO = new {$this->nomeClasse}DTO;\r\n");
        fwrite($fp, "        // Seta valor a(s) propriedade(s) chave primaria(s)\r\n");

        foreach ($this->colunas as $linha) {
            if (isset($linha['primary_key'])) {
                fwrite($fp, "        if ( !empty(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']) )\r\n");
                fwrite($fp, "        {\r\n");
                $nomeMetodo = $this->getNomeMetodo($linha['nome_coluna']);
                fwrite($fp, "            \${$this->tabelaPropriedade}DTO->set{$nomeMetodo}(\$dados{$this->tabelaMetodo}['{$linha['nome_coluna']}']);\r\n");
                fwrite($fp, "        }\r\n");
                fwrite($fp, "        else\r\n");
                fwrite($fp, "        {\r\n");
                fwrite($fp, "            throw new PDOException(\"Chave primaria {$linha['nome_coluna']} nao definida\");\r\n");
                fwrite($fp, "        }\r\n");
            }
        }

        fwrite($fp, "        // Instancia classe Data Access Object (DAO) para {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DAO = new {$this->nomeClasse}DAO(\$this->pdo, \$this->criaHistorico);\r\n");
        fwrite($fp, "        // Chama método responsável em excluir\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DAO->excluir(\${$this->tabelaPropriedade}DTO);\r\n");
        fwrite($fp, "        // Seta o sql executado a propriedade sql\r\n");
        fwrite($fp, "        \$this->sql = \${$this->tabelaPropriedade}DAO->getSql();\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function executaListar($fp) {
        fwrite($fp, "    /**\r\n");
        fwrite($fp, "     * Método Responsavel em listar\r\n");
        fwrite($fp, "     * @access public\r\n");
        fwrite($fp, "     * @param array \$campos Array contendo o nome dos campos\r\n");
        fwrite($fp, "     * @param array \$criterios Array contendo o(s) criterio(s) para listagem\r\n");
        fwrite($fp, "     * @param array \$propriedade Array contendo o nome das propriedades \r\n");
        fwrite($fp, "     * @return objeto Retorna o Objeto com o resultado da consulta\r\n");
        fwrite($fp, "     */\r\n");
        fwrite($fp, "    public function listar(\$campos = null, \$criterios = null, \$propriedade = null) { \r\n");
        fwrite($fp, "        // Instancia classe Data Access Object (DAO) para {$this->modulo}.{$this->tabela}\r\n");
        fwrite($fp, "        \${$this->tabelaPropriedade}DAO = new {$this->nomeClasse}DAO(\$this->pdo, \$this->criaHistorico);\r\n");
        fwrite($fp, "        // Chama método responsável em listar\r\n");
        fwrite($fp, "        return \${$this->tabelaPropriedade}DAO->listar(\$campos, \$criterios, \$propriedade);\r\n");
        fwrite($fp, "    }\r\n\r\n");
    }

    private function getNomePropriedade($string) {
        $camposEmPartes = explode("_", $string);
        $i = 0;
        $nomePropriedade = '';
        foreach ($camposEmPartes as $parte) {
            if ($i != 0) {
                $nomePropriedade .= ucfirst($parte);
            } else {
                $nomePropriedade = $parte;
            }
            $i++;
        }

        return $nomePropriedade;
    }

    private function getNomeMetodo($string) {
        $camposEmPartes = explode("_", $string);
        $campoNomeMetodo = str_replace(' ', '', ucwords(implode(" ", $camposEmPartes)));
        return $campoNomeMetodo;
    }

}

$modulo = (isset($_GET['modulo'])) ? $_GET['modulo'] : NULL;
$tabela = (isset($_GET['tabela'])) ? $_GET['tabela'] : NULL;

// ***************** Cria Por Tabela **********************
$dao = new CriaDO();
if ($modulo && $tabela) {
    $dao->cria($modulo, $tabela);
} else {
    echo "Devem ser passados os parametros por GET (modulo e tabela).<br/>Exemplo: ?modulo=MODULO&tabela=TABELA&pasta=CAMINHO<br/>Obs.: &pasta (caminho para destino dos arquivos) é opcional por padrão é \"teste\" ";
}
// ***************** Cria Por Tabela **********************