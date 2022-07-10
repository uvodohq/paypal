<?php

namespace Uvodo\Paypal\Presentation\Storefront\RequestHandlers;

use Presentation\Shared\Response\JsonResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Uvodo\Paypal\Presentation\AbstractPaypalRestApiRequestHandler;

class CaptureOrderRequestHandler extends AbstractPaypalRestApiRequestHandler
{
    /**
     * @throws ClientExceptionInterface
     */
    public function __invoke(
        ServerRequestInterface $req,
        string $orderId
    ): ResponseInterface
    {
        $accessToken = $this->createToken();

        $uri = $this->uriFactory->createUri('/' . $this->apiVersion . '/checkout/orders/' . $orderId . '/capture')
            ->withHost($this->host)
            ->withScheme($this->scheme);

        $request = $this->httpRequestFactory->createRequest('POST',$uri)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader(
                'Authorization',
                'Bearer ' . $accessToken
            );

        $resp = $this->httpClient->sendRequest($request);
        $json = json_decode($resp->getBody()->getContents());

        return new JsonResponse($json);
    }
}
