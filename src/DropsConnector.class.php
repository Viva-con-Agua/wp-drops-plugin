<?php

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 29.09.2017
 * Time: 12:18
 */
class DropsConnector
{

    /**
     * @var DropsDataHandler $dataHandler
     */
    private $dataHandler;

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
        $this->dataHandler->persistTemporarySession($session);

        // Redirect to drops
        $url = str_replace('%temporary_session_id', $session['id'], Config::get('DROPS_LOGIN_URL'));
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
        $temporarySession = $this->dataHandler->getTemporarySession($sessionId);

        // TODO Login user here and redirect back to drops if the user could not be logged in

        if (empty($temporarySession)) {
            $this->handleLoginRedirect();
        }

        // Read the parameters from the URL and persists them
        $dropsSessionId = $this->getParameter('dropsSessionId', $params);
        $userId = $this->getParameter('userId', $params);

        $this->dataHandler->persistDropsSession($sessionId, $dropsSessionId, $userId);

        // Redirect to drops
        $url = str_replace('%temporary_session_id', $sessionId, Config::get('DROPS_AUTHORIZATION_URL'));
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

        //
        $sessionId = $this->getParameter('sessionId', $params);
        $temporarySession = $this->dataHandler->getTemporarySession($sessionId);
        $this->dataHandler->persistAccessToken($sessionId, $response);
        $this->loginUser($temporarySession['user_id']);

        $url = $temporarySession['user_session']['url'];
        $this->redirect($url);

    }

    /**
     * Creates HTTP request and receives the access token
     * @param $parameters
     * @return array|mixed|object|string|void
     */
    private function requestAccessToken($parameters)
    {

        // TODO Use the correct call to the DROPS URL to get the access token

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($parameters)
            )
        );

        //$context  = stream_context_create($options);
        //$result = file_get_contents(Config::get('DROPS_ACCESSTOKEN_URL'), false, $context);

        $result = $this->fakeCall();

        if ($result === FALSE) {
            $this->handleLoginRedirect();
            die(__FILE__ . '#' . __CLASS__ . ':' . __FUNCTION__ . '#' . __LINE__);
        }

        return json_decode($result, true);

    }

    /**
     * @param DropsDataHandler $dataHandler
     */
    public function setDataHandler(DropsDataHandler $dataHandler)
    {
        $this->dataHandler = $dataHandler;
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

        $_SESSION['url'] = $url;
        $temporarySession = [
            'id' => session_id(),
            'session' => json_encode($_SESSION)
        ];

        session_destroy();

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
     * @return string
     */
    private function fakeCall()
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