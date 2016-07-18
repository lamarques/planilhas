<?php

namespace Classes;

class Menu {

    private $menus = array();
    private $permissao = 0;
    private $menu;

    /**
     * @return array
     */
    public function getMenus()
    {
        return $this->menus;
    }

    /**
     * @param array $menus
     */
    public function setMenus($menus)
    {
        $this->menus = $menus;
    }

    /**
     * @return int
     */
    public function getPermissao()
    {
        return $this->permissao;
    }

    /**
     * @param int $permissao
     */
    public function setPermissao($permissao)
    {
        $this->permissao = $permissao;
    }


    public function geraMenu(){
        foreach ($this->getMenus() as $menu){
            $leitura = $menu['permisoes']['leitura'];
            $escrita = $menu['permisoes']['escrita'];
            if($menu['exibir'] === true && (in_array($this->permissao, $leitura) || in_array($this->permissao, $escrita))){
                $this->menu .= "<li>
                        <a href=\"javascript: Load('{$menu['url']}', 'conteudo');\">
                            <span>{$menu['name']}</span>
                        </a>
                    </li>";
            }
        }
    }

    public function getMenu(){
        return $this->menu;
    }

}