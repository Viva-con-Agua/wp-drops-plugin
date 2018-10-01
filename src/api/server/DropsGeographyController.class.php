<?php

require_once 'DropsController.class.php';
require_once 'geography/DropsGeographyCreator.class.php';

/**
 * Class DropsGeographyController
 */
class DropsGeographyController extends DropsController
{
	
    /** Path to post userdata to, to update a user in the good ol' pool */
    const UPDATE = 'update';
	
    /** Path to post userdata to, to create a user in the good ol' pool */
    const CREATE = 'create';

    /**
     * Checks if the parameters are valid and calls the user creation action
     */
    public function run()
    {

        if (!$this->isRequestValid()) {
			die('dds');
            return;
        }
		
        switch ($this->apiFunction) {
            case NULL:
				$response = (new DropsResponse())->setCode(400)->setMessage('API Function not set!')->setContext(__CLASS__);
				self::logResponse($response);
				break;
            
            case self::CREATE:

                $data = $this->getData();
                $dataHandler = new DropsGeographyDataHandler();
                $receiver = new DropsGeographyCreator($data);
                $receiver->setDataHandler($dataHandler);
                $response = $receiver->run();

                self::logResponse($response);

                echo $response->getFormat(DropsResponse::JSON);
                exit;

                break;
            /*case TODO self::UPDATE:
			    
				return;
				
				$data = $this->getData();
                $dataHandler = new DropsGeographyDataHandler();
                $receiver = new DropsGeographyUpdater($data);
                $receiver->setDataHandler($dataHandler);
                $response = $receiver->run();

                self::logResponse($response);

                echo $response->getFormat(DropsResponse::JSON);
                exit;

                break;*/
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

        if (!isset($_POST['geography'])) {
            return false;
        }

        return true;

    }

    private function getData()
    {
        $data = $_POST['geography'];
        $data = str_replace('\\', '', $data);
        return json_decode($data, true);
    }

}