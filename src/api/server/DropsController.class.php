<?php

/**
 * Class DropsController
 * Defines the usage of the server functions to handle calls from drops
 */
abstract class DropsController
{

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

        if (!empty($response->getResponse())) {
            $logLine .= ' [' . $response->getResponse() . ']';
        }

        (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log($logLevel, $logLine);

    }

}