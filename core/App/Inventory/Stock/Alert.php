<?php


namespace App\Inventory\Stock;


use Novut\Tools\Notification;
use Novut\Tools\Notifications\Send\Send;

class Alert
{

    /** @var \Novut\Db\BN_PDOMethods|\Novut\Db\BN_PDOStatement|\PDO|\PDOStatement $db */
    protected $db;
    /** @var \BN_DB_WEB|\Novut\Db\BN_PDOMethods|\Novut\Db\BN_PDOStatement|\PDO|\PDOStatement $db_web */
    protected $db_web;

    protected $ProductID;


    function __construct($ProductID)
    {
        $this->db = \BN_DB::getInstance();
        $this->db_web = \BN_DB_WEB::getInstance();
        $this->ProductID = $ProductID;
    }

    public function alert(): bool
    {
        $ProductInfo = $this->db->TableInfo('inv_products_products', 'ProductID', $this->ProductID, " AND Cancelled = 0 AND ProductStock > 0");

        if (!$ProductInfo)
        {
            return false;
        }

        $total_subscribers = 0;

        // alert subscribers
        foreach ($this->db_web->GroupInfo('products_stock_alerts', 'ProductID', $this->ProductID, " AND SubscriberStatus = 1 AND Cancelled = 0 ") as $data)
        {
            $total_subscribers++;
            $this->sendAlert($ProductInfo, $data['WUserID'], $data);
        }

        // close subscriptions
        if ($total_subscribers)
        {
            $this->close();
        }
        
        return true;

    }

    public function sendAlert($ProductInfo, $WUserID, array $data = null)
    {
        $notification = new Notification('orders.alerts.enduser.stock');

        $notification->addParams($data);
        $notification->addParam('ProductID', $ProductInfo['ProductID']);
        $notification->addParam('ProductName', $ProductInfo['ProductName']);
        $notification->toWUser($WUserID);
        $notification->send();

    }

    /**
     * @param $WUserID
     * @param string|null $ip
     * @return bool
     */
    public function add($WUserID, string $ip = null): bool
    {
        if (!$WUserID)
        {
            return false;
        }

        $SubscriptionExist = $this->db_web->TableInfo('products_stock_alerts', 'WUserID', $WUserID, " AND SubscriberStatus = 1 AND Cancelled = 0  ");

        if ($SubscriptionExist)
        {
            return false;
        }

        $data['ProductID'] = $this->ProductID;
        $data['SubscriberDate'] = date('Y-m-d H:i:S');
        $data['WUserID'] = $WUserID;
        $data['SubscriberIP'] = $ip ? : \BN_Request::getIP();
        $data['SubscriberStatus'] = 1;
        $data['SubscriberStatusDate'] = $data['SubscriberDate'];

        $this->db_web->Insert('products_stock_alerts' , $data);

        return true;

    }

    public function delete($WUserID)
    {
        $this->db_web->Cancelled('products_stock_alerts', 'WUserID', $WUserID, " AND ProductID = :ProductID AND SubscriberStatus = 1 AND Cancelled = 0", ['ProductID' => $this->ProductID], true);
    }

    protected function close()
    {
        $this->db_web->Update('products_stock_alerts', ['SubscriberStatus' => 2, 'SubscriberStatusDate' => date('Y-m-d H:i:S')], 'ProductID', $this->ProductID, " AND SubscriberStatus = 1 AND Cancelled = 0 ");
    }


    function userSubscriptions($WUserID): array
    {
        $list = [];

        foreach ($this->db_web->GroupInfo('products_stock_alerts', 'WUserID', $WUserID, " AND SubscriberStatus = 1 AND Cancelled = 0 ") as $data)
        {
            $ProductInfo = $this->db->TableInfo('inv_products_products', 'ProductID', $data['ProductID'], " AND Cancelled = 0 ");
            if ($ProductInfo)
            {
                $_data['ID'] = $ProductInfo['ProductID'];
                $_data['Name'] = $ProductInfo['ProductName'];
                $_data['Date'] = $data['SubscriberDate'];

                $list[$ProductInfo['ProductID']] = $_data;
            }
        }

        return $list;
    }


}