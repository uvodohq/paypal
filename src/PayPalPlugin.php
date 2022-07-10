<?php

namespace Uvodo\Paypal;

use Framework\Contracts\Container\ContainerInterface;
use Modules\Plugin\Domain\Context;
use Modules\Plugin\Domain\PluginInterface;
use Support\PaymentGateway\PaymentGatewayFactory;

/** @package Uvodo\PayPal */
class PayPalPlugin implements PluginInterface
{
    private PaymentGatewayFactory $paymentGatewayFactory;
    private ContainerInterface $container;
    private RoutingBootstrapper $rb;

    /**
     * @param ContainerInterface $container
     * @param PaymentGatewayFactory $gatewayFactory
     * @param RoutingBootstrapper $rb
     */
    public function __construct(
        ContainerInterface $container,
        PaymentGatewayFactory $gatewayFactory,
        RoutingBootstrapper $rb
    ) {
        $this->container = $container;
        $this->paymentGatewayFactory = $gatewayFactory;
        $this->rb = $rb;
    }

    public function boot(Context $context)
    {
        $clientId = new ClientId(env('PAYPAL_CLIENT_ID'));
        $appSecret = new AppSecret(env('PAYPAL_APP_SECRET'));

        $this->container
            ->set(ClientId::class, $clientId)
            ->set(AppSecret::class, $appSecret);

        $this->paymentGatewayFactory
            ->registerPaymentGateway(
                PayPalPaymentGateway::SHORT_NAME,
                PayPalPaymentGateway::class
            );

        $this->rb->boot($context);
    }
}
