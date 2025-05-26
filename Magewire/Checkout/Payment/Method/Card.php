<?php

declare(strict_types=1);

namespace Ecommpay\Hyva\Magewire\Checkout\Payment\Method;

use Ecommpay\Payments\Common\EcpConfigHelper;
use Ecommpay\Payments\Common\RequestBuilder;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magewirephp\Magewire\Component\Form;
use Rakit\Validation\Validator;
use Magento\Checkout\Model\Session;

class Card extends Form
{
    public const DISPLAY_MODE_REDIRECT = 'redirect';
    public const DISPLAY_MODE_EMBEDDED = 'embedded';
    public const DISPLAY_MODE_POPUP = 'popup';

    protected EcpConfigHelper $configHelper;
    protected RequestBuilder $requestBuilder;
    protected Json $json;
    protected Session $checkoutSession;
    protected OrderRepositoryInterface $orderRepository;

    public string $paymentPageUrl = '';
    public string $embeddedFormParams = '';
    public string $paymentId = '';
    public string $successUrl = '';
    public bool $amountValid = false;

    public function __construct(
        EcpConfigHelper $configHelper,
        RequestBuilder $requestBuilder,
        Json $json,
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        Validator $validator
    )
    {
        $this->configHelper = $configHelper;
        $this->requestBuilder = $requestBuilder;
        $this->json = $json;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        parent::__construct($validator);
    }

    public function mount(): void
    {
        $this->paymentPageUrl = sprintf('https://%s', $this->configHelper->getPPHost());
        $this->embeddedFormParams = $this->parseEmbeddedFormParams();
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        return $resultFactory->createSuccess();
    }

    public function cancelOrder(): void
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order->getId() && $order->getState() !== Order::STATE_CANCELED) {
            $order->registerCancellation(__('Order was cancelled due to failed Ecommpay payment.'));
            $this->orderRepository->save($order);
        }
        $this->checkoutSession->restoreQuote();
        $this->embeddedFormParams = $this->parseEmbeddedFormParams();
    }

    public function validateAmount(int $amount): void
    {
        $grandTotal = $this->checkoutSession->getQuote()->getGrandTotal();
        $currencyCode = $this->checkoutSession->getQuote()->getQuoteCurrencyCode();
        $cartPaymentAmount = EcpConfigHelper::priceMultiplyByCurrencyCode($grandTotal, $currencyCode);
        $this->amountValid = (int) $cartPaymentAmount === (int) $amount;
    }

    public function getMode()
    {
        return $this->configHelper->getDisplayMode();
    }

    protected function parseEmbeddedFormParams(): string
    {
        $params = [];
        if ($this->configHelper->getDisplayMode() === self::DISPLAY_MODE_EMBEDDED) {
            $params = $this->requestBuilder->getPaymentPageParamsForEmbeddedMode();
            unset($params['paymentPageUrl']);
        }
        $this->paymentId = $params['payment_id'] ?? '';
        return $this->json->serialize($params);
    }
}