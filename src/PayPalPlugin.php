<?php

namespace Uvodo\Paypal;

use Framework\Contracts\Container\ContainerInterface;
use Modules\Option\Domain\Exceptions\OptionAlreadyExistsException;
use Modules\Plugin\Domain\Context;
use Modules\Plugin\Domain\Hooks\ActivateHookInterface;
use Modules\Plugin\Domain\PluginInterface;
use Modules\Plugin\Infrastructure\Helpers\OptionHelper;
use Support\PaymentGateway\PaymentGatewayFactory;

/** @package Uvodo\PayPal */
class PayPalPlugin implements PluginInterface, ActivateHookInterface
{
    private PaymentGatewayFactory $gatewayFactory;
    private ContainerInterface $container;
    private RoutingBootstrapper $rb;
    private OptionHelper $optionHelper;

    /**
     * @param ContainerInterface $container
     * @param PaymentGatewayFactory $gatewayFactory
     * @param RoutingBootstrapper $rb
     * @param OptionHelper $optionHelper
     */
    public function __construct(
        ContainerInterface $container,
        PaymentGatewayFactory $gatewayFactory,
        RoutingBootstrapper $rb,
        OptionHelper $optionHelper
    ) {
        $this->container = $container;
        $this->gatewayFactory = $gatewayFactory;
        $this->rb = $rb;
        $this->optionHelper = $optionHelper;
    }

    public function boot(Context $context): void
    {
        $clientId = new ClientId(
            $this->optionHelper
                ->getOption($context, 'PAYPAL_CLIENT_ID')
                ->getValue()
                ->getValue()
        );

        $appSecret = new AppSecret(
            $this->optionHelper
                ->getOption($context, 'PAYPAL_APP_SECRET')
                ->getValue()->getValue()
        );

        $this->container
            ->set(ClientId::class, $clientId)
            ->set(AppSecret::class, $appSecret);

        $this->gatewayFactory
            ->registerPaymentGateway(
                PayPalPaymentGateway::SHORT_NAME,
                PayPalPaymentGateway::class,
                $context
            );

        $this->rb->boot($context);
    }

    /**
     * @throws OptionAlreadyExistsException
     */
    public function activate(Context $context): void
    {
        $this->optionHelper->createOrUpdateOption(
            $context,
            'PAYPAL_CLIENT_ID',
            'PAYPAL_CLIENT_ID'
        );

        $this->optionHelper->createOrUpdateOption(
            $context,
            'PAYPAL_APP_SECRET',
            'PAYPAL_APP_SECRET');
    }
}
