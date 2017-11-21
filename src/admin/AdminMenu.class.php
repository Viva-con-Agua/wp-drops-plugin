<?php

require_once 'pages/DropsMain.class.php';

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 16.11.2017
 * Time: 00:50
 */
class AdminMenu
{

    public function __construct()
    {

        /* Home */
        add_menu_page(
            __( 'Drops', 'drops' ),
            __( 'Drops', 'drops' ),
            'read',
            'drops',
            array( new DropsMain(), 'home' ),
            DROPSPATH . 'src/admin/public/img/drops.png',
            101
        );

    }

}