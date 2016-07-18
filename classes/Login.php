<?php

namespace Classes;

use Classes\Conexao;
use Classes\TrataDados;
use Classes\Sessao;
use Classes\Excecao;

/**
 * Class Login{}
 * Responsavel em controlar o login da area administrativa
 */
class Login
{

    /**
     * Verifica se usuario e senha estao corretos
     *
     * @return json Ira conter informaçoes do que fazer
     */
    public function loginAdmin()
    {
        $retorno = array();
        $session = new Sessao();
        $session->limpaValoresSessao();
        $session->setCliete();
        $trataDados = new TrataDados();

        if (isset($_GET['origem_externa']) && $_GET['origem_externa'] == "sim") {
            $_POST['usuario'] = $_GET['usuario'];
            $_POST['senha'] = $_GET['senha'];
        }

        if ($trataDados->pegaDados()) {
            $arrDados = $trataDados->getDados();
            try {
                $sql = "SELECT *
                        FROM  cadastros.funcionarios
                        WHERE usuario = :usuario AND  senha = :senha AND  ativo = true";
                $pdo = Conexao::open('sistema');
                $consulta = $pdo->prepare($sql);
                $consulta->bindParam(':usuario', $arrDados['usuario'], \PDO::PARAM_STR);
                $senhaCript = md5($arrDados['senha']);
                $consulta->bindParam(':senha', $senhaCript, \PDO::PARAM_STR);
                $consulta->execute();
                $numeroRegistros = $consulta->rowCount();
                $linha = $consulta->fetch(\PDO::FETCH_ASSOC);
                if ($numeroRegistros == 1) {
                    $session->setValor('sessao_usuario', $linha['usuario']);
                    $session->setValor('sessao_id_funcionarios', $linha['id_funcionarios']);
                    $session->setValor('sessao_funcionario', $linha);
                    $session->setValor('sessao_ativa', 'sistema_planilhas_6511');
                    $retorno['resultado'] = 'sim';
                    if (isset($_GET['origem_externa']) && $_GET['origem_externa'] == "sim") {
                        header("Location: index.php");
                    }
                } else if ($this->inicializarBanco()) {
                    $session->setValor('sessao_id_usuario', 1);
                    $retorno['resultado'] = 'inserir';
                } else {
                    $retorno['resultado'] = 'nao';
                }
            } catch (\PDOException $e) {
                if ($e->getCode() == '3F000') {
                    $retorno['resultado'] = 'criar_tabela';
                } else {
                    new Excecao($e);
                    $retorno['resultado'] = 'erro';
                    $retorno['erro'] = 'consulta';
                }
            }
        } else {
            $retorno['resultado'] = 'dados_errados';
            $retorno['dados_errados'] = $trataDados->getErro();
        }
        return json_encode($retorno);
    }

    /**
     * Destruira a sessao
     *
     * @return json Ira retornar 'Sim' caso tenha dado tudo certo
     */
    public function logoffAdmin()
    {
        try {
            $retorno = array();
            $session = new Sessao();
            $session->limpaSessao();
            $retorno['resultado'] = 'sim';
        } catch (\PDOException $e) {
            new Excecao($e);
        }
        return json_encode($retorno);
    }

