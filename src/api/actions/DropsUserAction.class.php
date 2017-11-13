<?php

/**
 * Class DropsUserUpater
 * The class updates the user in drops
 * First it creates the userdata and posts it to drops
 */
abstract class DropsUserAction
{

    /**
     * @var UserDataHandlerInterface $dataHandler
     */
    private $dataHandler;

    /**
     * @var array $userData
     */
    private $userData;

    /**
     * Initializing function on calling the entry of an user
     * Checks if there is an existing user with the given id
     * If there is no user, a user with its usermeta data will be created
     * @param $userId
     * @return DropsResponse
     */
    public function run($userId)
    {

        $currentUserId = get_current_user_id();

        $sessionDataHandler = new DropsSessionDataHandler();
        $accessToken = $sessionDataHandler->getAccessToken($currentUserId);

        if (empty($accessToken)) {
            return (new DropsResponse())
                ->setCode(401)
                ->setContext(__CLASS__)
                ->setMessage('Missing access token! [ID => ' .  $currentUserId . ']');
        }

        // Create userdata in array

        $this->userData = $this->createUserData($userId);

        $options = array(
            'parameters' => array_merge($this->userData, array('access_token' => $accessToken, 'action' => $this->getAction()))
        );

        $restClient = new RestClient($options);
        $response = $restClient->post(Config::get('DROPS_ACCESSTOKEN_URL'));

        if ($response->info->http_code == 200) {
            return (new DropsResponse())
                ->setCode($response->info->http_code)
                ->setContext(__CLASS__)
                ->setMessage('Action ' . $this->getAction() . ' successful! [ID => ' . $currentUserId . '; USER => ' .  $userId . ']');
        }

        return (new DropsResponse())
            ->setCode($response->info->http_code)
            ->setContext(__CLASS__)
            ->setMessage('Action ' . $this->getAction() . ' failed! [ID => ' . $currentUserId . '; USER => ' .  $userId . '] Response message: ' . $response->error);

    }

    /**
     * @param UserDataHandlerInterface $dataHandler
     */
    public function setDataHandler(UserDataHandlerInterface $dataHandler)
    {
        $this->dataHandler = $dataHandler;
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