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

    public function persistTemporarySession($sessionData)
    {

        $time = $this->createExpiryTime();

        return $this->dbConnection->insert(
            Config::get('DB_SESSION_TABLE'),
            array(
                'temporary_session_id' => $sessionData['id'],
                'user_session' => $sessionData['session'],
                'expiry_timestamp' => $time
            ));

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

        return $this->dbConnection->update(
            Config::get('DB_SESSION_TABLE'),
            array(
                'drops_session_id' => $dropsSessionId,
                'user_id' => $userId,
                'expiry_timestamp' => $time,
            ),
            array('temporary_session_id' => $temorarySessionId));

    }

    public function persistAccessToken($temorarySessionId, $sessionData)
    {

        $time = $this->createExpiryTime();

        return $this->dbConnection->update(
            Config::get('DB_SESSION_TABLE'),
            array(
                'token_type' => $sessionData['token_type'],
                'access_token' => $sessionData['access_token'],
                'refresh_token' => $sessionData['refresh_token'],
                'expiry_timestamp' => $time,
            ),
            array('temporary_session_id' => $temorarySessionId));

    }

    public function createUser($userData)
    {
        return $this->dbConnection->insert(Config::get('DB_USER_TABLE'), $userData);
    }

    public function createUserMeta($userId, $userMetaData)
    {
        foreach ($userMetaData AS $key => $value) {
            $this->dbConnection->insert(
                Config::get('DB_USERMETA_TABLE'),
                array(
                    'user_id' => $userId,
                    'meta_key' => $key,
                    'meta_value' => $value
                )
            );
        }
    }

    private function createExpiryTime()
    {
        return date('Y-m-d H:i:s', strtotime('30 minute'));
    }

    public function getUser($userData)
    {
        return $this->dbConnection->get_row(
            'SELECT * ' .
            'FROM ' . Config::get('DB_USER_TABLE') . ' ' .
            'WHERE ID = ' . $userData['ID']
        );
    }

}