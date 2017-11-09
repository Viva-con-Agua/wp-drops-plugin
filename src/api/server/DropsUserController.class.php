<?php

require_once 'DropsController.class.php';

/**
 * Class DropsResponse
 */
class DropsUserController extends DropsController
{

    const CREATE = '/usercreate/';

    public static function run()
    {
        self::handleUserCreation();
    }

    private static function handleUserCreation()
    {

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            return;
        }

        $url = self::getParsedUrl();

        switch ($url['path']) {
            case self::CREATE:

                if ($_POST['hash'] !== Config::get('USER_ACCESS_HASH')) {
                    return;
                }

                $userData = self::getFakeUserData();

                $dataHandler = new DropsUserDataHandler();
                $userReceiver = new DropsUserCreator($userData);
                $userReceiver->setDataHandler($dataHandler);
                $response = $userReceiver->run();

                self::logResponse($response);

                echo $response->getFormat(DropsResponse::JSON);
                exit;

                break;
            default:
                break;
        }

    }

    private static function getFakeUserData()
    {
        return array(
            'user_login' => 'tobichka',
            'user_nicename' => 'tobi-chka',
            'user_email' => 'tobi@chka.de',
            'display_name' => 'Tobi Ka',
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

    }

}