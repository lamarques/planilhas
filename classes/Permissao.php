<?php
/**
 * Class Permissão
 * Responsavel em verificar se usuario tem permissão sobre determinado arquivo
 * 
 * @version 7.0 11/11/2014 - Tiago Volz
 */
class Permissao {
    
    private $id_usuarios;
    private $permissoes;
    private $url;
    private $pdo;
    private $resultadoBloqueio;
    private $dadosUsuario;

    public function __construct(PDO $pdo ) {
        $this->permissoes = array();
        $this->pdo = $pdo ;
        $this->buscaDadosUsuario();
    }

    public function setIdUsuarios($id_usuarios) {
        $this->id_usuarios = $id_usuarios;
    }
    
    public function setUrl($url) {
        $this->url = $url;
    }
    
    public function getTodasPermissoesDoUsuario(){
        try {
            $sql = "SELECT *
                    FROM modulos.permissoes
                    JOIN modulos.modulo ON modulo.id_modulo = permissoes.id_modulo
                    WHERE 
                    modulo.ativo IS TRUE
                    AND 
                    permissoes.id_usuarios = :idUsuario";
            $consulta = $this->pdo->prepare($sql);
            $consulta->bindParam(':idUsuario', $this->id_usuarios, PDO::PARAM_INT);
            $consulta->execute();
            $_SESSION['permissoes'] = $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            new Excecao($e);
        }
    }

    public function verifica($url) {
        $this->url = $url;
    }

    /**
     * Ira fazer a verificação dos usuarios que tem permissao sobre determinado arquivo.
     *
     * @param string $url caminho do arquivo
     * @param string $permissao permissao do usuario sobre a $url
     */
    public function verificaUsuariosPermissao($url,$permissao) {
        try {
            $sql = "SELECT usuario.id_usuario, usuario.nome, usuario.email
                    FROM configuracao.permissao
                    JOIN configuracao.menu_arquivo_permissao ON menu_arquivo_permissao.id_permissao = permissao.id_permissao
                    JOIN configuracao.permissao_usuario ON permissao_usuario.id_menu_arquivo_permissao = menu_arquivo_permissao.id_menu_arquivo_permissao
                    JOIN configuracao.menu_arquivo ON menu_arquivo.id_menu_arquivo = menu_arquivo_permissao.id_menu_arquivo
                    JOIN cadastros.usuario ON usuario.id_usuario = permissao_usuario.id_usuario
                    WHERE
                        usuario.ativo = 't' AND
                        menu_arquivo.pagina = :url AND
                        permissao.permissao = :permissao
                    ORDER BY usuario.nome ASC ";
            $consulta = $this->pdo->prepare($sql);
            $consulta->bindParam(':url', $url, PDO::PARAM_STR);
            $consulta->bindParam(':permissao', $permissao, PDO::PARAM_STR);
            $consulta->execute(); 

            $i = 0;
            $this->permissoes = "";
            while ($permissao = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $this->permissoes[$i]['id_usuario'] = $permissao['id_usuario'];
                $this->permissoes[$i]['nome'] = $permissao['nome'];
                $this->permissoes[$i]['email'] = $permissao['email'];
                $i++;
            }
            return $this->permissoes;
        } catch(PDOException $e) {
            new Excecao($e);
        }
    }

    /**
     * Responsavel em retornar se existe ou nao uma permissão
     *
     * @param string $permissao nome da permissão
     * @return bool ira retornar verdadeiro ou falso
     */
    public function getPermissao($permissao = false) {
        if ($permissao) {
            return (isset($_SESSION['permissao'][$this->url][$permissao])) ? true : false ;
        } else {
            return (empty($_SESSION['permissao'][$this->url])) ? false : true ;
        }
    }
    
    public function getPermissaoPasta(){
        try {
            $sql = "SELECT distinct menu_arquivo.id_menu_pasta
                    FROM configuracao.permissao_usuario
                    JOIN configuracao.menu_arquivo_permissao ON menu_arquivo_permissao.id_menu_arquivo_permissao = permissao_usuario.id_menu_arquivo_permissao
                    JOIN configuracao.menu_arquivo ON menu_arquivo.id_menu_arquivo = menu_arquivo_permissao.id_menu_arquivo
                    WHERE permissao_usuario.id_usuario = :idUsuario ";
            $consulta = $this->pdo->prepare($sql); 
            $consulta->bindParam(':idUsuario', $this->id_usuario, PDO::PARAM_INT);
            $consulta->execute();
            $permissao = $consulta->fetchAll(PDO::FETCH_ASSOC);
            return $permissao;
        } catch(PDOException $e) {
            new Excecao($e);
        }
    }

    private function buscaDadosUsuario(){
        $sql = "SELECT * FROM cadastros.usuarios WHERE id_usuarios = {$_SESSION['sessao_id_usuarios']}";
        $this->dadosUsuario = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getResultadoBloqueio(){
        return $this->resultadoBloqueio;
    }
    
    public function __destruct() {
        $this->pdo = null;
    }
}
