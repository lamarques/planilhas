<?php
/**
 * Created by PhpStorm.
 * User: rogerio.lamarques
 * Date: 14/07/2016
 * Time: 16:10
 */
require_once __DIR__ . "/autoload.php";

try {
    $pdo = Conexao::open('sistema');
} catch (PDOException $e) {
    print_r($e);
}

$smarty->display('login.tpl');