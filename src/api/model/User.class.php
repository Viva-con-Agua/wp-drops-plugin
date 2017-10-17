<?php

/**
 * Class User
 * Data model of a user of the pool
 */
class User
{

    /** @var int $dropsId */
    private $dropsId = 0;

    /** @var int $userId */
    private $userId = 0;

    /** @var  string $firstName */
    private $lastName;

    /** @var  string $lastName */
    private $eMail;

    /** @var  string $eMail */
    private $firstName;

    /** @var string $mobilePhone */
    private $mobilePhone;

    /** @var string $placeOfResidence */
    private $placeOfResidence;

    /** @var int $birthday */
    private $birthday;

    /** @var string $sex */
    private $sex;


    /**
     * @var Crew[] $crew
     */
    private $crews;

    /** @var Role[] $role */
    private $roles;

    /** @var Pillar[] $pillars */
    private $pillars;

    /**
     * @return int
     */
    public function getDropsId()
    {
        return $this->dropsId;
    }

    /**
     * @param int $dropsId
     */
    public function setDropsId($dropsId)
    {
        $this->dropsId = $dropsId;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEMail()
    {
        return $this->eMail;
    }

    /**
     * @param string $eMail
     */
    public function setEMail($eMail)
    {
        $this->eMail = $eMail;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }

    /**
     * @param string $mobilePhone
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = $mobilePhone;
    }

    /**
     * @return string
     */
    public function getPlaceOfResidence()
    {
        return $this->placeOfResidence;
    }

    /**
     * @param string $placeOfResidence
     */
    public function setPlaceOfResidence($placeOfResidence)
    {
        $this->placeOfResidence = $placeOfResidence;
    }

    /**
     * @return int
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param int $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return string
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @param string $sex
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
    }

    /**
     * @return Crew[]
     */
    public function getCrews()
    {
        return $this->crews;
    }

    /**
     * @return Crew|null
     */
    public function getCrew($index)
    {
        if (!isset($this->crews[$index])) {
            return null;
        }
        return $this->crews[$index];
    }

    /**
     * @param Crew $crew
     */
    public function addCrew($crew)
    {
        $this->crews[] = $crew;
    }

    /**
     * @return Role|null
     */
    public function getRole($index)
    {
        if (!isset($this->roles[$index])) {
            return null;
        }
        return $this->roles[$index];
    }

    /**
     * @return Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param Role $roles
     */
    public function addRole($role)
    {
        $this->roles[] = $role;
    }

    /**
     * @return Pillar|null
     */
    public function getPillar($index)
    {
        if (!isset($this->pillars[$index])) {
            return null;
        }
        return $this->pillars[$index];
    }

    /**
     * @return Pillar[]
     */
    public function getPillars()
    {
        return $this->pillars;
    }

    /**
     * @param Pillar $pillar
     */
    public function addPillar($pillar)
    {
        $this->pillars[] = $pillar;
    }

}