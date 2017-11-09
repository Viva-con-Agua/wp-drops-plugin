<?php

require_once 'Config.class.php';
require_once 'interfaces/UserDataHandlerInterface.class.php';

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 29.09.2017
 * Time: 15:56
 */
class DropsUserDataHandler implements UserDataHandlerInterface
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
    public function createUser($userData)
    {
        if ($this->dbConnection->insert(Config::get('DB_USER_TABLE'), $userData)) {
            return $this->dbConnection->insert_id;
        };
        return false;
    }

    /**
     * Creates an entry for every usermeta data
     * @param int $userId
     * @param array $userMetaData
     * @return bool
     */
    public function createUserMeta($userId, array $userMetaData)
    {
        $return = array();

        foreach ($userMetaData AS $key => $value) {
            $result = $this->dbConnection->insert(
                Config::get('DB_USERMETA_TABLE'),
                array(
                    'user_id' => $userId,
                    'meta_key' => $key,
                    'meta_value' => $value
                )
            );
            $return[$result] = true;
        }

        return !isset($return[false]);

    }

    public function getUser($userData)
    {
        return $this->dbConnection->get_row(
            'SELECT * ' .
            'FROM ' . Config::get('DB_USER_TABLE') . ' ' .
            "WHERE user_email = '" . $userData['user_email'] . "'"
        );
    }

}