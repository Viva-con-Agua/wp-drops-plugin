<?php

require_once dirname(__FILE__) . '/Config.class.php';
require_once dirname(__FILE__) . '/interfaces/SessionDataHandlerInterface.class.php';

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 29.09.2017
 * Time: 15:56
 */
class DropsSessionDataHandler implements SessionDataHandlerInterface
{

    /**
     * @var wpdb $dbConnection
     */
    private $dbConnection;

    /**
     * DropsDataHandler constructor
     *
     * Set up dataconnection to the wordpress database
     */
    public function __construct()
    {
        global $wpdb;
        $this->dbConnection = $wpdb;
    }

    /**
     * Persists the temporary created session with id and session data
     * @param array $sessionData
     * @return false|int
     */
    public function persistTemporarySession(array $sessionData)
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

    /**
     * Gets the temporary session from the wordpress database
     * @param string $sessionId
     * @return array
     */
    public function getTemporarySession($sessionId)
    {
        $session = $this->dbConnection->get_row("SELECT * FROM " . Config::get('DB_SESSION_TABLE') . " WHERE temporary_session_id = '" . $sessionId . "'", ARRAY_A);

        if (empty($session)) {
            return array();
        }

        $session['user_session'] = json_decode($session['user_session'], true);

        return $session;
    }

    /**
     * Gets the temporary session from the wordpress database
     * @param string $dropsId
     * @return array
     */
    public function getSessionByDropsId($dropsId)
    {
        $session = $this->dbConnection->get_row("SELECT * FROM " . Config::get('DB_SESSION_TABLE') . " WHERE drops_session_id = '" . $dropsId . "'", ARRAY_A);

        if (empty($session)) {
            return array();
        }

        $session['user_session'] = json_decode($session['user_session'], true);

        return $session;
    }

    /**
     * Saves the drops session id send from drops to the session table
     * @param string $temorarySessionId
     * @param string $dropsSessionId
     * @return false|int
     */
    public function persistDropsSessionId($temorarySessionId, $dropsSessionId)
    {
        // Update the existing temporary session

        $time = $this->createExpiryTime();
        return $this->dbConnection->update(
            Config::get('DB_SESSION_TABLE'),
            array(
                'drops_session_id' => $dropsSessionId,
                'expiry_timestamp' => $time
            ),
            array('temporary_session_id' => $temorarySessionId)
        );
    }

    /**
     * Saves the drops session id send from drops to the session table
     * @param string $temorarySessionId
     * @param string $userId
     * @return false|int
     */
    public function persistUserId($temorarySessionId, $userId)
    {
        // Update the existing temporary session

        $time = $this->createExpiryTime();

        return $this->dbConnection->update(
            Config::get('DB_SESSION_TABLE'),
            array(
                'user_id' => $userId,
                'expiry_timestamp' => $time
            ),
            array('temporary_session_id' => $temorarySessionId)
        );
    }

    /**
     * Deletes exired sessions or sessions with the given user id
     * @param int $userId
     */
    public function clearSessionsByUserId($userId)
    {
        $this->dbConnection->query(
            'DELETE FROM ' . Config::get('DB_SESSION_TABLE') . '
            WHERE user_id = ' . $userId . ' 
            OR expiry_timestamp < now()');
    }

    /**
     * Deletes exired sessions or sessions with the given user id
     * @param string $dropsId
     */
    public function clearSessionsByDropsId($dropsId)
    {
        $this->dbConnection->query(
            'DELETE FROM ' . Config::get('DB_SESSION_TABLE') . '
            WHERE drops_session_id = "' . $dropsId . '" 
            OR expiry_timestamp < now()');
    }

    /**
     * Updates the existing temporary session and adds the data received from drops MS
     * @param string $temorarySessionId
     * @param array $sessionData
     * @return false|int
     */
    public function persistAccessToken($temorarySessionId, array $sessionData)
    {
        $time = $this->createExpiryTime();
        return $this->dbConnection->update(
            Config::get('DB_SESSION_TABLE'),
            array(
                'token_type' => $sessionData['token_type'],
                'access_token' => $sessionData['access_token'],
                'drops_session_id' => isset($sessionData['drops_session_id']) ? $sessionData['drops_session_id'] : '',
                'refresh_token' => $sessionData['refresh_token'],
                'expiry_timestamp' => $time,
            ),
            array('temporary_session_id' => $temorarySessionId)
        );
    }

    /**
     * Creates the expiry time of the session
     * @return string
     */
    private function createExpiryTime()
    {
        return $this->dbConnection->get_var('SELECT now() + INTERVAL 30 minute;');
    }

    /**
     * Read acccess token from the user
     * @param int $userId
     * @return null|string
     */
    public function getAccessToken($userId)
    {
        return $this->dbConnection->get_var(
            'SELECT access_token ' .
            'FROM ' . Config::get('DB_SESSION_TABLE') . ' ' .
            'WHERE user_id = ' . $userId
        );
    }

    /**
     * Updates the expiry date
     * @param int $userId
     * @return bool
     */
    public function updateExpiryDate($id)
    {
        $time = $this->createExpiryTime();
        return $this->dbConnection->update(
            Config::get('DB_SESSION_TABLE'),
            array(
                'expiry_timestamp' => $time,
            ),
            array('user_id' => $id)
        );
    }

    /**
     * Checks if the session of the user is already expired
     * @param int $userId
     * @return bool
     */
    public function isSessionExpired($userId)
    {
        $expiredSession = $this->getExpiredUserSession($userId);
        return ($expiredSession > 0);
    }

    /**
     * Counts the exired sessions from the database
     * @param int $userId
     * @return null|string
     */
    private function getExpiredUserSession($userId)
    {
        return $this->dbConnection->get_var(
            'SELECT COUNT(*) ' .
            'FROM ' . Config::get('DB_SESSION_TABLE') . ' ' .
            'WHERE user_id = ' . $userId . ' ' .
            'AND expiry_timestamp < now()'
        );
    }

    /**
     * Checks if the session of the user is already expired
     * @param int $userId
     * @return bool
     */
    public function hasSession($userId)
    {
        $userSession = $this->getUserSession($userId);
        return (count($userSession) > 0);
    }

    private function getUserSession($userId)
    {
        return $this->dbConnection->get_row(
            'SELECT * ' .
            'FROM ' . Config::get('DB_SESSION_TABLE') . ' ' .
            'WHERE user_id = ' . $userId
        );
    }
		
	public function getError() {
		return $this->dbConnection->last_error;
	}

}