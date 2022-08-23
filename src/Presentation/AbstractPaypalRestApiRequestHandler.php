<?php

namespace Uvodo\Paypal\Presentation;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Services\HttpService;
use Uvodo\Paypal\AppSecret;
use Uvodo\Paypal\ClientId;

/** @package Uvodo\Paypal\Presentation\RequestHandlers */
abstract class AbstractPaypalRestApiRequestHandler
{
    protected string $host = 'api-m.sandbox.paypal.com';
    protected string $scheme = "https";
    protected string $authVersion = 'v1';
    protected string $apiVersion = 'v2';

    protected HttpService $httpService;
    protected ClientInterface $httpClient;
    protected RequestFactoryInterface $httpRequestFactory;
    protected UriFactoryInterface $uriFactory;
    protected StreamFactoryInterface $streamFactory;
    protected ClientId $clientId;
    protected AppSecret $appSecret;

    public function __construct(
        HttpService $httpService,
        ClientInterface $httpClient,
        RequestFactoryInterface $httpRequestFactory,
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        ClientId $clientId,
        AppSecret $appSecret
    ) {
        $this->httpService = $httpService;
        $this->httpClient = $httpClient;
        $this->httpRequestFactory = $httpRequestFactory;
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
        $this->clientId = $clientId;
        $this->appSecret = $appSecret;
    }

    /**
     * @return mixed
     * @throws ClientExceptionInterface
     */
    protected function createToken(): mixed
    {
        $uri = $this->uriFactory->createUri('/' . $this->authVersion . '/oauth2/token')
            ->withHost($this->host)
            ->withScheme($this->scheme);

        $body = $this->streamFactory->createStream(http_build_query([
            'grant_type' => 'client_credentials'
        ]));

        $req = $this->httpRequestFactory->createRequest('POST',$uri)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Authorization',
                'Basic ' . base64_encode($this->clientId->getValue() . ':' . $this->appSecret->getValue())
            )->withBody($body);

        $resp = $this->httpClient->sendRequest($req);
        $json = json_decode($resp->getBody()->getContents());
        return $json->access_token;
    }
}
