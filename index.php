<?php
/*
Plugin Name: Drops Wordpress Plugin
Plugin URI: https://github.com/Viva-con-Agua/wp-sluice-plugin
Description: Plugin to handle the authentification of the user
Version: 1.0
Author: Tobias Kaestle
Author URI: https://www.vivaconagua.org
*/

if ( ! defined( 'DROPSPATH' ) ) {
    define( 'DROPSPATH', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'PLUGINROOTFILE' ) ) {
    define( 'PLUGINROOTFILE', __FILE__);
}

if ( ! defined( 'DROPSHOME' ) ) {
    define( 'DROPSHOME', dirname(__FILE__));
}

require_once DROPSHOME .'/install.php';

require_once 'src/api/server/DropsSessionController.class.php';
require_once 'src/api/server/DropsUserController.class.php';

require_once 'src/api/DropsResponse.class.php';
require_once 'src/api/DropsUserCreator.class.php';
require_once 'src/api/actions/DropsUserReader.class.php';
require_once 'src/api/actions/DropsUserUpdater.class.php';
require_once 'src/api/actions/DropsUserImageUpdater.class.php';
require_once 'src/api/actions/DropsUserProfileReader.class.php';
require_once 'src/api/actions/DropsUserDeleter.class.php';
require_once 'src/api/DropsLoginHandler.class.php';
//require_once 'src/api/DropsLogoutProcess.class.php';
//require_once 'src/api/DropsLogoutHandler.class.php';

require_once 'src/DropsSessionDataHandler.class.php';
require_once 'src/DropsUserDataHandler.class.php';
require_once 'src/DropsMetaDataHandler.class.php';

require_once 'src/DropsLogger.class.php';

require_once 'src/api/client/restclient.php';

if (is_admin()) {
    require_once 'src/admin/AdminMenu.class.php';
}

// Handling login of an existing user
function handleDropsLogin() {

    if (!is_user_logged_in()) {
        (new DropsSessionController)->run();
    } else {
        $dataHandler = new DropsSessionDataHandler();

        if ($dataHandler->isSessionExpired(get_current_user_id())) {

            $dataHandler->clearSessionsByUserId(get_current_user_id());

            do_action('wp_logout');
            wp_redirect(get_home_url());

            die("2");
            exit;

        }

        if ($dataHandler->hasSession(get_current_user_id())) {

            $dataHandler->clearSessionsByUserId(get_current_user_id());

            do_action('wp_logout');
            wp_redirect(get_home_url());

            die("43");
            exit;

        }

    }

}

// Handling login of an existing user
/*function handleNatsLogout() {

    if (is_user_logged_in()) {
        $dataHandler = new DropsSessionDataHandler();
        (new DropsLogoutHandler())->setSessionDataHandler($dataHandler)->handleProcessing();
    }

}*/

// Handling creation of a new user
function handleDropsUserCreation() {
    (new DropsUserController())->run();
}

// Handling update of an existing user
function handleUserUpdate($userId) {
    $dataHandler = new DropsUserDataHandler();
    $userUpdater = new DropsUserUpdater();
    $userImageUpdater = new DropsUserImageUpdater();

    // Update the user itself
    $response = $userUpdater->setAccessToken(
        (new DropsSessionDataHandler())
            ->getAccessToken(
                get_current_user_id()
            )
    )->setDataHandler($dataHandler)->run($userId);

    DropsController::logResponse($response);

    // Update the user image
    $response = $userImageUpdater->setAccessToken(
        (new DropsSessionDataHandler())
            ->getAccessToken(
                get_current_user_id()
            )
    )->setDataHandler($dataHandler)->run($userId);

    DropsController::logResponse($response);
}

// Handling the deletion of an user
function handleUserDelete($userId) {
    $dataHandler = new DropsUserDataHandler();
    $userDeleter = new DropsUserDeleter();

    $response = $userDeleter->setAccessToken(
        (new DropsSessionDataHandler())
            ->getAccessToken(
                get_current_user_id()
            )
    )->setDataHandler($dataHandler)
    ->run($userId);

    DropsController::logResponse($response);
}

// Handling logout of an user
function handleUserLogout() {
    $userId = get_current_user_id();
    $dataHandler = new DropsSessionDataHandler();
    $dataHandler->clearSessionsByUserId($userId);
}

function createAdminMenu() {
    if (is_admin()) {
        new AdminMenu();
    }
}

//add_action('parse_request', 'handleNatsLogout');
add_action('parse_request', 'handleDropsUserCreation');

if (Config::get('LOGIN_ENABLED')) {
    add_action('parse_request', 'handleDropsLogin');
}

add_action('admin_menu', 'createAdminMenu' );
add_action('profile_update', 'handleUserUpdate', 10, 1);
add_action('delete_user', 'handleUserDelete', 10, 1);
add_action('wp_logout', 'handleUserLogout');

?>