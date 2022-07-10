<?php

namespace Uvodo\Paypal\Presentation\Storefront\RequestHandlers;

use Presentation\Shared\Response\JsonResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Uvodo\Paypal\Presentation\AbstractPaypalRestApiRequestHandler;

class CreateOrderRequestHandler extends AbstractPaypalRestApiRequestHandler
{
    /**
     * @throws ClientExceptionInterface
     */
    public function __invoke(ServerRequestInterface $req): ResponseInterface
    {
        $accessToken = $this->createToken();

        $body = $this->streamFactory->createStream(json_encode([
            'intent' => 'CAPTURE',
            'purchase_units'  => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => "35.60"
                    ]
                ]
            ]
        ]));

        $uri = $this->uriFactory->createUri('/' . $this->apiVersion . '/checkout/orders')
            ->withHost($this->host)
            ->withScheme($this->scheme);

        $request = $this->httpRequestFactory->createRequest('POST',$uri)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader(
                'Authorization',
                'Bearer ' . $accessToken
            )->withBody($body);

        $resp = $this->httpClient->sendRequest($request);
        $json = json_decode($resp->getBody()->getContents());

        return new JsonResponse($json);
    }
}
