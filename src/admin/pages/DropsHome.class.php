<?php

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 16.11.2017
 * Time: 00:50
 */
class DropsHome
{

    public function __construct()
    {
        $this->defineTemplateVariables();
    }

    private function defineTemplateVariables()
    {
    }

    public function render()
    {
        include 'templates/dropsHome.php';
    }

}