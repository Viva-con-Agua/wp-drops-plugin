<?php

interface GeographyDataHandlerInterface
{

    /**
     * Creates a new user
     * @param array $data
     * @return bool|int
     */
    public function createEntry($data);

    /**
     * Creates an entry for every usermeta data
     * @param int $userId
     * @param array $userMetaData
     * @return bool
     */
    public function createEntryHierarchy($id, array $groups);

    /**
     * Returns the user data
     * @param $email
     * @return mixed
     */
    public function getEntryByName($name);
	
    /**
     * Returns the user data
     * @param $userId
     * @return mixed
     */
    public function getEntryById($userId);
	
    /**
     * Updates the user data
     * @param $userId
     * @return mixed
     */
    public function updateEntry($id, array $data, array $hierarchyData);

}