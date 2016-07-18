<?php
/**
 * Created by PhpStorm.
 * User: rogerio.lamarques
 * Date: 15/07/2016
 * Time: 14:46
 */

namespace Classes;


class Excecao
{

    public function __construct($e)
    {
        echo "<pre>";
        print_r($e);
        echo "</pre>";
    }

}