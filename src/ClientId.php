<?php

namespace Uvodo\Paypal;

/** @package Uvodo\Paypal */
class ClientId
{
    private string $value;

    public function __construct(string $clientId)
    {
        $this->value = $clientId;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
