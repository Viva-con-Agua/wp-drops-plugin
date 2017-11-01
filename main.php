<?php
/*
Plugin Name: Drops Wordpress Plugin
Plugin URI: https://github.com/Viva-con-Agua/wp-sluice-plugin
Description: Plugin to handle the authentification of the user
Version: 1.0
Author: Tobias Kaestle
Author URI: https://www.vivaconagua.org
*/
require_once('src/api/DropsUserCreator.class.php');
require_once('src/api/DropsUserUpdater.class.php');
require_once('src/DropsDataHandler.class.php');
require_once ('src/DropsConnector.class.php');

// Handling login of an existing user
function handleDropsLogin() {

    if (!is_user_logged_in()) {

        $dataHandler = new DropsDataHandler();
        $drops = new DropsConnector();
        $drops->setDataHandler($dataHandler);

        $url = getParsedUrl();

        // TODO REMOVE THIS; THIS IS ONLY FOR TESTING THE ACCESS TOKEN REQUEST
        if ($url['path'] == '/access_url/') {
            echo $drops->fakeCall();
            die();
        }

        if ($url['path'] == '/useraccess/') {
            $drops->handleAuthorizationCodeResponse($_GET);
            die();
        } else if ($url['path'] == '/userlogin/') {
            $drops->handleLoginResponse($_GET);
        } else {
            $drops->handleLoginRedirect();
        }

    }

}

// Handling creation of a new user
function handleDropsUserCreation() {

    $url = getParsedUrl();

    if ($url['path'] !== '/usercreate/') {
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        wp_redirect(get_home_url());
        die();
    }

    if ($_POST['hash'] !== Config::get('USER_ACCESS_HASH')) {
        wp_redirect(get_home_url());
        die();
    }

    $fakeUserData = array(
        'ID' => 55123,
        'user_login' => 'tobichka',
        'user_nicename' => 'tobi-chka',
        'user_email' => 'tobi@chka.de',
        'user_name' => 'Tobias Chkastle',
        'usermeta' => array(
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
        )
    );

    $dataHandler = new DropsDataHandler();

    $userReceiver = new DropsUserCreator($fakeUserData);
    $userReceiver->setDataHandler($dataHandler);
    $response = $userReceiver->run();

    echo json_encode($response);
    die();

}

// Handling update of an existing user
function handleUserUpdate($userId) {
    $userUpdater = new DropsUserUpdater();
    $userUpdater->run($userId);
}

// Handling logout of an user
function handleUserLogout($userId) {
    $userId = get_current_user_id();
    $dataHandler = new DropsDataHandler();
    $dataHandler->clearSessions($userId);
}

function getParsedUrl() {
    $actualLink = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    return parse_url($actualLink);
}

add_action('parse_request', 'handleDropsUserCreation');
add_action('parse_request', 'handleDropsLogin' );
add_action('profile_update', 'handleUserUpdate', 10, 1);
add_action('wp_logout', 'handleUserLogout');

?>