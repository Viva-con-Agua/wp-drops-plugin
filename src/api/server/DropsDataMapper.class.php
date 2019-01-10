<?php

/**
 * Class DropsDataMapper
 * Defines the usage of the server functions to handle calls from drops
 */
class DropsDataMapper
{
	
	public static $mappedFields = ['pool_lang', 'secondary_nl', 'nation', 'wp_capabilities', 'birthday', 'crew_id'];

    /**
     * Maps data from drops to pool1
     * @param DropsResponse $response
     */
    public static function map($key, $value) {
		
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Mapping field ' . $key . ' (Line ' . __LINE__ . ')');

        switch ($key) {
            case 'pool_lang':
				return self::mapLanguage($value);
            case 'wp_capabilities':
				return self::mapCapabilities($value);
                break;
            case 'region':
				return self::mapRegions($value);
                break;
            case 'birthday':
				return self::mapBirthday($value);
                break;
            case 'secondary_nl':
            case 'nation':
				return self::mapGeography($value);
                break;
            case 'crew_id':
				return self::mapDropsGeography($value);
                break;
            default:
				break;
        }
		
		return false;

    }
	
	private static function mapRegions($value) 
	{
		
		$ancestorGeography = (new DropsGeographyDataHandler)->getHierarchyEntryById($value);
		
		if (empty($ancestorGeography)) {
			return $value;
		}
		
		$ancestorGeography->id;	
		
	}
	
	private static function mapDropsGeography($value) {
		
		$value = str_replace('-', '', $value);
		$value = strtoupper($value);
		
		$geography = (new DropsGeographyMappingDataHandler)->getEntryByDropsId($value);
		
		if (empty($geography)) {
			(new DropsLogger(''))->log(DropsLogger::ERROR, 'Mapping for drops geography not found: ' . $value . ' (Line ' . __LINE__ . ')');
			return 0;
		}
		
		return $geography;
		
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
	
	private static function mapBirthday($value) {
		return $value / 1000;
	}
	
	private static function mapCapabilities($capabilities) {
		
		if (empty($capabilities)) {
			return $capabilities;
		}
		
		$processedValues = [];
		
		$capabilitiesArray = explode(';', $capabilities);
				
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Mapping capabilities ' . serialize($capabilitiesArray) . ' (Line ' . __LINE__ . ')');
		
		foreach ($capabilitiesArray AS $role) {
			
			switch ($role) {
				case 'admin':
					$processedValues['administrator'] = true;
					break;
				case 'employee':
				
					if (!in_array('admin', $capabilitiesArray)) 
					{
						$processedValues['management_national'] = true;
					}
					break;
				case 'volunteerManager':
					if (!in_array('admin', $capabilitiesArray)
						&& !in_array('employee', $capabilitiesArray)) 
					{
						$processedValues['city'] = true;
					}
					break;
				case 'supporter':
				default:
					if (!in_array('admin', $capabilitiesArray)
						&& !in_array('employee', $capabilitiesArray)
						&& !in_array('volunteerManager', $capabilitiesArray)) 
					{
						$processedValues['supporter'] = true;
					}
				
					break;
			}			
			
		}
		
		(new DropsLogger(''))->log(DropsLogger::DEBUG, 'Mapped capabilities ' . serialize($processedValues) . ' (Line ' . __LINE__ . ')');
		
		return serialize($processedValues);		
		
	}

}