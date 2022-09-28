<?php

namespace Uvodo\Paypal\Presentation\Storefront\RequestHandlers;

use Presentation\Shared\Response\JsonResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Uvodo\Paypal\Presentation\AbstractPaypalRestApiRequestHandler;

class CreatePaymentRequestHandler extends AbstractPaypalRestApiRequestHandler
{
    /**
     * @throws ClientExceptionInterface
     */
    public function __invoke(ServerRequestInterface $req): ResponseInterface
    {
        $input = $this->httpService->getInput($req);

        $accessToken = $this->createToken();

        $uri = $this->uriFactory->createUri('/' . $this->authVersion . '/payments/payment')
            ->withHost($this->host)
            ->withScheme($this->scheme);

        $body = $this->streamFactory->createStream(json_encode([
            "intent" => "sale",
            "payer" => [
                "payment_method" => "paypal"
            ],
            "transactions" => [
                [
                    "amount" => [
                        "total" => $input->get('total_price'),
                        "currency" => $input->get('currency')
                    ]
                ]
            ],
            "redirect_urls" => [
                "return_url" => 'https://decode.az',
                "cancel_url" => 'https://aspen.az'
            ]
        ]));

        $req = $this->httpRequestFactory->createRequest('POST', $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader(
                'Authorization',
                'Bearer ' . $accessToken
            )->withBody($body);

        $resp = $this->httpClient->sendRequest($req);
        //echo $resp->getBody()->getContents();
        $body = json_decode($resp->getBody()->getContents());

        return new JsonResponse($body);
    }
}
