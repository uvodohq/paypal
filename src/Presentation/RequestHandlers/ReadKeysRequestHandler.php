<?php

namespace Uvodo\Paypal\Presentation\RequestHandlers;

use Modules\Plugin\Infrastructure\Helpers\OptionHelper;
use Presentation\Shared\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Uvodo\Paypal\PayPalContext;

class ReadKeysRequestHandler
{
    public function __construct(
        private PayPalContext $context,
        private OptionHelper $optionHelper
    ){
    }

    /**
     * @return ResponseInterface
     */
    public function __invoke(): ResponseInterface
    {
        $ctx = $this->context;
        return new JsonResponse([
            'paypal_client_id' => $this->optionHelper->getOptionValue($ctx::$context, 'PAYPAL_CLIENT_ID'),
            'paypal_app_secret' => $this->optionHelper->getOptionValue($ctx::$context, 'PAYPAL_APP_SECRET'),
            'paypal_sandbox_mode' => $this->optionHelper->getOptionValue($ctx::$context, 'PAYPAL_SANDBOX_MODE') == '1'
        ]);
    }
}
