<?php

require_once 'Config.class.php';
require_once 'interfaces/GeographyDataHandlerInterface.class.php';

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 29.09.2017
 * Time: 15:56
 */
class DropsGeographyDataHandler implements GeographyDataHandlerInterface
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
     * Creates a new wordpress user
     * @param array $userData
     * @return bool|int
     */
    public function createEntry($data)
    {
        if ($this->dbConnection->insert(Config::get('DB_GEOGRAPHY'), $data)) {
            return $this->dbConnection->insert_id;
        };
        return false;
    }

    /**
     * Creates an entry for every geography hierarchy data
     * @param int $id
     * @param array $groups
     * @return bool
     */
    public function createEntryHierarchy($id, array $groups)
    {
		
        $return = array();
		
        foreach ($groups AS $value) {
			
            $result = $this->dbConnection->insert(
                Config::get('DB_GEOGRAPHY') . '_hierarchy',
                array(
                    'ancestor' => $value[0],
                    'ancestor_type' => $value[1],
                    'descendant' => $value[2]
                )
            );
            $return[$result] = true;
        }

        return !isset($return[false]);

    }

    public function getEntryByName($name)
    {
        return $this->dbConnection->get_row(
            'SELECT * ' .
            'FROM ' . Config::get('DB_GEOGRAPHY') . ' ' .
            "WHERE name = '" . $name . "'"
        );
    }

    public function getEntryById($id)
    {
        return $this->dbConnection->get_row(
            'SELECT * ' .
            'FROM ' . Config::get('DB_GEOGRAPHY') . ' ' .
			'WHERE ID = "' . $id . '"'
        );
    }

    public function updateEntry($id, array $data, array $hierarchyData)
    {
		
		$returnValue['true'] = 1;
		
		if (!empty($data)) {
			
			$userDataSql = implode(', ', array_map(
				function ($v, $k) { return sprintf('%s="%s"', $k, $v); },
				$data,
				array_keys($data)
			));
			
			$updateSql = 'UPDATE ' . Config::get('DB_GEOGRAPHY') . ' SET ' .
				$userDataSql . ' ' .
				'WHERE ID = "' . $userId . '"';
			
			$returnValueKey = $this->dbConnection->query($updateSql);
			
			if($returnValueKey === false) {
				$returnValue[false] = 1;
			}
			
		}
		
		if (!empty($hierarchyData)) {
			
			foreach ($hierarchyData AS $metaKey => $metaValue) {
				$updateSql = 'UPDATE ' . Config::get('DB_GEOGRAPHY') . '_hierarchy SET ' .
					'meta_value = "' . $metaValue . '" ' . 
					'WHERE user_id = "' . $userId . '" and meta_key = "' . $metaKey . '"';
				$returnValueKey = $this->dbConnection->query($updateSql);
				
				if($returnValueKey === false) {
					$returnValue[false] = 1;
				}
				
			}
			
		}
		
		var_dump($returnValue);
		
		return !(isset($returnValue[false]));
		
    }

}