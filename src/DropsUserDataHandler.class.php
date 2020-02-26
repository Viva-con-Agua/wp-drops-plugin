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

    public function getUserByEMail($email)
    {
        return $this->dbConnection->get_row(
            'SELECT * ' .
            'FROM ' . Config::get('DB_USER_TABLE') . ' ' .
            "WHERE user_email = '" . $email . "'"
        );
    }

    public function getUserById($userId)
    {
        return $this->dbConnection->get_row(
            'SELECT * ' .
            'FROM ' . Config::get('DB_USER_TABLE') . ' ' .
			'WHERE ID = "' . $userId . '"'
        );
    }

    public function getUserMetaById($userId)
    {
        return $this->dbConnection->get_var(
            'SELECT meta_value ' .
            'FROM ' . Config::get('DB_USERMETA_TABLE') . ' ' .
            'WHERE meta_key = "mail_switch" AND user_id = "' . $userId . '"'
        );
    }

    public function getUsersMetaById()
    {

        var_dump('SELECT user_email, meta_value ' .
            'FROM ' . Config::get('DB_USER_TABLE') . ' ' .
            'JOIN ' . Config::get('DB_USERMETA_TABLE') . ' ON ID = user_id ' .
            'WHERE meta_key = "mail_switch"');
        return $this->dbConnection->get_results(
            'SELECT user_email, meta_value ' .
            'FROM ' . Config::get('DB_USER_TABLE') . ' ' .
            'JOIN ' . Config::get('DB_USERMETA_TABLE') . ' ON ID = user_id ' .
            'WHERE meta_key = "mail_switch"'
        );
    }

    public function updateUser($userId, array $userData, array $userMetaData)
    {
		
		$returnValue['true'] = 1;
		
		if (!empty($userData)) {
			
			$userDataSql = implode(', ', array_map(
				function ($v, $k) { return sprintf('%s=\'%s\'', $k, $v); },
				$userData,
				array_keys($userData)
			));
			
			$updateSql = 'UPDATE ' . Config::get('DB_USER_TABLE') . ' SET ' .
				$userDataSql . ' ' .
				'WHERE ID = "' . $userId . '"';
			
			$returnValueKey = $this->dbConnection->query($updateSql);
			
			if($returnValueKey === false) {
				$returnValue[false] = 1;
			}
			
		}
		
		if (!empty($userMetaData)) {
			
			foreach ($userMetaData AS $metaKey => $metaValue) {
				
				$updateSql = 'UPDATE ' . Config::get('DB_USERMETA_TABLE') . ' SET ' .
					'meta_value = \'' . $metaValue . '\' ' . 
					'WHERE user_id = "' . $userId . '" and meta_key = "' . $metaKey . '"';
				
				$returnValueKey = $this->dbConnection->query($updateSql);
				
				if($returnValueKey === false) {
					$returnValue[false] = 1;
				}
				
			}
			
		}
		
		return !(isset($returnValue[false]));
		
    }
	
	public function deleteUser($userId) {
		$deleteSql = 'DELETE FROM ' . Config::get('DB_USER_TABLE') . ' ' .
			'WHERE ID = "' . $userId . '"';
		return $this->dbConnection->query($deleteSql);
	}
	
	public function deleteUserMeta($userId) {
		$deleteSql = 'DELETE FROM ' . Config::get('DB_USERMETA_TABLE') . ' ' .
			'WHERE user_id = "' . $userId . '"';
		return $this->dbConnection->query($deleteSql);		
	}

}