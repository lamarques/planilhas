<?php
/**
 * Created by PhpStorm.
 * User: rogerio.lamarques
 * Date: 14/07/2016
 * Time: 16:10
 */
require_once __DIR__ . "/autoload.php";

$sessao = new Sessao();

if (!$sessao->verificaSessao()) {
    $sessao->setCliete();
    header('location: login.php');
    exit();
}

try {
    $pdo = Conexao::open('sistema');

    $sessaoFuncionario = $sessao->getValor('sessao_funcionario');
    $smarty->assign('funcionario', $sessaoFuncionario);
} catch (PDOException $e) {
    print_r($e);
}

$smarty->display('index.tpl');