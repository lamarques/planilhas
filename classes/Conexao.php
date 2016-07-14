<?php
final class Conexao {

    private static $coon = array();
    private static $instance = array();

    private function __construct() {
        
    }

    /**
     * @param string $banco
     * @return PDO
     */
    public static function open($banco) {
      
        if (!isset(self::$instance[$banco])) {

            $variavel = explode(".", $_SERVER['SERVER_NAME']);
            $objParametros = new ConexaoParametros();
            $parametros = $objParametros->{$variavel[0]}();

            self::$instance[$banco] = true;
            $db = array();
            switch ($banco) {
                case 'sistema' :
                    $db['user'] = $parametros['user_db'];
                    $db['pass'] = $parametros['pass'];
                    $db['name'] = (empty($parametros['name'])) ? $parametros['user_db'] : $parametros['name'];
                    $db['host'] = (empty($parametros['host'])) ? 'localhost' : $parametros['host'];
                    $db['port'] = (empty($parametros['port'])) ? '5432' : $parametros['port'];
                    $db['type'] = 'pgsql';
                    break;
                case 'cep' :
                    $db['user'] = 'root';
                    $db['pass'] = '';
                    $db['name'] = 'cep';
                    $db['host'] = 'localhost';
                    $db['type'] = 'mysql';
                    break;
                case 'cepSqlite' :
                    $db['name'] = '../banco/cep.db';
                    $db['type'] = 'sqlite';
                    break;
                default :
                    throw new PDOException("Banco n&atilde;o encontrado");
            }
            $user = isset($db['user']) ? $db['user'] : NULL;
            $pass = isset($db['pass']) ? $db['pass'] : NULL;
            $name = isset($db['name']) ? $db['name'] : NULL;
            $host = isset($db['host']) ? $db['host'] : NULL;
            $type = isset($db['type']) ? $db['type'] : NULL;
            $port = isset($db['port']) ? $db['port'] : NULL;
            switch ($type) {
                case 'mdb' :
                    self::$coon[$banco] = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb)}; Dbq={$name}; Uid={$user}");
                    break;
                case 'pgsql' :
                    $port = $port ? $port : '5432';
                    self::$coon[$banco] = new PDO("pgsql:dbname={$name}; user={$user}; password={$pass}; host=$host; port={$port}");
                    break;
                case 'mysql' :
                    $port = $port ? $port : '3306';
                    self::$coon[$banco] = new PDO("mysql:host={$host}; port={$port}; dbname={$name}", $user, $pass);//, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                    break;
                case 'sqlite' :
                    self::$coon[$banco] = new PDO("sqlite:{$name}");
                    break;
                case 'ibase' :
                    self::$coon[$banco] = new PDO("firebird:dbname={$name}", $user, $pass);
                    break;
                case 'oci8' :
                    self::$coon[$banco] = new PDO("oci:dbname={$name}", $user, $pass);
                    break;
                case 'mssql' :
                    self::$coon[$banco] = new PDO("mssql:host={$host},1433; dbname={$name}", $user, $pass);
                    break;
            }
            self::$coon[$banco]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$coon[$banco];
    }

    public function __destruct() {
        self::$instance = null;
    }

}
