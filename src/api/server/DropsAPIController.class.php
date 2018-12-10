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
	
    /**
     * The routine checks which path is called and calls the corresponding function
     */
    public function run()
    {
		
		$apiCall = $this->getParameter('api', $_GET);
		
		if (empty($apiCall)) {
			return;
		}
		
		$actionCall = $this->getParameter('action', $_GET);
		
		if (!$this->isValid()) {
			return;
		}
		
		
		switch ($apiCall) {
            case self::USER:
				$response = (new DropsUserController())->setFunction($actionCall)->run();
				break;
            case self::GEOGRAPHY:
				$response = (new DropsGeographyController())->setFunction($actionCall)->run();
				break;
            case 'user-certificate':
				return;
            default:
                break;
        }

		echo $response;
		die('{"context":"DropsAPIController","code":400,"message":"API has done nothing! Badum!"}');

    }
	
	private function isValid() {
		$hash = $this->getParameter('hash', $_POST);
		return ($hash == get_option('dropsUserAccessHash'));
	}

}