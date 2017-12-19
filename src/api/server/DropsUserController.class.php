<?php

require_once 'DropsController.class.php';

/**
 * Class DropsUserController
 */
class DropsUserController extends DropsController
{

    /** Path to post userdata to, to create a user in the good ol' pool */
    const CREATE = 'usercreate';

    /**
     * Checks if the parameters are valid and calls the user creation action
     */
    public function run()
    {

        if (!$this->isRequestValid()) {
            return;
        }

        $parameter = $this->getParameter(DropsSessionController::DROPSFNC, $_POST);

        switch ($parameter) {
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
     * Gets a parameter out of an array
     *
     * @param string $id Index of the searched params
     * @param array $params Array of params
     * @return mixed
     */
    private function getParameter($id, $params)
    {
        if (!isset($params[$id])) {
            return null;
        }

        return $params[$id];
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
        return json_decode($userData, true);
    }

}