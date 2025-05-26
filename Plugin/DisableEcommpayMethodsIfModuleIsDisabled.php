<?php

declare(strict_types=1);

namespace Ecommpay\Hyva\Plugin;

use Ecommpay\Payments\Common\EcpConfigHelper;
use Ecommpay\Payments\Model\EcpAbstractMethod;

class DisableEcommpayMethodsIfModuleIsDisabled
{
    private EcpConfigHelper $configHelper;

    public function __construct(EcpConfigHelper $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    public function afterIsAvailable(EcpAbstractMethod $subject, bool $result): bool
    {
        return $result && $this->configHelper->getPluginEnabled();
    }
}