<?php

/**
 * Class DropsGeographyUpdater
 * The class updates a wordpress geography entry out of the given data.
 * First it checks if there is already an existing and if not, we can update one considering its meta data
 */
class DropsGeographyUpdater
{

    private $requiredData = array('name');
	private $optionalData = array('new_name', 'new_type', 'new_groups');

    /**
     * @var GeographyDataHandlerInterface $dataHandler
     */
    private $dataHandler;

    /**
     * @var array $data
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Initializing function on calling the entry of an element
     * Checks if there is an existing element with the given data
     * If there is one, the element and its metadata will be updated
     * @return DropsResponse
     */
    public function run()
    {

        // Check if data is complete
        $invalidFields = $this->validateData();
        $isValid = empty($invalidFields);

        if (!$isValid) {
			return $this->validationError($invalidFields);
        }

        // Check if location already exists
        $entry = $this->dataHandler->getEntryByName($this->data['name']);

        if (empty($entry)) {
			
			return (new DropsResponse())
				->setCode(400)
				->setContext(__CLASS__)
				->setMessage('No entry found with given data! Parameters: ' . implode(', ', $this->data['name']));

        }

		$this->updateEntry($entry->id);

		if (empty($entry) || !isset($entry->id)) {
			return (new DropsResponse())
				->setCode(400)
				->setContext(__CLASS__)
				->setMessage('Database error during update! Parameters: ' . implode(', ', $this->data));
		}

		if (!empty($this->data['new_groups'])) {
							
			$isHierarchyupdated = $this->updateEntryHierarchy($entry->id);

			if (!$isHierarchyupdated) {
				return (new DropsResponse())
					->setCode(400)
					->setContext(__CLASS__)
					->setMessage('Database error during hierarchy update! [ID: ' .  $entry->id . '] Parameters: ' . implode(', ', $this->data));
			}
		
		}

		return (new DropsResponse())
			->setCode(200)
			->setContext(__CLASS__)
			->setMessage('Geography has been updated! [ID: ' . $entry->id . ']');
    }

    /**
     * @param GeographyDataHandlerInterface $dataHandler
     */
    public function setDataHandler(GeographyDataHandlerInterface $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    /**
     * Prepares the data and updates the entry
     * @return false|int
     */
    private function updateEntry($id)
    {

        $data = array(
            'name' => $this->data['new_name'],
            'type' => $this->data['new_type'],
        );

        return $this->dataHandler->updateEntry($id, $data);

    }

    /**
     * Prepares the data and updates the meta data entry
     */
    private function updateEntryHierarchy($id)
    {
		
		$entryGroups = [];
		foreach ($this->data['new_groups'] AS $group) {
			
			$groupEntry = $this->dataHandler->getEntryByName($group);
						
			if (!empty($groupEntry)) {
				$entryGroups[] = [$groupEntry->id, $groupEntry->type, $id]; 
			}
			
		}

        return $this->dataHandler->updateEntryHierarchy($id, $entryGroups);

    }

    /**
     * Validate the received data for completeness
     * @return array
     */
    private function validateData()
    {

        $invalidFields = array();

        foreach ($this->requiredData as $entry) {
            if (!isset($this->data[$entry])) {
                $invalidFields[] = $entry;
            }
        }

        return $invalidFields;

    }

	private function validationError($invalidFields) {
		
		ob_start();
		var_dump($this->data);
        $geoData = ob_get_clean();

		return (new DropsResponse())
			->setCode(400)
			->setContext(__CLASS__)
			->setMessage('Missing parameters: ' . implode(", ", $invalidFields) . ' | geographydata: [' . $geoData . ']');
	}

}