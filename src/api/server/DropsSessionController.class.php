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
	
		$sessionDataHandler = new DropsSessionDataHandler();
	
        $drops = (new DropsLoginHandler())
            ->setSessionDataHandler($sessionDataHandler)
            ->setMetaDataHandler(new DropsMetaDataHandler());

        $parameter = $drops->getParameter(self::DROPSFNC, $_GET);

        if (empty($parameter)) {
            $parameter = self::INITIAL;
        }

        $url = $this->getParsedUrl();
        if (isset($url['path']) && (stristr('wp-admin', $url['path']) || stristr('rausloggen', $url['path']))) {
            return;
        }
				
		// If there is no temporary session with the id, redirect to the login process
        $sessionId = $this->getParameter('sessionId', $_GET);
		
		if (empty($sessionId)) {
			$sessionId = $drops->getPool1Cookie();
		}
		
        $temporarySession = $sessionDataHandler->getTemporarySession($sessionId);

        if (!empty($temporarySession)) {
			
			switch ($parameter) {
				case self::INITIAL:
					$parameter = self::REDIRECT;
				case self::ACCESS:
					/*if (!empty($temporarySession['drops_session_id'])) {
						(new DropsLogger(''))->log(DropsLogger::DEBUG, 'DropsID already there: ' . $temporarySession['drops_session_id'] . ' (Line ' . __LINE__ . ')');
						$drops->redirect($temporarySession['user_session']['url']);
					}*/
					break;
				
			}
			
		}

        switch ($parameter) {
            case self::ACCESS:
                $sessionId = $drops->handleLoginResponse($_GET);
                $drops->handleAuthorizationCodeResponse(array_merge($_GET, array('sessionId' => $sessionId)));
                break;
				
            case self::REDIRECT:
                $drops->handleFrontendLoginResponse($sessionId);
				break;
			
            case self::INITIAL:
            default:
                $drops->handleFrontendLoginRedirect();
                break;
        }

    }

}