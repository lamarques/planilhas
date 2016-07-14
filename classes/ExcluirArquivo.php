<?php

/**
 * Class responsavel em limpar diretorio ou excluir arquivo unico
 * @author BCS Automação
 */
class ExcluirArquivo {

    /**
     * Método responsavel em apagar arquivos de um diretorio e pode apagar o proprio.
     *
     * @param string $diretorio caminho do diretorio
     * @param bool $apagar define se apaga o diretorio ou nao
     * @return <type>
     */
    public function diretorio($diretorio, $apagar = false) {
        if (is_dir($diretorio . '/')) {
            if (!$dh = opendir($diretorio)) {
                return;
            }
            while (($obj = readdir($dh))) {
                if ($obj == '.' || $obj == '..') {
                    continue;
                }
                if (is_file($diretorio . '/' . $obj)) {
                    unlink($diretorio . '/' . $obj);
                } else if (is_dir($diretorio . '/' . $obj)) {
                    $this->diretorio($diretorio . '/' . $obj, $apagar);
                }
            }
            if ($apagar === TRUE) {
                closedir($dh);
                rmdir($diretorio);
            }
        }
    }

    /**
     * Método responsavel em apagar um unico arquivo
     * @param string $arquivo caminho e nome do arquivo
     */
    public function arquivo($arquivo) {
        if (is_file($arquivo)) {
            unlink($arquivo);
        }
    }

}

?>