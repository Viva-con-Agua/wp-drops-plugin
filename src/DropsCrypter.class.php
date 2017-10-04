<?php

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 29.09.2017
 * Time: 15:56
 */
class DropsCrypter
{

    private static $instance;

    private function __construct()
    {
    }

    public static function getCrypter()
    {

        if (empty(self::$instance)) {
            self::$instance = new DropsCrypter();
        }
        return self::$instance;

    }

    public function encrypt($data)
    {
    }

    public function decrypt($data)
    {
    }

}