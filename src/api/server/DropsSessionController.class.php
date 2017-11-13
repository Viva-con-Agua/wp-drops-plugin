<?php

require_once 'DropsController.class.php';

/**
 * Class DropsResponse
 */
class DropsSessionController extends DropsController
{

    const INITIAL = '/autologin/';
    const LOGIN = '/userlogin/';
    const ACCESS = '/useraccess/';

    public function run()
    {
        $this->handleLogin();
    }

    private function handleLogin()
    {

        $drops = new DropsConnector();
        $drops->setSessionDataHander(new DropsSessionDataHandler());

        $url = $this->getParsedUrl();

        // TODO REMOVE THIS; THIS IS ONLY FOR TESTING THE ACCESS TOKEN REQUEST
        if ($url['path'] == '/access_url/') {
            echo $drops->fakeCall();
            exit;
        }

        switch ($url['path']) {
            case self::LOGIN:
                $drops->handleLoginResponse($_GET);
                break;

            case self::ACCESS:
                $drops->handleAuthorizationCodeResponse($_GET);
                break;

            case self::INITIAL:
                $drops->handleLoginRedirect();
            break;
        }

    }

}