<?php

declare(strict_types=1);

namespace Ecommpay\Hyva\ViewModel;

use Ecommpay\Payments\Common\EcpConfigHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class EcpConfig implements ArgumentInterface
{
    private EcpConfigHelper $configHelper;

    public function __construct(EcpConfigHelper $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    public function getCardDisplayMode(): string
    {
        return (string) $this->configHelper->getDisplayMode();
    }
}