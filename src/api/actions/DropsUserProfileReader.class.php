<?php

require_once 'DropsUserAction.class.php';

/**
 * Class DropsUserReader
 * The class reads the user from drops
 */
class DropsUserProfileReader extends DropsUserAction
{

    const ACTION_TYPE = 'READ';

    /**
     * Creates an array with the userdata
     * @param int $userId
     * @return array
     */
    protected function createUserData($userId)
    {
        return array();
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

        $user = wp_get_current_user();

        $actionUrl = get_option('dropsUserProfileUrl');
        $actionUrl = str_replace('<id>', $user->ID, $actionUrl);

        return $actionUrl;

    }

    /**
     * Returns the action to add it to the parameters
     */
    protected function getActionType()
    {
        return parent::ACTIONTYPE_GET;
    }

}