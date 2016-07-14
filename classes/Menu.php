<?php

class Menu {

    private $menu;
    private $id_usuario;

    /**
     * Define para qual usuario devera ser construido o menu.
     *
     * @param int $id_usuario id do usuario
     */
    public function setIdUsuario($id_usuario) {
        $this->id_usuario = $id_usuario;
    }

    /**
     * Responsavel em dar inicio a montagem do menu.
     *
     * @return string Ira retornar uma string do menu
     */
    public function montaMenu() {
        try {
            $pdo = Conexao::open('sistema');

            $sql = "
                SELECT  *
                FROM modulos.menus
                JOIN modulos.modulo ON modulo.id_menus = menus.id_menus
                JOIN modulos.permissoes on permissoes.id_modulo = modulo.id_modulo
                WHERE (permissoes.ler IS TRUE OR permissoes.editar IS TRUE OR permissoes.excluir IS TRUE OR permissoes.criar IS TRUE)
                AND permissoes.id_usuarios = :id_usuarios
            ";
            $consulta = $pdo->prepare($sql);
            $consulta->bindParam(':id_usuarios', $this->id_usuario, PDO::PARAM_INT);
            $consulta->execute();
            $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $menuAtual = '';
            $pasta = "";
            foreach ($resultado as $menu) {
                if($menuAtual != $menu['nome_menu']){
                    $pasta .= (!empty($pasta)) ? "</ul></li>" : "";
                    $pasta .= "<li class='treeview'>
                                  <a href='javascript: Void(0);'>
                                    <i class='fa fa-dashboard'></i> <span>{$menu['nome_menu']}</span> <i class='fa fa-angle-left pull-right'></i>
                                  </a>
                                  <ul class='treeview-menu'>";
                    $menuAtual = $menu['nome_menu'];
                }

                $pasta .= "<li><a href=\"javascript: Load('" . htmlspecialchars($menu['caminho']) . "','conteudo');\"><i class=\"fa fa-circle-o\"></i> {$menu['nome_modulo']}</a></li>";

            }
            $pasta .= (!empty($pasta)) ? "</ul></li>":"";
            $this->menu = $pasta;
            unset($pdo);
        } catch (PDOException $e) {
            echo "<div class=\"content-wrapper\" id=\"erros\"><section><pre>";
            print_r($e);
            echo "</pre></section></div>";
            exit;
        }
    }

    public function getMenu(){
        return $this->menu;
    }

}