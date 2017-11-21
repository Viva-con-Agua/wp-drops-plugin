<?php

/**
 * Class DropsMetaDataHandler
 * Counts the logins into the system per hour
 */
class DropsMetaDataHandler
{

    /**
     * @var wpdb $dbConnection
     */
    private $dbConnection;

    /**
     * DropsMetaDataHandler constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->dbConnection = $wpdb;
    }

    public function addMetaData()
    {

        $metaData = $this->getCurrentMetaData();

        if (empty($metaData)) {
            $this->insertMetaData();
        }

        $this->updateMetaData($metaData);

    }

    /**
     * Counts the exired sessions from the database
     * @return array
     */
    private function getCurrentMetaData()
    {
        return $this->dbConnection->get_row(
            'SELECT * ' .
            'FROM ' . Config::get('DB_META_TABLE') . ' ' .
            "WHERE login_time = '" . $this->getIndex() . "'",
            ARRAY_A
        );
    }

    private function insertMetaData()
    {
        return $this->dbConnection->insert(
            Config::get('DB_META_TABLE'),
            array(
                'login_time' => $this->getIndex(),
                'login_count' => 1
            ));
    }

    private function updateMetaData($metaData)
    {

        return $this->dbConnection->update(
            Config::get('DB_META_TABLE'),
            array(
                'login_count' => $metaData['login_count'] + 1,
            ),
            array('login_time' => $metaData['login_time'])
        );

    }

    private function getIndex()
    {
        return date('Y-m-d H:00:00', time());
    }

}