<?php

require_once 'client/restclient.php';

/**
 * Class DropsUserUpater
 * The class updates the user in drops
 * First it creates the userdata and posts it to drops
 */
class DropsUserUpdater
{

    private $requiredUserMeta = array('first_name', 'last_name', 'mobile', 'residence', 'birthday', 'gender', 'nation', 'city', 'region');

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

        $this->createUserData($userId);

        $options = array(
            'parameters' => array_merge($this->userData, array('access_token' => $accessToken))
        );

        $restClient = new RestClient($options);
        $response = $restClient->post(Config::get('DROPS_ACCESSTOKEN_URL'));

        if ($response->info->http_code == 200) {
            return (new DropsResponse())
                ->setCode($response->info->http_code)
                ->setContext(__CLASS__)
                ->setMessage('Update for user successful! [ID => ' . $currentUserId . '; USER => ' .  $userId . ']');
        }

        return (new DropsResponse())
            ->setCode($response->info->http_code)
            ->setContext(__CLASS__)
            ->setMessage('User update failed! [ID => ' . $currentUserId . '; USER => ' .  $userId . '] Response message: ' . $response->error);

    }

    /**
     * Creates an array with the userdata
     * @param int $userId
     */
    private function createUserData($userId)
    {

        $user = wp_get_current_user();

        $this->userData = array(
            'ID' => $userId,
            'user_login' => $user->login,
            'user_email' => $user->user_email,
            'user_name' => get_user_meta($userId, 'first_name', true) . ' ' . get_user_meta($userId, 'last_name', true),
            'usermeta' => array()
        );

        foreach ($this->requiredUserMeta as $entry) {
            $userMeta = get_user_meta($userId, $entry, true);
            $this->userData['usermeta'][$entry] = $userMeta;
        }

    }

    /**
     * @param UserDataHandlerInterface $dataHandler
     */
    public function setDataHandler(UserDataHandlerInterface $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

}