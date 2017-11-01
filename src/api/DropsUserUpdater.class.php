<?php

require_once ('client/restclient.php');

/**
 * Class DropsUserUpater
 * The class updates the user in drops
 * First it creates the userdata and posts it to drops
 */
class DropsUserUpdater
{

    private $requiredUserMeta = array('first_name', 'last_name', 'mobile', 'residence', 'birthday', 'gender', 'nation', 'city', 'region');

    /**
     * @var DropsDataHandler $dataHandler
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
     * @return array
     */
    public function run($userId)
    {
        // Create userdata in array
        $this->createUserData($userId);

        $parameters = $this->dataHandler->getAccessToken($userId);

        $options = array(
            'parameters' => array_merge($this->userData, $parameters)
        );

        // TODO HIER DIE URL FÜR DAS UPDATEN DER USER IN DROPS EINFUEGEN

        $restClient = new RestClient($options);
        $response = $restClient->post(Config::get('DROPS_ACCESSTOKEN_URL'));

        if ($response->info->http_code == 200) {
            die('Gut');
        }

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

}