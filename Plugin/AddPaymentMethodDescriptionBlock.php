<?php

declare(strict_types=1);

namespace Ecommpay\Hyva\Plugin;

use Ecommpay\Hyva\Block\Checkout\Payment\Description;
use Hyva\Checkout\ViewModel\Checkout\Payment\MethodList;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\MethodInterface as PaymentMethodInterface;

class AddPaymentMethodDescriptionBlock
{
    public function afterGetMethodBlock(MethodList $subject, $result, Template $block, PaymentMethodInterface $method)
    {
        if ($result === false && str_contains($method->getCode(), 'ecommpay')) {
            $result = $block->getLayout()->createBlock(Description::class)->setData('method', $method);
        }

        return $result;
    }
}