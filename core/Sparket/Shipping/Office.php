<?php

namespace Sparket\Shipping;

use Novut\Controllers\ErrorsSimple;
use Novut\Db\Orm;

class Office
{
    use Orm;
    use ErrorsSimple;

    /** @var int $OfficeID */
    private $OfficeID;

    /** @var string $OfficeName */
    private $OfficeName;

    /** @var string $OfficeContactEmail */
    private $OfficeContactEmail;

    /** @var string $OfficeContactPhone */
    private $OfficeContactPhone;

    /** @var string $OfficeAddressAddress */
    private $OfficeAddressAddress;

    /** @var string $OfficeAddressNeighborhood */
    private $OfficeAddressNeighborhood;

    /** @var string $OfficeAddressCity */
    private $OfficeAddressCity;

    /** @var int $OfficeAddressStateID */
    private $OfficeAddressStateID;

    /** @var string $OfficeAddressStateName */
    private $OfficeAddressStateName;

    /** @var int $OfficeAddressCountryID */
    private $OfficeAddressCountryID;

    /** @var string $OfficeAddressCountryName */
    private $OfficeAddressCountryName;

    /** @var string $OfficeAddressZipCode */
    private $OfficeAddressZipCode;

    /** @var string $OfficeAddressFull */
    private $OfficeAddressFull;

    /** @var int $Cancelled */
    private $Cancelled;

    /** string $CancelledInfo */
    private $CancelledInfo;


    function __construct(self $office = null)
    {
        $this->setOptions(Offices::getORMOptions());

        if ($office)
        {
            $this->load($office);
        }
    }

    /**
     * @return string
     */
    public function getOfficeID(): ?string
    {
        return $this->OfficeID;
    }

    /**
     * @param int $OfficeID
     */
    public function setOfficeID(string $OfficeID): void
    {
        $this->OfficeID = $OfficeID;
    }

    /**
     * @return string
     */
    public function getOfficeName(): ?string
    {
        return $this->OfficeName;
    }

    /**
     * @param string $OfficeName
     */
    public function setOfficeName(string $OfficeName): void
    {
        $this->OfficeName = $OfficeName;
    }

    /**
     * @return string
     */
    public function getOfficeContactEmail(): string
    {
        return $this->OfficeContactEmail;
    }

    /**
     * @param string $OfficeContactEmail
     */
    public function setOfficeContactEmail(string $OfficeContactEmail): void
    {
        $this->OfficeContactEmail = $OfficeContactEmail;
    }

    /**
     * @return string
     */
    public function getOfficeContactPhone(): string
    {
        return $this->OfficeContactPhone;
    }

    /**
     * @param string $OfficeContactPhone
     */
    public function setOfficeContactPhone(string $OfficeContactPhone): void
    {
        $this->OfficeContactPhone = $OfficeContactPhone;
    }

    /**
     * @return string
     */
    public function getOfficeAddressAddress(): string
    {
        return $this->OfficeAddressAddress;
    }

    /**
     * @param string $OfficeAddressAddress
     */
    public function setOfficeAddressAddress(string $OfficeAddressAddress): void
    {
        $this->OfficeAddressAddress = $OfficeAddressAddress;
    }

    /**
     * @return string
     */
    public function getOfficeAddressNeighborhood(): ?string
    {
        return $this->OfficeAddressNeighborhood;
    }

    /**
     * @param string $OfficeAddressNeighborhood
     */
    public function setOfficeAddressNeighborhood(string $OfficeAddressNeighborhood): void
    {
        $this->OfficeAddressNeighborhood = $OfficeAddressNeighborhood;
    }

    /**
     * @return string
     */
    public function getOfficeAddressCity(): string
    {
        return $this->OfficeAddressCity;
    }

    /**
     * @param string $OfficeAddressCity
     */
    public function setOfficeAddressCity(string $OfficeAddressCity): void
    {
        $this->OfficeAddressCity = $OfficeAddressCity;
    }

    /**
     * @return int
     */
    public function getOfficeAddressStateID(): ?int
    {
        return $this->OfficeAddressStateID;
    }

    /**
     * @param int $OfficeAddressStateID
     */
    public function setOfficeAddressStateID(int $OfficeAddressStateID): void
    {
        $this->OfficeAddressStateID = $OfficeAddressStateID;
    }

    /**
     * @return string
     */
    public function getOfficeAddressStateName(): string
    {
        return $this->OfficeAddressStateName;
    }

    /**
     * @param string $OfficeAddressStateName
     */
    public function setOfficeAddressStateName(string $OfficeAddressStateName): void
    {
        $this->OfficeAddressStateName = $OfficeAddressStateName;
    }

    /**
     * @return int
     */
    public function getOfficeAddressCountryID(): ?int
    {
        return $this->OfficeAddressCountryID;
    }

    /**
     * @param int $OfficeAddressCountryID
     */
    public function setOfficeAddressCountryID(int $OfficeAddressCountryID): void
    {
        $this->OfficeAddressCountryID = $OfficeAddressCountryID;
    }

