<?php

namespace Uvodo\Paypal\Presentation\RequestHandlers;

use Modules\Plugin\Domain\Context;
use Modules\Plugin\Infrastructure\Helpers\OptionHelper;
use Presentation\Shared\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;

class ReadKeysRequestHandler
{
    private Context $context;
    private OptionHelper $optionHelper;
    public function __construct(
        Context $context,
        OptionHelper $optionHelper
    ){
        $this->context = $context;
        $this->optionHelper = $optionHelper;
    }

    /**
     * @return ResponseInterface
     */
    public function __invoke(): ResponseInterface
    {
        $status = $this->optionHelper->getOption($this->context, 'paypal_status');
        return new JsonResponse([
            'paypal_client_id' => env('PAYPAL_CLIENT_ID'),
            'paypal_app_secret' => env('PAYPAL_APP_SECRET'),
            'paypal_sandbox_mode' => env('PAYPAL_SANDBOX_MODE'),
            'status' => !is_null($status) ? $status->getValue()->getValue() : $status
        ]);
    }
}
