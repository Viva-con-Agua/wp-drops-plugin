<?php

/**
 * Class Pillar
 * Data model of a user of the pool
 */
class Pillar
{

    const OPERATION = 'operation';
    const FINANCE   = 'finance';
    const EDUCATION = 'education';
    const NETWORK   = 'network';

    private $validTypes = [
        self::OPERATION,
        self::FINANCE,
        self::EDUCATION,
        self::NETWORK,
    ];

    /** @var string $type */
    private $type;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        if (!in_array($type, $this->validTypes)) {
            return false;
        }

        $this->type = $type;

        return true;
    }

}