<?php
/**
 * Copyright © Gsoft All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Barcode\Model;

class BarcodeManagement implements \Gsoft\Barcode\Api\BarcodeManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function getBarcode($param)
    {
        return 'hello api GET return the $param ' . $param;
    }
}

