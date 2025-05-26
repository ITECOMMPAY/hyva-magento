<?php

declare(strict_types=1);

namespace Ecommpay\Hyva\Service;

use Ecommpay\Payments\Common\OrderPaymentManager;
use Ecommpay\Payments\Common\RequestBuilder;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Hyva\Checkout\Model\Magewire\Payment\AbstractOrderData;
use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class PlaceEmbeddedCardOrderService extends AbstractPlaceOrderService
{
    private const BILLING_FIELDS = [
        "billing_address", "billing_city", "billing_country", "billing_region", "billing_postal", "customer_first_name",
        "customer_last_name", "customer_phone", "customer_email"
    ];

    protected OrderRepositoryInterface $orderRepository;
    protected OrderPaymentManager $orderPaymentManager;
    protected RequestBuilder $requestBuilder;
    protected LoggerInterface $logger;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderPaymentManager $orderPaymentManager,
        RequestBuilder $requestBuilder,
        CartManagementInterface $cartManagement,
        LoggerInterface $logger,
        AbstractOrderData $orderData = null
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderPaymentManager = $orderPaymentManager;
        $this->requestBuilder = $requestBuilder;
        $this->logger = $logger;
        parent::__construct($cartManagement, $orderData);
    }


    public function placeOrder(Quote $quote): int
    {
        $orderId = parent::placeOrder($quote);

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error(
                'Error occurred while placing an order with Ecommpay payment method',
                [
                    'exception' => $e,
                ]
            );
            return $orderId;
        }

        $paymentId = $this->getData()->getData('ecommpayPaymentId');

        $this->orderPaymentManager->insert($orderId, $paymentId);

        $order->setState(Order::STATE_PENDING_PAYMENT);
        $order->setStatus(Order::STATE_PENDING_PAYMENT);
        $order->addStatusToHistory($order->getStatus(), 'The customer made a payment. Waiting for response from payment platform');

        $this->orderRepository->save($order);

        return $orderId;
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory, ?int $orderId = null): EvaluationResultInterface
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error(
                'Error occurred while placing an order with Ecommpay payment method',
                [
                    'exception' => $e,
                ]
            );
            return $resultFactory->createErrorMessage();
        }

        return $resultFactory->createSuccess()->withDetails($this->prepareMicroframeRequestData($order))->withCustomEvent('ecommpay:card:success');
    }

    public function canPlaceOrder(): bool
    {
        return $this->getData()->hasData('ecommpayPaymentId') && $this->getData()->getData('ecommpayPaymentId') !== null;
    }

    public function canRedirect(): bool
    {
        return false;
    }

    protected function prepareMicroframeRequestData(Order $order): array
    {
        $requestData = [];

        $billingInfo = $this->requestBuilder->getBillingDataFromOrder($order);
        $billingInfo = $this->requestBuilder->signer->unsetNullParams($billingInfo);

        foreach ($billingInfo as $fieldName => $value) {
            if ($fieldName === 'billing_country') {
                $requestData["BillingInfo[country]"] = $value;
            } else if (in_array($fieldName, self::BILLING_FIELDS)) {
                $requestData["BillingInfo[" . $fieldName . "]"] = $value;
            } else {
                $requestData[$fieldName] = $value;
            }
        }

        return $requestData;
    }
}