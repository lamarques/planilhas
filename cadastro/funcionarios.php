<?php
/**
 * Created by PhpStorm.
 * User: rogerio.lamarques
 * Date: 15/07/2016
 * Time: 16:01
 */

require_once __DIR__ . "/../autoload.php";

use Classes\Conexao;
use Classes\Orm\CadastrosFuncionariosExecuta;
try {
    $pdo = Conexao::open('sistema');
    $funcionarios = new CadastrosFuncionariosExecuta($pdo);
    $consulta = $funcionarios->listar(NULL, array(array('ativo', '=', 'T')));

    $smarty->assign('funcionarios', $consulta->fetchAll(\PDO::FETCH_ASSOC));
} catch (\PDOException $e) {
    new \Classes\Excecao($e);
}

$smarty->display('cadastro/funcionarios.tpl');