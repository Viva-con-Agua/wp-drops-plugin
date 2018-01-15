<?php

require_once 'DropsUserAction.class.php';

/**
 * Class DropsUserDeleter
 * The class marks a user as deleted in drops for the pool
 */
class DropsUserDeleter extends DropsUserAction
{

    const ACTION_TYPE = 'DELETE';

    /**
     * Creates an array with the userdata
     * @param int $userId
     * @return array
     */
    protected function createUserData($userId)
    {

        $user = wp_get_current_user();

        return array(
            'email' => $user->user_email,
        );

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

        $actionUrl = get_option('dropsUserDeleteUrl');
        $actionUrl = str_replace('<id>', $user->ID, $actionUrl);

        return $actionUrl;

    }

    /**
     * Returns the action to add it to the parameters
     */
    protected function getActionType()
    {
        return parent::ACTIONTYPE_DELETE;
    }

    protected function getFormat()
    {
        return self::FORMAT_ARRAY;
    }

}