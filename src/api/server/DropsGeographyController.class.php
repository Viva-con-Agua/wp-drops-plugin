<?php

require_once 'DropsController.class.php';
require_once 'geography/DropsGeographyCreator.class.php';
require_once 'geography/DropsGeographyUpdater.class.php';
require_once 'geography/DropsGeographyDeleter.class.php';

/**
 * Class DropsGeographyController
 */
class DropsGeographyController extends DropsController
{
	
    /** Path to post data to, to create an elemnt in the good ol' pool */
    const CREATE = 'create';
	
    /** Path to post data to, to update an elemnt in the good ol' pool */
    const UPDATE = 'update';
	
    /** Path to post data to, to delete an elemnt in the good ol' pool */
    const REMOVE = 'delete';

    /**
     * Checks if the parameters are valid and calls the creation, update or delete action
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
            
            case self::CREATE:

                $data = $this->getData();
                $dataHandler = new DropsGeographyDataHandler();
                $creator = new DropsGeographyCreator($data);
                $creator->setDataHandler($dataHandler);
                $response = $creator->run();

                self::logResponse($response);

                echo $response->getFormat(DropsResponse::JSON);
                exit;

                break;
            case self::UPDATE:
			    				
				$data = $this->getData();
                $dataHandler = new DropsGeographyDataHandler();
                $updater = new DropsGeographyUpdater($data);
                $updater->setDataHandler($dataHandler);
                $response = $updater->run();

                self::logResponse($response);

                echo $response->getFormat(DropsResponse::JSON);
                exit;

                break;
            case self::REMOVE:
			    				
				$data = $this->getData();
                $dataHandler = new DropsGeographyDataHandler();
                $deleter = new DropsGeographyDeleter($data);
                $deleter->setDataHandler($dataHandler);
                $response = $deleter->run();

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

        if (!isset($_POST['geography'])) {
            return 'No crew given ' . print_r($_POST, true);
        }
		
		// DATAJSON
        /*if (!isset($this->data['geography'])) {
            return 'No user given ' . print_r($this->data, true);
        }*/

        return '';

    }

    private function getData()
    {
		// DATAJSON
		// return $this->data['geography'];
        $data = $_POST['geography'];
        $data = str_replace('\\', '', $data);
        return json_decode($data, true);
    }

}