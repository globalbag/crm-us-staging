<?php


namespace App\Inventory\Stock;


class Sync extends \Novut\Inventory\Stock\Sync
{
    function product($ProductID, $Quantity)
    {
        $this->db()->Update('inv_products_products', ['ProductStock' => $Quantity], 'ProductID', $ProductID);

        if ($Quantity > 0)
        {
            (new Alert($ProductID))->alert();
        }

    }

}