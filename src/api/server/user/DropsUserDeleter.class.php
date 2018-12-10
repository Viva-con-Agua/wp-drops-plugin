<?php
/** TODO: Wer darf alles nutzer löschen? Aktuell wäre es nur möglich sich selbst zu löschen? */

/**
 * Class DropsUserDeleter
 * The class deletes a wordpress user out of the given data.
 * First it checks if there is an existing user and if so, we delete the user and its metadata
 */
class DropsUserDeleter
{

    private $requiredUserData = array('uuid');

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
			return validationError($invalidFields);
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
				->setMessage('No user with given uuid! Parameters: ' . implode(', ', $this->userData));

        }
            
		if (!$this->doUserDelete($userId)) {
						
			return (new DropsResponse())
				->setCode(400)
				->setContext(__CLASS__)
				->setMessage('Database error during user detele! Parameters: ' . implode(', ', $this->userData));
				
		}
		
		return (new DropsResponse())
			->setCode(200)
			->setContext(__CLASS__)
			->setMessage('User has been deleted! [ID: ' .  $userId . ']');
			
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
    private function doUserDelete($userId)
    {		
		$this->dataHandler->deleteUser($userId);
		$this->dataHandler->deleteUserMeta($userId);
		
		return true;
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