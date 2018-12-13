<?php

require_once 'DropsUserAction.class.php';

/**
 * Class DropsUserReader
 * The class reads the user from drops
 */
class DropsUserReader extends DropsUserAction
{
	
    const ACTION_TYPE = 'READ';
	
	private $uuid = '';

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
		
        $actionUrl = get_option('dropsUserReadUrl');
        $actionUrl = str_replace('<id>', $this->uuid, $actionUrl);

		var_dump($actionUrl);
		
        return $actionUrl;

    }
	
	public function setDropsUuid($uuid) {
		$this->uuid = $uuid;
		return $this;
	}

    /**
     * Returns the action to add it to the parameters
     */
    protected function getActionType()
    {
        return parent::ACTIONTYPE_GET;
    }

    protected function getFormat()
    {
        return self::FORMAT_JSON;
    }

}