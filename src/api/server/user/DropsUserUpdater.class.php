<?php
/** TODO:

Mapping for fields:
usermeta:
'mail_switch', 'pool_lang', 'secondary_nl', 'nation', 'city'

*/


/**
 * Class DropsUserUpdater
 * The class updates a wordpress user out of the given data.
 * First it checks if there is already an existing user and if we update the user and its metadata
 */
class DropsUserUpdater
{

    private $requiredUserData = array('uuid');
    private $optionalUserFields = array('user_login', 'user_nicename', 'user_email', 'display_name');
    private $optionalUserMetaFields = array('nickname', 'first_name', 'mail_switch', 'pool_lang', 'secondary_nl', 'last_name', 'wp_capabilities', 'mobile', 'residence', 'birthday', 'gender', 'nation', 'city');

    /**
     * @var UserDataHandlerInterface $dataHandler
     */
    private $dataHandler;

    /**
     * @var array $userData
     */
    private $userData;

    public function __construct(array $userData)
    {
        $this->userData = $userData;
    }

    /**
     * Initializing function on calling the entry of an user
     * Checks if there is an existing user with the given id
     * If there is no user, a user with its usermeta data will be created
     * @return DropsResponse
     */
    public function run()
    {

        // Check if userdata is complete
        $invalidFields = $this->validateUserData();
        $isValid = empty($invalidFields);

        if (!$isValid) {
			(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Update validation error (Line ' . __LINE__ . ')');
			return $this->validationError($invalidFields);
        }

        // Check if user already exists
		
		$uuid = $this->userData['uuid'];
		
		$sessionDataHandler = new DropsSessionDataHandler();
		$session = $sessionDataHandler->getSessionByDropsId($uuid);
				
		if	(empty($session) || !isset($session['user_id'])) {
			
			return (new DropsResponse())
				->setCode(400)
				->setContext(__CLASS__)
				->setMessage('No session found with given uuid! Parameters: ' . implode(', ', $this->userData));
			
		}
		
		$userId = $session['user_id'];
        $user = $this->dataHandler->getUserById($userId);

        if (empty($user)) {
			
			return (new DropsResponse())
				->setCode(400)
				->setContext(__CLASS__)
				->setMessage('No user with given email address! Parameters: ' . implode(', ', $this->userData));

        }
            
		if (!$this->doUserUpdate($userId)) {
						
			return (new DropsResponse())
				->setCode(400)
				->setContext(__CLASS__)
				->setMessage('Database error during user update! Parameters: ' . implode(', ', $this->userData));
				
		}
		
		return (new DropsResponse())
			->setCode(200)
			->setContext(__CLASS__)
			->setMessage('User has been updated! [ID: ' .  $userId . ']');
			
    }

    /**
     * @param UserDataHandlerInterface $dataHandler
     */
    public function setDataHandler(UserDataHandlerInterface $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    /**
     * Prepares the data and creates the user entry
     * @return false|int
     */
    private function doUserUpdate($userId)
    {
		
		$userData = [];
		foreach ($this->optionalUserFields AS $key) {
			if (isset($this->userData[$key])) {
				$userData[$key] =  $this->userData[$key];
			}
		}
		
        $userMetaData = [
            'vca_asm_last_activity' => time(),
            Config::get('DB_PREFIX') . '_user-settings-time' => time(),
		];
		
		foreach ($this->optionalUserMetaFields AS $key) {
			
			if (isset($this->userData[$key])) {
				
				if (in_array($key, DropsDataMapper::$mappedFields)) {
					
					$mappedValue = DropsDataMapper::map($key, $this->userData[$key]);
					$userMetaData[$key] = $mappedValue;
										
					if ($key == 'city') {
						$userMetaData['region'] = $mappedValue;
					}
					
				} else {
					$userMetaData[$key] = $this->userData[$key];
				}
				
			}
			
		}
		
		return $this->dataHandler->updateUser($userId, $userData, $userMetaData);

    }

    /**
     * Validate the received userdata for completeness
     * @return array
     */
    private function validateUserData()
    {

        $invalidFields = array();

        foreach ($this->requiredUserData as $entry) {
            if (!isset($this->userData[$entry])) {
                $invalidFields[] = $entry;
            }
        }

        return $invalidFields;

    }
	
	private function validationError($invalidFields) {
		
		ob_start();
		var_dump($this->userData);
        $userData = ob_get_clean();

		return (new DropsResponse())
			->setCode(400)
			->setContext(__CLASS__)
			->setMessage('Missing parameters: ' . implode(", ", $invalidFields) . ' | userdata: [' . $userData . ']');
	}

}