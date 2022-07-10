<?php

namespace Uvodo\Paypal\Presentation\RequestHandlers;

use Framework\Contracts\Core\ApplicationInterface;
use Modules\Option\Domain\Exceptions\OptionAlreadyExistsException;
use Modules\Option\Domain\Exceptions\OptionNotFoundException;
use Modules\Plugin\Domain\Context;
use Modules\Plugin\Infrastructure\Helpers\OptionHelper;
use Presentation\Shared\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Services\HttpService;
use Uvodo\Menv\Env;
use Uvodo\Menv\Exceptions\Exception;
use Uvodo\Paypal\PluginInfoFacade;

class UpdateKeysRequestHandler
{
    private ApplicationInterface $app;
    private HttpService $httpService;
    private OptionHelper $optionHelper;
    private Context $context;

    public function __construct(
        ApplicationInterface $app,
        HttpService $httpService,
        OptionHelper $optionHelper,
        Context $context
    ) {
        $this->app = $app;
        $this->httpService = $httpService;
        $this->optionHelper = $optionHelper;
        $this->context = $context;
    }

    /**
     * @param ServerRequestInterface $req
     * @return ResponseInterface
     * @throws Exception|OptionNotFoundException
     */
    public function __invoke(ServerRequestInterface $req): ResponseInterface
    {

        $input = $this->httpService->getInput($req);
        $payPalClientId = $input->get('client_id');
        $payPalSecretKey = $input->get('app_secret');
        $payPalSandboxMode = $input->has('sandbox_mode') ? 1 : 0;

        $mEnv = new Env($this->app->getBasePath() . '/.env');
        $mEnv->set('PAYPAL_CLIENT_ID', $payPalClientId)
            ->set('PAYPAL_APP_SECRET', $payPalSecretKey)
            ->set('PAYPAL_SANDBOX_MODE', $payPalSandboxMode)
            ->save();

        $status = $input->has('status') ? 1 : 0;

        if (!$this->optionHelper->getOption($this->context, 'paypal_status')) {
            $this->optionHelper->addOption($this->context, 'paypal_status', 0);
        }
        $this->optionHelper->updateOption($this->context, 'paypal_status', $status);

        return new JsonResponse([
            'PAYPAL_CLIENT_ID' => $payPalClientId,
            'PAYPAL_APP_SECRET' => $payPalSecretKey
        ]);
    }
}
