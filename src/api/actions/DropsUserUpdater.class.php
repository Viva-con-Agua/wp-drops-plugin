<?php

require_once 'DropsUserAction.class.php';

/**
 * Class DropsUserUpater
 * The class updates the user in drops
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
            'email' => $user->user_email,
            'firstName' => get_user_meta($userId, 'first_name', true),
            'lastName' => get_user_meta($userId, 'last_name', true),
            'mobilePhone' => get_user_meta($userId, 'mobile', true),
            'placeOfResidence' => get_user_meta($userId, 'residence', true),
            'birthday' => (float)get_user_meta($userId, 'birthday', true),
            'sex' => get_user_meta($userId, 'gender', true),
        );

        return $userData;

    }

    /**
     * Returns the action to add it to the parameters
     */
    protected function getAction()
    {
        return self::ACTION_TYPE;
    }

    /**
     * Returns the actionUrl
     */
    protected function getActionUrl()
    {

        $userSession = (new DropsSessionDataHandler())->getTemporarySession(session_id());
        $uuid = $userSession['drops_session_id'];

        $actionUrl = get_option('dropsUserUpdateUrl');
        $actionUrl = str_replace('<id>', $uuid, $actionUrl);

        return $actionUrl;

    }

    /**
     * Returns the action to add it to the parameters
     */
    protected function getActionType()
    {
        return parent::ACTIONTYPE_PUT;
    }

    protected function getFormat()
    {
        return self::FORMAT_JSON;
    }
}