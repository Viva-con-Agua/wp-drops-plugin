<?php

/**
 * Class DropsNewsletterReader
 * The class updates a wordpress user out of the given data.
 * First it checks if there is already an existing user and if we read the user and its metadata
 */
class DropsNewsletterReader
{

    private $requiredUserData = array();

    /**
     * @var UserDataHandlerInterface $dataHandler
     */
    private $dataHandler;

    /**
     * @var array $userData
     */
    private $data;

    public function __construct()
    {
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
			(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Read validation error (Line ' . __LINE__ . ')');
			return $this->validationError($invalidFields);
        }

        // Check if user exists
		         
		$mail_switches = $this->dataHandler->getUsersMetaById();

		var_dump(mail_switches);

		$result =  [];
		foreach ($mail_switches as $key => $row) {
			$result[$row->user_email] = $row->mail_switch;
		}
				
		return (new DropsResponse())
			->setCode(200)
			->setContext(__CLASS__)
			->setResponse(['mail_switch' => $mail_switch])
			->setMessage('User has been read! [ID: ' .  $userId . ']');
			
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
				$userData[$key] = $this->userData[$key];
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