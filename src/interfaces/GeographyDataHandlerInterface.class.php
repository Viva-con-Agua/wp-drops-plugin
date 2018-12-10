<?php

interface GeographyDataHandlerInterface
{

    /**
     * Creates a new element
     * @param array $data
     * @return bool|int
     */
    public function createEntry($data);

    /**
     * Creates an entry for every elementmeta data
     * @param int $id
     * @param array $elementMetaData
     * @return bool
     */
    public function createEntryHierarchy($id, array $groups);

    /**
     * Returns the element data
     * @param $email
     * @return mixed
     */
    public function getEntryByName($name);
	
    /**
     * Returns the element data
     * @param $id
     * @return mixed
     */
    public function getEntryById($id);
	
    /**
     * Updates the element data
     * @param $id
     * @return mixed
     */
    public function updateEntry($id, array $data);
	
    /**
     * Updates the element data
     * @param $id
     * @return mixed
     */
    public function updateEntryHierarchy($id, array $data);
	
    /**
     * Deletes the element data
     * @param $id
     * @return mixed
     */
    public function deleteEntry($id);
	
    /**
     * Deletes the element data
     * @param $id
     */
    public function deleteEntryHierarchy($id);

}