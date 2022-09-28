<?php

namespace Uvodo\Paypal\Presentation\RequestHandlers;

use Modules\Option\Domain\Exceptions\OptionAlreadyExistsException;
use Modules\Plugin\Infrastructure\Helpers\OptionHelper;
use Presentation\Shared\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Services\HttpService;
use Uvodo\Paypal\PayPalContext;

class UpdateKeysRequestHandler
{
    public function __construct(
        private HttpService $httpService,
        private OptionHelper $optionHelper,
        private PayPalContext $context
    ) {
    }

    /**
     * @param ServerRequestInterface $req
     * @return ResponseInterface
     * @throws OptionAlreadyExistsException
     */
    public function __invoke(ServerRequestInterface $req): ResponseInterface
    {
        $input = $this->httpService->getInput($req);
        $payPalClientId = $input->get('paypal_client_id');
        $payPalSecretKey = $input->get('paypal_app_secret');
        $payPalSandboxMode = $input->get('paypal_sandbox_mode') ? "1" : "0";

        $this->optionHelper->createOrUpdateOption(
            $this->context::$context,
            'PAYPAL_CLIENT_ID',
            $payPalClientId
        );

        $this->optionHelper->createOrUpdateOption(
            $this->context::$context,
            'PAYPAL_APP_SECRET',
            $payPalSecretKey
        );

        $this->optionHelper->createOrUpdateOption(
            $this->context::$context,
            'PAYPAL_SANDBOX_MODE',
            $payPalSandboxMode
        );

        return new JsonResponse([
            'paypal_client_id' => $payPalClientId,
            'paypal_app_secret' => $payPalSecretKey,
            'paypal_sandbox_mode' => $payPalSandboxMode
        ]);
    }
}
