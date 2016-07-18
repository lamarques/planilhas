<?php
/**
 * Created by PhpStorm.
 * User: rogerio.lamarques
 * Date: 18/07/2016
 * Time: 12:08
 */
require_once __DIR__ . "/../autoload.php";

use Classes\Conexao;
use Classes\Funcionarios\Funcionarios;

try{

    $pdo = Conexao::open('sistema');
    $acao = filter_input(INPUT_POST, 'acao');

    switch ($acao){
        case 'cadastrafuncionario':
            $funcionarios = new Funcionarios($pdo);
            echo json_encode($funcionarios->salvar());
            break;
        default:
            echo "Setar a ação";
    }

} catch (\PDOException $e) {
    new \Classes\Excecao($e);
}
