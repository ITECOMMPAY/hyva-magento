<?php

declare(strict_types=1);

namespace Ecommpay\Hyva\Block\Checkout\Payment;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\MethodInterface;

class Description extends Template
{
    protected $_template = 'Ecommpay_Hyva::checkout/payment/description.phtml';

    public function getDescription(): string
    {
        $method = $this->getData('method');
        if ($method instanceof MethodInterface && $method->getConfigData('show_description')) {
            return (string) $method->getConfigData('description');
        }
        return '';
    }
}