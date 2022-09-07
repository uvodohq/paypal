<?php

namespace Uvodo\Paypal;

use Framework\Contracts\Container\ContainerInterface;
use Modules\Option\Domain\Exceptions\OptionAlreadyExistsException;
use Modules\Option\Domain\Exceptions\OptionNotFoundException;
use Modules\Plugin\Domain\Context;
use Modules\Plugin\Domain\Hooks\ActivateHookInterface;
use Modules\Plugin\Domain\Hooks\DeactivateHookInterface;
use Modules\Plugin\Domain\Hooks\InstallHookInterface;
use Modules\Plugin\Domain\Hooks\UninstallHookInterface;
use Modules\Plugin\Domain\PluginInterface;
use Modules\Plugin\Infrastructure\Helpers\OptionHelper;
use Modules\Setting\Domain\Exceptions\PaymentGatewayNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Support\PaymentGateway\PaymentGatewayFactory;

/** @package Uvodo\PayPal */
class PayPalPlugin implements PluginInterface, InstallHookInterface, UninstallHookInterface, ActivateHookInterface, DeactivateHookInterface
{
    /**
     * @param ContainerInterface $container
     * @param PaymentGatewayFactory $gatewayFactory
     * @param RoutingBootstrapper $rb
     * @param OptionHelper $optionHelper
     */
    public function __construct(
        private ContainerInterface $container,
        private PaymentGatewayFactory $gatewayFactory,
        private RoutingBootstrapper $rb,
        private OptionHelper $optionHelper
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws PaymentGatewayNotFoundException
     */
    public function boot(Context $context): void
    {
        $this->container
            ->set(
                ClientId::class,
                new ClientId(
                    $this->optionHelper
                        ->getOptionValue(
                            $context,
                            'PAYPAL_CLIENT_ID'
                        )
                )
            )->set(
                AppSecret::class,
                new AppSecret(
                    $this->optionHelper
                        ->getOptionValue(
                            $context,
                            'PAYPAL_APP_SECRET'
                        )
                )
            );

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
    public function install(Context $context): void
    {
        $this->addOptions($context);
    }

    /**
     * @throws OptionNotFoundException
     */
    public function uninstall(Context $context): void
    {
        $this->removeOptions($context);
    }

    /**
     * @throws OptionAlreadyExistsException
     */
    public function activate(Context $context): void
    {
        $this->addOptions($context);
    }

    /**
     * @throws OptionNotFoundException
     */
    public function deactivate(Context $context): void
    {
        $this->removeOptions($context);
    }

    /**
     * @throws OptionAlreadyExistsException
     */
    private function addOptions(Context $context)
    {
        $this->optionHelper->createOrUpdateOption(
            $context,
            'PAYPAL_CLIENT_ID',
            'PAYPAL_CLIENT_ID'
        );

        $this->optionHelper->createOrUpdateOption(
            $context,
            'PAYPAL_APP_SECRET',
            'PAYPAL_APP_SECRET'
        );
    }

    /**
     * @throws OptionNotFoundException
     */
    private function removeOptions(Context $context)
    {
        $this->optionHelper->deleteOption(
            $context,
            'PAYPAL_CLIENT_ID'
        );

        $this->optionHelper->deleteOption(
            $context,
            'PAYPAL_APP_SECRET'
        );
    }
}
