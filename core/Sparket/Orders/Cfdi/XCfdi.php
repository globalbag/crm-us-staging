<?php


namespace Sparket\Orders\Cfdi;


use Novut\Db\Orm;
use Novut\Db\OrmOptions;
use Sparket\Orders\Order;

class XCfdi
{

    use Orm;

    protected $WOCFDIID;
    protected $WOrderID;
    protected $WOCFDIDate;
    protected $WOCFDIType;
    protected $WOCFDICurrency;
    protected $WOCFDIFrom;
    protected $WOCFDITo;
    protected $WOCFDITotal;
    protected $WOCFDICode;
    protected $WOCFDIFFiscal;
    protected $WOCFDIPDF;
    protected $WOCFDIXML;
    protected $WOCFDICancelled;
    protected $WOCFDIReplacement;
    protected $Cancelled;
    protected $CancelledInfo;
    protected $_xml_source;
    protected $_xml_data;
    protected $_cfdi_type;

    function __construct(Order $order, $cfdi = null)
    {

        $this->setOptions(Cfdis::getORMOptions());

        $this->order = $order;

        if(!$this->order->getWOrderID() || ($this->order->getWOrderID() && $this->order->getCancelled()))
        {
            \BN_Responses::dev("La orden no existe.");
        }

        if ($cfdi)
        {
            $this->find($cfdi);

            if($this->getWOCFDIID() && $this->order->getWOrderID() != $this->getWOrderID())
            {
                \BN_Responses::dev("El CFDI no pertenece a la orden.");
            }
        }
    }

    /**
     * @return mixed
     */
    public function getWOCFDIID()
    {
        return $this->WOCFDIID;
    }

    /**
     * @return mixed
     */
    public function getWOrderID()
    {
        return $this->WOrderID;
    }

    /**
     * @return mixed
     */
    public function getWOCFDIDate()
    {
        return $this->WOCFDIDate;
    }

    /**
     * @return mixed
     */
    public function getWOCFDIType()
    {
        return $this->WOCFDIType;
    }

    /**
     * @return mixed
     */
    public function getWOCFDICurrency()
    {
        return $this->WOCFDICurrency;
    }

    /**
     * @return mixed
     */
    public function getWOCFDIFrom()
    {
        return $this->WOCFDIFrom;
    }

    /**
     * @return mixed
     */
    public function getWOCFDITo()
    {
        return $this->WOCFDITo;
    }

    /**
     * @return mixed
     */
    public function getWOCFDITotal()
    {
        return $this->WOCFDITotal;
    }

    /**
     * @return mixed
     */
    public function getWOCFDICode()
    {
        return $this->WOCFDICode;
    }

    /**
     * @return mixed
     */
    public function getWOCFDIFFiscal()
    {
        return $this->WOCFDIFFiscal;
    }

    /**
     * @return mixed
     */
    public function getWOCFDIPDF()
    {
        return $this->WOCFDIPDF;
    }

    /**
     * @return mixed
     */
    public function getWOCFDIXML()
    {
        return $this->WOCFDIXML;
    }

    /**
     * @return mixed
     */
    public function getWOCFDICancelled()
    {
        return $this->WOCFDICancelled;
    }

    /**
     * @return mixed
     */
    public function getWOCFDIReplacement()
    {
        return $this->WOCFDIReplacement;
    }

    /**
     * @return mixed
     */
    public function getCancelled()
    {
        return $this->Cancelled;
    }

    /**
     * @param mixed $Cancelled
     */
    public function setCancelled($Cancelled): void
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

    public function add($xml, string $xml_file, string $pdf_file)
    {


        if ($xml)
        {
            // parse xml
            $data = Tools::parseXML($xml);

            if ($data && $data['Comprobante'] && in_array($data['Comprobante']['TipoDeComprobante'], ['I', 'P']))
            {
                $this->WOrderID = $this->order->getWOrderID();
                $this->WOCFDIDate = $data['Comprobante']['Fecha'];
                $this->WOCFDIType = $data['Comprobante']['TipoDeComprobante'] == "I" ? 'invoice' : 'preceipt';
                $this->WOCFDICurrency = $data['Comprobante']['Moneda'];
                $this->WOCFDIFrom = $data['Comprobante']['Emisor']['Rfc'];
                $this->WOCFDITo = $data['Comprobante']['Receptor']['Rfc'];
                $this->WOCFDITotal = $data['Comprobante']['Total'];
                $this->WOCFDICode = $data['Comprobante']['Serie'].$data['Comprobante']['Folio'];
                $this->WOCFDIFFiscal = $data['Comprobante']['Complemento']['TimbreFiscalDigital']['UUID'];

                $this->WOCFDIXML = $xml_file;
                $this->WOCFDIPDF = $pdf_file;

                $this->_add();
            }


        }
    }

    public function update()
    {

        if ($this->_cfdi_type == 'invoices')
        {
            Invoices::updateOrder($this->order);
        }
        else if ($this->_cfdi_type == 'preceipt')
        {
            PReceipts::updateOrder($this->order);
        }

    }
    public function cancel_after()
    {

        if ($this->_cfdi_type == 'invoices')
        {
            Invoices::updateOrder($this->order);
        }
        else if ($this->_cfdi_type == 'preceipt')
        {
            PReceipts::updateOrder($this->order);
        }

    }


}