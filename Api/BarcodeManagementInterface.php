<?php
/**
 * Copyright © Gsoft All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Barcode\Api;

interface BarcodeManagementInterface
{

    /**
     * GET for Barcode api
     * @param string $param
     * @return string
     */
    public function getBarcode($param);
}

