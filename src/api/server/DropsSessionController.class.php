<?php

require_once 'DropsController.class.php';

/**
 * Class DropsResponse
 */
class DropsSessionController extends DropsController
{

    /** A call to this path will initialize the drops login routine */
    const INITIAL = '/autologin/';

    /** After the drops login has succeeded, the user has to be redireted to this path */
    const LOGIN = '/userlogin/';

    /** The user gets redirected to this path after getting the authorization code to get the access token */
    const ACCESS = '/useraccess/';

    /**
     * The routine checks which path is called and calls the corresponding function
     */
    public function run()
    {

        $drops = (new DropsLoginHandler())
            ->setSessionDataHandler(new DropsSessionDataHandler())
            ->setMetaDataHandler(new DropsMetaDataHandler());

        $url = $this->getParsedUrl();

        switch ($url['path']) {
            case self::LOGIN:
                $sessionId = $drops->handleLoginResponse($_GET);
                $drops->handleAuthorizationCodeResponse(array_merge($_GET, array('sessionId' => $sessionId)));
                break;

            case self::INITIAL:
                $drops->handleLoginRedirect();
                break;
        }

    }

}