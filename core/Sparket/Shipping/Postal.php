<?php

namespace Sparket\Shipping;

use Novut\Controllers\ErrorsSimple;
use Novut\Core\Query;
use Novut\Db\Orm;

class Postal
{
    use Orm;
    use ErrorsSimple;

    /** @var int $PostalID */
    private $PostalID;

    /** @var string $PostalCode */
    private $PostalCode;

    /** @var string $PostalCity */
    private $PostalCity;

    /** @var int $PostalStateID */
    private $PostalStateID;

    /** @var string $PostalStateName */
    private $PostalStateName;

    /** @var int $PostalCountryID */
    private $PostalCountryID;

    /** @var string $PostalCountryName */
    private $PostalCountryName;

    /** @var string $PostalPeriodicity */
    private $PostalPeriodicity;

    /** @var string $PostalDays */
    private $PostalDays;

    /** @var int $Cancelled */
    private $Cancelled;

    /** string $CancelledInfo */
    private $CancelledInfo;

    function __construct(self $postal = null)
    {
        $this->setOptions(Postals::getORMOptions());

        if ($postal)
        {
            $this->load($postal);
        }
    }

    /**
     * @return int
     */
    public function getPostalID(): ?int
    {
        return $this->PostalID;
    }

    /**
     * @param int $PostalID
     */
    public function setPostalID(int $PostalID): void
    {
        $this->PostalID = $PostalID;
    }

    /**
     * @return string
     */
    public function getPostalCode(): ?string
    {
        return $this->PostalCode;
    }

    /**
     * @param string $PostalCode
     */
    public function setPostalCode(string $PostalCode): void
    {
        $this->PostalCode = $PostalCode;
    }

    /**
     * @return string
     */
    public function getPostalCity(): ?string
    {
        return $this->PostalCity;
    }

    /**
     * @param string $PostalCity
     */
    public function setPostalCity(string $PostalCity): void
    {
        $this->PostalCity = $PostalCity;
    }

    /**
     * @return int
     */
    public function getPostalStateID(): ?int
    {
        return $this->PostalStateID;
    }

    /**
     * @param int $PostalStateID
     */
    public function setPostalStateID(int $PostalStateID = 0): void
    {
        $this->PostalStateID = $PostalStateID;
    }

    /**
     * @return string
     */
    public function getPostalStateName(): ?string
    {
        return $this->PostalStateName;
    }

    /**
     * @param string $PostalStateName
     */
    public function setPostalStateName(string $PostalStateName = ""): void
    {
        $this->PostalStateName = $PostalStateName;
    }

    /**
     * @return int
     */
    public function getPostalCountryID(): ?int
    {
        return $this->PostalCountryID;
    }

    /**
     * @param int $PostalCountryID
     */
    public function setPostalCountryID(int $PostalCountryID = 0): void
    {
        $this->PostalCountryID = $PostalCountryID;
    }

    /**
     * @return string
     */
    public function getPostalCountryName(): ?string
    {
        return $this->PostalCountryName;
    }

    /**
     * @param string $PostalCountryName
     */
    public function setPostalCountryName(string $PostalCountryName = ""): void
    {
        $this->PostalCountryName = $PostalCountryName;
    }

    /**
     * @return string
     */
    public function getPostalPeriodicity(): ?string
    {
        return $this->PostalPeriodicity;
    }

    /**
     * @param string $PostalPeriodicity
     */
    public function setPostalPeriodicity(string $PostalPeriodicity = ""): void
    {
        $this->PostalPeriodicity = $PostalPeriodicity;
    }

    /**
     * @return string
     */
    public function getPostalDays(): ?string
    {
        return $this->PostalDays;
    }

    /**
     * @param string $PostalDays
     */
    public function setPostalDays(string $PostalDays = ""): void
    {
        $this->PostalDays = $PostalDays;
    }

    /**
     * @return int
     */
    public function getCancelled(): ?int
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

}