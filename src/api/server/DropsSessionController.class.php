<?php

require_once 'DropsController.class.php';

/**
 * Class DropsResponse
 */
class DropsSessionController extends DropsController
{

    const DROPSFNC = 'loginFnc';

    /** A call to this path will initialize the drops login routine */
    const INITIAL = 'autologin';
	
    /** A call to this path will initialize the drops login routine */
    const REDIRECT = 'login_redirect';

    /** The user gets redirected to this path after getting the authorization code to get the access token */
    const ACCESS = 'useraccess';

    /**
     * The routine checks which path is called and calls the corresponding function
     */
    public function run()
    {

		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Arrived at drops');
	
        $drops = (new DropsLoginHandler())
            ->setSessionDataHandler(new DropsSessionDataHandler())
            ->setMetaDataHandler(new DropsMetaDataHandler());

		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Sessiondatahandler created');
        $parameter = $drops->getParameter(self::DROPSFNC, $_GET);

        if (empty($parameter)) {
            $parameter = self::INITIAL;
        }

		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Parameter set to ' . $parameter);
        $url = $this->getParsedUrl();
        if (isset($url['path']) && (stristr('wp-admin', $url['path']) || stristr('rausloggen', $url['path']))) {
            return;
        }

        switch ($parameter) {
            case self::ACCESS:
                $sessionId = $drops->handleLoginResponse($_GET);
                $drops->handleAuthorizationCodeResponse(array_merge($_GET, array('sessionId' => $sessionId)));
                break;
				
            case self::REDIRECT:
                $drops->handleFrontendLoginResponse();
				break;
			
            case self::INITIAL:
            default:
				(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Initial call triggered');
                $drops->handleFrontendLoginRedirect();
                break;
        }

    }

}