    /**
     * Verifica se banco esta limpo
     *
     * @return bool
     */
    private function inicializarBanco()
    {
        try {
            $pdo = Conexao::open('sistema');
            $consulta = $pdo->prepare('SELECT COUNT(*) AS numero_registros FROM cadastros.usuarios');
            $consulta->execute();
            $linha = $consulta->fetch(\PDO::FETCH_ASSOC);
            if ($linha['numero_registros'] == 0) {
                return true;
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            new Excecao($e);
        }
    }

    public function loginHotel()
    {
        $usuario = (empty($_POST['usuario'])) ? null : addslashes($_POST['usuario']);
        $senha = (empty($_POST['senha'])) ? null : addslashes($_POST['senha']);
        $retorno = array();
        $trataDados = new TrataDados();
        if ($trataDados->pegaDados()) {
            $dados = $trataDados->getDados();
            try {
                $sql = 'SELECT hotel.id_hotel
                        FROM hospedagem.hotel
                        JOIN cadastros.empresa_participacao ON empresa_participacao.id_empresa_participacao = hotel.id_empresa_participacao
                        JOIN cadastros.edicao ON edicao.id_edicao = empresa_participacao.id_edicao AND edicao.ativo = true
                        WHERE hotel.usuario = :usuario AND hotel.senha	= :senha;';
                $pdo = Conexao::open('sistema');
                $consulta = $pdo->prepare($sql);
                $consulta->bindParam(':usuario', $dados['usuario'], \PDO::PARAM_STR);
                $consulta->bindParam(':senha', $dados['senha'], \PDO::PARAM_STR);
                $consulta->execute();
                $numeroRegistros = $consulta->rowCount();
                $linha = $consulta->fetch(\PDO::FETCH_ASSOC);
                $session = new Sessao();

                if ($numeroRegistros == 1) {
                    $session->setValor('sessao_id_hotel', $linha['id_hotel']);
                    $session->setValor('sessao_ativa', 'sistema_hotel_7893');

                    $retorno['resultado'] = 'sim';
                } else {
                    $retorno['resultado'] = 'nao';
                }
            } catch (\PDOException $e) {
                new Excecao($e);
                $retorno['resultado'] = 'erro';
                $retorno['erro'] = 'consulta';
            }
        } else {
            $retorno['resultado'] = 'dados_errados';
            $retorno['dados_errados'] = $trataDados->getErro();
        }
        return json_encode($retorno);
    }

    private function gravaLogUsuario($pdo, $idUsuario, $tipo, $arrDados = false)
    {
        if (empty($idUsuario)) {
            return false;
        }

        if($tipo == 'entrou' && $arrDados) {
            $arrDados['resolucao_width'] = $arrDados['width'];
            $arrDados['resolucao_height'] = $arrDados['height'];
            $arrDados['navegador'] = $_SERVER['HTTP_USER_AGENT'];
            $arrDados['ip_cliente'] = $_SERVER['REMOTE_ADDR'];
            $arrDados['nome_pc'] = gethostname();
        }

        $arrDados['id_usuario'] = $idUsuario;
        $arrDados[$tipo] = 'true';
        $objLog = new HistoricoLogAcessoUsuarioExecuta($pdo, false);
        $objLog->inserir($arrDados);
    }

    public function listaLogUsuario($pdo, $idUsuario, $quantidade = 10)
    {
        try {
            $sql = "select
                        to_char(dt_cadastro, 'dd/mm/YYYY HH24:mi:ss') as data,
                        entrou,
                        saiu,
                        ip_cliente
                    from
                        historico.log_acesso_usuario
                    where
                        id_usuario = :id_usuario
                    order by
                        id_log_acesso_usuario
                    desc
                        limit :quantidade";
            $consulta = $pdo->prepare($sql);
            $consulta->bindParam(":id_usuario", $idUsuario, \PDO::PARAM_INT);
            $consulta->bindParam(":quantidade", $quantidade, \PDO::PARAM_INT);
            $consulta->execute();
            $dados = $consulta->fetchAll(\PDO::FETCH_ASSOC);
            return $dados;
        } catch (\PDOException $e) {
            new Excecao($e);
        }
    }

    private function gerarChaveSessao($arrDados, $pdo)
    {
        $pdo->exec("UPDATE 
                        cadastros.usuario 
                    SET 
                        chave = md5('{$arrDados['usuario']} ' || now()) 
                    WHERE 
                        id_usuario = {$arrDados['id_usuario']}");

        $objCadastrosUsuario = new CadastrosUsuarioExecuta($pdo);
        $consulta = $objCadastrosUsuario->listar(array('chave'), array(array('id_usuario', '=', $arrDados['id_usuario'])));
        $retorno = $consulta->fetch(\PDO::FETCH_ASSOC);

        return $retorno['chave'];
    }
}