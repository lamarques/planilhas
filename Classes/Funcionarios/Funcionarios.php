<?php
/**
 * Created by PhpStorm.
 * User: rogerio.lamarques
 * Date: 18/07/2016
 * Time: 13:34
 */

namespace Classes\Funcionarios;

use Classes\Orm\CadastrosFuncionariosExecuta;

class Funcionarios
{

    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return array
     */
    public function salvar()
    {
        try {
            $funcionario = new CadastrosFuncionariosExecuta($this->pdo);
            $id_funcionarios = filter_input(INPUT_POST, 'id_funcionarios');
            $dados = array(
                "matricula" => filter_input(INPUT_POST, 'matricula'),
                "nome" => filter_input(INPUT_POST, 'matricula'),
                "siglanomemeio" => filter_input(INPUT_POST, 'matricula'),
                "sobrenome" => filter_input(INPUT_POST, 'matricula'),
                "usuario" => filter_input(INPUT_POST, 'matricula'),
                "senha" => filter_input(INPUT_POST, 'matricula'),
                "email" => filter_input(INPUT_POST, 'matricula'),
                "id_clientes" => 1
            );

            if (!empty($dados['senha'])) {
                $dados['senha'] = md5($dados['senha']);
            }

            if (!empty($id_funcionarios)) {
                $dados['id_funcionarios'] = $id_funcionarios;
            }
            $funcionario->salvar($dados);
            $resposta['resultado'] = 'sim';
        } catch (\PDOException $e) {
            $resposta['resultado'] = 'erro';
            $resposta['erro'] = $e->getMessage();
        }
        return $resposta;
    }

}