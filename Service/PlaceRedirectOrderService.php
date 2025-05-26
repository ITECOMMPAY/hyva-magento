<?php

declare(strict_types=1);

namespace Ecommpay\Hyva\Service;

use Ecommpay\Payments\Common\RequestBuilder;
use GuzzleHttp\ClientInterface;
use Hyva\Checkout\Model\Magewire\Payment\AbstractOrderData;
use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

class PlaceRedirectOrderService extends AbstractPlaceOrderService
{
    private const MAGENTO_METHOD_NAME_TO_PAYMENT_PAGE_METHOD_NAME_MAPPING = [
        'ecommpay_card' => 'card',
        'ecommpay_paypal' => 'paypal-wallet',
        'ecommpay_paypal_paylater' => 'paypal-wallet',
        'ecommpay_applepay' => 'apple_pay_core',
        'ecommpay_googlepay' => 'google_pay_host',
        'ecommpay_open_banking' => 'open_banking',
        'ecommpay_sofort' => 'sofort',
        'ecommpay_ideal' => 'ideal',
        'ecommpay_blik' => 'blik',
        'ecommpay_giropay' => 'giropay',
        'ecommpay_klarna' => 'klarna',
        'ecommpay_neteller' => 'neteller',
        'ecommpay_skrill' => 'skrill',
        'ecommpay_bancontact' => 'bancontact',
        'ecommpay_multibanco' => 'multibanco',
        'ecommpay_more_methods' => 'more_methods'
    ];

    protected OrderRepositoryInterface $orderRepository;
    protected RequestBuilder $requestBuilder;
    protected RequestInterface $request;
    protected Session $checkoutSession;
    protected ClientInterface $client;
    protected LoggerInterface $logger;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        RequestBuilder $requestBuilder,
        RequestInterface $request,
        Session $checkoutSession,
        ClientInterface $client,
        CartManagementInterface $cartManagement,
        LoggerInterface $logger,
        AbstractOrderData $orderData = null
    ) {
        $this->orderRepository = $orderRepository;
        $this->requestBuilder = $requestBuilder;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->client = $client;
        $this->logger = $logger;
        parent::__construct($cartManagement, $orderData);
    }

    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        $params = $this->getPaymentPageParams($orderId);
        $url = $params['paymentPageUrl'];
        unset($params['paymentPageUrl']);

        $url .= '?' . http_build_query($params);

        try {
            $response = $this->client->get($url);
            if ($response->getStatusCode() === 200) {
                return $url;
            } else {
                $this->checkoutSession->restoreQuote();
                $this->logger->error(
                    'Error occurred while placing an order with Ecommpay payment method',
                    [
                        'responseCode' => $response->getStatusCode(),
                        'responseBody' => $response->getBody()->getContents(),
                    ]
                );
                throw new LocalizedException(__('There was an error processing the payment. Please try again. If the problem persists, please contact us.'));
            }
        } catch (\Exception $e) {
            $this->checkoutSession->restoreQuote();
            $this->logger->error(
                'Error occurred while placing an order with Ecommpay payment method',
                [
                    'exception' => $e,
                ]
            );
            throw new LocalizedException(__('There was an error processing the payment. Please try again. If the problem persists, please contact us.'));
        }
    }

    protected function getPaymentPageParams(int $orderId): array
    {
        try {
            $order = $this->orderRepository->get($orderId);
            $this->request->setParam('method', $this->getMappedPaymentMethodName($order->getPayment()->getMethod()));
            return $this->requestBuilder->getPaymentPageParams($order);
        } catch (NoSuchEntityException $e) {
            $this->checkoutSession->restoreQuote();
            $this->logger->error(
                'Error occurred while placing an order with Ecommpay payment method',
                [
                    'exception' => $e,
                ]
            );
            throw new LocalizedException(__('There was an error processing the payment. Please try again. If the problem persists, please contact us.'));
        }
    }

    protected function getMappedPaymentMethodName(string $magentoMethodName): string
    {
        return self::MAGENTO_METHOD_NAME_TO_PAYMENT_PAGE_METHOD_NAME_MAPPING[$magentoMethodName] ?? $magentoMethodName;
    }
}