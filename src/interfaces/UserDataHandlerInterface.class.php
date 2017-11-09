<?php

interface UserDataHandlerInterface
{

    /**
     * Creates a new user
     * @param array $userData
     * @return bool|int
     */
    public function createUser($userData);

    /**
     * Creates an entry for every usermeta data
     * @param int $userId
     * @param array $userMetaData
     * @return bool
     */
    public function createUserMeta($userId, array $userMetaData);

    /**
     * Returns the user data
     * @param $userData
     * @return mixed
     */
    public function getUser($userId);

}