<?php

require_once 'DropsController.class.php';

/**
 * Class DropsUserController
 */
class DropsUserController extends DropsController
{

    /** Path to post userdata to, to create a user in the good ol' pool */
    const CREATE = '/usercreate/';

    /**
     * Checks if the parameters are valid and calls the user creation action
     */
    public function run()
    {

        if (!$this->isRequestValid()) {
            return;
        }

        $url = $this->getParsedUrl();

        switch ($url['path']) {
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
            default:
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

        if (!isset($_POST['hash']) || $_POST['hash'] !== Config::get('USER_ACCESS_HASH')) {
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
        return json_decode($userData, true);
    }

}