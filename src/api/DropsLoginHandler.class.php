<?php

require_once DROPSHOME . '/src/api/client/restclient.php';

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
    public function handleLoginRedirect()
    {

        // We have to create a temporary session
        $session = $this->createTemporarySession($this->getCurrentUrl());

        // Store the current URL to it and redirect it to the login page
        $this->sessionDataHandler->persistTemporarySession($session);

        // Redirect to drops
        $url = str_replace('<temporarySessionId>', $session['id'], get_option('dropsLoginUrl'));
        $url = str_replace('<clientId>', get_option('clientId'), $url);

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

        // If there is no temporary session with the id, redirect to the login process
        $sessionId = $this->getParameter('sessionId', $params);
        $temporarySession = $this->sessionDataHandler->getTemporarySession($sessionId);

        if (empty($temporarySession)) {
            $session = $this->createTemporarySession(get_site_url());
            $this->sessionDataHandler->persistTemporarySession($session);
            $sessionId = $session['id'];
        }

        // Read the parameters from the URL and persists them
        $dropsSessionId = $this->getParameter('dropsSessionId', $params);

        $this->sessionDataHandler->persistDropsSessionId($sessionId, $dropsSessionId);

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

        $parameters = [
            'grant_type'    => 'authorization_code',
            'client_id'     => get_option('dropsClientId'),
            'code'          => $authorizationCode,
            'redirect_uri'  => ''
        ];

        // Trigger request
        $response = $this->requestAccessToken($parameters);

        $sessionId = $this->getParameter('sessionId', $params);
        $temporarySession = $this->sessionDataHandler->getTemporarySession($sessionId);

        if (empty($temporarySession)) {
            $this->handleLoginRedirect();
        }

        $this->sessionDataHandler->persistAccessToken($sessionId, $response);
        $userDataResponse = (new DropsUserReader())->setAccessToken($response['access_token'])->run(0);

        DropsController::logResponse($userDataResponse);

        if ($userDataResponse->getCode() != 200) {
            $this->handleLoginRedirect();
        }

        $userData = $userDataResponse->getResponse();

        // Check if user really exists
        $userDataHandler = new DropsUserDataHandler();
        $user = $userDataHandler->getUserById($userData->id);

        if (empty($user)) {
            $this->handleLoginRedirect();
        }

        $this->loginUser($userData->id);
        $this->sessionDataHandler->persistUserId($sessionId, $userData->id);

        if (isset($this->metaDataHandler)) {
            $this->metaDataHandler->addMetaData();
        }

        $url = $temporarySession['user_session']['url'];
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
        }

        $this->handleLoginRedirect();

    }

    /**
     * Gets a parameter out of an array
     *
     * @param string $id Index of the searched params
     * @param array $params Array of params
     * @return mixed
     */
    private function getParameter($id, $params)
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
    private function createTemporarySession($url)
    {

        session_destroy();
        session_unset();

        session_start();
        session_regenerate_id(true);

        $_SESSION['url'] = $url;
        $temporarySession = [
            'id' => session_id(),
            'session' => json_encode($_SESSION)
        ];

        session_destroy();
        session_unset();

        return $temporarySession;

    }

    /**
     * Redirects to the given url
     * @param string $url the URL to redirect to
     */
    private function redirect($url)
    {
        header('Location: ' . $url, true, 302);
        exit;
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

        update_user_meta( $user->ID, 'vca_asm_last_activity', time() );

        do_action('wp_login', $user->user_login);

    }

}