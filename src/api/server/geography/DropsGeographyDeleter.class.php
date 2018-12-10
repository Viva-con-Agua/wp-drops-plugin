<?php

/**
 * Class DropsGeographyDeleter
 * The class deletes a wordpress geography entry out of the given data.
 * First it checks if there is an existing one and if so, we can delete it and its hierarchy
 */
class DropsGeographyDeleter
{

    private $requiredData = array('name');

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
     * Checks if there is an existing element with the given parameters
     * If there is no one, a new one with its given data will be deleted
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
				->setMessage('No Geography data with the given name ! [Name: ' .  $this->data['name'] . ']');

        }

		if (!$this->deleteEntry($entry->id)) {
			return (new DropsResponse())
				->setCode(400)
				->setContext(__CLASS__)
				->setMessage('Database error during creation! Parameters: ' . implode(', ', $this->data));
		}

		if (!empty($this->data['groups'])) {
							
			$isHierarchydeleted = $this->deleteEntryHierarchy($entry->id);

			if (!$isHierarchydeleted) {
				return (new DropsResponse())
					->setCode(400)
					->setContext(__CLASS__)
					->setMessage('Database error during hierarchy creation! [ID: ' .  $entry->id . '] Parameters: ' . implode(', ', $this->data));
			}
		
		}

		return (new DropsResponse())
			->setCode(200)
			->setContext(__CLASS__)
			->setMessage('Geography has been deleted! [ID: ' . $entry->id . ']');
    }

    /**
     * @param GeographyDataHandlerInterface $dataHandler
     */
    public function setDataHandler(GeographyDataHandlerInterface $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    /**
     * Prepares the data and deletes the entry
     * @return false|int
     */
    private function deleteEntry($id)
    {

        $data = array(
            'name' => $this->data['name'],
        );

        return $this->dataHandler->deleteEntry($id, $data);

    }

    /**
     * Prepares the data and deletes the meta data entry
     */
    private function deleteEntryHierarchy($id)
    {
		
		$entryGroups = [];
		foreach ($this->data['groups'] AS $group) {
			
			$groupEntry = $this->dataHandler->getEntryByName($group);
						
			if (!empty($groupEntry)) {
				$entryGroups[] = [$groupEntry->id, $groupEntry->type, $id]; 
			}
			
		}

        return $this->dataHandler->deleteEntryHierarchy($id, $entryGroups);

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