<?php
/**
 * Created by PhpStorm.
 * User: rogerio.lamarques
 * Date: 14/07/2016
 * Time: 17:39
 */
set_include_path(implode(PATH_SEPARATOR, array(get_include_path(), __DIR__ )));
spl_autoload_extensions(".php");
spl_autoload_register();

$sessao = new \Classes\Sessao();

if (!$sessao->verificaSessao()) {
    $sessao->setCliete();
    header('location: login.php');
    exit();
}

require_once __DIR__ . '/smarty/libs/Smarty.class.php';

$smarty = new Smarty();
$smarty->compile_check = true;
$smarty->caching = false;
$smarty->cache_lifetime = 0;
$smarty->debugging = false;
$smarty->template_dir = "templates/";
$smarty->compile_dir = __DIR__ . "/smarty/templates_c/";
$smarty->config_dir = __DIR__ . "/smarty/configs/";
$smarty->cache_dir = __DIR__ . "/smarty/cache/";

$menusPermissao = array(
    0 => array(
        'name' => 'Funcionários',
        'url'  => 'cadastro/funcionarios.php',
        'permisoes' => array(
            'escrita' => array(
                999999
            ),
            'leitura' => array(
                999999
            ),
        ),
        'exibir' => true
    ),
);

$menu = new \Classes\Menu();
$sessao_funcionario = $sessao->getValor('sessao_funcionario');
$menu->setPermissao($sessao_funcionario['permissao']);
$menu->setMenus($menusPermissao);
$menu->geraMenu();
$smarty->assign('menu', $menu->getMenu());
$smarty->assign('filesPermissao', $menusPermissao);

$sessaoFuncionario = $sessao->getValor('sessao_funcionario');
$smarty->assign('sessao_funcionario', $sessaoFuncionario);