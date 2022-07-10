<?php

namespace Uvodo\Paypal;

/** @package Uvodo\Paypal */
class AppSecret
{
    private string $value;

    public function __construct(string $appSecret)
    {
        $this->value = $appSecret;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
