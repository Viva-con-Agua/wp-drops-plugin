<?php

/**
 * Class DropsUserAction
 * Abstract class for API Action calls to the drops service
 * Provides the funtion of connecting to the datahandler, creating the user data and sending the reqeuest
 */
abstract class DropsUserAction
{

    /**
     * @var UserDataHandlerInterface $dataHandler
     */
    private $dataHandler;

    /**
     * @var string $accessToken
     */
    private $accessToken;

    /**
     * @var array $userData
     */
    private $userData;

    /**
     * Initializing function on calling the entry of an user
     * Checks if there is a access token for the existing user with the given id in the session table
     * The user data parameters are created by its implementation, merged with the access token and amd sent to the API
     * @param $userId
     * @return DropsResponse
     */
    public function run($userId)
    {

        $currentUserId = get_current_user_id();

        if (empty($this->accessToken)) {
            return (new DropsResponse())
                ->setCode(401)
                ->setContext(__CLASS__)
                ->setMessage('Missing access token! [ID => ' .  $currentUserId . ']');
        }

        // Create userdata in array

        $this->userData = $this->createUserData($userId);

        $options = array(
            'parameters' => array_merge($this->userData,
                array(
                    'client_id' => get_option('dropsClientId'),
                    'access_token' => $this->accessToken,
                    'action' => $this->getAction())
                )
        );

        $restClient = new RestClient($options);
        $response = $restClient->get(get_option('dropsActionUrl'));

        var_dump($response);
        die();
        if ($response->info->http_code == 200) {

            return (new DropsResponse())
                ->setCode($response->info->http_code)
                ->setContext(__CLASS__)
                ->setResponse(json_decode($response->response))
                ->setMessage('Action ' . $this->getAction() . ' successful! [ID => ' . $currentUserId . '; USER => ' .  $userId . ']');
        }

        return (new DropsResponse())
            ->setCode($response->info->http_code)
            ->setContext(__CLASS__)
            ->setResponse(json_decode($response->response))
            ->setMessage('Action ' . $this->getAction() . ' failed! [ID => ' . $currentUserId . '; USER => ' .  $userId . '] Response message: ' . $response->error);

    }

    /**
     * @param string $accessToken
     * @return $this
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @param UserDataHandlerInterface $dataHandler
     * @return $this
     */
    public function setDataHandler(UserDataHandlerInterface $dataHandler)
    {
        $this->dataHandler = $dataHandler;
        return $this;
    }

    /**
     * Creates an array with the userdata
     * @param int $userId
     * @return array
     */
    abstract protected function createUserData($userId);

    /**
     * Returns the action to add it to the parameters
     */
    abstract protected function getAction();

}