<?php

require_once 'DropsController.class.php';
require_once 'DropsUserController.class.php';
require_once 'DropsGeographyController.class.php';

/**
 * Class DropsResponse
 */
class DropsAPIController extends DropsController
{

    /** Definition of pathes */
    const USER = 'user';
    const GEOGRAPHY = 'geography';
    const NEWSLETTER = 'newsletter';
	
	private $data;
	
    /**
     * The routine checks which path is called and calls the corresponding function
     */
    public function run()
    {
		
		$apiCall = $this->getParameter('api', $_GET);
		if (empty($apiCall)) {
			return;
		}

		// DATAJSON
		$this->createReceivedDataFromJson();		
		//$this->createReceivedDataFromArray($_POST);	
		$actionCall = $this->getParameter('action', $_GET);	
	
		if (!$this->isValid() || $actionCall == self::NEWSLETTER) {
			die();
			return;
		}
	
		$dataString = print_r($this->data, true);
	
		
		switch ($apiCall) {
            case self::USER:
				$response = (new DropsUserController())->setFunction($actionCall)->setData($this->data)->run();
				break;
            case self::GEOGRAPHY:
				$response = (new DropsGeographyController())->setFunction($actionCall)->setData($this->data)->run();
				break;
            case self::NEWSLETTER:
				$response = (new DropsNewsletterController())->setFunction($actionCall)->setData($this->data)->run();
				break;
            case 'user-certificate':
				return;
            default:
                break;
        }

		echo $response;
		die('{"context":"DropsAPIController","code":400,"message":"API has done nothing! Badum!"}');

    }

	private function createReceivedDataFromArray($data) {
		$this->data = $data;
	}
	
    private function createReceivedDataFromJson() {
		$this->data = json_decode(file_get_contents('php://input'), true);
	}
	
	private function isValid() {
		$hash = $this->getParameter('hash', $this->data);
		var_dump($this->data);
		return ($hash === get_option('dropsUserAccessHash'));
	}

}