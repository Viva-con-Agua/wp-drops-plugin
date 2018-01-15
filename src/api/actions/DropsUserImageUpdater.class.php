<?php

require_once 'DropsUserAction.class.php';

/**
 * Class DropsUserImageUpater
 * The class updates the user in drops
 */
class DropsUserImageUpdater extends DropsUserAction
{

    const ACTION_TYPE = 'UPDATE';

    /**
     * Creates an array with the userdata
     * @param int $userId
     * @return array
     */
    protected function createUserData($userId)
    {

        $user = wp_get_current_user();

        $avatarInfo = get_user_meta($userId, 'simple_local_avatar', true);
        $avatarUrl = empty($avatarInfo) ? '' : $avatarInfo['full'];

        $userData = array(
            'email' => $user->user_email,
            'url' => $avatarUrl
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

        $actionUrl = get_option('dropsUserImageUpdateUrl');
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