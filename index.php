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
require_once 'src/api/server/DropsDataMapper.class.php';

require_once 'src/api/DropsResponse.class.php';
require_once 'src/api/actions/DropsUserReader.class.php';
require_once 'src/api/actions/DropsUserActionUpdater.class.php';
require_once 'src/api/actions/DropsUserImageUpdater.class.php';
require_once 'src/api/actions/DropsUserProfileReader.class.php';
require_once 'src/api/actions/DropsUserDeleter.class.php';
require_once 'src/api/DropsLoginHandler.class.php';

require_once 'src/DropsSessionDataHandler.class.php';
require_once 'src/DropsUserDataHandler.class.php';
require_once 'src/DropsMetaDataHandler.class.php';
require_once 'src/DropsGeographyDataHandler.class.php';

require_once 'src/DropsLogger.class.php';

require_once 'src/api/client/restclient.php';

if (is_admin()) {
    require_once 'src/admin/AdminMenu.class.php';
}

// Handling login of an existing user
function handleDropsLogin() {

    if (!is_user_logged_in() && false) {
        (new DropsSessionController)->run();
    } else {
        $dataHandler = new DropsSessionDataHandler();

        if ($dataHandler->isSessionExpired(get_current_user_id()) || true) {

            $dataHandler->clearSessionsByUserId(get_current_user_id());

			// get all sessions for user with ID $user_id
            $sessions = WP_Session_Tokens::get_instance(get_current_user_id());

            // we have got the sessions, destroy them all!
            $sessions->destroy_all();

			//$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			
            wp_redirect('');
            //wp_redirect($url);

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

	
function allowProgrammaticLogin( $user, $username, $password ) {
	return get_user_by( 'login', $username );
}

// Handling update of an existing user
function handleUserUpdate($userId) {
    $dataHandler = new DropsUserDataHandler();
    $userUpdater = new DropsUserActionUpdater();
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
	wp_redirect(get_option( 'dropsLogoutUrl' ));
	die('User is logged out!');
}

function handleAPIRequest() {
	require_once 'src/api/server/DropsAPIController.class.php';
	(new DropsAPIController)->run();
}

function createAdminMenu() {
    if (is_admin()) {
        new AdminMenu();
    }
}

//add_action('parse_request', 'handleNatsLogout');

if (Config::get('LOGIN_ENABLED')) {
    add_action('parse_request', 'handleDropsLogin');
}

add_action('parse_request', 'handleAPIRequest');
add_action('admin_menu', 'createAdminMenu' );
add_action('profile_update', 'handleUserUpdate', 10, 1);
add_action('delete_user', 'handleUserDelete', 10, 1);
add_action('wp_logout', 'handleUserLogout');

?>