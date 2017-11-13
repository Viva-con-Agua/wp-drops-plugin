<?php

require_once 'DropsUserAction.class.php';

/**
 * Class DropsUserUpater
 * The class updates the user in drops
 * First it creates the userdata and posts it to drops
 */
class DropsUserUpdater extends DropsUserAction
{

    const ACTION_TYPE = 'UPDATE';

    private $requiredUserMeta = array('first_name', 'last_name', 'mobile', 'residence', 'birthday', 'gender', 'nation', 'city', 'region');

    /**
     * Creates an array with the userdata
     * @param int $userId
     * @return array
     */
    protected function createUserData($userId)
    {

        $user = wp_get_current_user();

        $userData = array(
            'ID' => $userId,
            'user_login' => $user->login,
            'user_email' => $user->user_email,
            'user_name' => get_user_meta($userId, 'first_name', true) . ' ' . get_user_meta($userId, 'last_name', true),
            'usermeta' => array()
        );

        foreach ($this->requiredUserMeta as $entry) {
            $userMeta = get_user_meta($userId, $entry, true);
            $userData['usermeta'][$entry] = $userMeta;
        }

        return $userData;

    }

    /**
     * Returns the action to add it to the parameters
     */
    protected function getAction()
    {
        return self::ACTION_TYPE;
    }

}