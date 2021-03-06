<?php

require_once 'DropsController.class.php';
require_once 'user/DropsUserCreator.class.php';
require_once 'user/DropsUserReader.class.php';
require_once 'user/DropsUserUpdater.class.php';
require_once 'user/DropsUserDeleter.class.php';

/**
 * Class DropsUserController
 */
class DropsUserController extends DropsController
{
	
    /** Path to post userdata to, to update a user in the good ol' pool */
    const UPDATE = 'update';
	
    /** Path to post userdata to, to create a user in the good ol' pool */
    const CREATE = 'create';

    /** Path to post userdata to, to logout a user from the good ol' pool */
    const LOGOUT = 'logout';

    /** Path to post userdata to, to logout a user from the good ol' pool */
    const REMOVE = 'delete';
	
    /** Path to post userdata to, to logout a user from the good ol' pool */
    const READ = 'read';

    /**
     * Checks if the parameters are valid and calls the user creation action
     */
    public function run()
    {
		
		$requestValidation = $this->isRequestValid();

        if (!empty($requestValidation)) {
            $response = (new DropsResponse())->setCode(400)->setMessage('Invalid request! Please check your data and format! Message: ' . $requestValidation)->setContext(__CLASS__);
			self::logResponse($response);
            return $response->getFormat(DropsResponse::JSON);
        }
		
        switch ($this->apiFunction) {
            case NULL:
				$response = (new DropsResponse())->setCode(400)->setMessage('API Function not set!')->setContext(__CLASS__);
				self::logResponse($response);
				break;
            case self::LOGOUT:

                $userData = $this->getUserData();

                if (isset($userData['uuid'])) {
                    $uuid = $userData['uuid'];

                    $sessionDataHandler = new DropsSessionDataHandler();
                    $session = $sessionDataHandler->getSessionByDropsId($uuid);

                    if (!empty($session)) {
                        $userId = $session['user_id'];

                        $sessionDataHandler->clearSessionsByUserId($userId);

                        // get all sessions for user with ID $user_id
                        $sessions = WP_Session_Tokens::get_instance($userId);

                        // we have got the sessions, destroy them all!
                        $sessions->destroy_all();
                        $response = (new DropsResponse())->setCode(200)->setMessage('Logout successful for ' . $uuid . '(' . $userId . ')')->setContext(__CLASS__);

                    } else {
                        $response = (new DropsResponse())->setCode(200)->setMessage('No session found for ' . $uuid)->setContext(__CLASS__);
                    }

                } else {
                    $response = (new DropsResponse())->setCode(400)->setMessage('UUID missing!')->setContext(__CLASS__);
                }

                self::logResponse($response);
                echo $response->getFormat(DropsResponse::JSON);
                exit;

                break;
            case self::CREATE:

                $userData = $this->getUserData();
                $dataHandler = new DropsUserDataHandler();
                $userCreator = new DropsUserCreator($userData);
                $userCreator->setDataHandler($dataHandler);
                $response = $userCreator->run();

                self::logResponse($response);

                echo $response->getFormat(DropsResponse::JSON);
                exit;

                break;
            case self::UPDATE:

                $userData = $this->getUserData();
                $dataHandler = new DropsUserDataHandler();
                $userUpdater = new DropsUserUpdater($userData);
                $userUpdater->setDataHandler($dataHandler);
                $response = $userUpdater->run();

                self::logResponse($response);

                echo $response->getFormat(DropsResponse::JSON);
                exit;

                break;
            case self::REMOVE:

                $userData = $this->getUserData();
                $dataHandler = new DropsUserDataHandler();
                $userDeleter = new DropsUserDeleter($userData);
                $userDeleter->setDataHandler($dataHandler);
                $response = $userDeleter->run();

                self::logResponse($response);

                echo $response->getFormat(DropsResponse::JSON);
                exit;

                break;            
			case self::READ:

                $userData = $this->getUserData();
                $dataHandler = new DropsUserDataHandler();
                $userReader = new DropsUserReader($userData);
                $userReader->setDataHandler($dataHandler);
                $response = $userReader->run();

                self::logResponse($response);

                echo $response->getFormat(DropsResponse::JSON);
                exit;

                break;
            default:
				$response = (new DropsResponse())->setCode(400)->setMessage('API Function not implemented!')->setContext(__CLASS__);
				self::logResponse($response);
                break;
        }

    }

    /**
     * @return bool
     */
    private function isRequestValid()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            return 'Wrong request method given!';
        }

        /*if (!isset($_POST['user'])) {
            return 'No user given ' . print_r($_POST, true);
        }*/
		
		// DATAJSON
        if (!isset($this->data['user'])) {
            return 'No user given ' . print_r($this->data, true);
        }
		
        return '';

    }

    private function getUserData()
    {
		// DATAJSON
		return $this->data['user'];
    }

}