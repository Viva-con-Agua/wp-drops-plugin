<?php

require_once 'DropsController.class.php';
require_once 'user/DropsUserCreator.class.php';
require_once 'user/DropsUserUpdater.class.php';

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

    /**
     * Checks if the parameters are valid and calls the user creation action
     */
    public function run()
    {

        if (!$this->isRequestValid()) {
            return;
        }
		
        switch ($this->apiFunction) {
            case NULL:
				$response = (new DropsResponse())->setCode(400)->setMessage('API Function not set!')->setContext(__CLASS__);
				self::logResponse($response);
				break;
            case self::LOGOUT:

                $userData = $this->getUserData();

                $uD = print_r($userData, true);
                (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log(DropsLogger::INFO, $uD);

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
                $userReceiver = new DropsUserCreator($userData);
                $userReceiver->setDataHandler($dataHandler);
                $response = $userReceiver->run();

                self::logResponse($response);

                echo $response->getFormat(DropsResponse::JSON);
                exit;

                break;
            case self::UPDATE:

                $userData = $this->getUserData();
                $dataHandler = new DropsUserDataHandler();
                $userReceiver = new DropsUserUpdater($userData);
                $userReceiver->setDataHandler($dataHandler);
                $response = $userReceiver->run();

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
            return false;
        }

        if (!isset($_POST['hash']) || $_POST['hash'] !== get_option('dropsUserAccessHash')) {
            return false;
        }

        if (!isset($_POST['user'])) {
            return false;
        }

        return true;

    }

    private function getUserData()
    {
        $userData = $_POST['user'];
        $userData = str_replace('\\', '', $userData);
        return json_decode($userData, true);
    }

}