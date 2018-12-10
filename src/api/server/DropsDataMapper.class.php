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
    public static function map($key, $value) {

        switch ($key) {
            case 'pool_lang':
				return self::mapLanguage($value);
            case 'capabilities':
				return self::mapCapabilities($value);
                break;
            case 'secondary_nl':
            case 'nation':
            case 'city':
				return self::mapGeography($value);
                break;
            default:
				break;
        }
		
		return false;

    }
	
	private static function mapGeography($value) {
		$geography = (new DropsGeographyDataHandler)->getEntryByName($value);
		
		if (empty($geography)) {
			return 0;
		}
		
		return $geography->id;	
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
	
	private static function mapCapabilities($capabilities) {
		
		$processedValues = [];
		
		$capabilitiesArray = explode(',', $capabilities);
		
		foreach ($capabilitiesArray AS $role) {
			
			switch ($role) {
				case 'admin':
					$processedValues[] = 'administrator';
					break;
				case 'employee':
					$processedValues[] = 'management_national';
					break;
				case 'volunteerManager':
					$processedValues[] = 'city';
					break;
				case 'supporter':
				default:
					$processedValues[] = 'supporter';
					break;
			}			
			
		}
		
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Mapped capabilities ' . serialize($processedValues) . ' (Line ' . __LINE__ . ')');
		
		return serialize($processedValues);		
		
	}

}