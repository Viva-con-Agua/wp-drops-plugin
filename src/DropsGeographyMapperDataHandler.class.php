<?php

require_once 'Config.class.php';

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 29.09.2017
 * Time: 15:56
 */
class DropsGeographyMapperDataHandler
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
	
    public function getEntryByDropsId($id)
    {
		
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Entered geo mapping id ' . $id . ' (Line ' . __LINE__ . ')');
		
		$sql = 'SELECT geography_id ' .
            'FROM ' . Config::get('DB_GEOGRAPHY') . '_mapping ' .
            "WHERE drops_id = '" . $id . "'";
			
			
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'SQL for geo mapping id ' . $sql . ' (Line ' . __LINE__ . ')');
			
		$result = $this->dbConnection->get_var($sql);
		
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'SQL result: ' . $result . ' (Line ' . __LINE__ . ')');
        return $result;
    }

}