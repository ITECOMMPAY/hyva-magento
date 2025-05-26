<?php

declare(strict_types=1);

namespace Ecommpay\Hyva\Service;

use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;

class PlacePopupCardOrderService extends AbstractPlaceOrderService
{
    public function canRedirect(): bool
    {
        return false;
    }
}