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
use Support\PaymentGateway\OtherPaymentGatewayInterface;
use Support\PaymentGateway\PaymentConfirmationRequest;
use Support\PaymentGateway\PaymentRequest;
use Support\PaymentGateway\PaymentResponseInterface;
use Support\PaymentGateway\RedirectResponse;
use Support\PaymentGateway\SuccessResponse;

/** @package Plugins\Paypal */
class PayPalPaymentGateway implements OtherPaymentGatewayInterface
{
    public const SHORT_NAME = "paypal";
    private string $host;
    private string $scheme = "https";
    private string $version = 'v1';

    /**
     * @param ClientInterface $httpClient
     * @param RequestFactoryInterface $httpRequestFactory
     * @param UriFactoryInterface $uriFactory
     * @param StreamFactoryInterface $streamFactory
     * @param ClientId $clientId
     * @param AppSecret $appSecret
     * @param SandboxMode $sandboxMode
     */
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $httpRequestFactory,
        private UriFactoryInterface $uriFactory,
        private StreamFactoryInterface $streamFactory,
        private ClientId $clientId,
        private AppSecret $appSecret,
        private SandboxMode $sandboxMode
    ) {
        $this->host = $this->sandboxMode->equals(SandboxMode::ACTIVE()) ? 'api-m.sandbox.paypal.com' : 'api-m.paypal.com';
    }

    public function getShortName(): string
    {
        return self::SHORT_NAME;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function pay(PaymentRequest $request): PaymentResponseInterface
    {
        $payment = $this->payment($request);

        $payResp = new RedirectResponse();
        $redirectUrl = $payment->links ? $payment->links[1]->href : null;
        $payResp->setAuthorization($payment->id);
        $payResp->setRedirectUrl($redirectUrl);
        return $payResp;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function confirm(PaymentConfirmationRequest $request): PaymentResponseInterface
    {
        $queryParams = https_parse_query($request->queryString);

        $execute = $this->execute(
            $queryParams['paymentId'],
            $queryParams['PayerID']
        );

        $res = new SuccessResponse();
        $res->setAuthorization($execute->id);
        return $res;
    }

    /**
     * @return mixed
     * @throws ClientExceptionInterface
     */
    private function createToken(): mixed
    {
        $uri = $this->uriFactory->createUri('/' . $this->version . '/oauth2/token')
            ->withHost($this->host)
            ->withScheme($this->scheme);

        $body = $this->streamFactory->createStream(http_build_query([
            'grant_type' => 'client_credentials'
        ]));

        $req = $this->httpRequestFactory->createRequest('POST', $uri)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader(
                'Authorization',
                'Basic ' . base64_encode($this->clientId->getValue() . ':' . $this->appSecret->getValue())
            )->withBody($body);

        $resp = $this->httpClient->sendRequest($req);
        return json_decode($resp->getBody()->getContents())->access_token;
    }


    /**
     * @throws ClientExceptionInterface
     */
    private function payment(
        PaymentRequest $request
    ) {
        $token = $this->createToken();

        $redirectUrl = 'http://uvodo.test/checkout/' . $request->cardToken->getValue() . '?';

        $returnQuery = http_build_query(
            [
                'gateway' => self::SHORT_NAME,
                'redirect_type' => 'return'
            ]
        );

        $cancelQuery = http_build_query(
            [
                'gateway' => self::SHORT_NAME,
                'redirect_type' => 'cancel'
            ]
        );


        $uri = $this->uriFactory->createUri('/' . $this->version . '/payments/payment')
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
                        "total" => $request->amount->getValue(),
                        "currency" => $request->currencyCode->getValue()
                    ]
                ]
            ],
            "redirect_urls" => [
                "return_url" => $redirectUrl . $returnQuery,
                "cancel_url" => $redirectUrl . $cancelQuery,
            ]
        ]));

        $req = $this->httpRequestFactory->createRequest('POST', $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader(
                'Authorization',
                'Bearer ' . $token
            )->withBody($body);

        $resp = $this->httpClient->sendRequest($req);
        return json_decode($resp->getBody()->getContents());
    }


    /**
     * @throws ClientExceptionInterface
     */
    public function execute(
        string $paymentId,
        string $payerId
    ) {
        ///v1/payments/payment/{payment_id}/execute
        $token = $this->createToken();

        $uri = $this->uriFactory->createUri('/' . $this->version . '/payments/payment/' . $paymentId . '/execute')
            ->withHost($this->host)
            ->withScheme($this->scheme);

        $body = $this->streamFactory->createStream(json_encode([
            "payer_id" => $payerId,
        ]));

        $req = $this->httpRequestFactory->createRequest('POST', $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader(
                'Authorization',
                'Bearer ' . $token
            )->withBody($body);

        $resp = $this->httpClient->sendRequest($req);
        return json_decode($resp->getBody()->getContents());
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
