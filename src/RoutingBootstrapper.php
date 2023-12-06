<?php

namespace Uvodo\Paypal;

use Framework\Routing\Route;
use Modules\Plugin\Domain\Context;
use Modules\Plugin\Infrastructure\Helpers\RoutingHelper;
use Presentation\Shared\RouteClass;
use Uvodo\Paypal\Presentation\RequestHandlers\ReadKeysRequestHandler;
use Uvodo\Paypal\Presentation\RequestHandlers\SettingsRequestHandler;
use Uvodo\Paypal\Presentation\RequestHandlers\TestRequestHandler;
use Uvodo\Paypal\Presentation\RequestHandlers\UpdateKeysRequestHandler;
use Uvodo\Paypal\Presentation\Storefront\RequestHandlers\CaptureOrderRequestHandler;
use Uvodo\Paypal\Presentation\Storefront\RequestHandlers\CreateOrderRequestHandler;
use Uvodo\Paypal\Presentation\Storefront\RequestHandlers\CreatePaymentRequestHandler;

/** @package Uvodo\PayPal */
class RoutingBootstrapper
{
    private RoutingHelper $helper;

    public function __construct(RoutingHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Context $context
     * @return void
     */
    public function boot(Context $context): void
    {
        /*$this->helper->addRoute(
            $context,
            RouteClass::PLUGINS_ADMIN_UI(),
            new Route('GET', '/test', TestRequestHandler::class)
        );*/

        $this->helper->addRoute(
            $context,
            RouteClass::PLUGINS_API(),
            new Route('POST', '/orders', CreatePaymentRequestHandler::class, 'paypal.payment')
        );

        $this->helper->addRoute(
            $context,
            RouteClass::PLUGINS_API(),
            new Route('POST', '/orders', CreateOrderRequestHandler::class)
        );

        $this->helper->addRoute(
            $context,
            RouteClass::PLUGINS_API(),
            new Route('POST', '/orders/[*:orderId]/capture', CaptureOrderRequestHandler::class)
        );

        $this->helper->addRoute(
            $context,
            RouteClass::PLUGINS_ADMIN_UI(),
            new Route('GET', '/settings/', SettingsRequestHandler::class)
        );

        $this->helper->addRoute(
            $context,
            RouteClass::PLUGINS_ADMIN_API(),
            new Route('GET', '/settings', ReadKeysRequestHandler::class)
        );

        $this->helper->addRoute(
            $context,
            RouteClass::PLUGINS_ADMIN_API(),
            new Route('POST', '/settings', UpdateKeysRequestHandler::class)
        );
    }
}
