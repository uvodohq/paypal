<?php

namespace Uvodo\Paypal\Presentation\RequestHandlers;

use Presentation\Shared\Response\HtmlResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SettingsRequestHandler
{
    private HtmlResponseFactory $responseFactory;

    public function __construct(
        HtmlResponseFactory $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(): ResponseInterface
    {
        return $this->responseFactory->createResponse(
            __DIR__ . '/../../../assets/index.html'
        );
    }
}
