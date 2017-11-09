<?php

/**
 * Class DropsResponse
 */
class DropsController
{

    protected static function getParsedUrl() {
        $actualLink = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        return parse_url($actualLink);
    }

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

        (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log($logLevel, '(' . $response->getContext() . ') ' . $response->getMessage());

    }

}