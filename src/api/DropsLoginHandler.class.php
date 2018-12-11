<?php

require_once DROPSHOME . '/src/api/client/restclient.php';
require_once DROPSHOME . '/src/api/server/user/DropsUserUpdater.class.php';

/**
 * Class DropsConnector
 */
class DropsLoginHandler
{

    /**
     * @var SessionDataHandlerInterface $sessionDataHandler
     */
    private $sessionDataHandler;

    /**
     * @var DropsMetaDataHandler $metaDataHandler
     */
    private $metaDataHandler;

    /**
     * Initializing function on users first visit on the page
     * We store the current called URL in a session and persist it, afterwards the user is redirected to the drops login page
     * We can call this the first step on the login process
     */
    public function handleFrontendLoginResponse($sessionId)
    {
		
		// Redirect to drops
        $url = str_replace('<temporarySessionId>', $sessionId, get_option('dropsLoginUrl'));
        $url = str_replace('<clientId>', get_option('dropsClientId'), $url);
		
        $this->redirect($url);

    }
	
    /**
     * Initializing function on users first visit on the page
     * We store the current called URL in a session and persist it, afterwards the user is redirected to the drops login page
     * We can call this the first step on the login process
     */
    public function handleFrontendLoginRedirect()
    {
				
		// If there is no temporary session with the id, redirect to the login process
        $sessionId = $this->getParameter('sessionId', $_GET);
		
		if (empty($sessionId)) {
			$sessionId = $this->getPool1Cookie();
		}
		
		if (empty($sessionId)) {
			
			(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Entered handleFrontendLoginRedirect (Line ' . __LINE__ . ')');
			$currentUrl = $this->getCurrentUrl();

			(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Current called URL is ' . $currentUrl . ' (Line ' . __LINE__ . ')');

			// We have to create a temporary session
			$session = $this->createTemporarySession($currentUrl);
			
			(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Session created and destroyed with id: ' . $session['id'] . ' (Line ' . __LINE__ . ')');

			// Store the current URL to it and redirect it to the login page
			$isPersisted = $this->sessionDataHandler->persistTemporarySession($session);
			
			if ($isPersisted === false) {
				(new DropsLogger(''))->log(DropsLogger::WARNING, 'Session could not be persisted, error: ' . $this->sessionDataHandler->getError(). ' (Line ' . __LINE__ . ')');
			} else {
				(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Session persisted before frontend login redirect: ' . $session['id'] . ' (Line ' . __LINE__ . ')');
			}
			
			$sessionId = $session['id'];
			
		}

        // Redirect to drops
        $url = str_replace('<temporarySessionId>', $sessionId, get_option('dropsFrontendLoginUrl'));
        $url = str_replace('<clientId>', get_option('dropsClientId'), $url);
		
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Will frontend login redirect to URL: ' . $url. ' (Line ' . __LINE__ . ')');
        $this->redirect($url);

    }

    /**
     * Handles the redirect from the drops service, after the user has logged in there
     * We have to read the URL parameters and get the session id created by drops
     * So we store the session id and the corresponding user_id in the database and have to redirect to drops to get
     * the authorization code
     * We can call this the second step
     *
     * @param array $params
     * @return string
     */
    public function handleLoginResponse($params)
    {
		
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Started handleLoginResponse (Line ' . __LINE__ . ')');

        // If there is no temporary session with the id, redirect to the login process
        $sessionId = $this->getParameter('sessionId', $params);
		
		if (empty($sessionId)) {
			$sessionId = $this->getPool1Cookie();
		}
		
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'SessionId from Parameters: ' . $sessionId . ' (Line ' . __LINE__ . ')');
        $temporarySession = $this->sessionDataHandler->getTemporarySession($sessionId);

        if (empty($temporarySession)) {
			(new DropsLogger(''))->log(DropsLogger::DEBUG, 'No temporary session found will create und with url: ' . get_site_url() . '/' . ' (Line ' . __LINE__ . ')');
            $session = $this->createTemporarySession(get_site_url() . '/');
			(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Temporary session created with id: ' . $session['id'] . ' (Line ' . __LINE__ . ')');
            $this->sessionDataHandler->persistTemporarySession($session);
            $sessionId = $session['id'];
        }

        return $sessionId;

    }

    /**
     * Handles the redirect from the drops service, after the user got his authorization code
     * We have to connect again to drops, to get the access token. This time we will directly call it without redirecting
     * the client to the service
     *
     * @param array $params
     */
    public function handleAuthorizationCodeResponse($params)
    {

        // Create parameters needed to request the access token
        $authorizationCode = $this->getParameter('authorizationCode', $params);

		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Will request access_token with authorizationCode: ' . $authorizationCode . ' (Line ' . __LINE__ . ')');
		
        $parameters = [
            'grant_type'    => 'authorization_code',
            'client_id'     => get_option('dropsClientId'),
            'client_secret' => get_option('dropsClientSecret'),
            'code'          => $authorizationCode,
            'redirect_uri'  => 'https://vca.informatik.hu-berlin.de/pool/?loginFnc=useraccess&authorizationCode='
        ];

        // Trigger request
        $response = $this->requestAccessToken($parameters);

        if (empty($response)) {
            (new DropsLogger(''))->log(DropsLogger::INFO, 'Empty response, will restart routine (Line ' . __LINE__ . ')');
            $this->handleFrontendLoginRedirect();
        }

        $sessionId = $this->getParameter('sessionId', $params);
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Will look for temporary session with id: ' . $sessionId . ' (Line ' . __LINE__ . ')');
        $temporarySession = $this->sessionDataHandler->getTemporarySession($sessionId);

        if (empty($temporarySession)) {
            (new DropsLogger(''))->log(DropsLogger::INFO, 'Empty session, will restart routine (Line ' . __LINE__ . ')');
            $this->handleFrontendLoginRedirect();
        } 
		
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Will persist token request userdata with access_token: ' . $response['access_token'] . ' (Line ' . __LINE__ . ')');
        $this->sessionDataHandler->persistAccessToken($sessionId, $response);
        $userDataResponse = (new DropsUserProfileReader())->setAccessToken($response['access_token'])->run(1);

        DropsController::logResponse($userDataResponse);

        if ($userDataResponse->getCode() != 200) {
            (new DropsLogger(''))->log(DropsLogger::INFO, 'Empty session, will restart routine (Line ' . __LINE__ . ')');
            $this->handleFrontendLoginRedirect();
        }

        $userData = $userDataResponse->getResponse();
        $userEmail = $userData->profiles[0]->email;
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Got user from drops profile request with email: ' . $userEmail . ' (Line ' . __LINE__ . ')');

        // Check if user really exists
        $userDataHandler = new DropsUserDataHandler();
        $user = $userDataHandler->getUserByEMail($userEmail);

        if (empty($user)) {
			(new DropsLogger(''))->log(DropsLogger::INFO, 'No user found with email: ' . $userEmail . ' (Line ' . __LINE__ . ')');
            $this->handleFrontendLoginRedirect();
        }
		
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Will login user id ' . $user->ID . ' (Line ' . __LINE__ . ')');
        //$this->loginUser($user->ID);
        $isSignedOn = $this->signonUser($user->user_login);
		
		if (!$isSignedOn) {
			(new DropsLogger(''))->log(DropsLogger::DEBUG, 'User is NOT signed on! ' . $user->user_login . ' (Line ' . __LINE__ . ')');
		} else {
			(new DropsLogger(''))->log(DropsLogger::DEBUG, 'User is logged in now with id ' . $user->ID . ' (Line ' . __LINE__ . ')');
		}
				
        $this->sessionDataHandler->persistDropsSessionId($sessionId, $userData->id);
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'User got drops id ' . $userData->id . ' (Line ' . __LINE__ . ')');
        $this->sessionDataHandler->persistUserId($sessionId, $user->ID);

		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Persisted session: ' . $sessionId . ' (Line ' . __LINE__ . ')');
        if (isset($this->metaDataHandler)) {
			(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Will add metadata (Line ' . __LINE__ . ')');
            $this->metaDataHandler->addMetaData();
			(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Metadata added (Line ' . __LINE__ . ')');
        }

		$this->updateUserCapabilities($userData);
		
        $url = $temporarySession['user_session']['url'];
		
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Will clear cookie: ' . $url . ' (Line ' . __LINE__ . ')');
		$this->clearPool1Cookie();
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Will redirect to url: ' . $url . ' (Line ' . __LINE__ . ')');
        $this->redirect($url);

    }

    /**
     * Setter for the datahandler
     * @param SessionDataHandlerInterface $sessionDataHandler
     * @return $this
     */
    public function setSessionDataHandler(SessionDataHandlerInterface $sessionDataHandler)
    {
        $this->sessionDataHandler = $sessionDataHandler;
        return $this;
    }

    /**
     * Setter for the datahandler
     * @param DropsMetaDataHandler $metaDataHandler
     * @return $this
     */
    public function setMetaDataHandler(DropsMetaDataHandler $metaDataHandler)
    {
        $this->metaDataHandler = $metaDataHandler;
        return $this;
    }

    /**
     * Creates HTTP request and receives the access token
     * @param $parameters
     * @return array|null
     */
    private function requestAccessToken($parameters)
    {

        $options = array(
            'parameters' => $parameters
        );

        $restClient = new RestClient($options);
        $response = $restClient->get(get_option('dropsAccessUrl'));

        if ($response->info->http_code == 200) {
            return json_decode($response->response, true);
        } else {
            (new DropsLogger(''))->log(DropsLogger::ERROR, '(' . $response->info->http_code . ' ' . $response->error . ') URL: ' . get_option('dropsAccessUrl'). ' (Line ' . __LINE__ . ')'. ' (Line ' . __LINE__ . ')');
            return null;
        }

    }

    /**
     * Gets a parameter out of an array
     *
     * @param string $id Index of the searched params
     * @param array $params Array of params
     * @return mixed
     */
    public function getParameter($id, $params)
    {
        if (!isset($params[$id])) {
            return null;
        }

        return $params[$id];
    }

    /**
     * Creates a temporary session and stores the current url in it
     *
     * @param string $url
     * @return array
     */
    private function clearPool1Cookie()
    {

		$cookieData = print_r($_COOKIE, true);
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Current cookie data to check: ' . $cookieData . ' (Line ' . __LINE__ . ')');
		
		if (isset($_COOKIE['pool1'])) {
			unset($_COOKIE['pool1']);
			setcookie('pool1', '', time() - 3600, '/'); // empty value and old timestamp
		}

        return false;

    }

    /**
     * Creates a temporary session and stores the current url in it
     *
     * @param string $url
     * @return array
     */
    public function getPool1Cookie()
    {

		$cookieData = print_r($_COOKIE, true);
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Current cookie data to check: ' . $cookieData . ' (Line ' . __LINE__ . ')');
		
		if (isset($_COOKIE['pool1'])) {
			return $_COOKIE['pool1'];
		}

        return false;

    }

    /**
     * Creates a temporary session and stores the current url in it
     *
     * @param string $url
     * @return array
     */
    private function createTemporarySession($url)
    {

        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
            session_unset();
        }

        session_start([
			'name' => 'pool1'
		]);
        session_regenerate_id(true);

		$_SESSION['url'] = $url;
        $temporarySession = [
            'id' => session_id(),
            'session' => json_encode($_SESSION)
        ];

        return $temporarySession;

    }

    /**
     * Redirects to the given url
     * @param string $url the URL to redirect to
     */
    public function redirect($url)
    {
        header('Location: ' . $url, true, 302);
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Will die now after redirection to ' . $url . ' (Line ' . __LINE__ . '). Good bye!');
        exit("Redirected");
    }

    /**
     * @return string The current URL
     */
    private function getCurrentUrl()
    {
        return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Triggers the login_action for automatically user login
     * @param int $userId
     */
    private function loginUser($userId)
    {

        // Get user
        $user = get_user_by('id', $userId);

        // Trigger Login actions
        wp_clear_auth_cookie();
        wp_set_current_user($userId, $user->user_login);
        wp_set_auth_cookie($userId);
        update_user_caches($user);

        update_user_meta( $userId, 'vca_asm_last_activity', time() );

        (new DropsLogger(''))->log(DropsLogger::INFO, 'Did login user ' . $user->user_login . ' with id ' . $userId);

        do_action('wp_login', $user->user_login, $user);

    }
	
    /**
     * Triggers the login_action for automatically user login
     * @param int $userId
     */
    private function signonUser($username)
    {

		add_filter( 'authenticate', 'allowProgrammaticLogin', 10, 3 ); // hook in earlier than other callbacks to short-circuit them
		$user = wp_signon( array( 'user_login' => $username ) );
		remove_filter( 'authenticate', 'allowProgrammaticLogin', 10, 3 );	
		
		if ( is_a( $user, 'WP_User' ) ) {
			wp_set_current_user( $user->ID, $user->user_login );
			
			if ( is_user_logged_in() ) {
				return true;
			}
		}

		return false;

    }
	
	private function updateUserCapabilities($userData) {
		
		if (!isset($userData->profiles[0])) 
		{
			return;
		}
		
		var_dump($userData->profiles[0]);die();
		
		$preparedUserData = [
			'uuid'			=> $userData->id,
			//'wp_capabilities'	=> 'admin'
			'wp_capabilities'	=> implode(', ', $userData->profiles[0]->roles)
		];

		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Setting userdata after login to ' . implode(', ', $preparedUserData) . ' (Line ' . __LINE__ . ')');
		
		$dataHandler = new DropsUserDataHandler();
		$userUpdater = new DropsUserUpdater($preparedUserData);
		$userUpdater->setDataHandler($dataHandler);
		$response = $userUpdater->run();
		
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'User updated data: ' . implode(', ', $preparedUserData) . ' (Line ' . __LINE__ . ')');
				
		DropsController::logResponse($response);
				
	}

}