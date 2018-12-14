<?php

/**
 * Class DropsController
 * Defines the usage of the server functions to handle calls from drops
 */
abstract class DropsController
{
		
	public $apiFunction;

    /**
     * @return mixed
     */
    abstract public function run();

    /**
     * Returns the current url parsed into its parts
     * @return array
     */
    protected function getParsedUrl() {
        $actualLink = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        return parse_url($actualLink);
    }
	
    /**
     * Returns the current url parsed into its parts
     * @return array
     */
    public function setFunction($apiFunction) {
        $this->apiFunction = $apiFunction;
		return $this;
    }
	
    /**
     * Returns the current url parsed into its parts
     * @return array
     */
    protected function getUrlPath() {
		
		if (!isset($_SERVER['REQUEST_URI'])) {
			return [];
		}
		
		if (empty($_SERVER['REQUEST_URI'])) {
			return [];
		}
		
		return explode('/', $_SERVER['REQUEST_URI']);
    }

    /**
     * Logs the response data to the drops logger
     * @param DropsResponse $response
     */
    public static function logResponse(DropsResponse $response)
    {

        $logLevel = DropsLogger::INFO;

        switch ($response->getCode()) {
            case 200:
                break;
            case 400:
                $logLevel = DropsLogger::ERROR;
                break;
            default:
        }

        $logLine = '(' . $response->getContext() . ') ' . $response->getMessage();

        if (!empty($response->getResponse()) && is_string($response->getResponse())) {
            $logLine .= ' [' . $response->getResponse() . ']';
        }

        (new DropsLogger(date('Y_m_d')))->log($logLevel, $logLine);

    }

    /**
     * Gets a parameter out of an array
     *
     * @param string $id Index of the searched params
     * @param array $params Array of params
     * @return mixed
     */
    public function getParameter($id, $params)
    {
        if (!isset($params[$id])) {
            return null;
        }

        return $params[$id];
    }


}