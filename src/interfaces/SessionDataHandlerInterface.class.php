<?php

interface SessionDataHandlerInterface
{

    /**
     * Persists the temporary created session with id and session data
     * @param array $sessionData
     * @return false|int
     */
    public function persistTemporarySession(array $sessionData);

    /**
     * Gets the temporary session from the wordpress database
     * @param string $sessionId
     * @return array
     */
    public function getTemporarySession($sessionId);

    /**
     * Saves the drops session id send from drops to the session table
     * @param string $temorarySessionId
     * @param string $dropsSessionId
     * @param int $userId
     * @return false|int
     */
    public function persistDropsSessionId($temorarySessionId, $dropsSessionId, $userId);

    /**
     * Deletes exired sessions or sessions with the given user id
     * @param int $userId
     */
    public function clearSessions($userId);

    /**
     * Updates the existing temporary session and adds the data received from drops MS
     * @param string $temorarySessionId
     * @param array $sessionData
     * @return false|int
     */
    public function persistAccessToken($temorarySessionId, array $sessionData);

    /**
     * Read acccess token from the user
     * @param int $userId
     * @return null|string
     */
    public function getAccessToken($userId);

    /**
     * Checks if the session of the user is already expired
     * @param int $userId
     * @return bool
     */
    public function isSessionExpired($userId);

}