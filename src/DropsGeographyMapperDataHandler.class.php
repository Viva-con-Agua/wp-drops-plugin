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
        return $this->dbConnection->get_row(
            'SELECT geography_id ' .
            'FROM ' . Config::get('DB_GEOGRAPHY') . '_mapping ' .
            "WHERE drops_id = '" . $id . "'"
        );
    }

}