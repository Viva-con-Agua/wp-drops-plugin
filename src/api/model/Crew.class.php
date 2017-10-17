<?php

/**
 * Class Crew
 * Data model of a crew of the vca supporter network
 */
class Crew
{

    /** @var string $dropsId */
    private $dropsId;

    /** @var string $name */
    private $name;

    /** @var string $country */
    private $country;

    /** @var string[] $cities */
    private $cities = [];

    /** @var boolean $active */
    private $active;

    /**
     * @return string
     */
    public function getDropsId()
    {
        return $this->dropsId;
    }

    /**
     * @param string $dropsId
     */
    public function setDropsId($dropsId)
    {
        $this->dropsId = $dropsId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string[]
     */
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * @return string|null
     */
    public function getCity($index)
    {
        if (!isset($this->cities[$index])) {
            return null;
        }
        return $this->cities[$index];
    }

    /**
     * @param string $cities
     */
    public function addCity($city)
    {
        $this->cities[] = $city;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

}