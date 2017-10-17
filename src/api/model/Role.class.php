<?php

/**
 * Class Role
 * Data model of a role in the vca network
 */
class Role
{

    const SUPPORTER         = 'supporter';
    const VOLUNTEER_MANAGER = 'volunteerManager';
    const EMPLOYEE          = 'employee';
    const ADMIN             = 'admin';

    private $validTypes = [
        self::SUPPORTER,
        self::VOLUNTEER_MANAGER,
        self::EMPLOYEE,
        self::ADMIN,
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
     * @return bool
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