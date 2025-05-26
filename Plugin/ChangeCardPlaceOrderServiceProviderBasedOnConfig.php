<?php

declare(strict_types=1);

namespace Ecommpay\Hyva\Plugin;

use Ecommpay\Hyva\Magewire\Checkout\Payment\Method\Card;
use Ecommpay\Payments\Common\EcpConfigHelper;
use Hyva\Checkout\Model\Magewire\Payment\PlaceOrderServiceInterface;
use Hyva\Checkout\Model\Magewire\Payment\PlaceOrderServiceProvider;

class ChangeCardPlaceOrderServiceProviderBasedOnConfig
{
    private EcpConfigHelper $configHelper;

    public function __construct(EcpConfigHelper $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    public function afterGetByCode(
        PlaceOrderServiceProvider $subject,
        PlaceOrderServiceInterface $result,
        string $code
    ): PlaceOrderServiceInterface
    {
        if ($code === 'ecommpay_card') {
            if ($this->configHelper->getDisplayMode() === Card::DISPLAY_MODE_REDIRECT) {
                return $subject->getByCode('ecommpay_card_redirect');
            } else if ($this->configHelper->getDisplayMode() === Card::DISPLAY_MODE_POPUP) {
                return $subject->getByCode('ecommpay_card_popup');
            }
        }

        return $result;
    }
}