<?php

/**
 * Class DropsDataMapper
 * Defines the usage of the server functions to handle calls from drops
 */
class DropsDataMapper
{

    /**
     * Maps data from drops to pool1
     * @param DropsResponse $response
     */
    public static function map($key, $value)
    {

        switch ($key) {
            case 'pool_lang':
				return self::mapLanguage($value);
                break;
            case 'secondary_nl':
				return self::mapGeography($value);
                break;
            case 'nation':
				return self::mapGeography($value);
                break;
            case 'city':
				return self::mapGeography($value);
                break;
            default:
				break;
        }
		
		return false;

    }
	
	private static function mapGeography($value) {
		global $wpdb;
	
		$geographyId = $wpdb->get_var(
            'SELECT id ' .
            'FROM ' . Config::get('DB_GEOGRAPHY') . ' ' .
            'WHERE name = "' . $value . '"'
		);
		
		if (empty($geographyId)) {
			return false;
		}
		
		return $geographyId;
		
	}
	
	private static function mapLanguage($language) {
		
		switch ($language) {
			case 'de_DE':
			case 'de_AT':
			case 'de_CH':
				return 'de';
				break;
			default:
				return 'en';
		}
		
	}

}