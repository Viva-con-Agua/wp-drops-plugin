<?php
/*
Plugin Name: Drops Wordpress Plugin
Plugin URI: https://github.com/Viva-con-Agua/wp-sluice-plugin
Description: Plugin to handle the authentification of the user
Version: 1.0
Author: Tobias Kaestle
Author URI: https://www.vivaconagua.org
*/
require_once 'src/api/server/DropsSessionController.class.php';
require_once 'src/api/server/DropsUserController.class.php';

require_once 'src/api/DropsResponse.class.php';
require_once 'src/api/DropsUserCreator.class.php';
require_once 'src/api/DropsUserUpdater.class.php';
require_once 'src/api/DropsConnector.class.php';

require_once 'src/DropsSessionDataHandler.class.php';
require_once 'src/DropsUserDataHandler.class.php';

require_once 'src/DropsLogger.class.php';

// Handling login of an existing user
function handleDropsLogin() {

    if (!is_user_logged_in()) {
        DropsSessionController::run();
    } else {
        $dataHandler = new DropsSessionDataHandler();
        if ($dataHandler->isSessionExpired(get_current_user_id())) {
            wp_logout();
            wp_redirect(get_home_url());
            die();
        }
    }

}

// Handling creation of a new user
function handleDropsUserCreation() {
    DropsUserController::run();
}

// Handling update of an existing user
function handleUserUpdate($userId) {
    $dataHandler = new DropsUserDataHandler();
    $userUpdater = new DropsUserUpdater();
    $userUpdater->setDataHandler($dataHandler);
    $response = $userUpdater->run($userId);
    DropsController::logResponse($response);
}

// Handling logout of an user
function handleUserLogout() {
    $userId = get_current_user_id();
    $dataHandler = new DropsSessionDataHandler();
    $dataHandler->clearSessions($userId);
}

add_action('parse_request', 'handleDropsUserCreation');
add_action('parse_request', 'handleDropsLogin' );
add_action('profile_update', 'handleUserUpdate', 10, 1);
add_action('wp_logout', 'handleUserLogout');

?>