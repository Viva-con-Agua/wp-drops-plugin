<?php
/*
Plugin Name: Drops Wordpress Plugin
Plugin URI: https://github.com/Viva-con-Agua/wp-sluice-plugin
Description: Plugin to handle the authentification of the user
Version: 1.0
Author: Tobias Kaestle
Author URI: https://www.vivaconagua.org
*/
require_once('src/DropsDataHandler.class.php');
require_once ('src/DropsConnector.class.php');


function handleDropsConnection() {

    if (!is_user_logged_in()) {

        $dataHandler = new DropsDataHandler();
        $drops = new DropsConnector();
        $drops->setDataHandler($dataHandler);

        $params = $_GET;

        if (!isset($_GET['fnc'])) {
            $_GET['fnc'] = '';
        }

        if ($_GET['fnc'] == 'receive_ac') {
            $drops->handleAuthorizationCodeResponse($params);
            die();
        } else if ($_GET['fnc'] == 'resignin') {
            $drops->handleLoginResponse($params);
        } else {
            $drops->handleLoginRedirect();
        }

    }

}
print_r(hash_algos());
die();
add_action( 'parse_request', 'handleDropsConnection' );


?>