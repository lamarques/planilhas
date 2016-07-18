<?php
/**
 * Created by PhpStorm.
 * User: rogerio.lamarques
 * Date: 15/07/2016
 * Time: 17:58
 */

require_once __DIR__ . "/../autoload.php";

use Classes\Conexao;
use Classes\Orm\CadastrosFuncionariosExecuta;
try {
    $pdo = Conexao::open('sistema');
    $funcionarios = new CadastrosFuncionariosExecuta($pdo);
    $id_funcionarios = filter_input(INPUT_GET, 'id_funcionarios');

    if(!empty($id_funcionarios)){
        $consulta = $funcionarios->listar(NULL, array(array('id_funcionarios', '=', $id_funcionarios)));
        $smarty->assign('funcionario', $consulta->fetch(\PDO::FETCH_ASSOC));
    } else {
        $smarty->assign('funcionario', array());
    }
} catch (\PDOException $e) {
    new \Classes\Excecao($e);
}

$smarty->display('cadastro/cadastro_funcionario.tpl');