    /**
     * @return string
     */
    public function getOfficeAddressCountryName(): string
    {
        return $this->OfficeAddressCountryName;
    }

    /**
     * @param string $OfficeAddressCountryName
     */
    public function setOfficeAddressCountryName(string $OfficeAddressCountryName): void
    {
        $this->OfficeAddressCountryName = $OfficeAddressCountryName;
    }

    /**
     * @return string
     */
    public function getOfficeAddressZipCode(): ?string
    {
        return $this->OfficeAddressZipCode;
    }

    /**
     * @param string $OfficeAddressZipCode
     */
    public function setOfficeAddressZipCode(string $OfficeAddressZipCode): void
    {
        $this->OfficeAddressZipCode = $OfficeAddressZipCode;
    }

    /**
     * @return string
     */
    public function getOfficeAddressFull(): ?string
    {
        return $this->OfficeAddressFull;
    }

    /**
     * @param string $OfficeAddressFull
     */
    public function setOfficeAddressFull(string $OfficeAddressFull): void
    {
        $this->OfficeAddressFull = $OfficeAddressFull;
    }

    /**
     * @return int
     */
    public function getCancelled(): int
    {
        return $this->Cancelled;
    }

    /**
     * @param int $Cancelled
     */
    public function setCancelled(int $Cancelled): void
    {
        $this->Cancelled = $Cancelled;
    }

    /**
     * @return mixed
     */
    public function getCancelledInfo()
    {
        return $this->CancelledInfo;
    }

    /**
     * @param mixed $CancelledInfo
     */
    public function setCancelledInfo($CancelledInfo): void
    {
        $this->CancelledInfo = $CancelledInfo;
    }

    function add_before()
    {
        if(!$this->getOfficeAddressZipCode())
        {
            $this->error_set("El C.P. no ha sido definido.");
        }

        $PostalInfo = (new \Sparket\Tools\Address\Postal)->info_by_code($this->getOfficeAddressZipCode(), $this->getOfficeAddressCountryID() ? : 1, $this->getOfficeAddressNeighborhood());

        if(!$PostalInfo)
        {
            $this->error_set("El C.P. no es v&aacute;lido.");
        }

        if(!$this->getOfficeAddressNeighborhood())
        {
            $this->error_set("La Colonia / Poblaci&oacute;n no ha sido definido.");
        }

        $this->setOfficeAddressStateName($PostalInfo['PostalState']);
        $this->setOfficeAddressNeighborhood($PostalInfo['PostalNeighborhood']);
        $this->setOfficeAddressZipCode($PostalInfo['PostalCode']);

        $this->setOfficeAddressCity($PostalInfo['PostalCity']);
        $this->setOfficeAddressStateID($PostalInfo['StateID']);

        $this->setOfficeAddressStateID($PostalInfo['StateID']);
        $this->setOfficeAddressStateName($PostalInfo['PostalState']);
        $this->setOfficeAddressCountryID($PostalInfo['CountryID']);
        $this->setOfficeAddressCountryName((new \Sparket\Tools\Address\Countries())->name($this->getOfficeAddressCountryID()));

        $this->setOfficeAddressFull("{$this->getOfficeAddressAddress()}, {$this->getOfficeAddressNeighborhood()}, {$this->getOfficeAddressCity()}, {$this->getOfficeAddressStateName()}, {$this->getOfficeAddressZipCode()}.");
    }

    function save_before()
    {
        if(!$this->getOfficeID())
        {
            $this->error_set("La sucursal no ha sido definida.");
        }

        $PostalInfo = (new \Sparket\Tools\Address\Postal)->info_by_code($this->getOfficeAddressZipCode(), $this->getOfficeAddressCountryID() ? : 1, $this->getOfficeAddressNeighborhood());

        if(!$PostalInfo)
        {
            $this->error_set("El C.P. no es v&aacute;lido.");
        }

        if(!$this->getOfficeAddressNeighborhood())
        {
            $this->error_set("La Colonia / Poblaci&oacute;n no ha sido definido.");
        }

        $this->setOfficeAddressStateName($PostalInfo['PostalState']);
        $this->setOfficeAddressNeighborhood($PostalInfo['PostalNeighborhood']);
        $this->setOfficeAddressZipCode($PostalInfo['PostalCode']);

        $this->setOfficeAddressCity($PostalInfo['PostalCity']);
        $this->setOfficeAddressStateID($PostalInfo['StateID']);

        $this->setOfficeAddressStateID($PostalInfo['StateID']);
        $this->setOfficeAddressStateName($PostalInfo['PostalState']);
        $this->setOfficeAddressCountryID($PostalInfo['CountryID']);
        $this->setOfficeAddressCountryName((new \Sparket\Tools\Address\Countries())->name($this->getOfficeAddressCountryID()));

        $this->setOfficeAddressFull("{$this->getOfficeAddressAddress()}, {$this->getOfficeAddressNeighborhood()}, {$this->getOfficeAddressCity()}, {$this->getOfficeAddressStateName()}, {$this->getOfficeAddressZipCode()}.");

    }

}