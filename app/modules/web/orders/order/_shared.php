<?php


use Novut\Tools\Files\S3Options;

class web_orders_order_shared extends web_orders_shared
{
    use \Novut\Controllers\Layout12;

    protected $WOrderID;
    /** @var \Sparket\Orders\Order $order */
    protected $order;
    static $autoload;


    function config_common()
    {
        parent::config_common();

        $this->ModuleUrlRoot = "web/orders/order/";
        $this->layout12Init('web_orders_order');

        $this->FormID = "WOrder";


        // order info
        $this->getWOrderInfo();
    }

    protected function view_data_presets_custom(array $data = null)
    {
        if ($this->order && $this->order->getWOrderID()) {
            $data['WOrder'] = $this->order->export();
            $data['WOrderID'] = $this->order->getWOrderID();
        }

        return $data;
    }

    protected function getWOrderInfo()
    {
        $WOrderID = $this->input['WOrderID'];
        $this->order = (new \Sparket\Orders\Order())->find($WOrderID);

        if ($this->order && $this->order->getWOrderID()) {
            $this->WOrderID = $this->order->getWOrderID();
        } else {
            \BN_Responses::alert_error("La orden {$WOrderID} no existe.", \BN_JSHelpers::redirect($this->ModuleUrlRoot));
        }
    }

    protected function getS3Options()
    {
        $S3Options = new S3Options();
        $S3Options->setBucket();

    }

    function validate_status_pending(string $message = "", string $callback = "")
    {
        $message = $message ?: "La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.";

        if ($this->order->getWOrderStatus() != \Sparket\Orders\Status::pending['value']) {
            BN_Responses::notification_error("{$message} No es posible continuar.", $callback);
        }
    }

    function validate_status_payment(string $message = "", string $callback = "")
    {
        $message = $message ?: "La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.";

        if ($this->order->getWOrderStatus() != \Sparket\Orders\Status::payment['value']) {
            BN_Responses::notification_error("{$message} No es posible continuar.", $callback);
        }
    }
    
}

