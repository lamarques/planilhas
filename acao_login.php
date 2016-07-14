<?php
/**
 * Created by PhpStorm.
 * User: rogerio.lamarques
 * Date: 14/07/2016
 * Time: 18:04
 */

require_once __DIR__ . "/autoload.php";

if ( isset( $_GET['origem_externa'] ) )
    $_POST = $_GET;

$opcao = empty( $_GET['acao'] ) ? empty( $_POST['acao'] ) ? null : $_POST['acao']  : $_GET['acao'];

if ( empty( $opcao ) ) {
    echo "erro";
    exit();
}


$retorno = "";
switch ( $opcao ) {
    case "LoginAdmin" :
        $login = new Login();
        $retorno = $login->loginAdmin();
        break;
    case 'LogoffAdmin' :
        $login = new Login();
        $retorno = $login->logoffAdmin();
        break;
    default :
        $retorno = "nao";
        break;
}

if ( !isset( $_GET['origem_externa'] ) )
    echo $retorno;