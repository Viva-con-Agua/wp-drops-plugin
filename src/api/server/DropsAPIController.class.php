<?php

require_once 'DropsController.class.php';

/**
 * Class DropsResponse
 */
class DropsAPIController extends DropsController
{


    /** Definition of pathes */
    const USER = ['user' => ['UPDATE']];
    const USER = ['crew' => ['UPDATE']];
	
	public function setAPIPath($path) {
		$this->path = 
	}

    /**
     * The routine checks which path is called and calls the corresponding function
     */
    public function run()
    {

        $drops = (new DropsLoginHandler())
            ->setSessionDataHandler(new DropsSessionDataHandler())
            ->setMetaDataHandler(new DropsMetaDataHandler());

        $parameter = $drops->getParameter(self::DROPSFNC, $_GET);

        if (empty($parameter)) {
            $parameter = self::INITIAL;
        }

        $url = $this->getParsedUrl();
        if (isset($url['path']) && stristr('wp-admin', $url['path'])) {
            return;
        }

        switch ($parameter) {
            case self::ACCESS:
                $sessionId = $drops->handleLoginResponse($_GET);
                $drops->handleAuthorizationCodeResponse(array_merge($_GET, array('sessionId' => $sessionId)));
                break;
				
            case self::REDIRECT:
                $drops->handleFrontendLoginResponse();
				break;
			
            case self::INITIAL:
            default:
                $drops->handleFrontendLoginRedirect();
                break;
        }

    }

}