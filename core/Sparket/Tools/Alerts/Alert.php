<?php


namespace Sparket\Tools\Alerts;

use Novut\Controllers\ErrorsSimple;
use Novut\Core\Query;
use Novut\Db\Orm;
use Novut\Db\OrmOptions;
use Sparket\DB\Web;

class Alert
{
    use Orm;

    use ErrorsSimple;

    /** @var int $AlertID */
    private $AlertID;

    /** @var string $AlertDate */
    private $AlertDate;

    /** @var string $ContactName */
    private $ContactName;

    /** @var string $ContactEmail */
    private $ContactEmail;

    /** @var string $AlertSubject */
    private $AlertSubject;

    /** @var string $AlertMessage */
    private $AlertMessage;

    /** @var int $AlertStatus */
    private $AlertStatus;

    /** @var string $AlertSend */
    private $AlertSend;

    /** @var int $CallerID */
    private $CallerID;

    /** @var string $CallerModule */
    private $CallerModule;

    /** @var int $Cancelled */
    private $Cancelled;

    /** string $CancelledInfo */
    private $CancelledInfo;


    function __construct(self $alert = null)
    {
        $options = (new OrmOptions);
        $options->setDb(Web::getDB());
        $options->setTableName('tools_alerts');
        $options->setPrimaryKey('AlertID');
        $options->setCancelled(true);

        $this->setOptions($options);

        if ($alert)
        {
            $this->load($alert);
        }
    }

    /**
     * @return int
     */
    public function getAlertID(): ?int
    {
        return $this->AlertID;
    }

    /**
     * @param int $AlertID
     */
    public function setAlertID(int $AlertID): void
    {
        $this->AlertID = $AlertID;
    }

    /**
     * @return string
     */
    public function getAlertDate(): string
    {
        return $this->AlertDate;
    }

    /**
     * @param string $AlertDate
     */
    public function setAlertDate(string $AlertDate): void
    {
        $this->AlertDate = $AlertDate;
    }

    /**
     * @return string
     */
    public function getContactName(): string
    {
        return $this->ContactName;
    }

    /**
     * @param string $ContactName
     */
    public function setContactName(string $ContactName): void
    {
        $this->ContactName = $ContactName;
    }

    /**
     * @return string
     */
    public function getContactEmail(): string
    {
        return $this->ContactEmail;
    }

    /**
     * @param string $ContactEmail
     */
    public function setContactEmail(string $ContactEmail): void
    {
        $this->ContactEmail = $ContactEmail;
    }

    /**
     * @return string
     */
    public function getAlertSubject(): string
    {
        return $this->AlertSubject;
    }

    /**
     * @param string $AlertSubject
     */
    public function setAlertSubject(string $AlertSubject): void
    {
        $this->AlertSubject = $AlertSubject;
    }

    /**
     * @return string
     */
    public function getAlertMessage(): string
    {
        return $this->AlertMessage;
    }

    /**
     * @param string $AlertMessage
     */
    public function setAlertMessage(string $AlertMessage): void
    {
        $this->AlertMessage = $AlertMessage;
    }

    /**
     * @return int
     */
    public function getAlertStatus(): int
    {
        return $this->AlertStatus;
    }

    /**
     * @param int $AlertStatus
     */
    public function setAlertStatus(int $AlertStatus): void
    {
        $this->AlertStatus = $AlertStatus;
    }

    /**
     * @return string
     */
    public function getAlertSend(): string
    {
        return $this->AlertSend;
    }

    /**
     * @param string $AlertSend
     */
    public function setAlertSend(string $AlertSend): void
    {
        $this->AlertSend = $AlertSend;
    }

    /**
     * @return int
     */
    public function getCallerID(): int
    {
        return $this->CallerID;
    }

    /**
     * @param int $CallerID
     */
    public function setCallerID(int $CallerID): void
    {
        $this->CallerID = $CallerID;
    }

    /**
     * @return string
     */
    public function getCallerModule(): string
    {
        return $this->CallerModule;
    }

    /**
     * @param string $CallerModule
     */
    public function setCallerModule(string $CallerModule): void
    {
        $this->CallerModule = $CallerModule;
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
        if(!$this->getContactName())
        {
            $this->error_set("El nombre del remitente no ha sido definido.");
        }

        if(!$this->getContactEmail())
        {
            $this->error_set("El correo del remitente no ha sido definido.");
        }

        if(!$this->getAlertSubject())
        {
            $this->error_set("El asunto no ha sido definido.");
        }

        if(!$this->getAlertMessage())
        {
            $this->error_set("El mensaje no ha sido definido.");
        }

        $this->setAlertDate(date('Y-m-d H:i:s'));
        $this->setAlertStatus(1);
    }

    function send($id = "")
    {
        if(!$this->getAlertID())
        {
            $this->find($id);
        }

        if(!$this->getAlertID())
        {
            $this->error_set("El id de la notificación no ha sido definido.");
        }

        $email_body = \BN_Load::file(__DIR__ . '/templates/body.twig');

        $email_data['AlertInfo'] = $this->export();

        $email_to = ['Name' => $this->getContactName(), 'Email' => $this->getContactEmail()];

        if(\Sparket\Tools\Email\Email::email_notification($this->getAlertSubject(), $email_body, $email_data, $email_to))
        {
            $sql_update['AlertStatus'] = 2;
            $sql_update['AlertSend'] = date('Y-m-d H:i:s');

            $this->_options->getDb()->Update($this->getTableName(), $sql_update, $this->getPrimaryKey(), $this->getAlertID());
        }
    }
}