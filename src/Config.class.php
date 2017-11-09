<?php

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 29.09.2017
 * Time: 15:56
 */
class Config
{

    private static $instance;

    private function __construct()
    {
        require_once dirname( __FILE__ ) . '\..\config.inc.php';
        $this->_config = $_CONFIG;
    }

    public static function get($key)
    {

        if (empty(self::$instance)) {
            self::$instance = new Config();
        }

        return self::$instance->getConfigEntry($key);

    }

    private function getConfigEntry($key)
    {

        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }

        throw new Exception('Key ' . $key . ' not found in config file');

    }


}