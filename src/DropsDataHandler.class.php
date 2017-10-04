<?php

require_once ('Config.class.php');

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 29.09.2017
 * Time: 15:56
 */
class DropsDataHandler
{

    /**
     * @var wpdb $dbConnection
     */
    private $dbConnection;

    public function __construct()
    {
        global $wpdb;
        $this->dbConnection = $wpdb;
    }

    public function getTemporarySession($sessionId)
    {
        $sessionData = $this->dbConnection->get_results("SELECT * FROM " . Config::get('DB_SESSION_TABLE') . " WHERE temporary_session_id = '" . $sessionId . "'");

        if (empty($sessionData)) {
            return null;
        }

        $session = (array)$sessionData[0];
        $session['user_session'] = json_decode($session['user_session'], true);

        return $session;

    }

    public function persistDropsSession($temorarySessionId, $dropsSessionId, $userId)
    {

        $time = $this->createExpiryTime();

        return $this->dbConnection->query(
            $this->dbConnection->prepare(
                "UPDATE `" . Config::get('DB_SESSION_TABLE') . "` 
                SET `drops_session_id` = '%s',
                `expiry_timestamp` = '%s',
                `user_id` = %s
                WHERE temporary_session_id = '%s'",
                $dropsSessionId, $time, $userId, $temorarySessionId) );

    }

    public function persistTemporarySession($sessionArray)
    {

        $time = $this->createExpiryTime();

        return $this->dbConnection->query(
            $this->dbConnection->prepare(
                "INSERT IGNORE INTO `" . Config::get('DB_SESSION_TABLE') . "` 
                ( `temporary_session_id`, `user_session`, `expiry_timestamp` ) VALUES 
                (%s, %s, %s)",
                $sessionArray['id'], $sessionArray['session'], $time) );

    }

    private function createExpiryTime()
    {
        return date('Y-m-d H:i:s', strtotime('30 minute'));
    }

    public function persistAccessToken($id, $response)
    {

        $time = $this->createExpiryTime();

        return $this->dbConnection->query(
            $this->dbConnection->prepare(
                "UPDATE `" . Config::get('DB_SESSION_TABLE') . "` 
                SET `token_type` = '%s',
                `access_token` = '%s',
                `refresh_token` = '%s',
                `expiry_timestamp` = '%s'
                WHERE temporary_session_id = '%s'",
                $response['token_type'], $response['access_token'], $response['refresh_token'], $time, $id) );

    }

}