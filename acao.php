<?php
/**
 * Created by PhpStorm.
 * User: rogerio.lamarques
 * Date: 15/07/2016
 * Time: 17:33
 */

require_once __DIR__ . "/autoload.php";

use Classes\Conexao;
try {
    /**
     * @var PDO $pdo
     */
    $pdo = Conexao::open('sistema');
    $acao = filter_input(INPUT_POST, 'acao');

    switch ($acao){
        case 'ExcluirPadrao':
            $id = filter_input(INPUT_POST, 'id_form');
            $tabela = filter_input(INPUT_POST, 'form');
            $tb = explode('.', $tabela);
            $campo = 'id_' . $tb[1];
            $definitivo = filter_input(INPUT_POST, 'definitivo');

            if($definitivo == 'false') {
                $sql = "UPDATE {$tabela} SET ativo = 'F' WHERE {$campo} = {$id}";
            } else {
                $sql = "DELETE FROM {$tabela} WHERE {$campo} = {$id}";
            }
            try{
                $consulta = $pdo->prepare($sql);
                $consulta->execute();
                echo json_encode(array(
                    'resultado' => 'sim'
                ));
            } catch (\Exception $e){
                echo json_encode(array(
                    'resultado' => 'erro',
                    'erro' => $e->getMessage()
                ));
            }
            break;
        default:
            echo "Setar a ação";
    }

} catch (\PDOException $e) {
    new \Classes\Excecao($e);
}