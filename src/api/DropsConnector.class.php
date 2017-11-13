<?php

require_once 'client/restclient.php';

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 29.09.2017
 * Time: 12:18
 */
class DropsConnector
{

    /**
     * @var SessionDataHandlerInterface $sessionDataHander
     */
    private $sessionDataHander;

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
        $this->sessionDataHander->persistTemporarySession($session);

        // Redirect to drops
        $url = str_replace('<temporarySessionId>', $session['id'], Config::get('DROPS_LOGIN_URL'));
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
     */
    public function handleLoginResponse($params)
    {

        // If there is no temporary session with the id, redirect to the login process
        $sessionId = $this->getParameter('sessionId', $params);
        $temporarySession = $this->sessionDataHander->getTemporarySession($sessionId);

        if (empty($temporarySession)) {
            $this->handleLoginRedirect();
        }

        // Read the parameters from the URL and persists them
        $dropsSessionId = $this->getParameter('dropsSessionId', $params);
        $userId = $this->getParameter('userId', $params);

        $this->sessionDataHander->persistDropsSessionId($sessionId, $dropsSessionId, $userId);

        // Redirect to drops
        $url = str_replace('<temporarySessionId>', $sessionId, Config::get('DROPS_AUTHORIZATION_URL'));
        $this->redirect($url);

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
            'client_id'     => Config::get('CLIENT_ID'),
            'code'          => $authorizationCode,
            'redirect_uri'  => ''
        ];

        // Trigger request
        $response = $this->requestAccessToken($parameters);

        $sessionId = $this->getParameter('sessionId', $params);
        $temporarySession = $this->sessionDataHander->getTemporarySession($sessionId);

        if (empty($temporarySession)) {
            $this->handleLoginRedirect();
        }

        // TODO CHECK IF USER WITH ID REALLY EXISTS

        $this->sessionDataHander->persistAccessToken($sessionId, $response);
        $this->loginUser($temporarySession['user_id']);

        $url = $temporarySession['user_session']['url'];
        $this->redirect($url);

    }

    /**
     * Creates HTTP request and receives the access token
     * @param $parameters
     * @return array|null
     */
    private function requestAccessToken($parameters)
    {

        // TODO Use the correct call to the DROPS URL to get the access token

        $options = array(
            'parameters' => $parameters
        );

        $restClient = new RestClient($options);
        $response = $restClient->get(Config::get('DROPS_ACCESSTOKEN_URL'));

        if ($response->info->http_code == 200) {
            return json_decode($response->response, true);
        }

        $this->handleLoginRedirect();

    }

    /**
     * Setter for the datahandler
     * @param SessionDataHandlerInterface $sessionDataHander
     */
    public function setSessionDataHander(SessionDataHandlerInterface $sessionDataHander)
    {
        $this->sessionDataHander = $sessionDataHander;
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

    /**
     * Fakes the call to the drops API for token exchange
     * // TODO REMOVE THIS FUNCTION AND ITS CALLS
     * @return string
     */
    public function fakeCall()
    {
        $response = array(
            "token_type" => "fdkfiuuuskdn48hf",
            "access_token" => "stringarandomdasda",
            "expires_in" => 1212121212,
            "refresh_token" => "a random string"
        );
        return json_encode($response);
    }

}