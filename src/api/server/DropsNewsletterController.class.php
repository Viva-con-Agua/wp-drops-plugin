<?php

require_once 'DropsController.class.php';
require_once 'newsletter/DropsNewsletterReader.class.php';


/**
 * Class DropsUserController
 */
class DropsNewsletterController extends DropsController
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
			case self::READ:

                $data = $this->getData();
                $dataHandler = new DropsUserDataHandler();
                $newsletterReader = new DropsNewsletterReader();
                $newsletterReader->setDataHandler($dataHandler);
                $response = $newsletterReader->run();

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

        return '';

    }

    private function getData()
    {
		// DATAJSON
		return $this->data['hash'];
    }

}