<?php
/**
 * Created by PhpStorm.
 * User: rogerio.lamarques
 * Date: 14/07/2016
 * Time: 17:39
 */

function __autoload($classe) {
    $pastas = array('classes', 'classes/do');
    foreach ($pastas AS $pasta) {
        if (file_exists(__DIR__ . "/{$pasta}/{$classe}.php")) {
            include_once __DIR__ . "/{$pasta}/{$classe}.php";
        }
    }
}

require_once __DIR__ . '/smarty/libs/Smarty.class.php';

$smarty = new Smarty();
$smarty->compile_check = true;
$smarty->caching = false;
$smarty->cache_lifetime = 0;
$smarty->debugging = false;
$skin = isset($_SESSION['sessao_skin']) && !empty($_SESSION['sessao_skin']) ? $_SESSION['sessao_skin'] : "";
$smarty->template_dir = "templates{$skin}/";
$smarty->compile_dir = "smarty/templates_c/";
$smarty->config_dir = "smarty/configs/";
$smarty->cache_dir = "smarty/cache/";