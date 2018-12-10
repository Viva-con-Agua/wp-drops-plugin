<?php

/**
 * Class DropsGeographyCreator
 * The class creates a wordpress geography entry out of the given data.
 * First it checks if there is already an existing and if not, we can create one considering its meta data
 */
class DropsGeographyCreator
{

    private $requiredData = array('name', 'type');

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
     * Initializing function on calling the entry of an user
     * Checks if there is an existing user with the given id
     * If there is no user, a user with its usermeta data will be created
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

        if (!empty($entry)) {
			
			return (new DropsResponse())
				->setCode(400)
				->setContext(__CLASS__)
				->setMessage('Geography already exists! [ID: ' .  $entry->id . ']');

        }

		$id = $this->createEntry();

		if (!$id) {
			return (new DropsResponse())
				->setCode(400)
				->setContext(__CLASS__)
				->setMessage('Database error during creation! Parameters: ' . implode(', ', $this->data));
		}

		if (!empty($this->data['groups'])) {
							
			$isHierarchyCreated = $this->createEntryHierarchy($id);

			if (!$isHierarchyCreated) {
				return (new DropsResponse())
					->setCode(400)
					->setContext(__CLASS__)
					->setMessage('Database error during hierarchy creation! [ID: ' .  $id . '] Parameters: ' . implode(', ', $this->data));
			}
		
		}

		return (new DropsResponse())
			->setCode(200)
			->setContext(__CLASS__)
			->setMessage('Geography has been created! [ID: ' . $id . ']');
    }

    /**
     * @param GeographyDataHandlerInterface $dataHandler
     */
    public function setDataHandler(GeographyDataHandlerInterface $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    /**
     * Prepares the data and creates the user entry
     * @return false|int
     */
    private function createEntry()
    {

        $data = array(
            'name' => $this->data['name'],
            'type' => $this->data['type'],
            'has_user' => 0,
            'alpha_code' => 'xx',
        );

        return $this->dataHandler->createEntry($data);

    }

    /**
     * Prepares the data and creates the usermeta data entry
     */
    private function createEntryHierarchy($id)
    {
		
		$entryGroups = [];
		foreach ($this->data['groups'] AS $group) {
			
			$groupEntry = $this->dataHandler->getEntryByName($group);
						
			if (!empty($groupEntry)) {
				$entryGroups[] = [$groupEntry->id, $groupEntry->type, $id]; 
			}
			
		}

        return $this->dataHandler->createEntryHierarchy($id, $entryGroups);

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