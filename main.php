<?php
/*
Plugin Name: Drops Wordpress Plugin
Plugin URI: https://github.com/Viva-con-Agua/wp-sluice-plugin
Description: Plugin to handle the authentification of the user
Version: 1.0
Author: Tobias Kaestle
Author URI: https://www.vivaconagua.org
*/
require_once('src/api/UserReceiver.class.php');
require_once('src/DropsDataHandler.class.php');
require_once ('src/DropsConnector.class.php');


function handleDropsConnection() {

    $fakeUserData = array(
        'ID' => 55123,
        'user_login' => 'tobichka',
        'user_nicename' => 'tobi-chka',
        'user_email' => 'tobi@chka.de',
        'display_name' => 'Tobias Chkastle'
    );

    $fakeUserMetaData = array(
        'nickname' => 'Tobichi Kachi',
        'first_name' => 'Tobias',
        'last_name' => 'Chkastle',
        'mobile' => '017670199782',
        'residence' => 'Hamburg',
        'birthday' => '585352800',
        'gender' => 'male',
        'nation' => '40',
        'city' => '1',
        'region' => '1'
    );

    $fakeUserData = array_merge($fakeUserData, $fakeUserMetaData);

    if (!is_user_logged_in()) {

        $dataHandler = new DropsDataHandler();
        $drops = new DropsConnector();
        $drops->setDataHandler($dataHandler);

        $params = $_GET;

        if (!isset($_GET['fnc'])) {
            $function = '';
        } else {
            $function = $_GET['fnc'];
        }

        if ($function == 'insertUser') {
            $userReceiver = new UserReceiver($fakeUserData);
            $userReceiver->setDataHandler($dataHandler);
            $userReceiver->handleRequest();
        }

        if ($function == 'receive_ac') {
            $drops->handleAuthorizationCodeResponse($params);
            die();
        } else if ($function == 'resignin') {
            $drops->handleLoginResponse($params);
        } else {
            $drops->handleLoginRedirect();
        }

    }

}
add_action( 'parse_request', 'handleDropsConnection' );


?>