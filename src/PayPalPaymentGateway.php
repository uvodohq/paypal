<?php

namespace Uvodo\Paypal;

use Framework\Http\StatusCodes;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Support\PaymentGateway\Exceptions\InvalidRequestException;
use Support\PaymentGateway\Exceptions\PaymentFailedExceptions;
use Support\PaymentGateway\PaymentGatewayInterface;
use Support\PaymentGateway\PaymentRequest;
use Support\PaymentGateway\PaymentResponseInterface;
use Support\PaymentGateway\SuccessResponse;
use Support\PaymentGateway\ValueObjects\OrderId;

/** @package Plugins\Paypal */
class PayPalPaymentGateway implements PaymentGatewayInterface
{
    public const SHORT_NAME = "paypal";
    private ClientInterface $httpClient;
    private RequestFactoryInterface $httpRequestFactory;
    private UriFactoryInterface $uriFactory;
    private StreamFactoryInterface $streamFactory;
    private string $host;
    private string $scheme = "https";
    private string $authVersion = 'v1';
    private string $apiVersion = 'v2';
    private ClientId $clientId;
    private AppSecret $appSecret;

    /**
     * @param ClientInterface $httpClient
     * @param RequestFactoryInterface $httpRequestFactory
     * @param UriFactoryInterface $uriFactory
     * @param StreamFactoryInterface $streamFactory
     * @param ClientId $clientId
     * @param AppSecret $appSecret
     */
    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $httpRequestFactory,
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        ClientId $clientId,
        AppSecret $appSecret
    ) {
        $this->httpClient = $httpClient;
        $this->httpRequestFactory = $httpRequestFactory;
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
        $this->clientId = $clientId;
        $this->appSecret = $appSecret;

        $this->host = env('PAYPAL_SANDBOX_MODE') ? 'api-m.sandbox.paypal.com' : 'api-m.paypal.com';
    }

    public function getShortName(): string
    {
        return self::SHORT_NAME;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidRequestException
     * @throws PaymentFailedExceptions
     */
    public function pay(PaymentRequest $request): PaymentResponseInterface
    {
        $res = $this->getOrder($request->paypal->orderId);
        $json = json_decode($res->getBody()->getContents());

        if ($json->intent !== "CAPTURE" || $json->status !== "COMPLETED") {
            throw new PaymentFailedExceptions();
        }

        $authorization = $json->id;

        $payResp = new SuccessResponse();
        $payResp->setAuthorization($authorization);
        return $payResp;
    }

    /**
     * @return mixed
     * @throws ClientExceptionInterface
     */
    private function createToken(): mixed
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
        return json_decode($resp->getBody()->getContents())->access_token;
    }

    /**
     * @param OrderId $orderId
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws InvalidRequestException
     */
    private function getOrder(
        OrderId $orderId
    ): ResponseInterface {
        $token = $this->createToken();

        $uri = $this->uriFactory->createUri('/' . $this->apiVersion . '/checkout/orders/' . $orderId->getValue())
            ->withHost($this->host)
            ->withScheme($this->scheme);

        $req = $this->httpRequestFactory->createRequest('GET', $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader(
                'Authorization',
                'Bearer ' . $token
            );

        $res = $this->httpClient->sendRequest($req);
        $this->validateResponse($res);
        return $res;
    }

    /**
     * @param ResponseInterface $res
     * @return void
     * @throws InvalidRequestException
     */
    private function validateResponse(ResponseInterface $res): void
    {
        if ($res->getStatusCode() !== StatusCodes::HTTP_OK) {
            throw new InvalidRequestException('', $res->getStatusCode());
        }
    }
}
